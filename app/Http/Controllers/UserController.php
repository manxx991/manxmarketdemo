<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        if ($currentUser->role !== 'owner') {
            abort(403, 'Akses terbatas untuk Owner.');
        }

        $userRole = 'owner';
        $filterRole = $request->input('role', 'all');
        $search = trim($request->input('search', ''));

        $query = User::query();

        if ($filterRole !== 'all') {
            $query->where('role', $filterRole);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->orderByRaw("CASE role WHEN 'owner' THEN 0 WHEN 'kasir' THEN 1 WHEN 'gudang' THEN 2 WHEN 'pembeli' THEN 3 ELSE 4 END")
            ->orderBy('name', 'asc')
            ->paginate(10)
            ->withQueryString();

        $statsRows = User::query()
            ->select('role', \DB::raw('COUNT(*) as count'))
            ->groupBy('role')
            ->pluck('count', 'role')
            ->toArray();

        return view('roles.index', compact('users', 'statsRows', 'filterRole', 'search', 'userRole'));
    }

    public function store(Request $request)
    {
        $currentUser = Auth::user();
        if ($currentUser->role !== 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya Owner yang memiliki wewenang mengelola role staf.'
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'username' => 'required|string|min:3|max:30|regex:/^[a-z0-9_.-]+$/|unique:users,username',
            'password' => 'required|string|min:6',
            'role' => 'required|string|in:owner,kasir,gudang,pembeli',
            'phone' => 'nullable|string|max:30',
            'credit_limit' => 'nullable|numeric|min:0',
        ]);

        $creditLimit = ($validated['role'] === 'pembeli')
            ? (float)($validated['credit_limit'] ?? 0)
            : 0.00;

        $user = User::create([
            'name' => trim($validated['name']),
            'username' => strtolower(trim($validated['username'])),
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'phone' => trim($validated['phone'] ?? ''),
            'credit_limit' => $creditLimit,
            'is_active' => 1,
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Pengguna baru dengan role [{$user->role}] berhasil ditambahkan.",
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'role' => $user->role,
                'phone' => $user->phone,
                'credit_limit' => $user->credit_limit,
            ]
        ]);
    }

    /**
     * Endpoint khusus Kasir mendaftarkan Pelanggan Baru (Pembeli).
     * Server memaksakan role 'pembeli' dan credit_limit 0 secara mutlak.
     */
    public function registerCustomer(Request $request)
    {
        $currentUser = Auth::user();
        if (!in_array($currentUser->role, ['owner', 'kasir'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya Kasir dan Owner yang berwenang mendaftarkan pelanggan.'
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'username' => 'required|string|min:3|max:30|regex:/^[a-z0-9_.-]+$/|unique:users,username',
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string|max:30',
        ]);

        // Aturan ketat server: role dipaksa 'pembeli', credit_limit dipaksa 0.00
        $user = User::create([
            'name' => trim($validated['name']),
            'username' => strtolower(trim($validated['username'])),
            'password' => Hash::make($validated['password']),
            'role' => 'pembeli',
            'phone' => trim($validated['phone'] ?? ''),
            'credit_limit' => 0.00,
            'is_active' => 1,
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pelanggan baru berhasil didaftarkan.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'role' => $user->role,
                'phone' => $user->phone,
                'credit_limit' => $user->credit_limit,
            ]
        ]);
    }

    public function toggleActive(Request $request, $id)
    {
        $currentUser = Auth::user();
        if ($currentUser->role !== 'owner') {
            return response()->json(['success' => false, 'message' => 'Hanya Owner yang dapat mengubah status aktif akun.'], 403);
        }

        $user = User::findOrFail($id);
        if ($user->id === $currentUser->id) {
            return response()->json(['success' => false, 'message' => 'Anda tidak dapat menonaktifkan akun sendiri.'], 400);
        }

        $newStatus = $user->is_active ? 0 : 1;
        $user->update(['is_active' => $newStatus]);

        return response()->json([
            'success' => true,
            'message' => "Status akun {$user->name} berhasil diubah menjadi " . ($newStatus ? 'Aktif' : 'Nonaktif') . ".",
            'is_active' => $newStatus,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'role' => $user->role,
                'is_active' => $newStatus,
            ],
        ]);
    }
}
