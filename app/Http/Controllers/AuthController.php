<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showSignin(Request $request): View
    {
        if ($request->has('session_expired')) {
            session()->flash('warning', __('Sesi Anda telah berakhir karena tidak ada interaksi selama 30 menit. Silakan masuk kembali.'));
        }

        return view('pages.auth.signin', ['title' => __('Sign In')]);
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $email = $request->string('email')->trim()->toString();
        $password = $request->string('password')->toString();

        $user = User::withTrashed()->where('email', $email)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => __('Email tidak terdaftar dalam sistem.'),
            ]);
        }

        if ($user->trashed() || ! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => __('Akun Anda sedang dinonaktifkan. Hubungi Administrator.'),
            ]);
        }

        $credentials = [
            'email' => $email,
            'password' => $password,
            'is_active' => true,
        ];

        if (! Auth::attempt($credentials, false)) {
            throw ValidationException::withMessages([
                'password' => __('Kata sandi yang Anda masukkan salah.'),
            ]);
        }

        $request->session()->regenerate();

        return redirect()
            ->intended(route('dashboard'))
            ->with('login_success', __('Berhasil masuk! Selamat datang kembali, :nama.', ['nama' => $user->nama]));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('signin');
    }
}
