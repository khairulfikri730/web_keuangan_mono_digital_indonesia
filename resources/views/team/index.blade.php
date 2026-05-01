@extends('layouts.app')

@section('title', 'Manajemen Tim')
@section('page-title', 'Manajemen Tim & Pengguna')
@section('page-subtitle', 'Kelola akses owner dan operator kasir')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
        <div class="card p-6 lg:p-8 sticky top-24 border border-slate-700/80 shadow-2xl relative overflow-hidden bg-slate-800/50 backdrop-blur-sm">
            <div class="absolute -right-10 -top-10 w-32 h-32 bg-blue-500/10 blur-3xl rounded-full pointer-events-none"></div>
            <h3 class="text-xl font-black text-white mb-6 flex items-center gap-2 relative z-10"><i class="fas fa-user-plus text-blue-400"></i> Tambah Anggota Baru</h3>
            <form action="{{ route('team.store') }}" method="POST" class="relative z-10">
                @csrf
                <div class="space-y-5">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Nama Lengkap <span class="text-red-400">*</span></label>
                        <input type="text" name="name" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 shadow-inner transition-all" required value="{{ old('name') }}" placeholder="John Doe">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Email <span class="text-red-400">*</span></label>
                        <input type="email" name="email" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 shadow-inner transition-all" required value="{{ old('email') }}" placeholder="john@example.com">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Role Akses <span class="text-red-400">*</span></label>
                        <div class="relative">
                            <select name="role" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl pl-4 pr-10 py-3 text-white text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 shadow-inner transition-all appearance-none cursor-pointer" required>
                                <option value="operator" {{ old('role') == 'operator' ? 'selected' : '' }}>Operator (Kasir)</option>
                                <option value="owner" {{ old('role') == 'owner' ? 'selected' : '' }}>Owner (Akses Penuh)</option>
                            </select>
                            <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-500 text-xs pointer-events-none"></i>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Password <span class="text-red-400">*</span></label>
                        <input type="password" name="password" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 shadow-inner transition-all" required minlength="6" placeholder="••••••••">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Ulangi Password <span class="text-red-400">*</span></label>
                        <input type="password" name="password_confirmation" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 shadow-inner transition-all" required minlength="6" placeholder="••••••••">
                    </div>
                    <div class="pt-2">
                        <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-500 hover:to-blue-400 text-white font-bold py-3.5 rounded-xl transition-all shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 hover:scale-105 active:scale-95 flex items-center justify-center gap-2">
                            TAMBAH ANGGOTA
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="grid gap-4">
            @foreach($users as $user)
            <div class="card p-5 lg:p-6 flex flex-col sm:flex-row sm:items-center justify-between group hover:bg-slate-800 transition-all duration-300 border-l-2 border-transparent hover:border-blue-500 cursor-default gap-4 sm:gap-0">
                <div class="flex items-center gap-5">
                    <div class="relative">
                        <div class="w-14 h-14 rounded-full flex items-center justify-center text-white font-black text-xl shadow-lg ring-4 ring-slate-800 group-hover:ring-slate-700 transition-all
                            {{ $user->role === 'owner' ? 'bg-gradient-to-br from-yellow-500 to-orange-600 shadow-orange-900/30' : 'bg-gradient-to-br from-blue-500 to-indigo-600 shadow-blue-900/30' }}">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        @if($user->is_active)
                            <div class="absolute bottom-0 right-0 w-3.5 h-3.5 bg-emerald-400 border-2 border-slate-800 rounded-full" title="Aktif"></div>
                        @else
                            <div class="absolute bottom-0 right-0 w-3.5 h-3.5 bg-red-500 border-2 border-slate-800 rounded-full" title="Nonaktif"></div>
                        @endif
                    </div>
                    <div>
                        <div class="flex items-center gap-3 mb-0.5">
                            <h4 class="font-bold text-white text-lg group-hover:text-blue-400 transition-colors">{{ $user->name }}</h4>
                            @if($user->id === auth()->id()) 
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold tracking-widest uppercase bg-blue-500/20 text-blue-400 border border-blue-500/30">Anda</span> 
                            @endif
                            @if(!$user->is_active) 
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold tracking-widest uppercase bg-red-500/20 text-red-400 border border-red-500/30">Nonaktif</span> 
                            @endif
                        </div>
                        <p class="text-sm text-slate-400">{{ $user->email }}</p>
                        <div class="mt-2">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold tracking-widest uppercase border {{ $user->role === 'owner' ? 'bg-orange-500/10 text-orange-400 border-orange-500/20' : 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20' }}">
                                <i class="{{ $user->role === 'owner' ? 'fas fa-crown text-[10px]' : 'fas fa-cash-register text-[10px]' }} mr-1.5"></i> {{ $user->role }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2 sm:opacity-0 group-hover:opacity-100 transition-opacity justify-end">
                    @if($user->id !== auth()->id())
                    <form action="{{ route('team.toggle-active', $user) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-10 h-10 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 flex items-center justify-center transition-all shadow-sm" title="{{ $user->is_active ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}">
                            <i class="fas {{ $user->is_active ? 'fa-ban text-orange-400' : 'fa-check text-emerald-400' }}"></i>
                        </button>
                    </form>
                    <form action="{{ route('team.destroy', $user) }}" method="POST" onsubmit="return confirm('Yakin hapus akun ini secara permanen?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="w-10 h-10 rounded-xl bg-red-500/10 hover:bg-red-500/20 text-red-400 flex items-center justify-center transition-all border border-transparent hover:border-red-500/30 shadow-sm" title="Hapus Permanen">
                            <i class="fas fa-trash text-sm"></i>
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        <div class="mt-4">{{ $users->links('pagination::tailwind') }}</div>
    </div>
</div>
@endsection
