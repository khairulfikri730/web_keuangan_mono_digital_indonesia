@extends('layouts.app')
@section('title', 'Ganti Password')
@section('page-title', 'Ganti Password')

@section('content')
<div x-data="{ showCurrent: false, showNew: false, showConfirm: false }" class="max-w-xl mx-auto">
    <div class="bg-slate-800/40 border border-slate-700/50 rounded-[2rem] p-8 shadow-xl">
        <h3 class="text-sm font-black text-slate-400 uppercase tracking-wider mb-6 flex items-center gap-3">
            <span class="w-2 h-6 bg-amber-500 rounded-full"></span> Ganti Password
        </h3>
        <form method="POST" action="{{ route('account.password.update') }}" class="space-y-5">
            @csrf @method('PUT')

            <div>
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-2 block">Password Lama</label>
                <div class="relative">
                    <input :type="showCurrent ? 'text' : 'password'" name="current_password" required class="w-full bg-slate-900/60 border border-white/5 rounded-xl px-4 py-3 pr-12 text-sm text-white focus:border-amber-500/50 outline-none transition">
                    <button type="button" @click="showCurrent = !showCurrent" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-white">
                        <i class="fas" :class="showCurrent ? 'fa-eye-slash' : 'fa-eye'"></i>
                    </button>
                </div>
                @error('current_password')<p class="text-red-400 text-[10px] mt-1 font-bold">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-2 block">Password Baru</label>
                <div class="relative">
                    <input :type="showNew ? 'text' : 'password'" name="password" required minlength="8" class="w-full bg-slate-900/60 border border-white/5 rounded-xl px-4 py-3 pr-12 text-sm text-white focus:border-amber-500/50 outline-none transition">
                    <button type="button" @click="showNew = !showNew" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-white">
                        <i class="fas" :class="showNew ? 'fa-eye-slash' : 'fa-eye'"></i>
                    </button>
                </div>
                @error('password')<p class="text-red-400 text-[10px] mt-1 font-bold">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-2 block">Konfirmasi Password Baru</label>
                <div class="relative">
                    <input :type="showConfirm ? 'text' : 'password'" name="password_confirmation" required minlength="8" class="w-full bg-slate-900/60 border border-white/5 rounded-xl px-4 py-3 pr-12 text-sm text-white focus:border-amber-500/50 outline-none transition">
                    <button type="button" @click="showConfirm = !showConfirm" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-white">
                        <i class="fas" :class="showConfirm ? 'fa-eye-slash' : 'fa-eye'"></i>
                    </button>
                </div>
            </div>

            <div class="bg-amber-500/5 border border-amber-500/20 rounded-2xl p-4 text-[10px] text-amber-400 font-bold">
                <i class="fas fa-shield-alt mr-2"></i> Mengganti password akan otomatis logout dari semua device lain.
            </div>

            <button type="submit" class="w-full bg-amber-500 hover:bg-amber-400 text-white font-black py-4 rounded-xl transition-all text-sm shadow-lg shadow-amber-600/20">
                <i class="fas fa-check-circle mr-2"></i> Perbarui Password
            </button>
        </form>
    </div>
</div>
@endsection
