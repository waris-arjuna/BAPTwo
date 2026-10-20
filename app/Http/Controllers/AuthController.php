<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\AuditTrailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showSignin(Request $request): View
    {
        return view('pages.auth.signin', ['title' => __('Masuk')]);
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $email = $request->string('email')->trim()->toString();
        $password = $request->string('password')->toString();
        $throttleKey = Str::transliterate(Str::lower($email).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => __('Terlalu banyak percobaan masuk. Silakan coba lagi dalam :seconds detik.', ['seconds' => $seconds]),
            ]);
        }

        $user = User::withTrashed()->where('email', $email)->first();

        $credentials = [
            'email' => $email,
            'password' => $password,
            'is_active' => true,
        ];

        if (! $user || $user->trashed() || ! $user->is_active || ! Auth::attempt($credentials, false)) {
            RateLimiter::hit($throttleKey, 60);

            app(AuditTrailService::class)->log(
                aksi: 'login_failed',
                tabel: 'user',
                recordId: $user?->id ?? 0,
                dataBaru: ['email' => $email]
            );

            throw ValidationException::withMessages([
                'email' => __('Email atau kata sandi salah.'),
            ]);
        }

        RateLimiter::clear($throttleKey);

        $request->session()->regenerate();

        return redirect()
            ->intended(route('dashboard'))
            ->with('login_success', [
                'title' => 'Berhasil Masuk',
                'message' => __('Selamat datang kembali, :nama.', ['nama' => $user->nama]),
            ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('masuk');
    }
}
