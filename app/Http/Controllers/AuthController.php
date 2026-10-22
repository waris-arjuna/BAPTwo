<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\AuditTrailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

        if (! $user || ! Hash::check($password, $user->password)) {
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

        if (! $user->is_active || $user->trashed()) {
            RateLimiter::hit($throttleKey, 60);

            app(AuditTrailService::class)->log(
                aksi: 'login_inactive',
                tabel: 'user',
                recordId: $user->id,
                dataBaru: [
                    'email' => $email,
                    'is_active' => (bool) $user->is_active,
                    'trashed' => $user->trashed(),
                ]
            );

            return redirect()->back()
                ->withInput($request->only('email'))
                ->with('account_inactive', __('Akun Anda sedang dinonaktifkan atau telah dihapus. Silakan hubungi Administrator IT BPOM.'))
                ->with('error', [
                    'title' => __('Akun Dinonaktifkan'),
                    'message' => __('Akun Anda sedang dinonaktifkan atau telah dihapus. Silakan hubungi Administrator IT BPOM.'),
                ]);
        }

        RateLimiter::clear($throttleKey);

        Auth::login($user, false);

        $request->session()->regenerate();

        return redirect()
            ->intended(route('dashboard'))
            ->with('login_success', [
                'title' => __('Berhasil Masuk'),
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
