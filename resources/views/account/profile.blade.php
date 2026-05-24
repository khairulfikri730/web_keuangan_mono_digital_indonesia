@extends('layouts.app')
@section('title', 'Pengaturan Akun')
@section('page-title', 'Pengaturan Akun')

@section('content')
<div x-data="profilePage()" class="max-w-3xl mx-auto space-y-8">
    {{-- Profile Card --}}
    <div class="bg-slate-800/40 border border-slate-700/50 rounded-[2rem] p-8 shadow-xl">
        <div class="flex flex-col sm:flex-row items-center gap-6">
            {{-- Avatar --}}
            <div class="relative group">
                <img src="{{ $user->avatarUrl() }}" class="w-24 h-24 rounded-full ring-4 ring-{{ $user->roleColor() }}-500/30 object-cover" alt="{{ $user->name }}">
                <button @click="avatarOpen = true" class="absolute inset-0 bg-black/50 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                    <i class="fas fa-camera text-white text-lg"></i>
                </button>
            </div>
            <div class="text-center sm:text-left">
                <h2 class="text-xl font-black text-white">{{ $user->name }}</h2>
                <div class="flex items-center gap-2 mt-1 justify-center sm:justify-start">
                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-{{ $user->roleColor() }}-500/10 text-{{ $user->roleColor() }}-400 border border-{{ $user->roleColor() }}-500/20">
                        {{ $user->roleBadge() }}
                    </span>
                    <span class="text-[10px] text-slate-500">{{ $user->email }}</span>
                </div>
                <p class="text-[10px] text-slate-600 mt-2">
                    <i class="fas fa-clock mr-1"></i> Terakhir login: {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Belum pernah' }}
                    @if($user->last_login_device)
                        <span class="ml-2">| {{ Str::limit($user->last_login_device, 40) }}</span>
                    @endif
                </p>
            </div>
        </div>
    </div>

    {{-- Edit Profile Form --}}
    <div class="bg-slate-800/40 border border-slate-700/50 rounded-[2rem] p-8 shadow-xl">
        <h3 class="text-sm font-black text-slate-400 uppercase tracking-wider mb-6 flex items-center gap-3">
            <span class="w-2 h-6 bg-blue-500 rounded-full"></span> Edit Informasi Akun
        </h3>
        <form method="POST" action="{{ route('account.profile.update') }}" class="space-y-5">
            @csrf @method('PUT')
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-2 block">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full bg-slate-900/60 border border-white/5 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-600 focus:border-blue-500/50 outline-none transition">
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-2 block">Username</label>
                    <input type="text" name="username" value="{{ old('username', $user->username) }}" class="w-full bg-slate-900/60 border border-white/5 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-600 focus:border-blue-500/50 outline-none transition">
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-2 block">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full bg-slate-900/60 border border-white/5 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-600 focus:border-blue-500/50 outline-none transition">
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-2 block">Nomor HP</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full bg-slate-900/60 border border-white/5 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-600 focus:border-blue-500/50 outline-none transition">
                </div>
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-3 rounded-xl font-bold text-sm transition-all shadow-lg shadow-blue-600/20">
                <i class="fas fa-save mr-2"></i> Simpan Perubahan
            </button>
        </form>
    </div>

    {{-- Quick Links & Activity Log --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="space-y-4">
            <a href="{{ route('account.password') }}" class="bg-slate-800/40 border border-slate-700/50 rounded-2xl p-5 hover:border-amber-500/30 transition-all group block">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-amber-500/10 flex items-center justify-center text-amber-400 group-hover:bg-amber-600 group-hover:text-white transition-all">
                        <i class="fas fa-key text-lg"></i>
                    </div>
                    <div>
                        <p class="text-sm font-black text-white">Ganti Password</p>
                        <p class="text-[10px] text-slate-500 mt-0.5">Perbarui password akun Anda</p>
                    </div>
                    <i class="fas fa-chevron-right text-slate-600 ml-auto"></i>
                </div>
            </a>


        </div>

        {{-- Activity Log --}}
        <div class="bg-slate-800/40 border border-slate-700/50 rounded-2xl p-6 shadow-xl">
            <h3 class="text-xs font-black text-slate-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                <i class="fas fa-history text-blue-500"></i> Riwayat Aktivitas
            </h3>
            <div class="space-y-4 max-h-[250px] overflow-y-auto custom-scrollbar pr-2">
                @forelse($activityLogs as $log)
                <div class="border-b border-slate-700/50 pb-3 last:border-0 last:pb-0">
                    <div class="flex justify-between items-start mb-1">
                        <span class="text-xs font-black text-white">{{ $log->action }}</span>
                        <span class="text-[9px] text-slate-500 font-bold">{{ $log->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-[10px] text-slate-400">{{ $log->description }}</p>
                    <p class="text-[9px] text-slate-600 mt-1"><i class="fas fa-desktop mr-1"></i>{{ Str::limit($log->user_agent, 30) }} â€¢ {{ $log->ip_address }}</p>
                </div>
                @empty
                <p class="text-[10px] text-slate-500 italic text-center py-4">Belum ada aktivitas tercatat.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Avatar Upload Modal --}}
    <div x-show="avatarOpen" x-transition class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display:none;">
        <div @click.away="avatarOpen=false" class="bg-white rounded-3xl w-full max-w-sm shadow-2xl border border-slate-200 p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-black text-slate-800">Upload Foto Profil</h3>
                <button @click="avatarOpen=false" class="w-8 h-8 bg-slate-100 rounded-full text-slate-500 hover:bg-slate-200"><i class="fas fa-times"></i></button>
            </div>
            <form method="POST" action="{{ route('account.avatar') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label class="block text-xs font-black text-slate-600 uppercase tracking-wider mb-2">Pilih Foto</label>
                    <input type="file" name="avatar" accept="image/*" required class="w-full text-sm text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-blue-500 file:text-white hover:file:bg-blue-400">
                </div>
                <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-black py-3 rounded-xl transition-all">Upload</button>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('profilePage', () => ({ avatarOpen: false }));
    });
</script>
@endsection
