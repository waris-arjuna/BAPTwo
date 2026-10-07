<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /**
     * Supported locales with their metadata (locked to Indonesian LTR).
     */
    public const SUPPORTED_LOCALES = [
        'id' => [
            'name' => 'Indonesia',
            'native' => 'Bahasa Indonesia',
            'flag' => 'id',
            'dir' => 'ltr',
        ],
    ];

    /**
     * Switch application locale (locked to 'id' / 'ltr').
     */
    public function switch(string $locale, Request $request): RedirectResponse
    {
        session(['locale' => 'id', 'dir' => 'ltr']);
        cookie()->queue('locale', 'id', 60 * 24 * 365);
        cookie()->queue('dir', 'ltr', 60 * 24 * 365);

        return redirect()->back();
    }
}
