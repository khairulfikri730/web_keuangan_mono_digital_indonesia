<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — KasirPro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>* { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="min-h-screen bg-slate-950 flex items-center justify-center p-4" style="background: radial-gradient(ellipse at 50% 0%, rgba(59,130,246,0.15) 0%, #0f172a 70%)">

    <div class="w-full max-w-md">
        {{-- Logo --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-600 rounded-2xl mb-4 shadow-xl shadow-blue-500/30">
                <i class="fas fa-cash-register text-white text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-white">KasirPro</h1>
            <p class="text-slate-400 text-sm mt-1">Sistem Kasir & Manajemen Bisnis</p>
        </div>

        {{-- Card --}}
        <div class="bg-slate-800 rounded-2xl border border-slate-700 p-8 shadow-2xl">
            <h2 class="text-lg font-semibold text-white mb-6">Masuk ke Akun</h2>

            @if($errors->any())
            <div class="mb-5 bg-red-500/15 border border-red-500/30 text-red-400 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
                <i class="fas fa-circle-exclamation"></i>
                {{ $errors->first() }}
            </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Email</label>
                    <div class="relative">
                        <i class="fas fa-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 text-sm"></i>
                        <input type="email" name="email" value="{{ old('email') }}" required
                            class="w-full bg-slate-900 border border-slate-600 rounded-xl pl-10 pr-4 py-2.5 text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all text-sm"
                            placeholder="email@example.com">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Password</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 text-sm"></i>
                        <input type="password" name="password" id="password" required
                            class="w-full bg-slate-900 border border-slate-600 rounded-xl pl-10 pr-12 py-2.5 text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all text-sm"
                            placeholder="••••••••">
                        <button type="button" onclick="togglePass()" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300">
                            <i class="fas fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="remember" id="remember" class="w-4 h-4 rounded border-slate-600 bg-slate-900 text-blue-600">
                    <label for="remember" class="text-sm text-slate-400">Ingat saya</label>
                </div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-blue-500/25 text-sm">
                    <i class="fas fa-right-to-bracket mr-2"></i>Masuk
                </button>
            </form>

            <div class="mt-6 pt-5 border-t border-slate-700 space-y-1">
                <p class="text-xs text-slate-500 text-center mb-3">Demo Credentials</p>
                <div class="grid grid-cols-2 gap-2">
                    <button onclick="fillLogin('owner@kasirpro.com')" class="text-xs bg-slate-700 hover:bg-slate-600 text-slate-300 px-3 py-2 rounded-lg transition-colors">
                        <i class="fas fa-crown text-yellow-400 mr-1"></i>Owner
                    </button>
                    <button onclick="fillLogin('operator@kasirpro.com')" class="text-xs bg-slate-700 hover:bg-slate-600 text-slate-300 px-3 py-2 rounded-lg transition-colors">
                        <i class="fas fa-user text-blue-400 mr-1"></i>Operator
                    </button>
                </div>
            </div>
        </div>
        <p class="text-center text-xs text-slate-600 mt-6">© {{ date('Y') }} KasirPro. All rights reserved.</p>
    </div>

    <script>
        function fillLogin(email) {
            document.querySelector('input[name=email]').value = email;
            document.querySelector('input[name=password]').value = 'password';
        }
        function togglePass() {
            const p = document.getElementById('password');
            const i = document.getElementById('eyeIcon');
            if (p.type === 'password') { p.type = 'text'; i.className = 'fas fa-eye-slash'; }
            else { p.type = 'password'; i.className = 'fas fa-eye'; }
        }
    </script>
</body>
</html>
