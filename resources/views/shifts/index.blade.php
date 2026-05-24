@extends('layouts.app')

@section('title', 'Sesi Shift Aktif')
@section('page-title', 'Live Operations Center')
@section('page-subtitle', 'Monitoring sesi shift dan operasional realtime')

@section('content')
<div x-data="{ showOpenModal: {{ request('open') ? 'true' : 'false' }}, showCloseModal: false }">
    
    @if(!$activeShift)
    {{-- NO ACTIVE SHIFT --}}
    <div class="bg-slate-800/50 rounded-3xl p-12 border border-slate-700 border-dashed flex flex-col items-center justify-center text-center max-w-4xl mx-auto mt-10 shadow-2xl">
        <div class="w-24 h-24 rounded-full bg-slate-900 border border-slate-800 flex items-center justify-center text-slate-500 text-4xl mb-6 shadow-inner relative">
            <i class="fas fa-bed"></i>
            <div class="absolute -right-2 -top-2 w-8 h-8 bg-red-500/20 text-red-500 rounded-full flex items-center justify-center text-xs border border-red-500/50 animate-pulse">
                <i class="fas fa-lock"></i>
            </div>
        </div>
        <h2 class="text-3xl font-black text-white mb-2 tracking-tight">Belum Ada Sesi Berjalan</h2>
        <p class="text-slate-400 mb-8 max-w-md">Sistem Point of Sale dan pencatatan transaksi terkunci. Buka shift sekarang untuk memulai operasional toko.</p>
        
        <button @click="showOpenModal = true" class="py-4 px-12 bg-gradient-to-r from-blue-600 to-cyan-500 hover:from-cyan-500 hover:to-blue-600 text-white font-black rounded-2xl transition-all shadow-[0_0_20px_rgba(59,130,246,0.3)] hover:shadow-[0_0_30px_rgba(6,182,212,0.5)] hover:-translate-y-1 active:scale-95 flex items-center justify-center gap-3 group uppercase tracking-[0.15em]">
            <i class="fas fa-power-off group-hover:rotate-180 transition-transform duration-700 text-lg"></i> 
            Mulai Operasional
        </button>
    </div>
    @else
    
    {{-- ACTIVE SHIFT DASHBOARD --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        {{-- KIRI: Live Financials & Core Actions (5 columns) --}}
        <div class="lg:col-span-5 space-y-6">
            
            {{-- LIVE INDICATOR & IDENTITAS --}}
            <div class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-3xl p-6 border border-emerald-500/30 shadow-[0_0_30px_rgba(16,185,129,0.05)] relative overflow-hidden">
                <div class="absolute -right-10 -top-10 w-40 h-40 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
                
                <div class="flex justify-between items-start mb-6 relative z-10">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-emerald-500/10 border border-emerald-500/20 mb-3 shadow-inner">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse shadow-[0_0_8px_rgba(52,211,153,0.8)]"></span>
                            <span class="text-[10px] font-black text-emerald-400 uppercase tracking-widest">LIVE SESSION</span>
                        </div>
                        <h2 class="text-2xl font-black text-white flex items-center gap-3">
                            <i class="fas fa-user-circle text-emerald-500"></i>
                            {{ $activeShift->opener->name }}
                        </h2>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Durasi</p>
                        <p class="text-sm font-black text-emerald-400 mt-1">{{ $activeShift->opened_at->diffForHumans(null, true) }}</p>
                    </div>
                </div>

                {{-- QUICK STATS --}}
                <div class="grid grid-cols-2 gap-3 mb-6 relative z-10">
                    {{-- Tunai --}}
                    <div class="bg-slate-950/50 rounded-2xl p-4 border border-white/5">
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1"><i class="fas fa-money-bill-wave text-emerald-400 mr-1"></i> Penjualan Tunai</p>
                        <p class="text-lg font-black text-white">Rp {{ number_format($currentSales, 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-slate-950/50 rounded-2xl p-4 border border-white/5">
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1"><i class="fas fa-minus-circle text-red-400 mr-1"></i> Pengeluaran Tunai</p>
                        <p class="text-lg font-black text-red-400">Rp {{ number_format($currentCashExpenses, 0, ',', '.') }}</p>
                    </div>
                    
                    {{-- Non-Tunai (Bank/QRIS) --}}
                    <div class="bg-slate-950/50 rounded-2xl p-4 border border-white/5">
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1"><i class="fas fa-qrcode text-blue-400 mr-1"></i> Penjualan Non-Tunai</p>
                        <p class="text-lg font-black text-white">Rp {{ number_format($currentBankSales, 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-slate-950/50 rounded-2xl p-4 border border-white/5">
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1"><i class="fas fa-university text-orange-400 mr-1"></i> Pengeluaran Bank</p>
                        <p class="text-lg font-black text-orange-400">Rp {{ number_format($currentBankExpenses, 0, ',', '.') }}</p>
                    </div>
                </div>

                {{-- ESTIMASI LACI --}}
                <div class="bg-emerald-950/30 rounded-2xl p-5 border border-emerald-500/20 relative z-10">
                    <div class="flex justify-between items-center mb-2">
                        <p class="text-xs font-black text-emerald-500 uppercase tracking-wider">Estimasi Uang Laci</p>
                        <i class="fas fa-cash-register text-emerald-500/50 text-xl"></i>
                    </div>
                    <h3 class="text-3xl font-black text-emerald-400 mb-1">Rp {{ number_format($currentExpected, 0, ',', '.') }}</h3>
                    <p class="text-[10px] font-medium text-emerald-500/70">Telah disinkronisasi dengan modal awal dan mutasi kasir.</p>
                </div>

                {{-- ACTION BUTTONS --}}
                <div class="mt-6 flex gap-3 relative z-10">
                    <button @click="showCloseModal = true" class="flex-1 py-4 bg-gradient-to-r from-red-600 to-rose-500 hover:from-rose-500 hover:to-red-600 text-white font-black rounded-2xl transition-all shadow-[0_0_15px_rgba(225,29,72,0.3)] hover:shadow-[0_0_25px_rgba(225,29,72,0.5)] hover:-translate-y-0.5 active:scale-95 text-xs uppercase tracking-wider flex items-center justify-center gap-2">
                        <i class="fas fa-lock"></i> Tutup Shift
                    </button>
                    <a href="{{ route('pos.index') }}" class="flex-1 py-4 bg-gradient-to-r from-blue-600 to-cyan-500 hover:from-cyan-500 hover:to-blue-600 text-white font-black rounded-2xl transition-all shadow-[0_0_15px_rgba(59,130,246,0.3)] hover:shadow-[0_0_25px_rgba(6,182,212,0.5)] hover:-translate-y-0.5 active:scale-95 text-xs uppercase tracking-wider flex items-center justify-center gap-2 text-center">
                        <i class="fas fa-store"></i> Buka POS
                    </a>
                </div>
            </div>

            {{-- OPERATIONAL STATUS --}}
            <div class="bg-slate-800 rounded-3xl p-6 border border-slate-700/80 shadow-sm">
                <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                    <i class="fas fa-satellite-dish"></i> Status Operasional
                </h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-900/50 border border-white/5">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center border border-emerald-500/20">
                                <i class="fas fa-wifi"></i>
                            </div>
                            <span class="text-sm font-bold text-slate-300">Koneksi Server</span>
                        </div>
                        <span class="text-[10px] font-black text-emerald-400 uppercase bg-emerald-500/10 px-2 py-1 rounded-md">Online</span>
                    </div>
                    
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-900/50 border border-white/5">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center border border-emerald-500/20">
                                <i class="fas fa-qrcode"></i>
                            </div>
                            <span class="text-sm font-bold text-slate-300">Gateway QRIS</span>
                        </div>
                        <span class="text-[10px] font-black text-emerald-400 uppercase bg-emerald-500/10 px-2 py-1 rounded-md">Aktif</span>
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-900/50 border border-white/5">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-400 flex items-center justify-center border border-blue-500/20">
                                <i class="fas fa-print"></i>
                            </div>
                            <span class="text-sm font-bold text-slate-300">Printer Thermal</span>
                        </div>
                        <span class="text-[10px] font-black text-blue-400 uppercase bg-blue-500/10 px-2 py-1 rounded-md">Tersedia</span>
                    </div>
                </div>
            </div>
            
        </div>

        {{-- KANAN: Realtime Timeline & Targets (7 columns) --}}
        <div class="lg:col-span-7 space-y-6">
            
            {{-- TARGET SHIFT PROGRESS --}}
            <div class="bg-slate-800 rounded-3xl p-6 border border-slate-700/80 shadow-sm relative overflow-hidden">
                <div class="flex justify-between items-end mb-4">
                    <div>
                        <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1">Target Harian Toko</h3>
                        <p class="text-2xl font-black text-white">Rp {{ number_format($todaySalesTotal, 0, ',', '.') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Goal</p>
                        <p class="text-sm font-bold text-slate-300">Rp {{ number_format($todayTarget, 0, ',', '.') }}</p>
                    </div>
                </div>
                
                @php
                    $progress = $todayTarget > 0 ? min(100, ($todaySalesTotal / $todayTarget) * 100) : 0;
                    $progressColor = $progress >= 100 ? 'from-emerald-500 to-emerald-400' : 'from-blue-600 to-cyan-400';
                    $shadowColor = $progress >= 100 ? 'shadow-[0_0_15px_rgba(16,185,129,0.5)]' : 'shadow-[0_0_15px_rgba(6,182,212,0.5)]';
                @endphp
                
                <div class="w-full bg-slate-900 rounded-full h-3 mb-2 border border-slate-700 overflow-hidden">
                    <div class="bg-gradient-to-r {{ $progressColor }} h-3 rounded-full {{ $shadowColor }} transition-all duration-1000" style="width: {{ $progress }}%"></div>
                </div>
                <div class="flex justify-between items-center text-[10px] font-bold">
                    <span class="{{ $progress >= 100 ? 'text-emerald-400' : 'text-blue-400' }}">{{ number_format($progress, 1) }}% Tercapai</span>
                    @if($progress >= 100)
                        <span class="text-emerald-400 animate-pulse"><i class="fas fa-check-circle"></i> Target Terlampaui!</span>
                    @else
                        <span class="text-slate-500">Sisa: Rp {{ number_format(max(0, $todayTarget - $todaySalesTotal), 0, ',', '.') }}</span>
                    @endif
                </div>
            </div>

            {{-- LIVE ACTIVITY TIMELINE --}}
            <div class="bg-slate-800 rounded-3xl border border-slate-700/80 shadow-sm flex flex-col h-[500px]">
                <div class="p-6 border-b border-slate-700/80 bg-slate-800/50 flex justify-between items-center shrink-0">
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                        <i class="fas fa-bolt text-yellow-400"></i> Aktivitas Realtime
                    </h3>
                    <button onclick="window.location.reload()" class="text-[10px] bg-slate-700 hover:bg-slate-600 text-white px-3 py-1.5 rounded-lg font-bold transition-colors shadow-sm flex items-center gap-1.5">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
                
                <div class="flex-1 overflow-y-auto p-6 space-y-6 custom-scrollbar relative">
                    {{-- Timeline Line --}}
                    <div class="absolute left-9 top-6 bottom-6 w-px bg-slate-700/50"></div>
                    
                    @if($recentTransactions->isEmpty() && $recentExpenses->isEmpty())
                        <div class="text-center py-12 text-slate-500">
                            <i class="fas fa-clock text-3xl mb-3 opacity-50"></i>
                            <p class="text-sm font-medium">Belum ada aktivitas di shift ini.</p>
                        </div>
                    @else
                        @php
                            // Combine and sort by date descending
                            $activities = collect();
                            foreach($recentTransactions as $trx) {
                                $activities->push(['type' => 'transaction', 'data' => $trx, 'time' => $trx->created_at]);
                            }
                            foreach($recentExpenses as $exp) {
                                $activities->push(['type' => 'expense', 'data' => $exp, 'time' => $exp->created_at]);
                            }
                            $activities = $activities->sortByDesc('time');
                        @endphp
                        
                        @foreach($activities as $act)
                            @if($act['type'] === 'transaction')
                                <div class="relative flex items-start gap-4 group">
                                    <div class="w-7 h-7 rounded-full bg-blue-500/20 text-blue-400 border border-blue-500/30 flex items-center justify-center shrink-0 relative z-10 shadow-[0_0_10px_rgba(59,130,246,0.2)]">
                                        <i class="fas fa-receipt text-[10px]"></i>
                                    </div>
                                    <div class="flex-1 bg-slate-900/50 rounded-2xl p-4 border border-white/5 group-hover:border-blue-500/30 transition-colors">
                                        <div class="flex justify-between items-start mb-1">
                                            <h4 class="text-sm font-bold text-white">{{ $act['data']->invoice_number }}</h4>
                                            <span class="text-[10px] text-slate-500">{{ $act['time']->format('H:i') }}</span>
                                        </div>
                                        <p class="text-[11px] text-slate-400 mb-2">Penjualan via {{ strtoupper($act['data']->payment_method) }}</p>
                                        <div class="flex justify-between items-center text-xs">
                                            <span class="font-bold text-blue-400">+ Rp {{ number_format($act['data']->total, 0, ',', '.') }}</span>
                                            <span class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-[9px] uppercase tracking-widest font-black">Sukses</span>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="relative flex items-start gap-4 group">
                                    <div class="w-7 h-7 rounded-full bg-red-500/20 text-red-400 border border-red-500/30 flex items-center justify-center shrink-0 relative z-10 shadow-[0_0_10px_rgba(239,68,68,0.2)]">
                                        <i class="fas fa-money-bill-wave text-[10px]"></i>
                                    </div>
                                    <div class="flex-1 bg-slate-900/50 rounded-2xl p-4 border border-white/5 group-hover:border-red-500/30 transition-colors">
                                        <div class="flex justify-between items-start mb-1">
                                            <h4 class="text-sm font-bold text-white">Pengeluaran: {{ $act['data']->category }}</h4>
                                            <span class="text-[10px] text-slate-500">{{ $act['time']->format('H:i') }}</span>
                                        </div>
                                        <p class="text-[11px] text-slate-400 mb-2">{{ $act['data']->description }}</p>
                                        <div class="flex justify-between items-center text-xs">
                                            <span class="font-bold text-red-400">- Rp {{ number_format($act['data']->amount, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                        
                        <div class="relative flex items-start gap-4">
                            <div class="w-7 h-7 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center shrink-0 relative z-10 shadow-[0_0_10px_rgba(16,185,129,0.2)]">
                                <i class="fas fa-play text-[10px] ml-0.5"></i>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-sm font-bold text-slate-300">Shift Dibuka</h4>
                                <span class="text-[10px] text-slate-500">{{ $activeShift->opened_at->format('H:i') }} - Modal Kas: Rp {{ number_format($activeShift->opening_cash, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
    
    {{-- MODAL TUTUP SHIFT (dipindahkan dari include menjadi inline Alpine/Form agar sesuai design Sesi Shift) --}}
    @include('components.modals.tutup-shift')
    
    @endif
    
    {{-- MODAL BUKA SHIFT --}}
    @if(!$activeShift)
        @include('components.modals.buka-shift')
    @endif
</div>

@endsection

@push('styles')
<style>
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(51, 65, 85, 0.5); border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(71, 85, 105, 0.8); }
</style>
@endpush
