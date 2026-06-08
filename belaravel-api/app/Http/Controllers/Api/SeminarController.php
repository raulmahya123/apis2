<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SeminarResource;
use App\Models\Seminar;
use App\Models\SeminarReviewer;
use App\Models\SeminarResult;
use App\Services\NotificationService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SeminarController extends Controller
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

        $query = Seminar::with(['mahasiswa', 'proposal', 'reviewers.dosen', 'results.penguji']);

        if ($user->role === 'mahasiswa') {
            $query->where('mahasiswa_id', $user->id);
        } elseif ($user->role === 'dosen_pembimbing') {
            $query->whereHas('reviewers', fn($q) => $q->where('dosen_id', $user->id));
        } elseif (in_array($user->role, ['penguji'])) {
            $query->whereHas('reviewers', fn($q) => $q->where('dosen_id', $user->id));
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->jenis) {
            $query->where('jenis', $request->jenis);
        }

        $seminars = $query->latest()->paginate($request->per_page ?? 15);

        return $this->success(SeminarResource::collection($seminars));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'research_proposal_id' => 'required|exists:research_proposals,id',
            'jenis' => 'required|in:seminar_proposal,sidang_skripsi',
            'judul' => 'required|string|max:500',
            'deskripsi' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,doc,docx|max:20480',
        ]);

        $seminar = Seminar::create([
            'research_proposal_id' => $request->research_proposal_id,
            'mahasiswa_id' => $request->user()->id,
            'jenis' => $request->jenis,
            'status' => 'diajukan',
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'file_path' => $request->hasFile('file') ? $request->file('file')->store('seminar-documents', 'public') : null,
            'tanggal_pengajuan' => now(),
        ]);

        $this->notificationService->sendToKaprodi(
            'seminar_diajukan',
            'Pengajuan Seminar/Sidang Baru',
            "Mahasiswa {$request->user()->name} mengajukan {$seminar->jenis}: {$seminar->judul}",
            $request->user(),
            ['seminar_id' => $seminar->id]
        );

        return $this->created(new SeminarResource($seminar), 'Pengajuan seminar berhasil');
    }

    public function show(Seminar $seminar): JsonResponse
    {
        $seminar->load(['mahasiswa', 'proposal', 'reviewers.dosen', 'results.penguji']);
        return $this->success(new SeminarResource($seminar));
    }

    public function approve(Request $request, Seminar $seminar): JsonResponse
    {
        if ($request->user()->role !== 'kaprodi') {
            return $this->forbidden('Hanya Kaprodi yang dapat menyetujui seminar');
        }

        $seminar->update(['status' => 'disetujui']);

        return $this->success(new SeminarResource($seminar), 'Seminar disetujui');
    }

    public function schedule(Request $request, Seminar $seminar): JsonResponse
    {
        if (!in_array($request->user()->role, ['kaprodi', 'admin_akademik'])) {
            return $this->forbidden('Hanya Kaprodi/Admin yang dapat menjadwalkan seminar');
        }

        $request->validate([
            'tanggal_seminar' => 'required|date|after:now',
            'ruangan' => 'required|string|max:100',
        ]);

        $seminar->update([
            'status' => 'dijadwalkan',
            'tanggal_seminar' => $request->tanggal_seminar,
            'ruangan' => $request->ruangan,
        ]);

        $this->notificationService->send(
            $seminar->mahasiswa,
            'jadwal_seminar',
            'Jadwal Seminar/Sidang',
            "{$seminar->jenis} Anda dijadwalkan pada {$request->tanggal_seminar} di ruangan {$request->ruangan}",
            $request->user(),
            ['seminar_id' => $seminar->id]
        );

        foreach ($seminar->reviewers as $reviewer) {
            $this->notificationService->send(
                $reviewer->dosen,
                'jadwal_seminar',
                'Jadwal Seminar/Sidang',
                "Anda ditugaskan sebagai {$reviewer->peran} untuk {$seminar->jenis} pada {$request->tanggal_seminar}",
                $request->user(),
                ['seminar_id' => $seminar->id]
            );
        }

        return $this->success(new SeminarResource($seminar), 'Seminar berhasil dijadwalkan');
    }

    public function assignReviewer(Request $request, Seminar $seminar): JsonResponse
    {
        if ($request->user()->role !== 'kaprodi') {
            return $this->forbidden('Hanya Kaprodi yang dapat menetapkan penguji');
        }

        $request->validate([
            'dosen_id' => 'required|exists:users,id',
            'peran' => 'required|in:ketua_penguji,anggota_penguji,pembimbing',
        ]);

        $exists = SeminarReviewer::where('seminar_id', $seminar->id)
            ->where('dosen_id', $request->dosen_id)
            ->exists();

        if ($exists) {
            return $this->error('Dosen sudah ditugaskan sebagai penguji', 422);
        }

        $reviewer = $seminar->reviewers()->create([
            'dosen_id' => $request->dosen_id,
            'peran' => $request->peran,
            'status' => 'ditugaskan',
        ]);

        $this->notificationService->send(
            $reviewer->dosen,
            'penugasan_penguji',
            'Penugasan Penguji',
            "Anda ditugaskan sebagai {$reviewer->peran} untuk {$seminar->jenis}: {$seminar->judul}",
            $request->user(),
            ['seminar_id' => $seminar->id]
        );

        return $this->created(new SeminarResource($seminar->fresh()->load('reviewers.dosen')), 'Penguji berhasil ditetapkan');
    }

    public function submitResult(Request $request, Seminar $seminar): JsonResponse
    {
        $request->validate([
            'nilai' => 'required|numeric|min:0|max:100',
            'catatan' => 'nullable|string',
            'keputusan' => 'required|in:lulus,lulus_dengan_revisi,tidak_lulus',
            'file_berita_acara' => 'nullable|file|mimes:pdf|max:20480',
        ]);

        $result = $seminar->results()->updateOrCreate(
            ['penguji_id' => $request->user()->id],
            [
                'nilai' => $request->nilai,
                'catatan' => $request->catatan,
                'keputusan' => $request->keputusan,
                'file_berita_acara' => $request->hasFile('file_berita_acara')
                    ? $request->file('file_berita_acara')->store('seminar-results', 'public')
                    : null,
            ]
        );

        $totalReviewers = $seminar->reviewers()->count();
        $totalResults = $seminar->results()->count();

        if ($totalResults >= $totalReviewers) {
            $allLulus = $seminar->results()->where('keputusan', '!=', 'tidak_lulus')->count() === $totalResults;
            $seminar->update(['status' => 'selesai']);
        }

        $this->notificationService->send(
            $seminar->mahasiswa,
            'hasil_seminar',
            'Hasil Seminar/Sidang',
            "Hasil {$seminar->jenis}: {$request->keputusan}. Catatan: {$request->catatan}",
            $request->user(),
            ['seminar_id' => $seminar->id, 'result_id' => $result->id]
        );

        return $this->success(new SeminarResource($seminar->fresh()->load('results', 'reviewers')), 'Nilai berhasil disubmit');
    }

    public function validateSyarat(Request $request, Seminar $seminar): JsonResponse
    {
        $seminar->update(['syarat_valid' => $request->valid ?? true]);
        return $this->success(new SeminarResource($seminar->fresh()), 'Syarat seminar diperbarui');
    }
}
