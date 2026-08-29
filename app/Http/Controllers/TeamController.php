<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TeamController extends Controller
{
    public function index()
    {
        $users = User::with('worksheets')->latest()->paginate(15);
        $availablePermissions = User::AVAILABLE_PERMISSIONS;
        $permissionGroups = User::PERMISSION_GROUPS;
        $worksheets = \App\Models\Worksheet::all();

        $stats = [
            'total' => $users->total(),
            'owner' => $users->filter(fn($u) => $u->isOwner())->count(),
            'kasir' => $users->filter(fn($u) => $u->isKasir())->count(),
            'active' => $users->filter(fn($u) => $u->is_active)->count(),
        ];

        return view('team.index', compact('users', 'availablePermissions', 'permissionGroups', 'worksheets', 'stats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:owner,kasir,crew',
            'password' => 'required|string|min:6|confirmed',
            'permissions' => 'nullable|array',
            'permissions.*' => 'in:' . implode(',', array_keys(User::AVAILABLE_PERMISSIONS)),
            'allowance_type' => 'nullable|in:none,daily,monthly',
            'allowance_amount' => 'nullable|numeric|min:0',
        ]);

        // Permissions logic
        $permissions = [];
        if ($request->role === 'owner') {
            $permissions = array_keys(User::AVAILABLE_PERMISSIONS);
        } elseif ($request->role === 'kasir') {
            $permissions = $request->permissions ?? [];
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $request->role,
            'permissions' => $permissions,
            'allowance_type' => $request->allowance_type ?? 'none',
            'allowance_amount' => $request->allowance_amount ?? 0,
            'password' => Hash::make($request->password),
            'is_active' => true,
        ]);

        if ($request->has('worksheets')) {
            $user->worksheets()->sync($request->worksheets);
        }

        return back()->with('success', 'Anggota tim berhasil ditambahkan!');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:owner,kasir,crew',
            'password' => 'nullable|string|min:6|confirmed',
            'permissions' => 'nullable|array',
            'permissions.*' => 'in:' . implode(',', array_keys(User::AVAILABLE_PERMISSIONS)),
            'allowance_type' => 'nullable|in:none,daily,monthly',
            'allowance_amount' => 'nullable|numeric|min:0',
        ]);

        $permissions = [];
        if ($request->role === 'owner') {
            $permissions = array_keys(User::AVAILABLE_PERMISSIONS);
        } elseif ($request->role === 'kasir') {
            $permissions = $request->permissions ?? [];
        }

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $request->role,
            'permissions' => $permissions,
            'allowance_type' => $request->allowance_type ?? 'none',
            'allowance_amount' => $request->allowance_amount ?? 0,
        ];
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);
        
        // Sync worksheets: only modify if the worksheet panel was submitted (role kasir)
        if ($request->role === 'kasir') {
            $worksheetIds = $request->input('worksheets', []);
            $user->worksheets()->sync($worksheetIds);
        } else {
            // Owner gets access to all worksheets implicitly, remove specific assignments
            $user->worksheets()->detach();
        }

        return back()->with('success', 'Data anggota tim berhasil diperbarui!');
    }

    public function toggleActive(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak bisa menonaktifkan akun sendiri!');
        }
        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Akun {$user->name} berhasil {$status}!");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak bisa menghapus akun sendiri!');
        }
        
        try {
            $user->delete();
            return back()->with('success', 'Anggota tim berhasil dihapus!');
        } catch (\Illuminate\Database\QueryException $e) {
            // Error code 23000 is for integrity constraint violations
            if ($e->getCode() == 23000) {
                return back()->with('error', 'Akun ini tidak dapat dihapus karena sudah memiliki riwayat data (transaksi, jadwal, atau kasir). Silakan nonaktifkan akun ini saja.');
            }
            return back()->with('error', 'Terjadi kesalahan saat menghapus data.');
        }
    }
}
