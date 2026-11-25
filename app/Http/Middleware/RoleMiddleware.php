<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Silakan login terlebih dahulu.'], 401);
            }
            return redirect()->route('login');
        }

        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Akun Anda telah dinonaktifkan.'], 403);
            }
            return redirect()->route('login')->withErrors(['username' => 'Akun Anda telah dinonaktifkan oleh Administrator.']);
        }

        if (!in_array($user->role, $roles, true)) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Akses ditolak. Peran Anda tidak memiliki izin untuk tindakan ini.'], 403);
            }
            abort(403, 'Akses ditolak: Peran akun ' . strtoupper($user->role) . ' tidak memiliki izin membuka halaman ini.');
        }

        return $next($request);
    }
}
