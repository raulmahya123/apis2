<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuidanceDocument extends Model
{
    protected $fillable = [
        'guidance_request_id',
        'uploader_id',
        'file_path',
        'original_name',
        'file_size',
        'file_type',
        'version',
    ];

    public function guidanceRequest()
    {
        return $this->belongsTo(GuidanceRequest::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }
}
