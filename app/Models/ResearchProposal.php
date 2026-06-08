<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResearchProposal extends Model
{
    protected $table = 'research_proposals';

    protected $fillable = [
        'mahasiswa_id',
        'judul',
        'abstrak',
        'topik',
        'file_path',
        'status',
        'catatan_review',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    public function mahasiswa()
    {
        return $this->belongsTo(User::class, 'mahasiswa_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function supervisors()
    {
        return $this->hasMany(ResearchSupervisor::class);
    }

    public function approvedSupervisors()
    {
        return $this->hasMany(ResearchSupervisor::class)->where('status', 'diterima');
    }

    public function progresses()
    {
        return $this->hasMany(ResearchProgress::class);
    }

    public function guidanceRequests()
    {
        return $this->hasMany(GuidanceRequest::class);
    }

    public function seminars()
    {
        return $this->hasMany(Seminar::class);
    }
}
