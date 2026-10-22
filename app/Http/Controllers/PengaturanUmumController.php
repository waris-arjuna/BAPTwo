<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class PengaturanUmumController extends Controller
{
    /**
     * Tampilkan halaman Pengaturan Umum sistem BAPTwo.
     */
    public function index(Request $request): View
    {
        return view('pages.pengaturan.umum');
    }
}
