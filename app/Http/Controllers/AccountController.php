<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    public function profile()
    {
        $user = Auth::user();
        $activityLogs = \App\Models\ActivityLog::where('user_id', $user->id)
                            ->latest()
                            ->take(10)
                            ->get();
        return view('account.profile', compact('user', 'activityLogs'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|max:50',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
        ]);

        $user->update($validated);

        \App\Models\ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'UPDATE_PROFILE',
            'description' => 'Memperbarui informasi akun',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|max:2048',
        ]);

        $user = Auth::user();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->update(['avatar' => $path]);

        return back()->with('success', 'Foto profil berhasil diupload.');
    }

    public function showPassword()
    {
        return view('account.password');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password lama tidak sesuai.']);
        }

        $user->update(['password' => $request->password]);

        \App\Models\ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'CHANGE_PASSWORD',
            'description' => 'Mengubah password melalui pengaturan akun',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        Auth::logoutOtherDevices($request->password);

        $request->session()->regenerate();

        return redirect()->route('account.profile')->with('success', 'Password berhasil diubah. Semua device lain telah logout.');
    }


}
