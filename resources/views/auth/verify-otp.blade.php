<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi OTP - MONOFRAME</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>body{font-family:'Segoe UI',system-ui,sans-serif;}</style>
</head>
<body class="bg-slate-950 min-h-screen flex items-center justify-center p-4" style="background:radial-gradient(ellipse at top,#1e293b,#0f172a 70%);">
    <div class="w-full max-w-sm" x-data="otpVerify()">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-blue-900/40">
                <i class="fas fa-shield-alt text-white text-2xl"></i>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">Verifikasi Kode</h1>
            <p class="text-sm text-slate-500 mt-2">Masukkan 6 digit kode OTP dari email</p>
        </div>

        <div class="bg-slate-900/80 border border-slate-800 rounded-[2rem] p-8 backdrop-blur-xl shadow-2xl">
            @if(session('success'))
            <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-bold p-3 rounded-xl mb-4 text-center">{{ session('success') }}</div>
            @endif

            @if($errors->any())
            <div class="bg-red-500/10 border border-red-500/20 text-red-400 text-xs font-bold p-3 rounded-xl mb-4 text-center">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('password.otp.check') }}" class="space-y-6">
                @csrf
                <div class="flex justify-center gap-3" x-ref="otpContainer">
                    @for($i = 0; $i < 6; $i++)
                    <input type="text" name="otp_code[]" maxlength="1" required
                        class="w-12 h-14 bg-slate-950 border-2 border-white/10 rounded-xl text-center text-xl font-black text-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition"
                        x-on:input="handleInput($event, {{ $i }})"
                        x-on:keydown.backspace="handleBackspace($event, {{ $i }})"
                        x-on:paste="handlePaste($event)">
                    @endfor
                </div>

                <div class="text-center">
                    <p class="text-[10px] text-slate-500">
                        <i class="far fa-clock mr-1 text-amber-400"></i> 
                        Kode berlaku <span class="font-bold text-white" x-text="timerText"></span>
                    </p>
                </div>

                <button type="submit" :disabled="timeLeft <= 0" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-black py-4 rounded-xl transition-all shadow-lg shadow-blue-900/40 text-sm uppercase tracking-wider disabled:opacity-40 disabled:cursor-not-allowed">
                    <i class="fas fa-check-circle mr-2"></i> Verifikasi Kode
                </button>
            </form>

            <div class="text-center mt-4 space-y-2">
                <form method="POST" action="{{ route('password.otp.send') }}">
                    @csrf
                    <input type="hidden" name="email" value="{{ session('otp_email') }}">
                    <button type="submit" class="text-[10px] font-bold text-slate-500 hover:text-blue-400 transition-colors" x-show="timeLeft <= 0">
                        <i class="fas fa-redo mr-1"></i> Kirim Ulang OTP
                    </button>
                </form>
                <a href="{{ route('login') }}" class="text-[10px] font-bold text-slate-500 hover:text-white transition-colors block">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali ke Login
                </a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('otpVerify', () => ({
                timeLeft: 300,
                timerText: '05:00',
                interval: null,

                init() {
                    this.startTimer();
                    this.$nextTick(() => {
                        const inputs = this.$refs.otpContainer.querySelectorAll('input');
                        if (inputs.length) inputs[0].focus();
                    });
                },

                startTimer() {
                    this.interval = setInterval(() => {
                        if (this.timeLeft <= 0) {
                            clearInterval(this.interval);
                            return;
                        }
                        this.timeLeft--;
                        const m = Math.floor(this.timeLeft / 60);
                        const s = this.timeLeft % 60;
                        this.timerText = String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
                    }, 1000);
                },

                handleInput(event, index) {
                    const value = event.target.value;
                    if (value && index < 5) {
                        const inputs = this.$refs.otpContainer.querySelectorAll('input');
                        inputs[index + 1].focus();
                    }
                },

                handleBackspace(event, index) {
                    if (!event.target.value && index > 0) {
                        const inputs = this.$refs.otpContainer.querySelectorAll('input');
                        inputs[index - 1].focus();
                    }
                },

                handlePaste(event) {
                    event.preventDefault();
                    const paste = (event.clipboardData || window.clipboardData).getData('text');
                    const digits = paste.replace(/\D/g, '').slice(0, 6);
                    const inputs = this.$refs.otpContainer.querySelectorAll('input');
                    digits.split('').forEach((d, i) => {
                        if (inputs[i]) inputs[i].value = d;
                    });
                    if (digits.length < 6 && inputs[digits.length]) {
                        inputs[digits.length].focus();
                    }
                }
            }));
        });
    </script>
</body>
</html>
