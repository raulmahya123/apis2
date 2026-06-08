<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuidanceRequest extends Model
{
    protected $fillable = [
        'research_proposal_id',
        'mahasiswa_id',
        'dosen_id',
        'judul',
        'deskripsi',
        'status',
    ];

    public function proposal()
    {
        return $this->belongsTo(ResearchProposal::class);
    }

    public function mahasiswa()
    {
        return $this->belongsTo(User::class, 'mahasiswa_id');
    }

    public function dosen()
    {
        return $this->belongsTo(User::class, 'dosen_id');
    }

    public function documents()
    {
        return $this->hasMany(GuidanceDocument::class);
    }

    public function comments()
    {
        return $this->hasMany(GuidanceComment::class);
    }
}
