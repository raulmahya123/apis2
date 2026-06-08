<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ResearchProgressResource;
use App\Models\ResearchProgress;
use App\Services\NotificationService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProgressController extends Controller
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

        $query = ResearchProgress::with(['documents', 'proposal']);

        if ($user->role === 'mahasiswa') {
            $query->where('mahasiswa_id', $user->id);
        } elseif ($user->role === 'dosen_pembimbing') {
            $query->whereHas('proposal.approvedSupervisors', fn($q) => $q->where('dosen_id', $user->id));
        }

        if ($request->proposal_id) {
            $query->where('research_proposal_id', $request->proposal_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $progresses = $query->latest()->paginate($request->per_page ?? 15);

        return $this->success(ResearchProgressResource::collection($progresses));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'research_proposal_id' => 'required|exists:research_proposals,id',
            'judul_progres' => 'required|string|max:500',
            'deskripsi' => 'required|string',
            'periode' => 'nullable|string|max:50',
            'documents' => 'nullable|array',
            'documents.*' => 'file|max:20480',
        ]);

        $user = $request->user();

        $progress = ResearchProgress::create([
            'research_proposal_id' => $request->research_proposal_id,
            'mahasiswa_id' => $user->id,
            'judul_progres' => $request->judul_progres,
            'deskripsi' => $request->deskripsi,
            'status' => 'on_track',
            'periode' => $request->periode,
        ]);

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                $progress->documents()->create([
                    'file_path' => $file->store('progress-documents', 'public'),
                    'original_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'file_type' => $file->getMimeType(),
                ]);
            }
        }

        $progress->load('documents');

        return $this->created(new ResearchProgressResource($progress), 'Progress berhasil ditambahkan');
    }

    public function reviewProgress(Request $request, ResearchProgress $progress): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:approved,revisi,on_track,tertinggal',
            'catatan' => 'nullable|string',
        ]);

        $progress->update([
            'status' => $request->status,
        ]);

        $statusLabels = [
            'approved' => 'Disetujui',
            'revisi' => 'Perlu Revisi',
            'on_track' => 'On Track',
            'tertinggal' => 'Tertinggal',
        ];

        $catatan = $request->catatan ? " Catatan: {$request->catatan}" : '';
        $this->notificationService->send(
            $progress->proposal->mahasiswa,
            'review_progress',
            'Review Progress',
            "Progress {$progress->judul_progres}: {$statusLabels[$request->status]}.{$catatan}",
            $request->user(),
            ['progress_id' => $progress->id]
        );

        return $this->success(new ResearchProgressResource($progress->fresh()), 'Review progress berhasil');
    }

    public function show(ResearchProgress $progress): JsonResponse
    {
        $progress->load(['documents', 'proposal']);
        return $this->success(new ResearchProgressResource($progress));
    }
}
