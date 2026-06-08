<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuidanceComment extends Model
{
    protected $fillable = [
        'guidance_request_id',
        'user_id',
        'konten',
        'is_revision',
    ];

    protected function casts(): array
    {
        return [
            'is_revision' => 'boolean',
        ];
    }

    public function guidanceRequest()
    {
        return $this->belongsTo(GuidanceRequest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
