<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    use ApiResponse;

    public function show(string $path): mixed
    {
        $user = request()->user();

        if (!$user || !$user->hasAnyRole(['mahasiswa', 'kaprodi', 'dosen_pembimbing'])) {
            return $this->forbidden('Anda tidak memiliki akses ke file ini');
        }

        if (!Storage::disk('public')->exists($path)) {
            return $this->notFound('File tidak ditemukan');
        }

        return Storage::disk('public')->response($path);
    }
}
