<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgressDocument extends Model
{
    protected $table = 'progress_documents';

    protected $fillable = [
        'research_progress_id',
        'file_path',
        'original_name',
        'file_size',
        'file_type',
        'keterangan',
    ];

    public function progress()
    {
        return $this->belongsTo(ResearchProgress::class);
    }
}
