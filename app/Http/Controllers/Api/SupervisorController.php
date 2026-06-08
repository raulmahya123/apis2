<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ResearchSupervisorResource;
use App\Models\ResearchProposal;
use App\Models\ResearchSupervisor;
use App\Services\NotificationService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupervisorController extends Controller
{
    use ApiResponse;

    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function assignSupervisor(Request $request, ResearchProposal $proposal): JsonResponse
    {
        if ($request->user()->role !== 'kaprodi') {
            return $this->forbidden('Hanya Kaprodi yang dapat menetapkan pembimbing');
        }

        $request->validate([
            'dosen_id' => 'required|exists:users,id',
        ]);

        $dosenId = $request->dosen_id;

        $exists = ResearchSupervisor::where('research_proposal_id', $proposal->id)
            ->where('dosen_id', $dosenId)
            ->exists();

        if ($exists) {
            return $this->error('Dosen sudah ditugaskan sebagai pembimbing', 422);
        }

        $supervisor = ResearchSupervisor::create([
            'research_proposal_id' => $proposal->id,
            'dosen_id' => $dosenId,
            'status' => 'menunggu',
            'assigned_by' => $request->user()->id,
            'assigned_at' => now(),
        ]);

        $supervisor->load('dosen');

        $dosen = $supervisor->dosen;
        $this->notificationService->send(
            $dosen,
            'penugasan_pembimbing',
            'Penugasan Pembimbing Baru',
            "Anda ditugaskan sebagai pembimbing untuk proposal: {$proposal->judul}",
            $request->user(),
            ['proposal_id' => $proposal->id, 'supervisor_id' => $supervisor->id]
        );

        return $this->created(new ResearchSupervisorResource($supervisor), 'Pembimbing berhasil ditetapkan');
    }

    public function respondSupervisor(Request $request, ResearchSupervisor $supervisor): JsonResponse
    {
        if ($request->user()->id !== $supervisor->dosen_id) {
            return $this->forbidden('Anda tidak memiliki akses');
        }

        $request->validate([
            'action' => 'required|in:terima,tolak',
            'keterangan' => 'nullable|string',
        ]);

        $status = $request->action === 'terima' ? 'diterima' : 'ditolak';
        $supervisor->update([
            'status' => $status,
            'keterangan' => $request->keterangan,
            'responded_at' => now(),
        ]);

        $proposal = $supervisor->proposal;

        if ($status === 'diterima') {
            $allApproved = $proposal->supervisors()->where('status', 'diterima')->exists();
            if ($allApproved) {
                $proposal->update(['status' => 'disetujui']);
            }
        }

        $this->notificationService->send(
            $proposal->mahasiswa,
            'respon_pembimbing',
            'Respon Penugasan Pembimbing',
            "Dosen {$request->user()->name} {$request->action} penugasan sebagai pembimbing",
            $request->user(),
            ['proposal_id' => $proposal->id, 'supervisor_id' => $supervisor->id]
        );

        return $this->success(new ResearchSupervisorResource($supervisor->fresh()), 'Respon berhasil dikirim');
    }

    public function myAssignments(Request $request): JsonResponse
    {
        $supervisors = ResearchSupervisor::with(['proposal.mahasiswa', 'dosen'])
            ->where('dosen_id', $request->user()->id)
            ->paginate($request->per_page ?? 15);

        return $this->success(ResearchSupervisorResource::collection($supervisors));
    }

    public function proposalSupervisors(ResearchProposal $proposal): JsonResponse
    {
        $supervisors = $proposal->supervisors()->with(['dosen', 'assignedBy'])->get();
        return $this->success(ResearchSupervisorResource::collection($supervisors));
    }
}
