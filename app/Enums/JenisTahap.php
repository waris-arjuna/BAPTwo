<?php

namespace App\Enums;

enum JenisTahap: string
{
    case SPDP = 'spdp';
    case P16 = 'p16';
    case P17P = 'p17p';
    case TAHAP_1 = 'tahap_1';
    case P18P = 'p18p';
    case P19 = 'p19';
    case P21 = 'p21';
    case TAHAP_2 = 'tahap_2';
    case KEPUTUSAN_PENGADILAN = 'keputusan_pengadilan';

    public function label(): string
    {
        return match ($this) {
            self::SPDP => 'SPDP',
            self::P16 => 'P-16',
            self::P17P => 'P-17P',
            self::TAHAP_1 => 'TAHAP I',
            self::P18P => 'P-18P',
            self::P19 => 'P-19',
            self::P21 => 'P-21/P21a',
            self::TAHAP_2 => 'TAHAP II',
            self::KEPUTUSAN_PENGADILAN => 'Keputusan Pengadilan',
        };
    }

    public function bobot(): int
    {
        return match ($this) {
            self::SPDP => 15,
            self::P16 => 0,
            self::P17P => 0,
            self::TAHAP_1 => 55,
            self::P18P => 0,
            self::P19 => 0,
            self::P21 => 85,
            self::TAHAP_2 => 100,
            self::KEPUTUSAN_PENGADILAN => 100,
        };
    }

    public function isOptional(): bool
    {
        return match ($this) {
            self::P16, self::P17P, self::P18P, self::P19 => true,
            default => false,
        };
    }

    public function urutan(): int
    {
        return match ($this) {
            self::SPDP => 1,
            self::P16 => 2,
            self::P17P => 3,
            self::TAHAP_1 => 4,
            self::P18P => 5,
            self::P19 => 6,
            self::P21 => 7,
            self::TAHAP_2 => 8,
            self::KEPUTUSAN_PENGADILAN => 9,
        };
    }
}
