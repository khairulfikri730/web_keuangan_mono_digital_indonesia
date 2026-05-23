{{-- Premium Redesign Buka Shift Modal --}}
<div x-data="{ 
        physicCash: {{ (int)$laciBalance }}, 
        systemCash: {{ (int)$laciBalance }},
        notes: '',
        get diff() { return (parseInt(this.physicCash) || 0) - this.systemCash },
        get formattedPhysic() { 
            return 'Rp ' + (parseInt(this.physicCash) || 0).toLocaleString('id-ID');
        },
        updatePhysic(val) {
            let num = val.replace(/\D/g, '');
            this.physicCash = num ? parseInt(num) : 0;
        }
    }" 
    class="fixed inset-0 z-[100] flex items-center justify-center p-4"
    x-show="showOpenModal"
    x-cloak
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0">
    
    {{-- Backdrop Blur --}}
    <div class="absolute inset-0 bg-navy-900/60 backdrop-blur-md" @click="showOpenModal = false"></div>

    <div class="glass-modal rounded-[2.5rem] overflow-hidden flex flex-col w-full max-w-lg lg:max-w-xl max-h-[95vh] shadow-2xl border border-white/5 relative z-10 max-h-[90vh] overflow-y-auto scrollbar-hide ">
        
        <!-- Decoration Glows -->
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-blue-600/10 blur-[80px] rounded-full pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-emerald-600/10 blur-[80px] rounded-full pointer-events-none"></div>

        <!-- HEADER -->
        <div class="px-8 pt-8 pb-6 text-center relative">
            <div class="w-16 h-16 bg-blue-500/10 rounded-2xl flex items-center justify-center mx-auto mb-4 neon-border-blue transition-transform hover:scale-110 duration-500">
                <i class="fas fa-cash-register text-blue-400 text-2xl neon-text-blue"></i>
            </div>
            <h2 class="text-2xl font-black text-white tracking-tight poppins uppercase italic">Buka Shift Baru</h2>
            <p class="text-slate-400 text-sm mt-1 font-medium">Masukkan jumlah modal kas awal di laci.</p>
            <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-24 h-0.5 bg-gradient-to-r from-transparent via-blue-500 to-transparent opacity-20"></div>
        </div>

        <!-- MODAL CONTENT (Scrollable) -->
        <div class="px-8 py-4 overflow-y-auto custom-scrollbar space-y-6">
            
            <!-- SECTION 1 â€” AUTO SYNC INFO -->
            <div class="bg-emerald-500/5 border border-emerald-500/20 rounded-3xl p-5 flex items-center gap-5 group hover:bg-emerald-500/10 transition-all duration-300 neon-border-green">
                <div class="w-12 h-12 bg-emerald-500/10 rounded-2xl flex items-center justify-center text-emerald-400 shrink-0 shadow-lg shadow-emerald-500/10 group-hover:scale-110 transition-transform">
                    <i class="fas fa-sync-alt animate-spin-slow"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-2 mb-1">
                        <h4 class="text-xs font-black text-emerald-400 uppercase tracking-[0.15em]">Disinkronkan dari Shift Terakhir</h4>
                        <span class="bg-emerald-500 text-[8px] font-black px-2 py-0.5 rounded-full text-emerald-950 uppercase tracking-tighter">Sinkron Laci Aktif</span>
                    </div>
                    <p class="text-sm font-bold text-white truncate">{{ auth()->user()->name }} â€¢ {{ now()->translatedFormat('d M Y') }} â€¢ {{ now()->format('H:i') }}</p>
                    <p class="text-[10px] text-slate-500 font-bold uppercase tracking-wider flex items-center gap-1 mt-0.5">
                        <i class="fas fa-check-circle text-emerald-500"></i> Sinkronisasi laci kasir otomatis
                    </p>
                </div>
            </div>

            <form action="{{ route('shifts.open') }}" method="POST" id="bukaShiftFormMain">
                @csrf
                <input type="hidden" name="opening_cash" :value="physicCash">
                
                <div class="space-y-6">
                    <!-- SECTION 2 â€” PILIH KASIR (MULTI) -->
                    @if(auth()->user()->isOwner())
                    <div class="space-y-2" x-data="{ selectedUsers: [{{ auth()->id() }}] }">
                        <div class="flex items-center justify-between">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] ml-2">Pilih User Bertugas</label>
                            <span class="text-[10px] font-bold text-blue-400 bg-blue-500/10 px-2 py-0.5 rounded-full border border-blue-500/20" x-text="selectedUsers.length + ' user dipilih'"></span>
                        </div>
                        <div class="bg-slate-900 border border-white/10 rounded-2xl overflow-hidden divide-y divide-white/5">
                            @foreach($users as $u)
                            <label class="flex items-center gap-4 px-5 py-3.5 cursor-pointer hover:bg-slate-800/60 transition-all group"
                                   :class="selectedUsers.includes({{ $u->id }}) ? 'bg-blue-500/5' : ''">
                                <input type="checkbox"
                                       name="user_ids[]"
                                       value="{{ $u->id }}"
                                       x-model.number="selectedUsers"
                                       :checked="selectedUsers.includes({{ $u->id }})"
                                       {{ $u->id == auth()->id() ? 'checked' : '' }}
                                       class="w-4 h-4 rounded text-blue-500 bg-slate-800 border-slate-600 focus:ring-blue-500 focus:ring-offset-0 shrink-0">
                                <div class="flex items-center gap-3 flex-1">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-black shrink-0
                                        {{ $u->role === 'owner' ? 'bg-gradient-to-br from-orange-500 to-amber-600' : 'bg-gradient-to-br from-blue-500 to-indigo-600' }}">
                                        {{ strtoupper(substr($u->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-white group-hover:text-blue-300 transition-colors">{{ $u->name }}</p>
                                        <p class="text-[9px] font-bold uppercase tracking-wider {{ $u->role === 'owner' ? 'text-orange-400' : 'text-blue-400' }}">
                                            <i class="{{ $u->role === 'owner' ? 'fas fa-crown' : 'fas fa-cash-register' }} mr-1"></i>{{ $u->role }}
                                        </p>
                                    </div>
                                </div>
                                <div :class="selectedUsers.includes({{ $u->id }}) ? 'text-blue-400 opacity-100' : 'opacity-0'"
                                     class="transition-all">
                                    <i class="fas fa-check-circle text-sm"></i>
                                </div>
                            </label>
                            @endforeach
                        </div>
                        <p class="text-[10px] text-slate-600 ml-2 flex items-center gap-1">
                            <i class="fas fa-info-circle text-blue-500/50"></i>
                            Semua user yang dipilih dapat menggunakan shift ini secara bersamaan.
                        </p>
                    </div>
                    @endif


                    <!-- SECTION 3 â€” KAS AWAL SISTEM -->
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] ml-2">Kas Awal Sistem (Auto Sync)</label>
                        <div class="relative bg-slate-900/80 border border-white/5 rounded-2xl px-5 py-4 flex items-center justify-between shadow-inner">
                            <span class="text-xl font-black text-white/40 tracking-tight">Rp {{ number_format($laciBalance ?? 0, 0, ',', '.') }}</span>
                            <i class="fas fa-lock text-slate-700 text-sm"></i>
                            <div class="absolute -bottom-3 right-5 bg-slate-800 px-2 text-[9px] font-bold text-slate-500 italic rounded border border-white/5">
                                Nominal tersimpan di database
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 4 â€” INPUT UANG FISIK -->
                    <div class="space-y-2 pt-2">
                        <label class="text-[10px] font-black text-blue-400 uppercase tracking-[0.2em] ml-2 flex items-center gap-2">
                            Uang Fisik di Laci Saat Ini
                            <span class="w-1 h-1 rounded-full bg-blue-500 animate-pulse"></span>
                        </label>
                        <div class="relative group">
                            <input type="text" 
                                   :value="formattedPhysic" 
                                   @input="updatePhysic($event.target.value)"
                                   class="w-full bg-blue-500/5 border border-blue-500/20 rounded-2xl px-6 py-5 text-2xl font-black text-white focus:outline-none neon-border-blue transition-all focus:bg-blue-500/10 tracking-tight"
                                   placeholder="Rp 0">
                            <div class="absolute right-6 top-1/2 -translate-y-1/2 text-blue-500/30">
                                <i class="fas fa-keyboard text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 5 â€” VALIDASI SELISIH -->
                    <div x-show="diff != 0" 
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="bg-red-500/5 border border-red-500/20 rounded-3xl p-5 neon-border-red">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-red-500/10 rounded-xl flex items-center justify-center text-red-500">
                                    <i class="fas fa-exclamation-triangle text-xs"></i>
                                </div>
                                <span class="text-[10px] font-black text-red-400 uppercase tracking-widest">Selisih Terdeteksi</span>
                            </div>
                            <span class="bg-red-500/20 text-red-400 text-[8px] font-black px-2 py-0.5 rounded border border-red-500/30 uppercase">Tidak Sesuai</span>
                        </div>
                        <div class="flex items-baseline justify-between">
                            <div>
                                <p class="text-3xl font-black text-red-500 tracking-tighter" x-text="(diff < 0 ? '- ' : '+ ') + 'Rp ' + Math.abs(diff).toLocaleString('id-ID')"></p>
                                <p class="text-[10px] text-slate-500 font-bold mt-1 uppercase tracking-wide">Uang fisik tidak sesuai dengan kas awal sistem</p>
                            </div>
                            <i class="fas fa-chart-line-down text-red-500/20 text-3xl"></i>
                        </div>
                    </div>

                    <!-- SECTION 5.5 â€” WAKTU BUKA SHIFT (Opsional / Testing) -->
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] ml-2">Waktu Buka Shift (Opsional)</label>
                        <div class="relative">
                            <input type="datetime-local" name="opened_at"
                                   class="w-full bg-slate-900 border border-white/5 rounded-2xl px-5 py-4 text-sm text-white focus:outline-none focus:border-blue-500/30 transition-all cursor-text"
                                   title="Biarkan kosong untuk menggunakan waktu saat ini">
                            <div class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-500 pointer-events-none">
                                <i class="fas fa-calendar-alt text-lg"></i>
                            </div>
                        </div>
                        <p class="text-[9px] text-slate-600 ml-2 italic">Kosongkan untuk menggunakan waktu sekarang. Isi hanya jika ingin backdate shift.</p>
                    </div>

                    <!-- SECTION 6 â€” CATATAN -->
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] ml-2">Catatan Pembukaan</label>
                        <div class="relative">
                            <textarea name="notes" x-model="notes" 
                                      maxlength="200"
                                      class="w-full bg-slate-900 border border-white/5 rounded-2xl px-5 py-4 text-sm text-white focus:outline-none focus:border-blue-500/30 transition-all min-h-[100px] resize-none"
                                      placeholder="Contoh: Titipan modal tambahan, catatan khusus..."></textarea>
                            <span class="absolute bottom-4 right-5 text-[9px] font-black text-slate-600 uppercase tracking-widest" x-text="notes.length + '/200'"></span>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- SECTION 7 â€” ACTION BUTTONS -->
        <div class="p-8 space-y-4 bg-slate-900/50 border-t border-white/10">
            <div class="flex gap-4">
                <button @click="showOpenModal = false" class="flex-1 bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white font-black py-4 rounded-2xl text-xs uppercase tracking-[0.2em] transition-all active:scale-95 border border-white/5">
                    Batal
                </button>
                <button form="bukaShiftFormMain" class="flex-[2] text-white font-black py-4 rounded-2xl text-xs uppercase tracking-[0.2em] transition-all active:scale-95 flex items-center justify-center gap-3"
                        :class="diff == 0 ? 'bg-gradient-to-r from-blue-600 to-blue-500 shadow-lg shadow-blue-500/20 hover:shadow-blue-500/40' : 'bg-gradient-to-r from-orange-600 to-amber-500 btn-glow-orange'">
                    <i :class="diff == 0 ? 'fas fa-play' : 'fas fa-exclamation-circle animate-pulse'"></i>
                    <span x-text="diff == 0 ? 'Buka Shift Baru' : 'Buka Shift Dengan Selisih'"></span>
                </button>
            </div>
            
            <!-- FOOTER -->
            <div class="flex items-center justify-center gap-2 text-slate-600">
                <i class="fas fa-shield-halved text-[10px]"></i>
                <span class="text-[9px] font-black uppercase tracking-[0.15em]">Data kas Anda aman dan terenkripsi</span>
            </div>
        </div>

    </div>
    
    <style>
        @keyframes spin-slow {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .animate-spin-slow {
            animation: spin-slow 8s linear infinite;
        }
        .glass-modal {
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
        }
        .neon-border-blue {
            box-shadow: 0 0 15px rgba(59, 130, 246, 0.2);
            border: 1px solid rgba(59, 130, 246, 0.4);
        }
        .neon-border-green {
            box-shadow: 0 0 10px rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        .neon-border-red {
            box-shadow: 0 0 15px rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.4);
        }
        .btn-glow-orange {
            box-shadow: 0 4px 20px rgba(245, 158, 11, 0.3);
        }
        .btn-glow-orange:hover {
            box-shadow: 0 8px 30px rgba(245, 158, 11, 0.5);
        }
    </style>
</div>


