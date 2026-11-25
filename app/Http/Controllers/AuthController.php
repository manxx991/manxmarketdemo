<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectUser(Auth::user());
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $credentials['username'])->first();

        if ($user && \Illuminate\Support\Facades\Hash::check($credentials['password'], $user->password)) {
            if (!$user->is_active) {
                return back()->withErrors([
                    'username' => 'Akun Anda berstatus nonaktif. Silakan hubungi Administrator atau Owner untuk mengaktifkan kembali akun ini.',
                ])->withInput($request->only('username'));
            }

            Auth::login($user);
            $request->session()->regenerate();
            return $this->redirectUser($user);
        }

        return back()->withErrors([
            'username' => 'Username atau password yang Anda masukkan tidak sesuai.',
        ])->withInput($request->only('username'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function switchRole(Request $request)
    {
        if (Auth::user()->role !== 'owner') {
            abort(403, 'Hanya Owner yang berhak beralih role.');
        }

        $request->validate([
            'role' => 'nullable|string|in:owner,kasir,gudang,pembeli',
            'user_id' => 'nullable|integer|exists:users,id',
        ]);

        $targetUser = null;
        if ($request->filled('user_id')) {
            $targetUser = User::where('id', $request->user_id)->where('is_active', 1)->first();
        } elseif ($request->filled('role')) {
            $targetUser = User::where('role', $request->role)->where('is_active', 1)->first();
        }

        if (!$targetUser) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Akun untuk role tersebut tidak ditemukan atau tidak aktif.'], 404);
            }
            return back()->with('error', 'Akun untuk role tersebut tidak ditemukan atau tidak aktif.');
        }

        Auth::login($targetUser);
        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        $roleRoutes = [
            'owner' => route('reports.index'),
            'kasir' => route('pos.index'),
            'gudang' => route('inventory.index'),
            'pembeli' => route('portal.index'),
        ];
        $targetUrl = $roleRoutes[$targetUser->role] ?? route('pos.index');
        $msg = 'Berhasil beralih ke role ' . strtoupper($targetUser->role) . ' (' . $targetUser->name . ').';

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'redirect' => $targetUrl,
                'role' => $targetUser->role,
                'user' => [
                    'id' => $targetUser->id,
                    'name' => $targetUser->name,
                    'username' => $targetUser->username,
                ]
            ]);
        }

        return redirect($targetUrl)->with('success', $msg);
    }

    private function redirectUser($user)
    {
        switch ($user->role) {
            case 'owner':
                return redirect()->intended(route('reports.index'));
            case 'kasir':
                return redirect()->intended(route('pos.index'));
            case 'gudang':
                return redirect()->intended(route('inventory.index'));
            case 'pembeli':
                return redirect()->intended(route('portal.index'));
            default:
                return redirect()->route('pos.index');
        }
    }
}
