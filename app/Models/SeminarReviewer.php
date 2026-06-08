<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeminarReviewer extends Model
{
    protected $fillable = [
        'seminar_id',
        'dosen_id',
        'peran',
        'status',
    ];

    public function seminar()
    {
        return $this->belongsTo(Seminar::class);
    }

    public function dosen()
    {
        return $this->belongsTo(User::class, 'dosen_id');
    }
}
