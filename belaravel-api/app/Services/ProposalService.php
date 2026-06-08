<?php

namespace App\Services;

use App\Models\ResearchProposal;
use App\Models\ResearchSupervisor;
use Illuminate\Support\Facades\Storage;

class ProposalService
{
    public function submitProposal(array $data, int $mahasiswaId): ResearchProposal
    {
        $data['mahasiswa_id'] = $mahasiswaId;
        $data['status'] = 'diajukan';

        if (isset($data['file']) && $data['file']->isValid()) {
            $file = $data['file'];
            $path = $file->store('proposals', 'public');
            $data['file_path'] = $path;
            unset($data['file']);
        }

        return ResearchProposal::create($data);
    }

    public function approveProposal(ResearchProposal $proposal, int $reviewerId, ?string $catatan = null): ResearchProposal
    {
        $proposal->update([
            'status' => 'disetujui',
            'reviewed_by' => $reviewerId,
            'catatan_review' => $catatan,
            'reviewed_at' => now(),
        ]);

        return $proposal;
    }

    public function rejectProposal(ResearchProposal $proposal, int $reviewerId, string $catatan): ResearchProposal
    {
        $proposal->update([
            'status' => 'ditolak',
            'reviewed_by' => $reviewerId,
            'catatan_review' => $catatan,
            'reviewed_at' => now(),
        ]);

        return $proposal;
    }

    public function requestRevision(ResearchProposal $proposal, int $reviewerId, string $catatan): ResearchProposal
    {
        $proposal->update([
            'status' => 'direvisi',
            'reviewed_by' => $reviewerId,
            'catatan_review' => $catatan,
            'reviewed_at' => now(),
        ]);

        return $proposal;
    }

    public function assignSupervisor(ResearchProposal $proposal, int $dosenId, int $assignedBy): ResearchSupervisor
    {
        return ResearchSupervisor::create([
            'research_proposal_id' => $proposal->id,
            'dosen_id' => $dosenId,
            'status' => 'menunggu',
            'assigned_by' => $assignedBy,
            'assigned_at' => now(),
        ]);
    }
}
