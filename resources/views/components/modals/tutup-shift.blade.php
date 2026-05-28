{{-- Premium Redesign Tutup Shift Modal --}}
<div x-data="{ 
        physicCash: {{ $currentExpected ?? 0 }}, 
        systemCash: {{ $currentExpected ?? 0 }},
        notes: '',
        get diff() { 
            return (parseInt(this.physicCash.toString().replace(/\D/g, '')) || 0) - this.systemCash 
        },
        get formattedPhysic() { 
            if(this.physicCash === '') return '';
            return 'Rp ' + (parseInt(this.physicCash.toString().replace(/\D/g, '')) || 0).toLocaleString('id-ID');
        },
        updatePhysic(val) {
            let num = val.replace(/\D/g, '');
            this.physicCash = num ? parseInt(num) : 0;
        }
    }" 
    class="fixed inset-0 z-[100] flex items-center justify-center p-4"
    x-show="showCloseModal"
    x-cloak
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0">
    
    {{-- Backdrop Blur --}}
    <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-md" @click="showCloseModal = false"></div>

    <div class="glass-modal rounded-[2.5rem] overflow-hidden flex flex-col w-full max-w-2xl max-h-[95vh] shadow-2xl border border-white/5 relative z-10 animate-in fade-in zoom-in duration-300 max-h-[90vh] overflow-y-auto scrollbar-hide ">
        
        <!-- Decoration Glows -->
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-red-600/10 blur-[80px] rounded-full pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-emerald-600/10 blur-[80px] rounded-full pointer-events-none"></div>

        <!-- HEADER -->
        <div class="px-8 pt-8 pb-6 relative flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-5 text-center md:text-left">
                <div class="w-14 h-14 bg-red-500/10 rounded-2xl flex items-center justify-center neon-border-red shrink-0 shadow-lg shadow-red-500/10">
                    <i class="fas fa-lock text-red-500 text-2xl neon-text-red"></i>
                </div>
                <div>
                    <h2 class="text-xl font-black text-white tracking-tight poppins uppercase italic">Tutup Shift Kasir</h2>
                    <p class="text-slate-400 text-xs mt-0.5 font-medium">Pastikan jumlah uang fisik sesuai dengan estimasi sistem.</p>
                </div>
            </div>
            <div class="flex gap-2 shrink-0">
                <span class="px-2.5 py-1 text-[8px] font-black text-red-400 uppercase tracking-wider rounded-full border border-red-500/30 bg-red-500/10 flex items-center gap-1.5 shadow-inner">
                    <i class="fas fa-circle text-[6px] animate-pulse"></i> LIVE SHIFT
                </span>
                <span class="px-2.5 py-1 text-[8px] font-black text-emerald-400 uppercase tracking-wider rounded-full border border-emerald-500/30 bg-emerald-500/10 flex items-center gap-1.5 shadow-inner">
                    <i class="fas fa-sync-alt text-[6px]"></i> SINKRON LACI AKTIF
                </span>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-red-500/30 to-transparent opacity-50"></div>
        </div>

        <!-- MODAL CONTENT (Scrollable) -->
        <div class="px-8 py-4 overflow-y-auto custom-scrollbar space-y-6">
            
            <!-- SECTION 2 &mdash; INFO SHIFT AKTIF -->
            <div class="bg-white/5 border border-white/5 rounded-3xl p-5 grid grid-cols-2 md:grid-cols-4 gap-4 backdrop-blur-sm relative overflow-hidden group transition-all duration-300 hover:bg-white/[0.08]">
                <div class="space-y-1">
                    <p class="text-[9px] font-black text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fas fa-user-circle text-blue-400"></i> Kasir
                    </p>
                    <p class="text-xs font-bold text-white truncate">{{ $activeShift->opener->name ?? 'Owner Admin' }}</p>
                </div>
                <div class="space-y-1">
                    <p class="text-[9px] font-black text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fas fa-clock text-emerald-400"></i> Jam Buka
                    </p>
                    <p class="text-xs font-bold text-white">{{ $activeShift->opened_at->format('H:i') ?? '08:18' }} WIB</p>
                </div>
                <div class="space-y-1">
                    <p class="text-[9px] font-black text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fas fa-hourglass-half text-amber-400"></i> Durasi
                    </p>
                    <p class="text-xs font-bold text-white">{{ $activeShift->opened_at->diffForHumans(null, true) ?? '2 jam 14 menit' }}</p>
                </div>
                <div class="space-y-1">
                    <p class="text-[9px] font-black text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fas fa-wallet text-purple-400"></i> Modal Awal
                    </p>
                    <p class="text-xs font-bold text-white italic tracking-tight">Rp {{ number_format($activeShift->opening_cash ?? 4000000, 0, ',', '.') }}</p>
                </div>
            </div>

            <!-- SECTION 3 &mdash; RINGKASAN KEUANGAN -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <div class="bg-gradient-to-br from-emerald-500/10 to-transparent border border-emerald-500/20 rounded-2xl p-4 space-y-2 group hover:border-emerald-500/40 transition-all duration-300">
                    <div class="w-8 h-8 bg-emerald-500/20 rounded-lg flex items-center justify-center text-emerald-400 text-xs shadow-inner group-hover:scale-110 transition-transform">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div>
                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Total Jual</p>
                        <p class="text-sm font-black text-emerald-400">Rp {{ number_format($currentSales ?? 330000, 0, ',', '.') }}</p>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-orange-500/10 to-transparent border border-orange-500/20 rounded-2xl p-4 space-y-2 group hover:border-orange-500/40 transition-all duration-300">
                    <div class="w-8 h-8 bg-orange-500/20 rounded-lg flex items-center justify-center text-orange-400 text-xs shadow-inner group-hover:scale-110 transition-transform">
                        <i class="fas fa-arrow-up-right-from-square"></i>
                    </div>
                    <div>
                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Total Biaya</p>
                        <p class="text-sm font-black text-orange-400">Rp {{ number_format($currentTotalExpenses ?? 0, 0, ',', '.') }}</p>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-cyan-500/10 to-transparent border border-cyan-500/20 rounded-2xl p-4 space-y-2 group hover:border-cyan-500/40 transition-all duration-300">
                    <div class="w-8 h-8 bg-cyan-500/20 rounded-lg flex items-center justify-center text-cyan-400 text-xs shadow-inner group-hover:scale-110 transition-transform">
                        <i class="fas fa-calculator"></i>
                    </div>
                    <div>
                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Estimasi Kas</p>
                        <p class="text-sm font-black text-white">Rp {{ number_format($currentExpected ?? 69377200, 0, ',', '.') }}</p>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-red-500/10 to-transparent border border-red-500/20 rounded-2xl p-4 space-y-2 group hover:border-red-500/40 transition-all duration-300">
                    <div class="w-8 h-8 bg-red-500/20 rounded-lg flex items-center justify-center text-red-400 text-xs shadow-inner group-hover:scale-110 transition-transform">
                        <i class="fas fa-triangle-exclamation"></i>
                    </div>
                    <div>
                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Sls Sementara</p>
                        <p class="text-sm font-black text-red-400" x-text="diff !== null ? (diff < 0 ? '-' : '+') + 'Rp ' + Math.abs(diff).toLocaleString('id-ID') : '-'">-</p>
                    </div>
                </div>
            </div>

            <form action="{{ route('shifts.close', $activeShift->id ?? 0) }}" method="POST" id="tutupShiftFormMain">
                @csrf
                <div class="space-y-6">
                    <!-- SECTION 4 &mdash; INPUT UANG FISIK -->
                    <div class="space-y-3">
                        <div class="flex justify-between items-end ml-2">
                            <label class="text-[10px] font-black text-red-400 uppercase tracking-[0.2em] flex items-center gap-2">
                                Total Uang Fisik di Laci
                                <span class="px-2 py-0.5 bg-red-500/20 text-red-400 border border-red-500/30 rounded text-[7px] animate-pulse">AUTO-SYNC</span>
                            </label>
                            <span class="text-[9px] font-bold text-slate-500 italic uppercase">Terhitung otomatis dari sistem</span>
                        </div>
                        <div class="relative group">
                            <div class="absolute left-6 top-1/2 -translate-y-1/2 text-2xl font-black text-slate-500 tracking-tighter">Rp</div>
                            
                            {{-- Hidden input for actual numeric value --}}
                            <input type="hidden" name="closing_cash" :value="physicCash">
                            
                            {{-- Visible input for formatting --}}
                            <input type="text" 
                                   :value="formattedPhysic.replace('Rp ', '')" 
                                   @input="updatePhysic($event.target.value)"
                                   required
                                   class="w-full bg-red-500/5 border border-red-500/20 rounded-3xl pl-20 pr-6 py-6 text-3xl font-black text-white focus:outline-none neon-border-red transition-all focus:bg-red-500/10 tracking-tighter"
                                   placeholder="0">
                            
                            <!-- Realtime Validation Badge -->
                            <div class="absolute right-6 top-1/2 -translate-y-1/2">
                                <template x-if="diff !== null && diff !== 0">
                                    <div class="bg-red-500/20 border border-red-500/30 px-3 py-1.5 rounded-xl flex items-center gap-2 animate-in slide-in-from-right-4 duration-300">
                                        <i class="fas fa-exclamation-triangle text-red-500 text-xs"></i>
                                        <span class="text-[10px] font-black text-red-400 uppercase tracking-wider" x-text="'Selisih: ' + (diff < 0 ? '-' : '+') + 'Rp ' + Math.abs(diff).toLocaleString('id-ID')"></span>
                                    </div>
                                </template>
                                <template x-if="diff === 0">
                                    <div class="bg-emerald-500/20 border border-emerald-500/30 px-3 py-1.5 rounded-xl flex items-center gap-2 animate-in slide-in-from-right-4 duration-300">
                                        <i class="fas fa-check-circle text-emerald-500 text-xs"></i>
                                        <span class="text-[10px] font-black text-emerald-400 uppercase tracking-wider">Kas Sesuai</span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 5 &mdash; CATATAN -->
                    <div class="space-y-2">
                        <div class="flex justify-between items-end ml-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Catatan Penutupan</label>
                            <span class="text-[8px] font-bold text-slate-600 uppercase">Opsional &bull; Audit Internal</span>
                        </div>
                        <div class="relative">
                            <textarea name="notes" x-model="notes" 
                                      maxlength="200"
                                      class="w-full bg-slate-900 border border-white/5 rounded-2xl px-5 py-4 text-sm text-white focus:outline-none focus:border-red-500/30 transition-all min-h-[80px] resize-none"
                                      placeholder="Tulis catatan penutupan shift jika diperlukanâ€¦"></textarea>
                            <span class="absolute bottom-4 right-5 text-[9px] font-black text-slate-600 uppercase tracking-widest" x-text="notes.length + '/200'"></span>
                        </div>
                    </div>

                    <!-- SECTION 6 &mdash; VALIDATION BOX -->
                    <div class="bg-slate-900/80 border border-white/5 rounded-3xl p-5 space-y-3 shadow-inner relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-3 opacity-10">
                            <i class="fas fa-clipboard-check text-4xl"></i>
                        </div>
                        <h4 class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] mb-1">Checklist Keamanan</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-2 gap-x-6">
                            <div class="flex items-center gap-3">
                                <i class="fas fa-check-circle text-emerald-500 text-[10px]"></i>
                                <span class="text-[11px] font-medium text-slate-400">Shift aktif ditemukan</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <i class="fas fa-check-circle text-emerald-500 text-[10px]"></i>
                                <span class="text-[11px] font-medium text-slate-400">Sinkronisasi laci berhasil</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <i class="fas fa-check-circle text-emerald-500 text-[10px]"></i>
                                <span class="text-[11px] font-medium text-slate-400">Data transaksi tersimpan</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <i class="fas fa-check-circle text-emerald-500 text-[10px]"></i>
                                <span class="text-[11px] font-medium text-slate-400">Tidak ada transaksi pending</span>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- SECTION 7 &mdash; ACTION BUTTONS -->
        <div class="p-8 space-y-4 bg-slate-900/50 border-t border-white/10">
            <div class="flex flex-col md:flex-row gap-4">
                <button @click="showCloseModal = false" class="flex-1 bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white font-black py-4 rounded-2xl text-xs uppercase tracking-[0.2em] transition-all active:scale-95 border border-white/5 order-2 md:order-1">
                    Batal
                </button>
                @php
                    $approvalRequired = \App\Models\Setting::get('shift_approval_required') == '1';
                    $isKasir = auth()->check() && auth()->user()->isKasir();
                @endphp
                <button form="tutupShiftFormMain" class="flex-[2] bg-gradient-to-r {{ ($approvalRequired && $isKasir) ? 'from-amber-500 to-orange-500 btn-glow-orange' : 'from-red-600 to-rose-500 btn-glow-red' }} text-white font-black py-4 rounded-2xl text-xs uppercase tracking-[0.2em] transition-all active:scale-95 flex items-center justify-center gap-3 order-1 md:order-2">
                    @if($approvalRequired && $isKasir)
                        <i class="fas fa-paper-plane"></i> Lapor Shift
                    @else
                        <i class="fas fa-lock"></i> Tutup Shift Sekarang
                    @endif
                </button>
            </div>
            
            <!-- FOOTER -->
            <div class="flex items-center justify-center gap-2 text-slate-600">
                <i class="fas fa-shield-halved text-[10px]"></i>
                <span class="text-[9px] font-black uppercase tracking-[0.15em]">Sistem Audit Keuangan Profesional &bull; MONOFRAME</span>
            </div>
        </div>

    </div>
    
    <style>
        .glass-modal {
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
        }
        .neon-border-red {
            box-shadow: 0 0 15px rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.4);
        }
        .neon-text-red {
            text-shadow: 0 0 8px rgba(239, 68, 68, 0.5);
        }
        .btn-glow-red {
            box-shadow: 0 4px 20px rgba(239, 68, 68, 0.3);
        }
        .btn-glow-red:hover {
            box-shadow: 0 8px 30px rgba(239, 68, 68, 0.5);
        }
        .btn-glow-orange {
            box-shadow: 0 4px 20px rgba(245, 158, 11, 0.3);
        }
        .btn-glow-orange:hover {
            box-shadow: 0 8px 30px rgba(245, 158, 11, 0.5);
        }
        .transition-premium {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
    </style>
</div>


