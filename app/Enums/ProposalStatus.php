<?php

namespace App\Enums;

enum ProposalStatus: string
{
    case Draft = 'draft';
    case Diajukan = 'diajukan';
    case Direview = 'direview';
    case Disetujui = 'disetujui';
    case Direvisi = 'direvisi';
    case Ditolak = 'ditolak';
}
