<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentVersion extends Model
{
    protected $table = 'document_versions';

    protected $fillable = [
        'documentable_type',
        'documentable_id',
        'uploader_id',
        'file_path',
        'original_name',
        'version_number',
        'file_size',
        'file_type',
        'keterangan',
    ];

    public function documentable()
    {
        return $this->morphTo();
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }
}
