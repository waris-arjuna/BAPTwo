<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMINISTRATOR = 'administrator';
    case PENYIDIK = 'penyidik';
    case UMUM = 'umum';

    /**
     * Alias kompatibilitas: Pimpinan menggunakan role Umum.
     */
    public const PIMPINAN = self::UMUM;

    public function label(): string
    {
        return match ($this) {
            self::ADMINISTRATOR => 'Administrator',
            self::PENYIDIK => 'Penyidik',
            self::UMUM => 'Umum',
        };
    }
}
