<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Jika user belum ter-load tapi ada sesi login aktif di session, periksa apakah akun telah dihapus secara soft-delete
        if (! $user && $request->hasSession()) {
            $guardName = Auth::guard('web')->getName();
            if ($userId = $request->session()->get($guardName)) {
                $candidate = User::withTrashed()->find($userId);
                if ($candidate && ($candidate->trashed() || ! $candidate->is_active)) {
                    $user = $candidate;
                }
            }
        }

        if ($user && (! $user->is_active || $user->trashed())) {
            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('Akun Anda sedang dinonaktifkan atau telah dihapus. Silakan hubungi Administrator IT BPOM.'),
                ], 403);
            }

            return redirect()->route('masuk')
                ->with('account_inactive', __('Akun Anda sedang dinonaktifkan atau telah dihapus. Silakan hubungi Administrator IT BPOM.'))
                ->with('error', [
                    'title' => __('Akun Dinonaktifkan'),
                    'message' => __('Akun Anda sedang dinonaktifkan atau telah dihapus. Silakan hubungi Administrator IT BPOM.'),
                ]);
        }

        return $next($request);
    }
}
