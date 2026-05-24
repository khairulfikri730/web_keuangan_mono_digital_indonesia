<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - MONOFRAME</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>body{font-family:'Segoe UI',system-ui,sans-serif;}</style>
</head>
<body class="bg-slate-950 min-h-screen flex items-center justify-center p-4" style="background:radial-gradient(ellipse at top,#1e293b,#0f172a 70%);">
    <div class="w-full max-w-sm">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-blue-900/40">
                <i class="fas fa-lock text-white text-2xl"></i>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">Lupa Password</h1>
            <p class="text-sm text-slate-500 mt-2">Masukkan email terdaftar Anda</p>
        </div>

        <div class="bg-slate-900/80 border border-slate-800 rounded-[2rem] p-8 backdrop-blur-xl shadow-2xl">
            @if(session('success'))
            <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-bold p-3 rounded-xl mb-4">{{ session('success') }}</div>
            @endif

            @if($errors->any())
            <div class="bg-red-500/10 border border-red-500/20 text-red-400 text-xs font-bold p-3 rounded-xl mb-4">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('password.otp.send') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Email</label>
                    <div class="relative">
                        <i class="fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-slate-600 text-sm"></i>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus class="w-full bg-slate-950 border border-white/10 rounded-xl pl-11 pr-4 py-3.5 text-sm text-white placeholder-slate-600 focus:border-blue-500/50 focus:ring-4 focus:ring-blue-500/5 outline-none transition" placeholder="akun@email.com">
                    </div>
                </div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-black py-4 rounded-xl transition-all shadow-lg shadow-blue-900/40 text-sm uppercase tracking-wider">
                    <i class="fas fa-paper-plane mr-2"></i> Kirim Kode OTP
                </button>
            </form>

            <div class="text-center mt-6">
                <a href="{{ route('login') }}" class="text-[10px] font-bold text-slate-500 hover:text-white transition-colors">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali ke Login
                </a>
            </div>
        </div>
    </div>
</body>
</html>
