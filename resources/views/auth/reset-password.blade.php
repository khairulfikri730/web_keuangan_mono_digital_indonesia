<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - MONOFRAME</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>body{font-family:'Segoe UI',system-ui,sans-serif;}</style>
</head>
<body class="bg-slate-950 min-h-screen flex items-center justify-center p-4" style="background:radial-gradient(ellipse at top,#1e293b,#0f172a 70%);">
    <div class="w-full max-w-sm" x-data="{ showPassword: false, showConfirm: false }">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-emerald-900/40">
                <i class="fas fa-key text-white text-2xl"></i>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">Password Baru</h1>
            <p class="text-sm text-slate-500 mt-2">Buat password baru untuk akun Anda</p>
        </div>

        <div class="bg-slate-900/80 border border-slate-800 rounded-[2rem] p-8 backdrop-blur-xl shadow-2xl">
            @if($errors->any())
            <div class="bg-red-500/10 border border-red-500/20 text-red-400 text-xs font-bold p-3 rounded-xl mb-4">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Password Baru</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-600 text-sm"></i>
                        <input :type="showPassword ? 'text' : 'password'" name="password" required minlength="8" class="w-full bg-slate-950 border border-white/10 rounded-xl pl-11 pr-12 py-3.5 text-sm text-white placeholder-slate-600 focus:border-emerald-500/50 outline-none transition" placeholder="Minimal 8 karakter">
                        <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-white">
                            <i class="fas" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Konfirmasi Password</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-600 text-sm"></i>
                        <input :type="showConfirm ? 'text' : 'password'" name="password_confirmation" required minlength="8" class="w-full bg-slate-950 border border-white/10 rounded-xl pl-11 pr-12 py-3.5 text-sm text-white placeholder-slate-600 focus:border-emerald-500/50 outline-none transition" placeholder="Ulangi password">
                        <button type="button" @click="showConfirm = !showConfirm" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-white">
                            <i class="fas" :class="showConfirm ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-black py-4 rounded-xl transition-all shadow-lg shadow-emerald-900/40 text-sm uppercase tracking-wider">
                    <i class="fas fa-check-circle mr-2"></i> Simpan Password Baru
                </button>
            </form>
        </div>
    </div>
</body>
</html>
