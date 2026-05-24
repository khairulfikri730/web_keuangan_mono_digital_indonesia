<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            if (!Auth::user()->is_active) {
                Auth::logout();
                return back()->withErrors(['email' => 'Akun Anda telah dinonaktifkan.']);
            }

            Auth::user()->update([
                'last_login_at' => now(),
                'last_login_device' => $request->userAgent(),
                'last_login_ip' => $request->ip(),
            ]);

            \App\Models\ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'LOGIN',
                'description' => 'Berhasil login ke sistem',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            \App\Models\ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'LOGOUT',
                'description' => 'Logout dari sistem',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    // ===== FORGOT PASSWORD FLOW =====

    public function showForgot()
    {
        return view('auth.forgot-password');
    }

    public function sendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $key = 'otp:' . $request->email;
        if (RateLimiter::tooManyAttempts($key, 1)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors(['email' => 'Terlalu banyak permintaan. Silakan coba lagi dalam ' . $seconds . ' detik.']);
        }
        RateLimiter::hit($key, 60);

        $email = $request->email;
        $otpCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiredAt = now()->addMinutes(5);

        DB::table('password_reset_otps')->insert([
            'email' => $email,
            'otp_code' => $otpCode,
            'expired_at' => $expiredAt,
            'created_at' => now(),
        ]);

        Mail::to($email)->send(new OtpMail($email, $otpCode, $expiredAt->format('H:i')));

        session(['otp_email' => $email]);

        return redirect()->route('password.otp.verify')->with('success', 'Kode OTP telah dikirim ke email Anda.');
    }

    public function showVerifyOtp()
    {
        if (!session('otp_email')) {
            return redirect()->route('password.request');
        }
        return view('auth.verify-otp');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp_code' => 'required|array|size:6',
            'otp_code.*' => 'required|string|size:1',
        ]);

        $email = session('otp_email');
        if (!$email) {
            return redirect()->route('password.request');
        }

        $otpCode = implode('', $request->otp_code);

        $record = DB::table('password_reset_otps')
            ->where('email', $email)
            ->where('otp_code', $otpCode)
            ->whereNull('used_at')
            ->where('expired_at', '>', now())
            ->latest()
            ->first();

        if (!$record) {
            return back()->withErrors(['otp_code' => 'Kode OTP salah atau sudah expired.']);
        }

        DB::table('password_reset_otps')->where('id', $record->id)->update(['used_at' => now()]);

        session(['otp_verified_email' => $email]);
        session()->forget('otp_email');

        return redirect()->route('password.reset');
    }

    public function showReset()
    {
        if (!session('otp_verified_email')) {
            return redirect()->route('password.request');
        }
        return view('auth.reset-password');
    }

    public function resetPassword(Request $request)
    {
        $email = session('otp_verified_email');
        if (!$email) {
            return redirect()->route('password.request');
        }

        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::where('email', $email)->first();
        if (!$user) {
            return redirect()->route('password.request');
        }

        $user->update([
            'password' => $request->password
        ]);

        \App\Models\ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'RESET_PASSWORD',
            'description' => 'Reset password menggunakan OTP email',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        Auth::logoutOtherDevices($request->password);

        session()->forget('otp_verified_email');

        return redirect()->route('login')->with('success', 'Password berhasil direset. Silakan login dengan password baru.');
    }
}
