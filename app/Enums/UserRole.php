<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMINISTRATOR = 'administrator';
    case PIMPINAN = 'pimpinan';
    case PENYIDIK = 'penyidik';
    case UMUM = 'umum';

    public function label(): string
    {
        return match ($this) {
            self::ADMINISTRATOR => 'Administrator',
            self::PIMPINAN => 'Pemimpin',
            self::PENYIDIK => 'Penyidik',
            self::UMUM => 'Umum',
        };
    }
}
