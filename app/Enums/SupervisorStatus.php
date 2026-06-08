<?php

namespace App\Enums;

enum SupervisorStatus: string
{
    case Menunggu = 'menunggu';
    case Diterima = 'diterima';
    case Ditolak = 'ditolak';
}
