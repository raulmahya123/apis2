<?php

namespace App\Enums;

enum SeminarStatus: string
{
    case Diajukan = 'diajukan';
    case Disetujui = 'disetujui';
    case Dijadwalkan = 'dijadwalkan';
    case Selesai = 'selesai';
    case Ditolak = 'ditolak';
}
