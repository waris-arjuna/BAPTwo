<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PotensiTerlimpahController extends Controller
{
    /**
     * Redirect monitoring Potensi Terlimpah ke Daftar Perkara (Poin 10 & 11)
     */
    public function index(Request $request): RedirectResponse
    {
        return redirect()->route('data-perkara.index', $request->query());
    }
}
