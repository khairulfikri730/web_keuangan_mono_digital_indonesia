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
            'role' => 'required|in:owner,kasir',
            'password' => 'required|string|min:6|confirmed',
            'permissions' => 'nullable|array',
            'permissions.*' => 'in:' . implode(',', array_keys(User::AVAILABLE_PERMISSIONS)),
        ]);

        // Owner always gets all permissions
        $permissions = $request->role === 'owner' ? array_keys(User::AVAILABLE_PERMISSIONS) : ($request->permissions ?? []);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $request->role,
            'permissions' => $permissions,
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
            'role' => 'required|in:owner,kasir',
            'password' => 'nullable|string|min:6|confirmed',
            'permissions' => 'nullable|array',
            'permissions.*' => 'in:' . implode(',', array_keys(User::AVAILABLE_PERMISSIONS)),
        ]);

        $permissions = $request->role === 'owner' ? array_keys(User::AVAILABLE_PERMISSIONS) : ($request->permissions ?? []);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $request->role,
            'permissions' => $permissions,
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
        $user->delete();
        return back()->with('success', 'Anggota tim berhasil dihapus!');
    }
}
