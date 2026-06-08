<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seminar extends Model
{
    protected $table = 'seminars';

    protected $fillable = [
        'research_proposal_id',
        'mahasiswa_id',
        'jenis',
        'status',
        'judul',
        'deskripsi',
        'file_path',
        'syarat_valid',
        'tanggal_pengajuan',
        'tanggal_seminar',
        'ruangan',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'syarat_valid' => 'boolean',
            'tanggal_pengajuan' => 'datetime',
            'tanggal_seminar' => 'datetime',
        ];
    }

    public function proposal()
    {
        return $this->belongsTo(ResearchProposal::class);
    }

    public function mahasiswa()
    {
        return $this->belongsTo(User::class, 'mahasiswa_id');
    }

    public function reviewers()
    {
        return $this->hasMany(SeminarReviewer::class);
    }

    public function results()
    {
        return $this->hasMany(SeminarResult::class);
    }
}
