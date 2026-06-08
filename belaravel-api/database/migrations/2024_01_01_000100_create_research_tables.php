<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('research_proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('users')->cascadeOnDelete();
            $table->string('judul');
            $table->text('abstrak');
            $table->string('topik');
            $table->string('file_path')->nullable();
            $table->string('status')->default('draft'); // draft, diajukan, direview, disetujui, direvisi, ditolak
            $table->text('catatan_review')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('research_supervisors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('research_proposal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dosen_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('menunggu'); // menunggu, diterima, ditolak
            $table->text('keterangan')->nullable();
            $table->foreignId('assigned_by')->constrained('users');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
        });

        Schema::create('research_progresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('research_proposal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mahasiswa_id')->constrained('users')->cascadeOnDelete();
            $table->string('judul_progres');
            $table->text('deskripsi');
            $table->string('status')->default('on_track'); // approved, revisi, on_track, tertinggal
            $table->string('periode')->nullable();
            $table->timestamps();
        });

        Schema::create('progress_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('research_progress_id')->constrained()->cascadeOnDelete();
            $table->string('file_path');
            $table->string('original_name');
            $table->bigInteger('file_size')->nullable();
            $table->string('file_type')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('guidance_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('research_proposal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mahasiswa_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('dosen_id')->constrained('users')->cascadeOnDelete();
            $table->string('judul');
            $table->text('deskripsi');
            $table->string('status')->default('diajukan'); // diajukan, direspon, selesai
            $table->timestamps();
        });

        Schema::create('guidance_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guidance_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploader_id')->constrained('users');
            $table->string('file_path');
            $table->string('original_name');
            $table->bigInteger('file_size')->nullable();
            $table->string('file_type')->nullable();
            $table->integer('version')->default(1);
            $table->timestamps();
        });

        Schema::create('guidance_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guidance_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('konten');
            $table->boolean('is_revision')->default(false);
            $table->timestamps();
        });

        Schema::create('seminars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('research_proposal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mahasiswa_id')->constrained('users')->cascadeOnDelete();
            $table->string('jenis'); // seminar_proposal, sidang_skripsi
            $table->string('status')->default('diajukan'); // diajukan, disetujui, dijadwalkan, selesai, ditolak
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->string('file_path')->nullable();
            $table->boolean('syarat_valid')->default(false);
            $table->timestamp('tanggal_pengajuan')->nullable();
            $table->timestamp('tanggal_seminar')->nullable();
            $table->string('ruangan')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        Schema::create('seminar_reviewers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seminar_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dosen_id')->constrained('users')->cascadeOnDelete();
            $table->string('peran'); // ketua_penguji, anggota_penguji, pembimbing
            $table->string('status')->default('ditugaskan'); // ditugaskan, konfirmasi
            $table->timestamps();
        });

        Schema::create('seminar_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seminar_id')->constrained()->cascadeOnDelete();
            $table->foreignId('penguji_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('nilai', 5, 2)->nullable();
            $table->text('catatan')->nullable();
            $table->string('keputusan')->nullable(); // lulus, lulus_dengan_revisi, tidak_lulus
            $table->string('file_berita_acara')->nullable();
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pengirim_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type');
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('document_versions', function (Blueprint $table) {
            $table->id();
            $table->morphs('documentable');
            $table->foreignId('uploader_id')->constrained('users');
            $table->string('file_path');
            $table->string('original_name');
            $table->integer('version_number');
            $table->bigInteger('file_size')->nullable();
            $table->string('file_type')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_versions');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('seminar_results');
        Schema::dropIfExists('seminar_reviewers');
        Schema::dropIfExists('seminars');
        Schema::dropIfExists('guidance_comments');
        Schema::dropIfExists('guidance_documents');
        Schema::dropIfExists('guidance_requests');
        Schema::dropIfExists('progress_documents');
        Schema::dropIfExists('research_progresses');
        Schema::dropIfExists('research_supervisors');
        Schema::dropIfExists('research_proposals');
    }
};
