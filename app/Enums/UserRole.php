<?php

namespace App\Enums;

enum UserRole: string
{
    case Mahasiswa = 'mahasiswa';
    case DosenPembimbing = 'dosen_pembimbing';
    case AdminAkademik = 'admin_akademik';
    case Kaprodi = 'kaprodi';
    case Penguji = 'penguji';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
