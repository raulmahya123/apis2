<?php

namespace App\Enums;

enum ProgressStatus: string
{
    case Approved = 'approved';
    case Revisi = 'revisi';
    case OnTrack = 'on_track';
    case Tertinggal = 'tertinggal';
}
