<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResearchProgress extends Model
{
    protected $fillable = [
        'research_proposal_id',
        'mahasiswa_id',
        'judul_progres',
        'deskripsi',
        'status',
        'periode',
    ];

    public function proposal()
    {
        return $this->belongsTo(ResearchProposal::class);
    }

    public function mahasiswa()
    {
        return $this->belongsTo(User::class, 'mahasiswa_id');
    }

    public function documents()
    {
        return $this->hasMany(ProgressDocument::class);
    }
}
