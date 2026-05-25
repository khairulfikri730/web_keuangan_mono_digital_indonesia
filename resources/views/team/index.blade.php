@extends('layouts.app')

@section('title', 'Manajemen Tim')
@section('page-title', 'Manajemen Tim')
@section('page-subtitle', 'Kelola akun Super Admin & Kasir')

@section('content')
<div class="space-y-6">

    {{-- Stats Bar --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="bg-slate-800/40 border border-slate-700/50 rounded-2xl p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-400"><i class="fas fa-users"></i></div>
            <div><p class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Total Anggota</p><p class="text-lg font-black text-white">{{ $stats['total'] }}</p></div>
        </div>
        <div class="bg-slate-800/40 border border-slate-700/50 rounded-2xl p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-400"><i class="fas fa-crown"></i></div>
            <div><p class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Super Admin</p><p class="text-lg font-black text-emerald-400">{{ $stats['owner'] }}</p></div>
        </div>
        <div class="bg-slate-800/40 border border-slate-700/50 rounded-2xl p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-purple-500/10 flex items-center justify-center text-purple-400"><i class="fas fa-cash-register"></i></div>
            <div><p class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Kasir</p><p class="text-lg font-black text-purple-400">{{ $stats['kasir'] }}</p></div>
        </div>
        <div class="bg-slate-800/40 border border-slate-700/50 rounded-2xl p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-500/10 flex items-center justify-center text-amber-400"><i class="fas fa-user-check"></i></div>
            <div><p class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Aktif</p><p class="text-lg font-black text-amber-400">{{ $stats['active'] }}</p></div>
        </div>
    </div>

    {{-- Header + Add Button --}}
    <div class="flex items-center justify-between">
        <p class="text-sm text-slate-400">{{ $stats['total'] }} anggota terdaftar</p>
        <button onclick="document.getElementById('modal-add').classList.remove('hidden')" class="bg-blue-600 hover:bg-blue-500 text-white px-5 py-2.5 rounded-xl font-bold text-sm transition-all shadow-lg shadow-blue-500/20 flex items-center gap-2">
            <i class="fas fa-user-plus"></i> Tambah Anggota
        </button>
    </div>

    {{-- User Card Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($users as $user)
        <div class="bg-slate-800/40 border border-slate-700/50 rounded-2xl p-5 hover:border-blue-500/20 transition-all group">
            <div class="flex items-start gap-4 mb-4">
                <div class="relative shrink-0">
                    <img src="{{ $user->avatarUrl() }}" class="w-14 h-14 rounded-full ring-2 ring-{{ $user->roleColor() }}-500/30 object-cover" alt="{{ $user->name }}">
                    <span class="absolute -bottom-0.5 -right-0.5 w-4 h-4 rounded-full border-2 border-slate-800 {{ $user->is_active ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <h4 class="font-black text-white text-sm truncate">{{ $user->name }}</h4>
                        @if($user->id === auth()->id())
                            <span class="px-1.5 py-0.5 rounded text-[8px] font-black uppercase bg-blue-500/20 text-blue-400 border border-blue-500/20 shrink-0">Anda</span>
                        @endif
                    </div>
                    <p class="text-[11px] text-slate-500 truncate mt-0.5">{{ $user->email }}</p>
                    @if($user->phone)<p class="text-[11px] text-slate-600 truncate mt-0.5"><i class="fas fa-phone-alt mr-1 text-[9px]"></i>{{ $user->phone }}</p>@endif
                    <div class="flex items-center gap-2 mt-1.5">
                        <span class="px-2 py-0.5 rounded-lg text-[9px] font-black uppercase bg-{{ $user->roleColor() }}-500/10 text-{{ $user->roleColor() }}-400 border border-{{ $user->roleColor() }}-500/20">{{ $user->roleBadge() }}</span>
                        @if($user->isKasir() && $user->permissions)<span class="text-[9px] font-bold text-slate-500">{{ count($user->permissions) }} akses</span>@endif
                    </div>
                </div>
            </div>
            @if($user->last_login_at)
            <p class="text-[9px] text-slate-600 mb-3"><i class="far fa-clock mr-1"></i> Login {{ $user->last_login_at->diffForHumans() }}</p>
            @endif
            <div class="flex gap-2 sm:opacity-100 sm:flex opacity-0 group-hover:opacity-100 transition-opacity">
                @if($user->id !== auth()->id())
                <button onclick="document.getElementById('modal-edit-{{ $user->id }}').classList.remove('hidden')" class="flex-1 bg-slate-700 hover:bg-blue-600 text-slate-300 hover:text-white text-[10px] font-black py-2 rounded-xl transition-all text-center">
                    <i class="fas fa-pen mr-1"></i> Edit
                </button>
                <form action="{{ route('team.toggle-active', $user) }}" method="POST" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full bg-slate-700 hover:bg-{{ $user->is_active ? 'orange' : 'emerald' }}-600 text-slate-300 hover:text-white text-[10px] font-black py-2 rounded-xl transition-all">
                        <i class="fas {{ $user->is_active ? 'fa-ban' : 'fa-check' }} mr-1"></i> {{ $user->is_active ? 'Nona' : 'Aktif' }}
                    </button>
                </form>
                <form action="{{ route('team.destroy', $user) }}" method="POST" onsubmit="return confirm('Hapus {{ $user->name }} secara permanen?')" class="shrink-0">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-9 h-9 bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-xl transition-all flex items-center justify-center">
                        <i class="fas fa-trash text-xs"></i>
                    </button>
                </form>
                @endif
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-16">
            <div class="w-20 h-20 bg-slate-800/50 rounded-full flex items-center justify-center mx-auto mb-4"><i class="fas fa-users text-3xl text-slate-600"></i></div>
            <p class="text-sm font-black text-white">Belum ada anggota tim</p>
            <p class="text-xs text-slate-500 mt-1">Tambahkan anggota pertama melalui tombol di atas</p>
        </div>
        @endforelse
    </div>

    <div>{{ $users->links('pagination::tailwind') }}</div>

    {{-- ADD MODAL --}}
    <div id="modal-add" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl w-full max-w-2xl max-h-[90vh] overflow-y-auto shadow-2xl border border-slate-200">
            <div class="p-5 border-b border-slate-100 flex justify-between items-center sticky top-0 bg-white rounded-t-3xl z-10">
                <h3 class="text-lg font-black text-slate-800">Tambah Anggota Baru</h3>
                <button onclick="document.getElementById('modal-add').classList.add('hidden')" class="w-8 h-8 bg-slate-100 text-slate-500 rounded-full hover:bg-slate-200 hover:text-slate-800 transition-colors"><i class="fas fa-times"></i></button>
            </div>
            <form action="{{ route('team.store') }}" method="POST" autocomplete="off" class="p-5 space-y-5" x-data="{ role: 'kasir', perms: {{ json_encode(\App\Models\User::DEFAULT_KASIR_PERMISSIONS) }}, wsheets: [] }">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-wider mb-2 block">Nama Lengkap <span class="text-red-400">*</span></label>
                        <input type="text" name="name" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-800 focus:border-blue-500 outline-none transition" placeholder="John Doe">
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-wider mb-2 block">Email <span class="text-red-400">*</span></label>
                        <input type="email" name="email" required autocomplete="off" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-800 focus:border-blue-500 outline-none transition" placeholder="john@example.com">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-wider mb-2 block">Nomor HP</label>
                        <input type="text" name="phone" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-800 focus:border-blue-500 outline-none transition" placeholder="0812xxxxxxxx">
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-wider mb-2 block">Role <span class="text-red-400">*</span></label>
                        <select name="role" x-model="role" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-800 focus:border-blue-500 outline-none transition">
                            <option value="kasir">Kasir</option>
                            <option value="owner">Super Admin (Akses Penuh)</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-wider mb-2 block">Password <span class="text-red-400">*</span></label>
                    <div class="grid grid-cols-2 gap-4">
                        <input type="password" name="password" required minlength="6" autocomplete="new-password" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-800 focus:border-blue-500 outline-none transition" placeholder="Minimal 6 karakter">
                        <input type="password" name="password_confirmation" required minlength="6" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-800 focus:border-blue-500 outline-none transition" placeholder="Ulangi password">
                    </div>
                </div>

                <div x-show="role === 'kasir'" x-transition class="space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-wider">Hak Akses Menu</label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" @change="perms = $el.checked ? {{ json_encode(array_keys(\App\Models\User::AVAILABLE_PERMISSIONS)) }} : []" class="w-3.5 h-3.5 rounded text-blue-500 border-slate-300 focus:ring-blue-500">
                            <span class="text-[10px] font-bold text-slate-500">Pilih Semua</span>
                        </label>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-[40vh] overflow-y-auto pr-1">
                        @foreach($permissionGroups as $groupName => $perms)
                        <div class="bg-slate-50 border border-slate-200 rounded-xl overflow-hidden">
                            <div class="px-3 py-2 bg-slate-100 border-b border-slate-200 flex items-center justify-between">
                                <span class="text-[10px] font-black text-slate-500 uppercase">{{ $groupName }}</span>
                            </div>
                            <div class="p-2 space-y-0.5">
                                @foreach($perms as $key => $label)
                                <label class="flex items-center gap-2.5 cursor-pointer hover:bg-white px-2 py-1 rounded-lg transition-colors">
                                    <input type="checkbox" name="permissions[]" value="{{ $key }}" x-model="perms" class="w-3.5 h-3.5 rounded text-blue-500 border-slate-300 focus:ring-blue-500">
                                    <span class="text-[12px] text-slate-600">{{ $label }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                @if($worksheets->count() > 0)
                <div x-show="role === 'kasir'" x-transition>
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-wider mb-2 block">Akses Cabang / Worksheet</label>
                    <div class="grid grid-cols-2 gap-2 bg-slate-50 border border-slate-200 rounded-xl p-3">
                        @foreach($worksheets as $ws)
                        <label class="flex items-center gap-2 cursor-pointer hover:bg-white px-2 py-1.5 rounded-lg transition-colors">
                            <input type="checkbox" name="worksheets[]" value="{{ $ws->id }}" x-model="wsheets" class="w-3.5 h-3.5 rounded text-emerald-500 border-slate-300 focus:ring-emerald-500">
                            <span class="text-xs text-slate-600">{{ $ws->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="flex items-center gap-2 p-3 bg-blue-50 rounded-xl border border-blue-100">
                    <i class="fas fa-info-circle text-blue-500 text-xs"></i>
                    <p class="text-[11px] text-blue-600 leading-tight">Super Admin otomatis memiliki akses penuh ke seluruh fitur tanpa perlu mengatur permission.</p>
                </div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-black py-3.5 rounded-xl transition-all shadow-lg shadow-blue-500/20 text-sm uppercase tracking-wider">
                    <i class="fas fa-user-plus mr-2"></i> Tambah Anggota
                </button>
            </form>
        </div>
    </div>

    {{-- EDIT MODALS (one per user) --}}
    @foreach($users as $user)
    @if($user->id !== auth()->id())
    <div id="modal-edit-{{ $user->id }}" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl w-full max-w-2xl max-h-[90vh] overflow-y-auto shadow-2xl border border-slate-200">
            <div class="p-5 border-b border-slate-100 flex justify-between items-center sticky top-0 bg-white rounded-t-3xl z-10">
                <h3 class="text-lg font-black text-slate-800">Edit: {{ $user->name }}</h3>
                <button onclick="document.getElementById('modal-edit-{{ $user->id }}').classList.add('hidden')" class="w-8 h-8 bg-slate-100 text-slate-500 rounded-full hover:bg-slate-200 hover:text-slate-800 transition-colors"><i class="fas fa-times"></i></button>
            </div>
            <form action="{{ route('team.update', $user) }}" method="POST" autocomplete="off" class="p-5 space-y-5" x-data="{ editRole: '{{ $user->role }}', editPerms: {{ json_encode($user->isKasir() ? ($user->permissions ?? []) : []) }}, editWsheets: {{ json_encode($user->worksheets->pluck('id')->toArray()) }} }">
                @csrf @method('PUT')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-wider mb-2 block">Nama Lengkap <span class="text-red-400">*</span></label>
                        <input type="text" name="name" value="{{ $user->name }}" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-800 focus:border-blue-500 outline-none transition">
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-wider mb-2 block">Email <span class="text-red-400">*</span></label>
                        <input type="email" name="email" value="{{ $user->email }}" required autocomplete="off" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-800 focus:border-blue-500 outline-none transition">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-wider mb-2 block">Nomor HP</label>
                        <input type="text" name="phone" value="{{ $user->phone }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-800 focus:border-blue-500 outline-none transition">
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-wider mb-2 block">Role <span class="text-red-400">*</span></label>
                        <select name="role" x-model="editRole" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-800 focus:border-blue-500 outline-none transition">
                            <option value="kasir">Kasir</option>
                            <option value="owner">Super Admin (Akses Penuh)</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-wider mb-2 block">Password Baru (opsional)</label>
                    <div class="grid grid-cols-2 gap-4">
                        <input type="password" name="password" minlength="8" autocomplete="new-password" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-800 focus:border-blue-500 outline-none transition" placeholder="Minimal 8 karakter">
                        <input type="password" name="password_confirmation" autocomplete="new-password" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-800 focus:border-blue-500 outline-none transition" placeholder="Ulangi password baru">
                    </div>
                </div>

                <div x-show="editRole === 'kasir'" x-transition class="space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-wider">Hak Akses Menu</label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" @change="editPerms = $el.checked ? {{ json_encode(array_keys(\App\Models\User::AVAILABLE_PERMISSIONS)) }} : []" class="w-3.5 h-3.5 rounded text-blue-500 border-slate-300 focus:ring-blue-500">
                            <span class="text-[10px] font-bold text-slate-500">Pilih Semua</span>
                        </label>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-[40vh] overflow-y-auto pr-1">
                        @foreach($permissionGroups as $groupName => $perms)
                        <div class="bg-slate-50 border border-slate-200 rounded-xl overflow-hidden">
                            <div class="px-3 py-2 bg-slate-100 border-b border-slate-200">
                                <span class="text-[10px] font-black text-slate-500 uppercase">{{ $groupName }}</span>
                            </div>
                            <div class="p-2 space-y-0.5">
                                @foreach($perms as $key => $label)
                                <label class="flex items-center gap-2.5 cursor-pointer hover:bg-white px-2 py-1 rounded-lg transition-colors">
                                    <input type="checkbox" name="permissions[]" value="{{ $key }}" x-model="editPerms" class="w-3.5 h-3.5 rounded text-blue-500 border-slate-300 focus:ring-blue-500">
                                    <span class="text-[12px] text-slate-600">{{ $label }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                @if($worksheets->count() > 0)
                <div x-show="editRole === 'kasir'" x-transition>
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-wider mb-2 block">Akses Cabang / Worksheet</label>
                    <div class="grid grid-cols-2 gap-2 bg-slate-50 border border-slate-200 rounded-xl p-3">
                        @foreach($worksheets as $ws)
                        <label class="flex items-center gap-2 cursor-pointer hover:bg-white px-2 py-1.5 rounded-lg transition-colors">
                            <input type="checkbox" name="worksheets[]" value="{{ $ws->id }}" x-model="editWsheets" class="w-3.5 h-3.5 rounded text-emerald-500 border-slate-300 focus:ring-emerald-500">
                            <span class="text-xs text-slate-600">{{ $ws->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="flex items-center gap-2 p-3 bg-blue-50 rounded-xl border border-blue-100">
                    <i class="fas fa-info-circle text-blue-500 text-xs"></i>
                    <p class="text-[11px] text-blue-600 leading-tight">Super Admin otomatis memiliki akses penuh ke seluruh fitur tanpa perlu mengatur permission.</p>
                </div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-black py-3.5 rounded-xl transition-all shadow-lg shadow-blue-500/20 text-sm uppercase tracking-wider">
                    <i class="fas fa-save mr-2"></i> Simpan Perubahan
                </button>
            </form>
        </div>
    </div>
    @endif
    @endforeach
</div>
@endsection
