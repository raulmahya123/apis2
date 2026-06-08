<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|string|in:mahasiswa,dosen_pembimbing,admin_akademik,kaprodi,penguji',
            'nomor_induk' => 'nullable|string|unique:users,nomor_induk',
            'jurusan' => 'nullable|string|max:255',
            'angkatan' => 'nullable|string|max:20',
            'no_telepon' => 'nullable|string|max:20',
        ];
    }
}
