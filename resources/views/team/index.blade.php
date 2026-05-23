@extends('layouts.app')

@section('title', 'Manajemen Tim')
@section('page-title', 'Manajemen Tim & Pengguna')
@section('page-subtitle', 'Kelola akses Super Admin dan Kasir')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
        <div class="card p-6 lg:p-8 sticky top-24 border border-slate-700/80 shadow-2xl relative overflow-hidden bg-slate-800/50 backdrop-blur-sm">
            <div class="absolute -right-10 -top-10 w-32 h-32 bg-blue-500/10 blur-3xl rounded-full pointer-events-none"></div>
            <h3 class="text-xl font-black text-white mb-6 flex items-center gap-2 relative z-10"><i class="fas fa-user-plus text-blue-400"></i> Tambah Anggota Baru</h3>
            <form action="{{ route('team.store') }}" method="POST" class="relative z-10" x-data="{ role: '{{ old('role', 'kasir') }}' }">
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
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Role <span class="text-red-400">*</span></label>
                        <div class="relative">
                            <select name="role" x-model="role" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl pl-4 pr-10 py-3 text-white text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 shadow-inner transition-all appearance-none cursor-pointer" required>
                                <option value="kasir">Kasir</option>
                                <option value="owner">Super Admin (Akses Penuh)</option>
                            </select>
                            <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-500 text-xs pointer-events-none"></i>
                        </div>
                    </div>

                    {{-- Permission Checkboxes (only for kasir) --}}
                    <div x-show="role === 'kasir'" x-transition 
                         x-data="{ 
                            allPerms: [],
                            toggleGroup(groupKeys, checked) {
                                groupKeys.forEach(k => {
                                    if (checked) {
                                        if (!this.allPerms.includes(k)) this.allPerms.push(k);
                                    } else {
                                        this.allPerms = this.allPerms.filter(x => x !== k);
                                    }
                                });
                            },
                            isGroupChecked(groupKeys) {
                                return groupKeys.every(k => this.allPerms.includes(k));
                            },
                            toggleAll(checked) {
                                if (checked) {
                                    this.allPerms = {{ json_encode(array_keys($availablePermissions)) }};
                                } else {
                                    this.allPerms = [];
                                }
                            }
                         }"
                         class="space-y-4">
                        
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest">Hak Akses Menu</label>
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="checkbox" @change="toggleAll($el.checked)" :checked="allPerms.length === @json(count($availablePermissions))" class="w-3.5 h-3.5 rounded text-blue-500 bg-slate-800 border-slate-600 focus:ring-blue-500 focus:ring-offset-0 transition-all">
                                <span class="text-[10px] font-bold text-slate-500 group-hover:text-blue-400 transition-colors uppercase tracking-wider">Pilih Semua</span>
                            </label>
                        </div>

                        <div class="space-y-4">
                            @foreach($permissionGroups as $groupName => $perms)
                            <div class="bg-slate-900/40 rounded-2xl border border-slate-700/50 overflow-hidden shadow-sm">
                                <div class="px-4 py-2.5 bg-slate-800/40 border-b border-slate-700/50 flex items-center justify-between">
                                    <span class="text-[10px] font-black text-slate-300 uppercase tracking-widest flex items-center gap-2">
                                        <i class="fas fa-layer-group text-blue-400/70"></i> {{ $groupName }}
                                    </span>
                                    @php $groupKeys = array_keys($perms); @endphp
                                    <label class="flex items-center gap-1.5 cursor-pointer group/toggle">
                                        <input type="checkbox" 
                                               @change="toggleGroup({{ json_encode($groupKeys) }}, $el.checked)" 
                                               :checked="isGroupChecked({{ json_encode($groupKeys) }})"
                                               class="w-3 h-3 rounded text-blue-500 bg-slate-900 border-slate-700 focus:ring-blue-500 focus:ring-offset-0">
                                        <span class="text-[9px] font-bold text-slate-500 group-hover/toggle:text-blue-400 transition-colors uppercase tracking-tighter">Grup</span>
                                    </label>
                                </div>
                                <div class="p-3 grid grid-cols-1 gap-1">
                                    @foreach($perms as $key => $label)
                                    @php $isSub = str_contains($key, '.'); @endphp
                                    <label class="flex items-center gap-3 cursor-pointer group/perm hover:bg-slate-800/30 px-2 py-1.5 rounded-lg transition-all {{ $isSub ? 'ml-6' : 'mt-1' }}">
                                        <input type="checkbox" name="permissions[]" value="{{ $key }}" 
                                               x-model="allPerms"
                                               class="w-4 h-4 rounded text-blue-500 bg-slate-800 border-slate-600 focus:ring-blue-500 focus:ring-offset-0 transition-transform active:scale-90">
                                        <span class="{{ $isSub ? 'text-[13px] text-slate-500' : 'text-sm font-bold text-slate-300' }} group-hover/perm:text-white transition-colors">{{ $label }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>
                        
                        <div class="flex items-center gap-2 p-3 bg-blue-500/5 rounded-xl border border-blue-500/10">
                            <i class="fas fa-info-circle text-blue-400 text-xs"></i>
                            <p class="text-[10px] text-slate-400 leading-tight">Super Admin (Owner) secara otomatis memiliki akses penuh ke seluruh fitur sistem.</p>
                        </div>
                    </div>


                    {{-- Worksheet Assignment (only for kasir) --}}
                    @if($worksheets->count() > 0)
                    <div x-show="role === 'kasir'" x-transition class="space-y-3">
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Akses Worksheet / Cabang</label>
                        <div class="grid grid-cols-1 gap-2 bg-slate-900/30 p-4 rounded-xl border border-slate-700/50">
                            @foreach($worksheets as $ws)
                            <label class="flex items-center gap-3 cursor-pointer group/ws hover:bg-slate-800/50 px-3 py-2 rounded-lg transition-all">
                                <input type="checkbox" name="worksheets[]" value="{{ $ws->id }}" class="w-4 h-4 rounded text-emerald-500 bg-slate-800 border-slate-600 focus:ring-emerald-500 focus:ring-offset-0">
                                <span class="text-sm text-slate-300 group-hover/ws:text-white transition-colors">{{ $ws->name }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endif

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
            <div class="card p-5 lg:p-6 flex flex-col sm:flex-row sm:items-center justify-between group hover:bg-slate-800 transition-all duration-300 border-l-2 border-transparent hover:border-blue-500 cursor-default gap-4 sm:gap-0"
                 x-data="{ showEdit: false }">
                <div class="flex items-center gap-5 flex-1">
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
                        <div class="mt-2 flex items-center flex-wrap gap-1.5">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold tracking-widest uppercase border {{ $user->role === 'owner' ? 'bg-orange-500/10 text-orange-400 border-orange-500/20' : 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20' }}">
                                <i class="{{ $user->role === 'owner' ? 'fas fa-crown text-[10px]' : 'fas fa-cash-register text-[10px]' }} mr-1.5"></i> {{ $user->role === 'owner' ? 'Super Admin' : 'Kasir' }}
                            </span>
                            @if($user->isKasir() && $user->permissions)
                                <span class="text-[10px] text-slate-500 font-bold">{{ count($user->permissions) }}/{{ count($availablePermissions) }} akses</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2 sm:opacity-0 group-hover:opacity-100 transition-opacity justify-end">
                    @if($user->id !== auth()->id())
                    <button @click="showEdit = !showEdit" class="w-10 h-10 rounded-xl bg-slate-800 hover:bg-slate-700 text-blue-400 flex items-center justify-center transition-all shadow-sm" title="Edit">
                        <i class="fas fa-pen text-sm"></i>
                    </button>
                    <form action="{{ route('team.toggle-active', $user) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-10 h-10 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 flex items-center justify-center transition-all shadow-sm" title="{{ $user->is_active ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}">
                            <i class="fas {{ $user->is_active ? 'fa-ban text-orange-400' : 'fa-check text-emerald-400' }}"></i>
                        </button>
                    </form>
                    <form action="{{ route('team.destroy', $user) }}" method="POST">
                        @csrf @method('DELETE')
                        <button type="button" 
                                onclick="Swal.fire({
                                    title: 'Hapus Anggota?',
                                    text: 'Akun {{ $user->name }} akan dihapus secara permanen.',
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonColor: '#ef4444',
                                    cancelButtonColor: '#64748b',
                                    confirmButtonText: 'Ya, Hapus!',
                                    cancelButtonText: 'Batal'
                                }).then((result) => {
                                    if (result.isConfirmed) this.closest('form').submit();
                                })"
                                class="w-10 h-10 rounded-xl bg-red-500/10 hover:bg-red-500/20 text-red-400 flex items-center justify-center transition-all border border-transparent hover:border-red-500/30 shadow-sm" title="Hapus Permanen">
                            <i class="fas fa-trash text-sm"></i>
                        </button>
                    </form>
                    @endif
                </div>

                {{-- Inline Edit Form --}}
                @if($user->id !== auth()->id())
                <div x-show="showEdit" x-transition class="w-full mt-4 pt-4 border-t border-slate-700/50" x-data="{ editRole: '{{ $user->role }}' }">
                    <form action="{{ route('team.update', $user) }}" method="POST">
                        @csrf @method('PUT')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Nama</label>
                                <input type="text" name="name" value="{{ $user->name }}" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-2 text-white text-sm focus:outline-none focus:border-blue-500 transition-all" required>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Email</label>
                                <input type="email" name="email" value="{{ $user->email }}" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-2 text-white text-sm focus:outline-none focus:border-blue-500 transition-all" required>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Role</label>
                                <select name="role" x-model="editRole" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-2 text-white text-sm focus:outline-none focus:border-blue-500 transition-all" required>
                                    <option value="kasir">Kasir</option>
                                    <option value="owner">Super Admin</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Password Baru (kosongkan jika tidak diubah)</label>
                                <input type="password" name="password" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-2 text-white text-sm focus:outline-none focus:border-blue-500 transition-all" minlength="6">
                                <input type="hidden" name="password_confirmation" value="">
                            </div>
                        </div>

                        {{-- Permission checkboxes for edit --}}
                        <div x-show="editRole === 'kasir'" x-transition 
                             x-data="{ 
                                allPerms: {{ json_encode($user->permissions ?? []) }},
                                toggleGroup(groupKeys, checked) {
                                    groupKeys.forEach(k => {
                                        if (checked) {
                                            if (!this.allPerms.includes(k)) this.allPerms.push(k);
                                        } else {
                                            this.allPerms = this.allPerms.filter(x => x !== k);
                                        }
                                    });
                                },
                                isGroupChecked(groupKeys) {
                                    return groupKeys.every(k => this.allPerms.includes(k));
                                },
                                toggleAll(checked) {
                                    if (checked) {
                                        this.allPerms = {{ json_encode(array_keys($availablePermissions)) }};
                                    } else {
                                        this.allPerms = [];
                                    }
                                }
                             }"
                             class="mt-6 space-y-4">
                            
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest">Hak Akses Menu</label>
                                <label class="flex items-center gap-2 cursor-pointer group">
                                    <input type="checkbox" @change="toggleAll($el.checked)" :checked="allPerms.length === @json(count($availablePermissions))" class="w-3.5 h-3.5 rounded text-blue-500 bg-slate-800 border-slate-600 focus:ring-blue-500 focus:ring-offset-0 transition-all">
                                    <span class="text-[10px] font-bold text-slate-500 group-hover:text-blue-400 transition-colors uppercase tracking-wider">Pilih Semua</span>
                                </label>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @foreach($permissionGroups as $groupName => $perms)
                                <div class="bg-slate-900/40 rounded-2xl border border-slate-700/50 overflow-hidden shadow-sm">
                                    <div class="px-4 py-2 bg-slate-800/40 border-b border-slate-700/50 flex items-center justify-between">
                                        <span class="text-[9px] font-black text-slate-300 uppercase tracking-widest flex items-center gap-2">
                                            <i class="fas fa-layer-group text-blue-400/70 text-[10px]"></i> {{ $groupName }}
                                        </span>
                                        @php $groupKeys = array_keys($perms); @endphp
                                        <label class="flex items-center gap-1.5 cursor-pointer group/toggle">
                                            <input type="checkbox" 
                                                   @change="toggleGroup({{ json_encode($groupKeys) }}, $el.checked)" 
                                                   :checked="isGroupChecked({{ json_encode($groupKeys) }})"
                                                   class="w-3 h-3 rounded text-blue-500 bg-slate-900 border-slate-700 focus:ring-blue-500 focus:ring-offset-0">
                                            <span class="text-[8px] font-bold text-slate-500 group-hover/toggle:text-blue-400 transition-colors uppercase tracking-tighter">Grup</span>
                                        </label>
                                    </div>
                                    <div class="p-2 grid grid-cols-1 gap-0.5">
                                        @foreach($perms as $key => $label)
                                        @php $isSub = str_contains($key, '.'); @endphp
                                        <label class="flex items-center gap-2.5 cursor-pointer group/perm hover:bg-slate-800/30 px-2 py-1 rounded-lg transition-all {{ $isSub ? 'ml-5' : 'mt-0.5' }}">
                                            <input type="checkbox" name="permissions[]" value="{{ $key }}" 
                                                   x-model="allPerms"
                                                   class="w-3.5 h-3.5 rounded text-blue-500 bg-slate-800 border-slate-600 focus:ring-blue-500 focus:ring-offset-0 transition-transform active:scale-90">
                                            <span class="{{ $isSub ? 'text-[11px] text-slate-500' : 'text-[12px] font-bold text-slate-300' }} group-hover/perm:text-white transition-colors leading-tight">{{ $label }}</span>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>


                        {{-- Worksheet assignment for edit (always rendered in DOM so checkboxes submit) --}}
                        @if($worksheets->count() > 0)
                        <div x-show="editRole === 'kasir'" class="mt-4">
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Akses Worksheet / Cabang</label>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-2 bg-slate-900/30 p-3 rounded-xl border border-slate-700/50">
                                @php
                                    $userWorksheetIds = $user->worksheets->pluck('id')->toArray();
                                @endphp
                                @foreach($worksheets as $ws)
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-slate-800/50 px-2 py-1.5 rounded-lg transition-all">
                                    <input type="checkbox" name="worksheets[]" value="{{ $ws->id }}" class="w-3.5 h-3.5 rounded text-emerald-500 bg-slate-800 border-slate-600 focus:ring-emerald-500 focus:ring-offset-0"
                                        {{ in_array($ws->id, $userWorksheetIds) ? 'checked' : '' }}>
                                    <span class="text-xs text-slate-300">{{ $ws->name }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <div class="mt-4 flex justify-end gap-2">
                            <button type="button" @click="showEdit = false" class="px-4 py-2 rounded-xl text-sm font-bold text-slate-300 bg-slate-700 hover:bg-slate-600 transition-all">Batal</button>
                            <button type="submit" class="px-4 py-2 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-500 transition-all shadow-lg shadow-blue-500/20">Simpan</button>
                        </div>
                    </form>
                </div>
                @endif
            </div>
            @endforeach
        </div>
        <div class="mt-4">{{ $users->links('pagination::tailwind') }}</div>
    </div>
</div>
@endsection
