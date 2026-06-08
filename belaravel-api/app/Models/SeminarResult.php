<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeminarResult extends Model
{
    protected $fillable = [
        'seminar_id',
        'penguji_id',
        'nilai',
        'catatan',
        'keputusan',
        'file_berita_acara',
    ];

    public function seminar()
    {
        return $this->belongsTo(Seminar::class);
    }

    public function penguji()
    {
        return $this->belongsTo(User::class, 'penguji_id');
    }
}
