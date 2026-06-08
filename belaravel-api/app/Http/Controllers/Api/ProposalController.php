<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ResearchProposalResource;
use App\Models\ResearchProposal;
use App\Services\NotificationService;
use App\Services\ProposalService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProposalController extends Controller
{
    use ApiResponse;

    protected ProposalService $proposalService;
    protected NotificationService $notificationService;

    public function __construct(ProposalService $proposalService, NotificationService $notificationService)
    {
        $this->proposalService = $proposalService;
        $this->notificationService = $notificationService;
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = ResearchProposal::with(['mahasiswa', 'reviewer', 'approvedSupervisors.dosen']);

        if ($user->role === 'mahasiswa') {
            $query->where('mahasiswa_id', $user->id);
        } elseif ($user->role === 'kaprodi') {
            $query->orderBy('created_at', 'desc');
        } elseif ($user->role === 'dosen_pembimbing') {
            $query->whereHas('approvedSupervisors', fn($q) => $q->where('dosen_id', $user->id));
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $proposals = $query->paginate($request->per_page ?? 15);

        return $this->success(ResearchProposalResource::collection($proposals));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'judul' => 'required|string|max:500',
            'abstrak' => 'required|string',
            'topik' => 'required|string|max:255',
            'file' => 'nullable|file|mimes:pdf,doc,docx|max:20480',
        ]);

        $proposal = $this->proposalService->submitProposal($request->all(), $request->user()->id);

        $this->notificationService->sendToKaprodi(
            'proposal_diajukan',
            'Pengajuan Penelitian Baru',
            "Mahasiswa {$request->user()->name} mengajukan proposal penelitian: {$proposal->judul}",
            $request->user(),
            ['proposal_id' => $proposal->id]
        );

        return $this->created(new ResearchProposalResource($proposal), 'Proposal berhasil diajukan');
    }

    public function show(ResearchProposal $proposal): JsonResponse
    {
        $proposal->load(['mahasiswa', 'reviewer', 'approvedSupervisors.dosen', 'progresses.documents', 'guidanceRequests.comments', 'seminars']);
        return $this->success(new ResearchProposalResource($proposal));
    }

    public function review(Request $request, ResearchProposal $proposal): JsonResponse
    {
        $request->validate([
            'action' => 'required|in:approve,reject,revision',
            'catatan' => 'required_if:action,reject,revision|string',
        ]);

        $user = $request->user();

        if ($user->role !== 'kaprodi') {
            return $this->forbidden('Hanya Kaprodi yang dapat mereview proposal');
        }

        $proposal = match ($request->action) {
            'approve' => $this->proposalService->approveProposal($proposal, $user->id, $request->catatan),
            'reject' => $this->proposalService->rejectProposal($proposal, $user->id, $request->catatan),
            'revision' => $this->proposalService->requestRevision($proposal, $user->id, $request->catatan),
        };

        $notificationType = match ($request->action) {
            'approve' => ['type' => 'proposal_disetujui', 'title' => 'Proposal Disetujui', 'msg' => 'Proposal penelitian Anda telah disetujui'],
            'reject' => ['type' => 'proposal_ditolak', 'title' => 'Proposal Ditolak', 'msg' => 'Proposal penelitian Anda ditolak'],
            'revision' => ['type' => 'proposal_revisi', 'title' => 'Proposal Perlu Revisi', 'msg' => 'Proposal penelitian Anda perlu direvisi'],
        };

        $this->notificationService->send(
            $proposal->mahasiswa,
            $notificationType['type'],
            $notificationType['title'],
            $notificationType['msg'] . ': ' . ($request->catatan ?? ''),
            $user,
            ['proposal_id' => $proposal->id]
        );

        return $this->success(new ResearchProposalResource($proposal), 'Review berhasil');
    }

    public function update(Request $request, ResearchProposal $proposal): JsonResponse
    {
        if ($request->user()->id !== $proposal->mahasiswa_id) {
            return $this->forbidden('Anda tidak memiliki akses');
        }

        $request->validate([
            'judul' => 'sometimes|string|max:500',
            'abstrak' => 'sometimes|string',
            'topik' => 'sometimes|string|max:255',
            'file' => 'nullable|file|mimes:pdf,doc,docx|max:20480',
        ]);

        $data = $request->except('file');
        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('proposals', 'public');
        }
        if ($proposal->status === 'direvisi') {
            $data['status'] = 'diajukan';
            $data['catatan_review'] = null;
            $data['reviewed_by'] = null;
            $data['reviewed_at'] = null;
        }

        $proposal->update($data);

        return $this->success(new ResearchProposalResource($proposal->fresh()), 'Proposal berhasil diperbarui');
    }
}
