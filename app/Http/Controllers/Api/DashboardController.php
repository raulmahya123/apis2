<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ResearchProposal;
use App\Models\ResearchProgress;
use App\Models\Seminar;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = match ($user->role) {
            'mahasiswa' => $this->mahasiswaDashboard($user),
            'dosen_pembimbing' => $this->dosenDashboard($user),
            'kaprodi' => $this->kaprodiDashboard(),
            'admin_akademik' => $this->adminDashboard(),
            default => [],
        };

        return $this->success($data);
    }

    private function mahasiswaDashboard(User $user): array
    {
        $proposal = ResearchProposal::where('mahasiswa_id', $user->id)->first();
        $progressCount = ResearchProgress::where('mahasiswa_id', $user->id)->count();
        $onTrackCount = ResearchProgress::where('mahasiswa_id', $user->id)->where('status', 'on_track')->count();
        $seminarCount = Seminar::where('mahasiswa_id', $user->id)->count();

        $recentProgress = ResearchProgress::with('documents')
            ->where('mahasiswa_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        return [
            'proposal' => $proposal ? [
                'id' => $proposal->id,
                'judul' => $proposal->judul,
                'status' => $proposal->status,
            ] : null,
            'statistics' => [
                'total_progress' => $progressCount,
                'on_track' => $onTrackCount,
                'tertinggal' => $progressCount - $onTrackCount,
                'total_seminar' => $seminarCount,
            ],
            'recent_progress' => $recentProgress,
        ];
    }

    private function dosenDashboard(User $user): array
    {
        $bimbinganCount = ResearchProposal::whereHas('approvedSupervisors', fn($q) => $q->where('dosen_id', $user->id))->count();
        $pendingAssignments = $user->supervisorAssignments()->where('status', 'menunggu')->count();
        $seminarCount = Seminar::whereHas('reviewers', fn($q) => $q->where('dosen_id', $user->id))->count();

        $mahasiswaBimbingan = ResearchProposal::with(['mahasiswa', 'progresses'])
            ->whereHas('approvedSupervisors', fn($q) => $q->where('dosen_id', $user->id))
            ->get()
            ->map(fn($p) => [
                'mahasiswa' => $p->mahasiswa->name,
                'judul' => $p->judul,
                'status' => $p->status,
                'progress_count' => $p->progresses->count(),
            ]);

        return [
            'statistics' => [
                'total_bimbingan' => $bimbinganCount,
                'pending_assignments' => $pendingAssignments,
                'total_seminar' => $seminarCount,
            ],
            'mahasiswa_bimbingan' => $mahasiswaBimbingan,
        ];
    }

    private function kaprodiDashboard(): array
    {
        $totalMahasiswa = User::where('role', 'mahasiswa')->count();
        $totalDosen = User::whereIn('role', ['dosen_pembimbing', 'penguji'])->count();
        $totalProposals = ResearchProposal::count();
        $pendingProposals = ResearchProposal::where('status', 'diajukan')->count();
        $approvedProposals = ResearchProposal::where('status', 'disetujui')->count();
        $rejectedProposals = ResearchProposal::where('status', 'ditolak')->count();
        $pendingSeminars = Seminar::where('status', 'diajukan')->count();
        $completedSeminars = Seminar::where('status', 'selesai')->count();

        $recentProposals = ResearchProposal::with('mahasiswa')
            ->latest()
            ->take(10)
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'mahasiswa' => $p->mahasiswa->name,
                'judul' => $p->judul,
                'status' => $p->status,
                'created_at' => $p->created_at,
            ]);

        return [
            'statistics' => [
                'total_mahasiswa' => $totalMahasiswa,
                'total_dosen' => $totalDosen,
                'total_proposals' => $totalProposals,
                'pending_proposals' => $pendingProposals,
                'approved_proposals' => $approvedProposals,
                'rejected_proposals' => $rejectedProposals,
                'pending_seminars' => $pendingSeminars,
                'completed_seminars' => $completedSeminars,
            ],
            'recent_proposals' => $recentProposals,
        ];
    }

    private function adminDashboard(): array
    {
        $totalMahasiswa = User::where('role', 'mahasiswa')->count();
        $totalDosen = User::whereIn('role', ['dosen_pembimbing', 'penguji'])->count();
        $totalKaprodi = User::where('role', 'kaprodi')->count();
        $totalProposals = ResearchProposal::count();
        $totalSeminars = Seminar::count();
        $upcomingSeminars = Seminar::where('status', 'dijadwalkan')
            ->with(['mahasiswa', 'reviewers.dosen'])
            ->where('tanggal_seminar', '>=', now())
            ->orderBy('tanggal_seminar')
            ->take(10)
            ->get();

        return [
            'statistics' => [
                'total_mahasiswa' => $totalMahasiswa,
                'total_dosen' => $totalDosen,
                'total_kaprodi' => $totalKaprodi,
                'total_proposals' => $totalProposals,
                'total_seminars' => $totalSeminars,
            ],
            'upcoming_seminars' => $upcomingSeminars,
        ];
    }

    public function report(Request $request): JsonResponse
    {
        $data = [
            'total_mahasiswa' => User::where('role', 'mahasiswa')->count(),
            'total_dosen' => User::whereIn('role', ['dosen_pembimbing', 'penguji'])->count(),
            'total_proposals' => ResearchProposal::count(),
            'proposals_by_status' => ResearchProposal::selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
            'total_progress' => ResearchProgress::count(),
            'progress_by_status' => ResearchProgress::selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
            'total_seminars' => Seminar::count(),
            'seminars_by_status' => Seminar::selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
            'seminars_by_jenis' => Seminar::selectRaw('jenis, count(*) as total')
                ->groupBy('jenis')
                ->pluck('total', 'jenis'),
        ];

        return $this->success($data);
    }

    public function export(Request $request): JsonResponse
    {
        $type = $request->type ?? 'proposals';

        $data = match ($type) {
            'proposals' => ResearchProposal::with('mahasiswa')->get()->map(fn($p) => [
                'Mahasiswa' => $p->mahasiswa->name,
                'Judul' => $p->judul,
                'Topik' => $p->topik,
                'Status' => $p->status,
                'Tanggal' => $p->created_at->format('Y-m-d'),
            ]),
            'progress' => ResearchProgress::with('mahasiswa', 'proposal')->get()->map(fn($p) => [
                'Mahasiswa' => $p->mahasiswa->name,
                'Proposal' => $p->proposal->judul,
                'Progress' => $p->judul_progres,
                'Status' => $p->status,
                'Periode' => $p->periode,
            ]),
            'seminars' => Seminar::with('mahasiswa')->get()->map(fn($s) => [
                'Mahasiswa' => $s->mahasiswa->name,
                'Jenis' => $s->jenis,
                'Status' => $s->status,
                'Tanggal' => $s->tanggal_seminar?->format('Y-m-d'),
                'Ruangan' => $s->ruangan,
            ]),
            default => [],
        };

        return $this->success($data);
    }
}
