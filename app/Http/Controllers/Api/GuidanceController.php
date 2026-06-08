<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GuidanceRequestResource;
use App\Models\GuidanceComment;
use App\Models\GuidanceDocument;
use App\Models\GuidanceRequest;
use App\Services\NotificationService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GuidanceController extends Controller
{
    use ApiResponse;

    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = GuidanceRequest::with(['mahasiswa', 'dosen', 'documents', 'comments.user']);

        if ($user->role === 'mahasiswa') {
            $query->where('mahasiswa_id', $user->id);
        } elseif ($user->role === 'dosen_pembimbing') {
            $query->where('dosen_id', $user->id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->proposal_id) {
            $query->where('research_proposal_id', $request->proposal_id);
        }

        $requests = $query->latest()->paginate($request->per_page ?? 15);

        return $this->success(GuidanceRequestResource::collection($requests));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'research_proposal_id' => 'required|exists:research_proposals,id',
            'dosen_id' => 'required|exists:users,id',
            'judul' => 'required|string|max:500',
            'deskripsi' => 'required|string',
        ]);

        $guidanceRequest = GuidanceRequest::create([
            'research_proposal_id' => $request->research_proposal_id,
            'mahasiswa_id' => $request->user()->id,
            'dosen_id' => $request->dosen_id,
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'status' => 'diajukan',
        ]);

        $this->notificationService->send(
            $guidanceRequest->dosen,
            'bimbingan_baru',
            'Permohonan Bimbingan Baru',
            "Mahasiswa {$request->user()->name} mengajukan bimbingan: {$guidanceRequest->judul}",
            $request->user(),
            ['guidance_id' => $guidanceRequest->id]
        );

        return $this->created(new GuidanceRequestResource($guidanceRequest), 'Permohonan bimbingan berhasil dikirim');
    }

    public function show(GuidanceRequest $guidanceRequest): JsonResponse
    {
        $guidanceRequest->load(['mahasiswa', 'dosen', 'documents.uploader', 'comments.user']);
        return $this->success(new GuidanceRequestResource($guidanceRequest));
    }

    public function uploadDocument(Request $request, GuidanceRequest $guidanceRequest): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx,zip|max:20480',
        ]);

        $file = $request->file('file');
        $latestVersion = $guidanceRequest->documents()->max('version') ?? 0;

        $guidanceRequest->documents()->create([
            'uploader_id' => $request->user()->id,
            'file_path' => $file->store('guidance-documents', 'public'),
            'original_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'file_type' => $file->getMimeType(),
            'version' => $latestVersion + 1,
        ]);

        $guidanceRequest->load('documents');

        $recipientId = $request->user()->id === $guidanceRequest->mahasiswa_id
            ? $guidanceRequest->dosen_id
            : $guidanceRequest->mahasiswa_id;

        $this->notificationService->send(
            \App\Models\User::find($recipientId),
            'dokumen_diupload',
            'Dokumen Baru',
            "Dokumen baru diunggah pada bimbingan: {$guidanceRequest->judul}",
            $request->user(),
            ['guidance_id' => $guidanceRequest->id]
        );

        return $this->success(new GuidanceRequestResource($guidanceRequest), 'Dokumen berhasil diunggah');
    }

    public function addComment(Request $request, GuidanceRequest $guidanceRequest): JsonResponse
    {
        $request->validate([
            'konten' => 'required|string',
            'is_revision' => 'boolean',
        ]);

        $comment = $guidanceRequest->comments()->create([
            'user_id' => $request->user()->id,
            'konten' => $request->konten,
            'is_revision' => $request->is_revision ?? false,
        ]);

        $guidanceRequest->update(['status' => 'direspon']);

        $guidanceRequest->load('comments.user');

        $recipientId = $request->user()->id === $guidanceRequest->mahasiswa_id
            ? $guidanceRequest->dosen_id
            : $guidanceRequest->mahasiswa_id;

        $notificationType = $request->is_revision ? 'revisi' : 'komentar';
        $this->notificationService->send(
            \App\Models\User::find($recipientId),
            $notificationType,
            $request->is_revision ? 'Catatan Revisi' : 'Komentar Baru',
            $request->user()->role === 'dosen_pembimbing'
                ? "Dosen memberikan {$notificationType}: {$request->konten}"
                : "Mahasiswa memberikan {$notificationType}: {$request->konten}",
            $request->user(),
            ['guidance_id' => $guidanceRequest->id, 'comment_id' => $comment->id]
        );

        return $this->created(new GuidanceRequestResource($guidanceRequest), 'Komentar berhasil ditambahkan');
    }

    public function approveDocument(Request $request, GuidanceRequest $guidanceRequest): JsonResponse
    {
        $guidanceRequest->update(['status' => 'selesai']);

        $this->notificationService->send(
            $guidanceRequest->mahasiswa,
            'dokumen_disetujui',
            'Dokumen Disetujui',
            "Dokumen bimbingan {$guidanceRequest->judul} telah disetujui",
            $request->user(),
            ['guidance_id' => $guidanceRequest->id]
        );

        return $this->success(new GuidanceRequestResource($guidanceRequest->fresh()), 'Dokumen berhasil disetujui');
    }
}
