<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResearchSupervisor extends Model
{
    protected $fillable = [
        'research_proposal_id',
        'dosen_id',
        'status',
        'keterangan',
        'assigned_by',
        'assigned_at',
        'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'responded_at' => 'datetime',
        ];
    }

    public function proposal()
    {
        return $this->belongsTo(ResearchProposal::class);
    }

    public function dosen()
    {
        return $this->belongsTo(User::class, 'dosen_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
