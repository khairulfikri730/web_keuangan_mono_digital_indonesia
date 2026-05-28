@extends('layouts.app')

@section('title', 'Cashflow Dashboard')
@section('page-title', 'Arus Kas')
@section('page-subtitle', 'Pantau pemasukan & pengeluaran bisnis secara real-time')

@push('styles')
<style>
    /* Premium Glassmorphism */
    .glass-card {
        background: rgba(30, 41, 59, 0.4);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
    
    .glass-card:hover {
        background: rgba(30, 41, 59, 0.6);
        border-color: rgba(255, 255, 255, 0.1);
        transform: translateY(-2px);
        box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.5);
    }

    /* Transition Utils */
    .transition-premium {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Custom Gradient Backgrounds */
    .grad-blue { background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(59, 130, 246, 0.02) 100%); }
    .grad-emerald { background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0.02) 100%); }
    .grad-red { background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(239, 68, 68, 0.02) 100%); }
    .grad-amber { background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(245, 158, 11, 0.02) 100%); }
    .grad-purple { background: linear-gradient(135deg, rgba(168, 85, 247, 0.1) 0%, rgba(168, 85, 247, 0.02) 100%); }

    /* Hide scrollbar for timeline */
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }

    .period-btn.active {
        box-shadow: 0 4px 12px -2px rgba(59, 130, 246, 0.5);
    }

    @keyframes scaleUp {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    .animate-balance-update {
        animation: scaleUp 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    }
</style>
@endpush

@section('content')
<div class="space-y-6 text-slate-200">
    
    <!-- MAIN NAVIGATION TABS (PREMIUM) -->
    <div class="flex items-center gap-2 mb-6 overflow-x-auto scrollbar-hide">
        <a href="{{ route('cashflow.index') }}" class="px-8 py-3 rounded-2xl {{ request()->routeIs('cashflow.index') ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/20' : 'bg-slate-800/40 text-slate-500 hover:text-slate-300 border border-white/5' }} font-black text-xs uppercase tracking-widest transition-premium whitespace-nowrap">
            <i class="fas fa-chart-line mr-2"></i> Dashboard Arus Kas
        </a>
        <a href="{{ route('sales.index') }}" class="px-8 py-3 rounded-2xl bg-slate-800/40 text-slate-500 hover:text-slate-300 border border-white/5 font-black text-xs uppercase tracking-widest transition-premium whitespace-nowrap">
            <i class="fas fa-shopping-cart mr-2"></i> Pemasukan (Sales)
        </a>
        <a href="{{ route('monthly_expenses.index') }}" class="px-8 py-3 rounded-2xl bg-slate-800/40 text-slate-500 hover:text-slate-300 border border-white/5 font-black text-xs uppercase tracking-widest transition-premium whitespace-nowrap">
            <i class="fas fa-file-invoice mr-2"></i> Pengeluaran (Biaya)
        </a>
    </div>
    
    <!-- 1. HEADER SECTION (PREMIUM COMPACT) -->
    <div class="bg-slate-800/40 backdrop-blur-xl border border-white/5 rounded-[2rem] p-5 mb-8 flex flex-col lg:flex-row lg:items-center justify-between gap-6 shadow-2xl" style="position: relative; z-index: 50;">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-600/10 border border-blue-500/20 flex items-center justify-center text-blue-400 shadow-inner">
                <i class="fas fa-money-bill-transfer text-xl"></i>
            </div>
            <div>
                <h1 class="text-2xl font-black text-white tracking-tighter leading-none">Arus Kas</h1>
                <p class="text-[9px] text-slate-500 font-black uppercase tracking-widest mt-1.5 flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    Live Monitoring
                </p>
            </div>
        </div>
        
        <div class="flex flex-wrap items-center gap-3">
            <x-custom-filter :dateFrom="$start" :dateTo="$end" />

            <div class="h-8 w-px bg-white/5 mx-1 hidden xl:block"></div>

            <!-- Source Selector -->
            <div class="relative group">
                <select id="sourceFilter" class="appearance-none bg-slate-900/80 border border-white/5 rounded-2xl pl-10 pr-10 py-2.5 text-[10px] font-black text-white uppercase tracking-widest focus:ring-0 focus:border-blue-500 transition-premium shadow-inner cursor-pointer">
                    <option value="all">Semua Sumber</option>
                    @foreach(\App\Models\Cashflow::sourceLabels() as $val => $label)
                        <option value="{{ $val }}" {{ $source == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-blue-400 text-[10px]">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-500 text-[10px] pointer-events-none">
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2">
                <button onclick="document.getElementById('analysisModal').classList.remove('hidden')" class="w-11 h-11 bg-indigo-600/10 border border-indigo-500/20 text-indigo-400 rounded-2xl hover:bg-indigo-600 hover:text-white transition-premium flex items-center justify-center shadow-lg group" title="Analisis">
                    <i class="fas fa-chart-pie group-hover:rotate-12 transition-transform"></i>
                </button>

                <button onclick="window.openExportModal()" class="w-11 h-11 bg-slate-800 border border-white/5 text-slate-400 rounded-2xl hover:bg-slate-700 hover:text-white transition-premium flex items-center justify-center shadow-lg" title="Ekspor Laporan (PDF/Excel/CSV)">
                    <i class="fas fa-file-export"></i>
                </button>

                <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="bg-blue-600 hover:bg-blue-500 text-white font-black px-6 py-3 rounded-2xl transition-premium shadow-xl shadow-blue-900/30 active:scale-95 text-[10px] uppercase tracking-widest flex items-center gap-3">
                    <i class="fas fa-plus-circle text-sm"></i>
                    <span>Catat Transaksi</span>
                </button>
            </div>
        </div>
    </div>

    <!-- 2. SUMMARY STRIP (PREMIUM CARDS) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 sm:gap-6 mb-6 sm:mb-8" style="position: relative; z-index: 1;">
        <!-- Modal Investasi &rarr; redirect ke capitals dengan auto-open edit modal -->
        <div onclick="window.location.href='{{ route('capitals.index') }}?action=edit_latest'" class="glass-card grad-blue rounded-[2.5rem] p-7 transition-premium group relative overflow-hidden cursor-pointer block hover:scale-[1.02] hover:shadow-[0_0_40px_-5px_rgba(59,130,246,0.4)]">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-blue-500/10 rounded-full blur-2xl group-hover:bg-blue-500/20 transition-premium"></div>
            <div class="flex justify-between items-start mb-4">
                <div class="w-12 h-12 rounded-2xl bg-blue-500/10 flex items-center justify-center text-blue-400 border border-blue-500/20 group-hover:bg-blue-600 group-hover:text-white transition-premium shadow-lg">
                    <i class="fas fa-vault text-lg"></i>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Modal Investasi</span>
                    <span class="w-7 h-7 rounded-lg bg-blue-500/10 group-hover:bg-blue-600 text-blue-400 group-hover:text-white transition-all flex items-center justify-center border border-blue-500/20">
                        <i class="fas fa-arrow-up-right-from-square text-[10px]"></i>
                    </span>
                </div>
            </div>
            <h3 class="text-2xl font-black text-white tracking-tighter leading-none mb-2" id="valTotalInvestment">Rp {{ number_format($totalInvestment, 0, ',', '.') }}</h3>
            <p class="text-[9px] font-bold text-slate-500 uppercase tracking-tighter flex items-center gap-1.5">
                <i class="fas fa-chart-pie text-blue-400"></i>
                Klik untuk Edit Modal Usaha
            </p>
        </div>

        <!-- Analisis BEP -->
        <div class="glass-card grad-emerald rounded-[2.5rem] p-7 transition-premium group relative overflow-hidden cursor-pointer" onclick="document.getElementById('modalTargetBEP').classList.remove('hidden')">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-emerald-500/10 rounded-full blur-2xl group-hover:bg-emerald-500/20 transition-premium"></div>
            <div class="flex justify-between items-start mb-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 flex items-center justify-center text-emerald-400 border border-emerald-500/20 group-hover:bg-emerald-600 group-hover:text-white transition-premium shadow-lg">
                    <i class="fas fa-bullseye text-lg"></i>
                </div>
                <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">ROI (Target: {{ $targetPaybackMonths }} Bln)</span>
            </div>
            <div class="flex justify-between items-end mb-3">
                <h3 class="text-3xl font-black text-white tracking-tighter leading-none">{{ number_format($totalInvestment > 0 ? ($totalCollectedProfit / $totalInvestment) * 100 : 0, 1) }}%</h3>
                <div class="text-right">
                    <p class="text-[10px] font-black text-emerald-400 leading-none">Rp {{ number_format($requiredMonthlyProfit, 0, ',', '.') }}</p>
                    <p class="text-[8px] font-bold text-slate-500 uppercase tracking-tighter mt-1">/Bulan</p>
                </div>
            </div>
            <div class="w-full bg-slate-900/50 rounded-full h-2 overflow-hidden border border-white/5">
                @php 
                    $roiPct = $totalInvestment > 0 ? ($totalCollectedProfit / $totalInvestment) * 100 : 0;
                    $roiPct = min(100, max(0, $roiPct));
                @endphp
                <div class="bg-emerald-500 h-full rounded-full transition-all duration-1000 shadow-[0_0_10px_rgba(16,185,129,0.5)]" style="width: {{ $roiPct }}%"></div>
            </div>
        </div>

        <!-- Omset Kotor -->
        <div class="glass-card grad-emerald rounded-[2.5rem] p-7 transition-premium group relative overflow-hidden">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-emerald-500/10 rounded-full blur-2xl group-hover:bg-emerald-500/20 transition-premium"></div>
            <div class="flex justify-between items-start mb-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 flex items-center justify-center text-emerald-400 border border-emerald-500/20 group-hover:bg-emerald-600 group-hover:text-white transition-premium shadow-lg">
                    <i class="fas fa-arrow-down text-lg"></i>
                </div>
                <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Omset Kotor</span>
            </div>
            <h3 class="text-2xl font-black text-white tracking-tighter leading-none mb-2" id="valTotalIncome">Rp {{ number_format($totalIncome, 0, ',', '.') }}</h3>
            <div class="flex items-center gap-1.5 text-[9px] font-black text-emerald-400 uppercase tracking-tighter">
                <i class="fas fa-arrow-trend-up"></i>
                Trend Positif
            </div>
        </div>

        <!-- Pengeluaran -->
        <div class="glass-card grad-red rounded-[2.5rem] p-7 transition-premium group relative overflow-hidden">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-red-500/10 rounded-full blur-2xl group-hover:bg-red-500/20 transition-premium"></div>
            <div class="flex justify-between items-start mb-4">
                <div class="w-12 h-12 rounded-2xl bg-red-500/10 flex items-center justify-center text-red-400 border border-red-500/20 group-hover:bg-red-600 group-hover:text-white transition-premium shadow-lg">
                    <i class="fas fa-arrow-up text-lg"></i>
                </div>
                <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Total Pengeluaran</span>
            </div>
            <h3 class="text-2xl font-black text-white tracking-tight" id="valTotalExpense">Rp {{ number_format($totalExpense, 0, ',', '.') }}</h3>
            <div class="flex items-center gap-1.5 text-[9px] font-black text-red-400 uppercase tracking-tighter">
                <i class="fas fa-eye"></i>
                Monitor Ketat
            </div>
        </div>

        <!-- Saldo Bersih -->
        <div class="glass-card grad-amber rounded-[2.5rem] p-7 transition-premium group relative overflow-hidden bg-slate-900 shadow-2xl border-blue-500/20">
            <div class="absolute -right-4 -top-4 w-32 h-32 bg-blue-500/10 rounded-full blur-3xl group-hover:bg-blue-600/20 transition-premium"></div>
            <div class="flex justify-between items-start mb-4">
                <div class="w-12 h-12 rounded-2xl bg-blue-500/20 flex items-center justify-center text-blue-400 border border-blue-500/30 group-hover:bg-blue-600 group-hover:text-white transition-premium shadow-xl">
                    <i class="fas fa-wallet text-lg"></i>
                </div>
                <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Saldo Bersih (Profit)</span>
            </div>
            <h3 class="{{ $netProfit < 0 ? 'text-red-400' : 'text-white' }} text-2xl font-black tracking-tight" id="valNetProfit">
                {{ $netProfit < 0 ? '-' : '' }}Rp {{ number_format(abs($netProfit), 0, ',', '.') }}
            </h3>
            <div class="flex items-center gap-1.5 text-[9px] font-black text-blue-400 uppercase tracking-tighter">
                <i class="fas fa-chart-line"></i>
                Periode Terpilih
            </div>
        </div>

        <!-- Saldo Laci -->
        <div onclick="window.dispatchEvent(new CustomEvent('open-add-modal', { detail: { source: 'pos_cash', quick: true } }))" 
             class="glass-card grad-amber rounded-[2.5rem] p-7 transition-premium group relative overflow-hidden cursor-pointer">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-amber-500/10 rounded-full blur-2xl group-hover:bg-amber-500/20 transition-premium"></div>
            <div class="flex justify-between items-start mb-4">
                <div class="w-12 h-12 rounded-2xl bg-amber-500/10 flex items-center justify-center text-amber-400 border border-amber-500/20 group-hover:bg-amber-600 group-hover:text-white transition-premium shadow-lg">
                    <i class="fas fa-cash-register text-lg"></i>
                </div>
                <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Tunai / Laci</span>
            </div>
            <h3 class="text-2xl font-black text-white tracking-tight transition-all duration-500" id="valSaldoLaci">Rp {{ number_format($saldoLaci, 0, ',', '.') }}</h3>
            <div class="flex items-center gap-1.5 text-[9px] font-black text-amber-400 uppercase tracking-tighter">
                <i class="fas fa-money-bill-wave"></i>
                Saldo Fisik Saat Ini
            </div>
        </div>

        <!-- Saldo Bank Synced -->
        <div onclick="window.dispatchEvent(new CustomEvent('open-add-modal', { detail: { source: 'pos_bank', quick: true } }))" 
             class="glass-card grad-purple rounded-[2.5rem] p-7 transition-premium group relative overflow-hidden cursor-pointer">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-purple-500/10 rounded-full blur-2xl group-hover:bg-purple-500/20 transition-premium"></div>
            <div class="flex justify-between items-start mb-4">
                <div class="w-12 h-12 rounded-2xl bg-purple-500/10 flex items-center justify-center text-purple-400 border border-purple-500/20 group-hover:bg-purple-600 group-hover:text-white transition-premium shadow-lg">
                    <i class="fas fa-university text-lg"></i>
                </div>
                <div class="text-right">
                    <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest block">Saldo Bank</span>
                    <span class="inline-block mt-1 text-[8px] font-black bg-emerald-500/20 text-emerald-400 px-2 py-0.5 rounded-full uppercase tracking-widest">&check; Synced</span>
                </div>
            </div>
            <h3 class="text-2xl font-black text-white tracking-tight transition-all duration-500" id="valSaldoBankSynced">Rp {{ number_format($saldoBankSynced, 0, ',', '.') }}</h3>
            <div class="flex items-center gap-1.5 text-[9px] font-black text-purple-400 uppercase tracking-tighter">
                <i class="fas fa-check-circle"></i>
                Rekening Bisnis
            </div>
        </div>

        <!-- Adjustment Kas &rarr; buka modal khusus -->
        <div onclick="openModalAdjKas()" class="glass-card grad-amber rounded-[2.5rem] p-7 transition-premium group relative overflow-hidden bg-slate-900/40 border-amber-500/20 cursor-pointer hover:scale-[1.02] hover:shadow-[0_0_40px_-5px_rgba(245,158,11,0.35)]">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-amber-500/10 rounded-full blur-2xl group-hover:bg-amber-500/20 transition-premium"></div>
            <div class="flex justify-between items-start mb-4">
                <div class="w-12 h-12 rounded-2xl bg-amber-500/20 flex items-center justify-center text-amber-400 border border-amber-500/30 group-hover:bg-amber-600 group-hover:text-white transition-premium shadow-xl">
                    <i class="fas fa-sliders-h text-lg"></i>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Adjustment Kas</span>
                    <span class="w-7 h-7 rounded-lg bg-amber-500/10 group-hover:bg-amber-600 text-amber-400 group-hover:text-white transition-all flex items-center justify-center border border-amber-500/20">
                        <i class="fas fa-plus text-[10px]"></i>
                    </span>
                </div>
            </div>
            <div class="flex flex-col gap-1">
                <div class="flex justify-between items-center">
                    <span class="text-[9px] font-bold text-slate-500 uppercase">Masuk:</span>
                    <span class="text-xs font-black text-emerald-400" id="valAdjIn">+ Rp {{ number_format($totalAdjIn, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-[9px] font-bold text-slate-500 uppercase">Keluar:</span>
                    <span class="text-xs font-black text-red-400" id="valAdjOut">- Rp {{ number_format($totalAdjOut, 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-[9px] font-black text-amber-400 uppercase tracking-tighter">
                <i class="fas fa-info-circle"></i>
                Klik untuk Koreksi Saldo
            </div>
        </div>
    </div>

    <!-- 2.5 ACTION COMMAND CENTER -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 mb-6 sm:mb-8">
        <!-- Unified Sync Status -->
        <div class="lg:col-span-2 bg-slate-800/40 backdrop-blur-xl border border-white/5 rounded-[2.5rem] p-8 shadow-2xl relative overflow-hidden group">
            <div class="absolute -right-10 -top-10 w-40 h-40 bg-blue-500/5 rounded-full blur-3xl"></div>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-8 relative z-10">
                <div class="space-y-4 flex-1">
                    <h4 class="text-[10px] font-black text-slate-500 uppercase tracking-widest flex items-center gap-3">
                        <span class="w-2 h-4 bg-blue-500 rounded-full"></span>
                        Status Sinkronisasi Kas
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <!-- Laci Item -->
                        <div class="flex items-center gap-4 group/item">
                            <div class="w-14 h-14 rounded-2xl {{ $pendingLaciCount > 0 ? 'bg-amber-500/10 text-amber-400 border-amber-500/20 shadow-amber-500/10' : 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' }} border flex items-center justify-center text-2xl transition-premium shadow-lg">
                                <i class="fas fa-cash-register {{ $pendingLaciCount > 0 ? 'animate-pulse' : '' }}"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-slate-500 uppercase tracking-tighter mb-1">Tunai ke Laci</p>
                                <p class="text-xl font-black text-white">Rp {{ number_format($saldoLaciPending, 0, ',', '.') }}</p>
                                <p class="text-[9px] font-bold {{ $pendingLaciCount > 0 ? 'text-amber-500' : 'text-slate-500' }} uppercase">{{ $pendingLaciCount }} Menunggu Konfirmasi</p>
                            </div>
                        </div>
                        <!-- Bank Item -->
                        <div class="flex items-center gap-4 group/item">
                            <div class="w-14 h-14 rounded-2xl {{ $pendingBankCount > 0 ? 'bg-purple-500/10 text-purple-400 border-purple-500/20 shadow-purple-500/10' : 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' }} border flex items-center justify-center text-2xl transition-premium shadow-lg">
                                <i class="fas fa-university {{ $pendingBankCount > 0 ? 'animate-pulse' : '' }}"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-slate-500 uppercase tracking-tighter mb-1">QRIS & Bank</p>
                                <p class="text-xl font-black text-white">Rp {{ number_format($saldoBankPending, 0, ',', '.') }}</p>
                                <p class="text-[9px] font-bold {{ $pendingBankCount > 0 ? 'text-purple-500' : 'text-slate-500' }} uppercase">{{ $pendingBankCount }} Menunggu Konfirmasi</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col gap-3 shrink-0">
                    <button onclick="confirmSyncLaci()" {{ $pendingLaciCount == 0 ? 'disabled' : '' }}
                        class="px-6 py-3 rounded-xl font-black text-[10px] uppercase tracking-widest transition-premium {{ $pendingLaciCount > 0 ? 'bg-amber-500 hover:bg-amber-400 text-slate-900 shadow-xl shadow-amber-500/20 active:scale-95' : 'bg-slate-800 text-slate-600 cursor-not-allowed' }}">
                        <i class="fas fa-check-double mr-2"></i> Sinkron Laci
                    </button>
                    <button onclick="confirmSyncBank()" {{ $pendingBankCount == 0 ? 'disabled' : '' }}
                        class="px-6 py-3 rounded-xl font-black text-[10px] uppercase tracking-widest transition-premium {{ $pendingBankCount > 0 ? 'bg-purple-600 hover:bg-purple-500 text-white shadow-xl shadow-purple-500/20 active:scale-95' : 'bg-slate-800 text-slate-600 cursor-not-allowed' }}">
                        <i class="fas fa-sync mr-2"></i> Sinkron Bank
                    </button>
                </div>
            </div>
        </div>

        <!-- Internal Transfer -->
        <div class="bg-gradient-to-br from-blue-600 to-blue-800 rounded-[2.5rem] p-8 shadow-2xl relative overflow-hidden group border border-blue-400/20">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-white/10 rounded-full blur-2xl group-hover:scale-125 transition-premium"></div>
            <div class="relative z-10 flex flex-col h-full justify-between">
                <div>
                    <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center text-white mb-6 shadow-lg">
                        <i class="fas fa-paper-plane"></i>
                    </div>
                    <h4 class="text-xl font-black text-white tracking-tighter leading-none mb-2">Pindahkan Uang</h4>
                    <p class="text-[10px] font-bold text-blue-100 uppercase tracking-widest opacity-80">Setoran Laci &rarr; Bank</p>
                </div>
                <button onclick="document.getElementById('transferModal').classList.remove('hidden')" 
                    class="w-full mt-8 py-4 bg-white text-blue-700 rounded-2xl font-black text-xs uppercase tracking-widest transition-premium shadow-xl hover:shadow-white/20 active:scale-95">
                    Buat Transfer Baru
                </button>
            </div>
        </div>
    </div>

    <!-- 2.6 INCOME BREAKDOWN -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mb-6 sm:mb-8">
        <!-- Pemasukan QRIS/Bank -->
        <div class="bg-[#111827] border border-slate-800 rounded-[1.5rem] p-6 flex justify-between items-center shadow-lg transition-colors hover:bg-slate-800/80">
            <div>
                <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1">Pemasukan QRIS/Bank</p>
                <h3 class="text-2xl font-black text-[#C084FC]" id="valIncomeQris">Rp {{ number_format($incomeQris, 0, ',', '.') }}</h3>
            </div>
            <div class="w-12 h-12 rounded-full bg-[#111827] flex items-center justify-center text-[#C084FC] border border-slate-700/50">
                <i class="fas fa-qrcode text-lg"></i>
            </div>
        </div>

        <!-- Pemasukan Tunai -->
        <div class="bg-[#111827] border border-slate-800 rounded-[1.5rem] p-6 flex justify-between items-center shadow-lg transition-colors hover:bg-slate-800/80">
            <div>
                <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1">Pemasukan Tunai</p>
                <h3 class="text-2xl font-black text-[#34D399]" id="valIncomeCash">Rp {{ number_format($incomeCash, 0, ',', '.') }}</h3>
            </div>
            <div class="w-12 h-12 rounded-full bg-[#111827] flex items-center justify-center text-[#34D399] border border-slate-700/50">
                <i class="fas fa-money-bill-wave text-lg"></i>
            </div>
        </div>

        <!-- Pemasukan Transfer -->
        <div class="bg-[#111827] border border-slate-800 rounded-[1.5rem] p-6 flex justify-between items-center shadow-lg transition-colors hover:bg-slate-800/80">
            <div>
                <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1">Pemasukan Transfer</p>
                <h3 class="text-2xl font-black text-[#60A5FA]" id="valIncomeTransfer">Rp {{ number_format($incomeTransfer, 0, ',', '.') }}</h3>
            </div>
            <div class="w-12 h-12 rounded-full bg-[#111827] flex items-center justify-center text-[#60A5FA] border border-slate-700/50">
                <i class="fas fa-university text-lg"></i>
            </div>
        </div>
    </div>

    <!-- 3. TARGET VS REALISASI (NEW SECTION) -->
    <div class="bg-slate-800/40 backdrop-blur-md border border-white/5 rounded-[2rem] p-8 shadow-2xl mb-8 relative overflow-hidden">
        <div class="flex justify-between items-end mb-8">
            <div>
                <h3 class="text-xl font-black text-white tracking-tighter flex items-center gap-3">
                    <span class="w-3 h-8 bg-blue-500 rounded-full"></span>
                    Target vs Realisasi
                </h3>
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-2">Pantau pencapaian target bulan ini</p>
            </div>
            <button onclick="document.getElementById('targetModal').classList.remove('hidden')" class="px-5 py-2.5 bg-slate-900/50 border border-white/5 hover:border-blue-500/30 hover:bg-blue-500/10 text-blue-400 rounded-xl text-[10px] font-black uppercase tracking-widest transition-premium">
                <i class="fas fa-bullseye mr-2"></i> Atur Target
            </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-8">
            <!-- Omzet -->
            <div>
                <div class="flex justify-between items-center mb-2">
                    <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Omzet</p>
                    <p class="text-[10px] font-bold {{ $targetData['omzet']['progress'] >= 100 ? 'text-emerald-400' : 'text-blue-400' }}">{{ $targetData['omzet']['progress'] }}%</p>
                </div>
                <div class="w-full bg-slate-900/50 rounded-full h-3 mb-3 border border-white/5 overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-1000 {{ $targetData['omzet']['progress'] >= 100 ? 'bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.5)]' : 'bg-blue-500 shadow-[0_0_10px_rgba(59,130,246,0.5)]' }}" style="width: {{ min(100, $targetData['omzet']['progress']) }}%"></div>
                </div>
                <div class="flex justify-between items-end">
                    <div>
                        <p class="text-sm font-black text-white">Rp {{ number_format($targetData['omzet']['realisasi'], 0, ',', '.') }}</p>
                        <p class="text-[9px] font-bold text-slate-500">dari Rp {{ number_format($targetData['omzet']['target'], 0, ',', '.') }}</p>
                    </div>
                    @if($targetData['omzet']['sisa'] > 0)
                        <p class="text-[9px] font-black text-amber-400">Sisa: Rp {{ number_format($targetData['omzet']['sisa'], 0, ',', '.') }}</p>
                    @endif
                </div>
            </div>

            <!-- Profit -->
            <div>
                <div class="flex justify-between items-center mb-2">
                    <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Profit Bersih</p>
                    <p class="text-[10px] font-bold {{ $targetData['profit']['progress'] >= 100 ? 'text-emerald-400' : 'text-purple-400' }}">{{ $targetData['profit']['progress'] }}%</p>
                </div>
                <div class="w-full bg-slate-900/50 rounded-full h-3 mb-3 border border-white/5 overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-1000 {{ $targetData['profit']['progress'] >= 100 ? 'bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.5)]' : 'bg-purple-500 shadow-[0_0_10px_rgba(168,85,247,0.5)]' }}" style="width: {{ min(100, $targetData['profit']['progress']) }}%"></div>
                </div>
                <div class="flex justify-between items-end">
                    <div>
                        <p class="text-sm font-black text-white">Rp {{ number_format($targetData['profit']['realisasi'], 0, ',', '.') }}</p>
                        <p class="text-[9px] font-bold text-slate-500">dari Rp {{ number_format($targetData['profit']['target'], 0, ',', '.') }}</p>
                    </div>
                    @if($targetData['profit']['sisa'] > 0)
                        <p class="text-[9px] font-black text-amber-400">Sisa: Rp {{ number_format($targetData['profit']['sisa'], 0, ',', '.') }}</p>
                    @endif
                </div>
            </div>

            <!-- Transaksi -->
            <div>
                <div class="flex justify-between items-center mb-2">
                    <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Transaksi</p>
                    <p class="text-[10px] font-bold {{ $targetData['transaksi']['progress'] >= 100 ? 'text-emerald-400' : 'text-amber-400' }}">{{ $targetData['transaksi']['progress'] }}%</p>
                </div>
                <div class="w-full bg-slate-900/50 rounded-full h-3 mb-3 border border-white/5 overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-1000 {{ $targetData['transaksi']['progress'] >= 100 ? 'bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.5)]' : 'bg-amber-500 shadow-[0_0_10px_rgba(245,158,11,0.5)]' }}" style="width: {{ min(100, $targetData['transaksi']['progress']) }}%"></div>
                </div>
                <div class="flex justify-between items-end">
                    <div>
                        <p class="text-sm font-black text-white">{{ number_format($targetData['transaksi']['realisasi'], 0, ',', '.') }} Tx</p>
                        <p class="text-[9px] font-bold text-slate-500">dari {{ number_format($targetData['transaksi']['target'], 0, ',', '.') }} Tx</p>
                    </div>
                    @if($targetData['transaksi']['sisa'] > 0)
                        <p class="text-[9px] font-black text-amber-400">Sisa: {{ number_format($targetData['transaksi']['sisa'], 0, ',', '.') }} Tx</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- 4. METODE PEMBAYARAN & RISIKO CASHFLOW (UPGRADED) -->
    <div class="bg-slate-800/40 backdrop-blur-md border border-white/5 rounded-[2rem] p-8 shadow-2xl mb-8 relative overflow-hidden">
        <h3 class="text-xl font-black text-white tracking-tighter flex items-center gap-3 mb-8">
            <span class="w-3 h-8 bg-purple-500 rounded-full"></span>
            Metode Pembayaran & Analisis Risiko
        </h3>
        
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-8">
            <!-- AI Insights Cards -->
            <div class="xl:col-span-4 space-y-4">
                <div class="bg-slate-900/50 border border-white/5 rounded-2xl p-5 flex gap-4 items-start hover:border-blue-500/30 transition-premium group">
                    <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center shrink-0 border border-blue-500/20 group-hover:bg-blue-500 group-hover:text-white transition-all">
                        <i class="fas fa-crown"></i>
                    </div>
                    <div>
                        <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1">Metode Dominan</p>
                        <h4 class="text-base font-black text-white mb-1">
                            {{ ucfirst($pmInsights['dominant_method']) }} mendominasi {{ $pmInsights['dominant_percentage'] }}%
                        </h4>
                        <p class="text-xs text-slate-400">Nilai transaksi tertinggi berasal dari metode ini.</p>
                    </div>
                </div>

                <div class="bg-slate-900/50 border border-white/5 rounded-2xl p-5 flex gap-4 items-start hover:border-emerald-500/30 transition-premium group">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center shrink-0 border border-emerald-500/20 group-hover:bg-emerald-500 group-hover:text-white transition-all">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div>
                        <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1">Pertumbuhan QRIS</p>
                        <h4 class="text-base font-black text-white mb-1">
                            QRIS {{ ($pmAnalytics['qris']['growth'] ?? 0) >= 0 ? 'naik' : 'turun' }} {{ abs($pmAnalytics['qris']['growth'] ?? 0) }}% periode ini
                        </h4>
                        <p class="text-xs text-slate-400">Tren adopsi pembayaran digital oleh pelanggan.</p>
                    </div>
                </div>

                <div class="bg-slate-900/50 border border-{{ $pmInsights['risk_color'] }}-500/30 rounded-2xl p-5 flex gap-4 items-start bg-gradient-to-br from-{{ $pmInsights['risk_color'] }}-500/5 to-transparent">
                    <div class="w-10 h-10 rounded-xl bg-{{ $pmInsights['risk_color'] }}-500/20 text-{{ $pmInsights['risk_color'] }}-400 flex items-center justify-center shrink-0 border border-{{ $pmInsights['risk_color'] }}-500/30">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div>
                        <p class="text-[9px] font-black text-{{ $pmInsights['risk_color'] }}-400 uppercase tracking-widest mb-1">Risiko Cashflow: {{ $pmInsights['risk_level'] }}</p>
                        <p class="text-xs text-slate-300 leading-relaxed">{{ $pmInsights['risk_msg'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Trend Multi-line Chart & Detailed Stats -->
            <div class="xl:col-span-8 flex flex-col gap-6">
                <!-- Detailed Horizontal Progress -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach(['cash' => ['name' => 'Tunai / Cash', 'color' => 'amber', 'icon' => 'fa-money-bill-wave'], 
                              'qris' => ['name' => 'QRIS', 'color' => 'purple', 'icon' => 'fa-qrcode'], 
                              'transfer' => ['name' => 'Transfer Bank', 'color' => 'blue', 'icon' => 'fa-building-columns'],
                              'debit' => ['name' => 'Debit/Kredit', 'color' => 'teal', 'icon' => 'fa-credit-card']] as $key => $meta)
                    @if(isset($pmAnalytics[$key]) && $pmAnalytics[$key]['revenue'] > 0)
                    <div class="bg-slate-900/30 rounded-xl p-4 border border-white/5">
                        <div class="flex justify-between items-center mb-3">
                            <div class="flex items-center gap-2">
                                <i class="fas {{ $meta['icon'] }} text-{{ $meta['color'] }}-400"></i>
                                <span class="text-xs font-bold text-white">{{ $meta['name'] }}</span>
                            </div>
                            <span class="text-[10px] font-black text-slate-400">{{ $pmAnalytics[$key]['percentage'] }}%</span>
                        </div>
                        <div class="w-full bg-slate-800 rounded-full h-1.5 mb-3">
                            <div class="bg-{{ $meta['color'] }}-500 h-1.5 rounded-full" style="width: {{ $pmAnalytics[$key]['percentage'] }}%"></div>
                        </div>
                        <div class="flex justify-between items-end">
                            <div>
                                <p class="text-[10px] font-bold text-slate-500 mb-0.5">Total Revenue</p>
                                <p class="text-sm font-black text-white">Rp {{ number_format($pmAnalytics[$key]['revenue'], 0, ',', '.') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] font-bold text-slate-500 mb-0.5">Avg: Rp {{ number_format($pmAnalytics[$key]['avg'], 0, ',', '.') }}</p>
                                <p class="text-[10px] font-black {{ $pmAnalytics[$key]['growth'] >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                                    <i class="fas fa-arrow-{{ $pmAnalytics[$key]['growth'] >= 0 ? 'up' : 'down' }}"></i> {{ abs($pmAnalytics[$key]['growth']) }}%
                                </p>
                            </div>
                        </div>
                    </div>
                    @endif
                    @endforeach
                </div>

                <!-- ApexChart Wrapper -->
                <div class="bg-slate-900/30 border border-white/5 rounded-2xl p-4 flex-1">
                    <div class="w-full h-[250px]" id="paymentMethodTrendChart"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ROI INFORMATION CARD -->
    @if($totalInvestment > 0)
    <div class="bg-indigo-600/10 border border-indigo-500/20 rounded-2xl p-5 flex flex-col md:flex-row items-center gap-6 mb-6">
        <div class="w-16 h-16 rounded-2xl bg-indigo-500/20 flex items-center justify-center text-indigo-400 shrink-0">
            <i class="fas fa-rocket text-2xl"></i>
        </div>
        <div class="flex-1 text-center md:text-left">
            <h4 class="text-lg font-black text-white">Target Balik Modal: <span class="text-indigo-400">{{ $targetPaybackMonths }} Bulan</span></h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-3">
                <div class="bg-slate-900/50 p-3 rounded-xl border border-slate-800">
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Target Laba /Bln</p>
                    <p class="text-sm font-black text-white">Rp {{ number_format($requiredMonthlyProfit, 0, ',', '.') }}</p>
                </div>
                <div class="bg-slate-900/50 p-3 rounded-xl border border-slate-800">
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Target Laba /Hari</p>
                    <p class="text-sm font-black text-white">Rp {{ number_format($requiredDailyProfit, 0, ',', '.') }}</p>
                </div>
                <div class="bg-slate-900/50 p-3 rounded-xl border border-slate-800">
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Kondisi Saat Ini</p>
                    @if($profitGap >= 0)
                        <p class="text-sm font-black text-emerald-400">+ Rp {{ number_format($profitGap, 0, ',', '.') }} (Surplus)</p>
                    @else
                        <p class="text-sm font-black text-red-400">- Rp {{ number_format(abs($profitGap), 0, ',', '.') }} (Kurang)</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="shrink-0">
            <button onclick="document.getElementById('modalTargetBEP').classList.remove('hidden')" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition-all">
                Ubah Target
            </button>
        </div>
    </div>
    @endif

    <!-- 3. MAIN AREA SPLIT -->
    <div class="flex flex-col lg:flex-row gap-6 mb-6">
        <!-- LEFT (70%): Grafik Trend Laba Bersih -->
        <div class="w-full lg:w-[70%] bg-[#111827] border border-slate-700/50 rounded-2xl p-5 relative overflow-hidden">
            <div class="flex justify-between items-start mb-6 relative z-10">
                <div>
                    <h2 class="text-xl font-black text-white tracking-tight">Trend Laba Bersih</h2>
                    <p class="text-xs text-slate-400 font-medium mt-1" id="valProfitSummary">{{ $profitInsights['summary'] }}</p>
                </div>
                <div class="flex gap-4">
                    <div class="bg-slate-800/50 border border-emerald-500/10 rounded-xl px-4 py-2 text-right">
                        <p class="text-[9px] text-slate-500 uppercase tracking-widest font-bold mb-1">Rata-rata Profit</p>
                        <p class="text-sm font-black text-emerald-400" id="valAvgProfit">Rp {{ number_format($profitInsights['avg_profit'], 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-slate-800/50 border border-blue-500/10 rounded-xl px-4 py-2 text-right">
                        <p class="text-[9px] text-slate-500 uppercase tracking-widest font-bold mb-1">Prediksi (Bulan Depan)</p>
                        <p class="text-sm font-black text-blue-400" id="valPredictedProfit">Rp {{ number_format($profitInsights['predicted_profit'], 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
            <div class="w-full h-[300px] relative z-10" id="cashflowChart"></div>
            
            <!-- Decorative Glow -->
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[80%] h-[80%] bg-blue-500/5 blur-[100px] rounded-full pointer-events-none"></div>
        </div>

        <!-- RIGHT (30%): Quick Insight Panel -->
        <div class="w-full lg:w-[30%] space-y-4">
            
            <!-- Insight 1: Avg Income -->
            <div class="bg-[#111827] border border-slate-700/50 rounded-2xl p-4 flex items-center gap-4 hover:bg-[#1F2937] transition-colors cursor-default">
                <div class="w-12 h-12 rounded-full bg-emerald-500/10 flex items-center justify-center text-emerald-400 flex-shrink-0">
                    <i class="fas fa-chart-line text-lg"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-400 font-medium uppercase tracking-wider">Rata-rata Pemasukan / Hari</p>
                    <p class="text-lg font-black text-white mt-0.5" id="valAvgIncome">Rp {{ number_format($avgIncome, 0, ',', '.') }}</p>
                </div>
            </div>

            <!-- Tambahan Insight jika diperlukan (bisa untuk metric lain di masa depan) -->
            <div class="bg-[#111827] border border-slate-700/50 rounded-2xl p-4 flex flex-col justify-center items-center h-full min-h-[200px] border-dashed">
                <i class="fas fa-chart-pie text-slate-600 text-3xl mb-3"></i>
                <p class="text-sm text-slate-500 font-medium">Ruang Insight Tambahan</p>
            </div>
        </div>
    </div>

    <!-- 5. BIAYA OPERASIONAL & ANALISIS PENGELUARAN -->
    <div class="bg-slate-800/40 backdrop-blur-md border border-white/5 rounded-[2rem] p-8 shadow-2xl mb-8 relative overflow-hidden">
        <h3 class="text-xl font-black text-white tracking-tighter flex items-center gap-3 mb-8">
            <span class="w-3 h-8 bg-red-500 rounded-full"></span>
            Biaya Operasional & Analisis Pengeluaran
        </h3>
        
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-8">
            <!-- Left: Overview & Insights -->
            <div class="xl:col-span-4 space-y-4">
                <!-- Total Operational Cost -->
                <div class="bg-slate-900/50 border border-white/5 rounded-2xl p-6 flex flex-col justify-center relative overflow-hidden group hover:border-red-500/30 transition-premium">
                    <div class="absolute -right-6 -top-6 w-32 h-32 bg-red-500/5 rounded-full blur-2xl group-hover:bg-red-500/10 transition-all duration-500"></div>
                    <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 relative z-10">Total Operasional</p>
                    <h4 class="text-3xl font-black text-white mb-2 relative z-10">Rp {{ number_format($expenseInsights['total'], 0, ',', '.') }}</h4>
                    <div class="flex items-center gap-2 relative z-10">
                        <span class="px-2 py-1 rounded-lg text-[10px] font-bold {{ $expenseInsights['is_increasing'] ? 'bg-red-500/20 text-red-400' : 'bg-emerald-500/20 text-emerald-400' }}">
                            <i class="fas fa-arrow-{{ $expenseInsights['is_increasing'] ? 'up' : 'down' }}"></i> {{ abs($expenseInsights['growth']) }}%
                        </span>
                        <span class="text-xs text-slate-400">vs periode lalu</span>
                    </div>
                </div>

                <!-- AI Insight -->
                <div class="bg-slate-900/50 border border-{{ $expenseInsights['color'] }}-500/30 rounded-2xl p-5 flex gap-4 items-start bg-gradient-to-br from-{{ $expenseInsights['color'] }}-500/5 to-transparent">
                    <div class="w-10 h-10 rounded-xl bg-{{ $expenseInsights['color'] }}-500/20 text-{{ $expenseInsights['color'] }}-400 flex items-center justify-center shrink-0 border border-{{ $expenseInsights['color'] }}-500/30">
                        <i class="fas {{ $expenseInsights['icon'] }}"></i>
                    </div>
                    <div>
                        <p class="text-[9px] font-black text-{{ $expenseInsights['color'] }}-400 uppercase tracking-widest mb-1">Analisis Sistem</p>
                        <p class="text-xs text-slate-300 leading-relaxed">{{ $expenseInsights['message'] }}</p>
                    </div>
                </div>
                
                <!-- Most Wasteful -->
                @if($biggestExpense)
                <div class="bg-slate-900/50 border border-white/5 rounded-2xl p-5 flex gap-4 items-start hover:border-amber-500/30 transition-premium">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center shrink-0 border border-amber-500/20">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div>
                        <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1">Pengeluaran Terbesar</p>
                        <h4 class="text-sm font-bold text-white">{{ $biggestExpense->category }}</h4>
                        <p class="text-amber-400 font-bold mt-1 text-sm">- Rp {{ number_format($biggestExpense->amount, 0, ',', '.') }}</p>
                    </div>
                </div>
                @endif
            </div>

            <!-- Right: Breakdown Progress -->
            <div class="xl:col-span-8 flex flex-col">
                <div class="bg-slate-900/30 rounded-2xl p-6 border border-white/5 h-full">
                    <h4 class="text-sm font-black text-white mb-6 uppercase tracking-wider flex items-center gap-2">
                        <i class="fas fa-list-ul text-slate-400"></i> Proporsi Pengeluaran
                    </h4>
                    
                    <div class="space-y-5">
                        @forelse($expenseByCategory as $index => $cat)
                            @php
                                $percentage = $expenseInsights['total'] > 0 ? round(($cat->total / $expenseInsights['total']) * 100) : 0;
                                // Variasi warna untuk visualisasi yang menarik
                                $colors = ['red', 'orange', 'amber', 'rose', 'pink'];
                                $color = $colors[$index % count($colors)];
                            @endphp
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-xs font-bold text-slate-300">{{ $cat->category }}</span>
                                    <div class="text-right">
                                        <span class="text-sm font-black text-white">Rp {{ number_format($cat->total, 0, ',', '.') }}</span>
                                        <span class="text-[10px] font-bold text-slate-500 ml-2">({{ $percentage }}%)</span>
                                    </div>
                                </div>
                                <div class="w-full bg-slate-800 rounded-full h-2 overflow-hidden">
                                    <div class="bg-{{ $color }}-500 h-full rounded-full transition-all duration-1000 shadow-[0_0_10px_rgba(239,68,68,0.3)]" style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                        @empty
                            <div class="flex flex-col items-center justify-center py-10 text-slate-500">
                                <i class="fas fa-box-open text-4xl mb-3 opacity-20"></i>
                                <p class="text-sm font-medium">Belum ada pengeluaran pada periode ini</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 6. TRANSACTION LIST (TIMELINE STYLE) -->
    <div class="bg-[#111827] border border-slate-700/50 rounded-2xl p-5 mb-6">
        <!-- 7. FILTER & SEARCH -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div class="flex items-center gap-3">
                <h2 class="text-lg font-bold text-white">Riwayat Transaksi</h2>
                <form action="" method="GET" class="flex items-center" onchange="this.submit()">
                    @foreach(request()->except(['per_page', 'page']) as $key => $val)
                        @if(is_array($val))
                            @foreach($val as $v)
                                <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                            @endforeach
                        @else
                            <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                        @endif
                    @endforeach
                    <select name="per_page" class="bg-[#0F172A] border border-slate-700 text-slate-300 text-[10px] font-black uppercase tracking-widest rounded-xl px-3 py-1.5 hover:bg-slate-800 transition-all focus:outline-none focus:border-blue-500 cursor-pointer">
                        <option value="5" {{ request('per_page') == 5 ? 'selected' : '' }}>5 Baris</option>
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10 Baris</option>
                        <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20 Baris</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 Baris</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 Baris</option>
                    </select>
                </form>
            </div>
            
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <!-- Search Input -->
                <div class="relative w-full sm:w-64">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                    <input type="text" id="searchInput" placeholder="Cari transaksi..." class="w-full bg-[#0F172A] border border-slate-700 rounded-full pl-9 pr-4 py-2 text-sm text-slate-300 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors">
                </div>
                
                <!-- Filter Pills -->
                <div class="flex bg-[#0F172A] border border-slate-700 rounded-full p-1 shrink-0 overflow-x-auto scrollbar-hide max-w-full max-h-[90vh] overflow-y-auto scrollbar-hide ">
                    <button class="filter-btn active px-3 py-1 text-xs font-medium rounded-full bg-slate-700 text-white transition-all whitespace-nowrap" data-filter="all">Semua</button>
                    <button class="filter-btn px-3 py-1 text-xs font-medium rounded-full text-slate-400 hover:text-slate-200 transition-all whitespace-nowrap" data-filter="Penjualan">Penjualan</button>
                    <button class="filter-btn px-3 py-1 text-xs font-medium rounded-full text-slate-400 hover:text-slate-200 transition-all whitespace-nowrap" data-filter="Input Saldo Manual">Input Saldo</button>
                    <button class="filter-btn px-3 py-1 text-xs font-medium rounded-full text-slate-400 hover:text-slate-200 transition-all whitespace-nowrap" data-filter="Transfer Internal">Transfer</button>
                    <button class="filter-btn px-3 py-1 text-xs font-medium rounded-full text-slate-400 hover:text-slate-200 transition-all whitespace-nowrap" data-filter="adjustment">Penyesuaian Kas</button>

                    <button class="filter-btn px-3 py-1 text-xs font-medium rounded-full text-slate-400 hover:text-slate-200 transition-all whitespace-nowrap" data-filter="income">Uang Masuk</button>
                    <button class="filter-btn px-3 py-1 text-xs font-medium rounded-full text-slate-400 hover:text-slate-200 transition-all whitespace-nowrap" data-filter="expense">Uang Keluar</button>
                </div>
            </div>
        </div>

        <!-- TIMELINE LIST -->
        <div id="transactionsContainer">
            @include('cashflow._transactions')
        </div>
        
        <div id="paginationContainer" class="mt-6 pt-4 border-t border-slate-800 {{ $cashflows->hasPages() ? '' : 'hidden' }}">
            {{ $cashflows->links('pagination::tailwind') }}
        </div>
    </div>

</div>

{{-- Modal Transfer --}}
<div id="transferModal" x-data="{ amount: '', amountFormatted: '', formatCurrency(value) { let val = value.toString().replace(/[^0-9]/g, ''); if(val) { val = parseInt(val, 10).toString(); return val.replace(/\B(?=(\d{3})+(?!\d))/g, '.'); } return ''; } }" class="fixed inset-0 bg-[#0F172A]/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-[#111827] rounded-2xl w-full max-w-md p-6 border border-slate-700 shadow-2xl transform scale-100 transition-transform max-h-[90vh] overflow-y-auto scrollbar-hide ">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-bold text-white"><i class="fas fa-exchange-alt text-blue-400 mr-2"></i> Pindahkan Uang</h3>
            <button onclick="document.getElementById('transferModal').classList.add('hidden')" class="text-slate-400 hover:text-white transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form action="{{ route('cashflow.transfer') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1.5 uppercase tracking-wider">Arah Transfer</label>
                    <select name="direction" class="w-full bg-[#0F172A] border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required>
                        <option value="laci_to_bank">Laci (Kasir) &rarr; Bank Rekening</option>
                        <option value="bank_to_laci">Bank Rekening &rarr; Laci (Kasir)</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1.5 uppercase tracking-wider">Nominal (Rp)</label>
                    <input type="text" x-model="amountFormatted" @input="amountFormatted = formatCurrency($event.target.value); amount = amountFormatted.replace(/\./g, '')" class="w-full bg-[#0F172A] border border-slate-700 rounded-xl px-4 py-2.5 text-lg font-bold text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 placeholder-slate-600" required placeholder="0">
                    <input type="hidden" name="amount" :value="amount">
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1.5 uppercase tracking-wider">Catatan (Opsional)</label>
                    <textarea name="notes" rows="2" class="w-full bg-[#0F172A] border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" placeholder="Keterangan transfer..."></textarea>
                </div>
                
                <div class="pt-4 mt-6 border-t border-slate-700">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 rounded-xl transition-colors shadow-lg shadow-blue-500/20">
                        Konfirmasi Transfer
                    </button>
                    <p class="text-[10px] text-center text-slate-500 mt-3 italic">Transaksi ini akan mencatat pengeluaran di sumber asal dan pemasukan di tujuan.</p>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit --}}
<div id="editModal" x-data="{ 
    id: '', 
    type: 'income', 
    source: 'manual', 
    category: '', 
    amount: '', 
    amountFormatted: '', 
    description: '', 
    transaction_date: '',
    formatCurrency(value) { 
        let val = value.toString().replace(/[^0-9]/g, ''); 
        if(val) { 
            val = parseInt(val, 10).toString(); 
            return val.replace(/\B(?=(\d{3})+(?!\d))/g, '.'); 
        } 
        return ''; 
    },
    openEdit(data) {
        this.id = data.id;
        this.type = data.type;
        this.source = data.source;
        this.category = data.category;
        this.amount = data.amount;
        this.amountFormatted = this.formatCurrency(data.amount);
        this.description = data.description;
        this.transaction_date = data.transaction_date;
        document.getElementById('editModal').classList.remove('hidden');
        document.getElementById('editForm').action = '/cashflow/' + data.id;
    }
}" @open-edit.window="openEdit($event.detail)" class="fixed inset-0 bg-[#0F172A]/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-[#111827] rounded-2xl w-full max-w-md p-6 border border-slate-700 shadow-2xl transform scale-100 transition-transform max-h-[90vh] overflow-y-auto scrollbar-hide ">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-bold text-white">Edit Transaksi</h3>
            <button onclick="document.getElementById('editModal').classList.add('hidden')" class="text-slate-400 hover:text-white transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1.5 uppercase tracking-wider">Tanggal</label>
                    <input type="date" name="transaction_date" x-model="transaction_date" class="w-full bg-[#0F172A] border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required>
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1.5 uppercase tracking-wider">Jenis Transaksi</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="cursor-pointer relative">
                            <input type="radio" name="type" value="income" x-model="type" class="peer sr-only">
                            <div class="w-full text-center px-4 py-2.5 bg-[#0F172A] border border-slate-700 rounded-xl text-sm font-medium text-slate-400 peer-checked:bg-emerald-500/10 peer-checked:border-emerald-500/50 peer-checked:text-emerald-400 transition-all">
                                Pemasukan
                            </div>
                        </label>
                        <label class="cursor-pointer relative">
                            <input type="radio" name="type" value="expense" x-model="type" class="peer sr-only">
                            <div class="w-full text-center px-4 py-2.5 bg-[#0F172A] border border-slate-700 rounded-xl text-sm font-medium text-slate-400 peer-checked:bg-red-500/10 peer-checked:border-red-500/50 peer-checked:text-red-400 transition-all">
                                Pengeluaran
                            </div>
                        </label>
                    </div>
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1.5 uppercase tracking-wider">Sumber Dana</label>
                    <select name="source" x-model="source" class="w-full bg-[#0F172A] border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required>
                        <option value="pos_cash">Tunai</option>
                        <option value="pos_bank">Bank / QRIS</option>
                        <option value="transfer">Transfer Kasir ke Bank</option>
                        <option value="manual">Lainnya (Manual)</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1.5 uppercase tracking-wider">Kategori</label>
                    <input type="text" name="category" x-model="category" class="w-full bg-[#0F172A] border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required>
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1.5 uppercase tracking-wider">Nominal (Rp)</label>
                    <input type="text" x-model="amountFormatted" @input="amountFormatted = formatCurrency($event.target.value); amount = amountFormatted.replace(/\./g, '')" class="w-full bg-[#0F172A] border border-slate-700 rounded-xl px-4 py-2.5 text-lg font-bold text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 placeholder-slate-600" required>
                    <input type="hidden" name="amount" :value="amount">
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1.5 uppercase tracking-wider">Catatan</label>
                    <textarea name="description" x-model="description" rows="2" class="w-full bg-[#0F172A] border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required></textarea>
                </div>
                
                <div class="pt-4 mt-6 border-t border-slate-700">
                    <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-3 rounded-xl transition-colors shadow-lg shadow-emerald-500/20">
                        Perbarui Transaksi
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Modal Add / Quick Input Revamped --}}
<div id="addModal" x-data="{ 
    amount: '', 
    amountFormatted: '', 
    type: 'masuk', {{-- masuk / keluar --}}
    source: 'pos_cash',
    category: 'Input Saldo Manual',
    isQuick: false,
    dateText: '{{ date('d/m/Y') }}',
    sourceText: 'Tunai',
    formatCurrency(value) { 
        let val = value.toString().replace(/[^0-9]/g, ''); 
        if(val) { 
            val = parseInt(val, 10).toString(); 
            return val.replace(/\B(?=(\d{3})+(?!\d))/g, '.'); 
        } 
        return ''; 
    },
    openAdd(data = {}) {
        this.isQuick = !!data.quick;
        this.type = data.type === 'expense' ? 'expense' : 'income';
        this.source = data.source || 'pos_cash';
        this.category = data.category || 'Input Saldo Manual';
        this.sourceText = this.source === 'pos_cash' ? 'Tunai / Laci' : 'Saldo Bank';
        this.amount = '';
        this.amountFormatted = '';
        document.getElementById('addModal').classList.remove('hidden');
    }
}" @open-add-modal.window="openAdd($event.detail)" class="fixed inset-0 bg-[#0F172A]/90 backdrop-blur-md z-[100] hidden flex items-center justify-center p-4">
    <div class="bg-[#111827] rounded-[2.5rem] w-full max-w-md border border-white/10 shadow-[0_0_50px_-12px_rgba(0,0,0,0.5)] overflow-hidden transform transition-all duration-500 max-h-[90vh] overflow-y-auto scrollbar-hide "
         :class="type === 'income' ? 'shadow-emerald-500/10' : 'shadow-red-500/10'">
        
        <!-- Modal Header -->
        <div class="p-8 pb-4 flex justify-between items-center relative overflow-hidden">
            <div class="absolute inset-0 opacity-10 pointer-events-none transition-all duration-700"
                 :class="type === 'income' ? 'bg-emerald-500' : 'bg-red-500'"></div>
            
            <div class="relative z-10 flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-xl transition-all duration-500 shadow-lg"
                     :class="type === 'income' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-red-500/20 text-red-400 border border-red-500/30'">
                    <i :class="type === 'income' ? 'fas fa-plus-circle' : 'fas fa-minus-circle'"></i>
                </div>
                <div>
                    <h3 class="text-xl font-black text-white tracking-tighter leading-none" x-text="type === 'income' ? 'Menambah Saldo' : 'Mengurangi Saldo'"></h3>
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-1.5" x-text="isQuick ? 'Input Saldo Cepat' : 'Transaksi Manual'"></p>
                </div>
            </div>
            <button onclick="document.getElementById('addModal').classList.add('hidden')" class="w-10 h-10 rounded-full bg-white/5 hover:bg-white/10 text-slate-400 hover:text-white transition-all flex items-center justify-center relative z-10">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form :action="isQuick ? '{{ route('cashflow.quick-store') }}' : '{{ route('cashflow.store') }}'" method="POST" class="p-8 pt-4">
            @csrf
            <div class="space-y-6">
                <!-- Transaction Type Toggle (Only if isQuick) -->
                <template x-if="isQuick">
                    <div class="flex p-1.5 bg-slate-900/80 rounded-2xl border border-white/5 shadow-inner">
                        <button type="button" @click="type = 'income'" 
                                class="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all duration-500"
                                :class="type === 'income' ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-500/20' : 'text-slate-500 hover:text-slate-300'">
                            <i class="fas fa-arrow-up-long"></i>
                            Tambah (+)
                        </button>
                        <button type="button" @click="type = 'expense'" 
                                class="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all duration-500"
                                :class="type === 'expense' ? 'bg-red-600 text-white shadow-lg shadow-red-500/20' : 'text-slate-500 hover:text-slate-300'">
                            <i class="fas fa-arrow-down-long"></i>
                            Kurangi (-)
                        </button>
                    </div>
                </template>

                <!-- Detailed Fields (Only if NOT isQuick) -->
                <div x-show="!isQuick" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Tanggal</label>
                            <input type="date" name="transaction_date" value="{{ date('Y-m-d') }}" class="w-full bg-slate-900/50 border border-white/5 rounded-xl px-4 py-3 text-sm text-white focus:ring-2 focus:ring-blue-500/50 transition-all">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Tipe Transaksi</label>
                            <select name="type" x-model="type" class="w-full bg-slate-900/50 border border-white/5 rounded-xl px-4 py-3 text-sm text-white focus:ring-2 focus:ring-blue-500/50 transition-all">
                                <option value="income">Pemasukan</option>
                                <option value="expense">Pengeluaran</option>
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Sumber Dana</label>
                        <select name="source" x-model="source" class="w-full bg-slate-900/50 border border-white/5 rounded-xl px-4 py-3 text-sm text-white focus:ring-2 focus:ring-blue-500/50 transition-all">
                            <option value="pos_cash">Tunai / Laci</option>
                            <option value="pos_bank">Bank / QRIS</option>
                            <option value="transfer">Transfer Internal</option>
                            <option value="manual">Lainnya (Manual)</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Kategori</label>
                        <input type="text" name="category" x-model="category" class="w-full bg-slate-900/50 border border-white/5 rounded-xl px-4 py-3 text-sm text-white focus:ring-2 focus:ring-blue-500/50 transition-all" placeholder="Misal: Operasional, Gaji, dsb">
                    </div>
                </div>

                <!-- Quick Mode Info Badges -->
                <template x-if="isQuick">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-slate-900/50 rounded-2xl p-4 border border-white/5">
                            <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Sumber Saldo</p>
                            <div class="flex items-center gap-2">
                                <i :class="source === 'pos_cash' ? 'fas fa-cash-register text-amber-400' : 'fas fa-university text-purple-400'"></i>
                                <span class="text-xs font-black text-white" x-text="sourceText"></span>
                            </div>
                        </div>
                        <div class="bg-slate-900/50 rounded-2xl p-4 border border-white/5">
                            <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Tanggal</p>
                            <div class="flex items-center gap-2 text-white">
                                <i class="far fa-calendar-alt text-blue-400"></i>
                                <span class="text-xs font-black" x-text="dateText"></span>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Hidden Inputs for Quick Store -->
                <template x-if="isQuick">
                    <div>
                        <input type="hidden" name="transaction_date" value="{{ date('Y-m-d') }}">
                        <input type="hidden" name="type" :value="type">
                        <input type="hidden" name="source" :value="source">
                        <input type="hidden" name="category" value="Input Saldo Manual">
                    </div>
                </template>
                
                <!-- Main Inputs -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Nominal (Rp)</label>
                        <div class="relative group">
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 font-black group-focus-within:text-white transition-colors">Rp</div>
                            <input type="text" x-model="amountFormatted" 
                                   @input="amountFormatted = formatCurrency($event.target.value); amount = amountFormatted.replace(/\./g, '')" 
                                   class="w-full bg-slate-900 border border-white/10 rounded-2xl pl-12 pr-4 py-5 text-3xl font-black text-white focus:ring-4 transition-all placeholder-slate-800"
                                   :class="type === 'income' ? 'focus:ring-emerald-500/20 focus:border-emerald-500/50' : 'focus:ring-red-500/20 focus:border-red-500/50'"
                                   required placeholder="0" autofocus>
                            <input type="hidden" name="amount" :value="amount">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Catatan Transaksi</label>
                        <textarea name="notes" rows="2" class="w-full bg-slate-900/50 border border-white/5 rounded-2xl px-4 py-3 text-sm text-white focus:ring-2 focus:ring-blue-500/50 transition-all placeholder-slate-700" 
                                  required placeholder="Berikan keterangan singkat untuk histori transaksi..."></textarea>
                    </div>
                </div>
                
                <!-- Action Button -->
                <div class="pt-2">
                    <button type="submit" class="w-full font-black py-5 rounded-2xl transition-all shadow-xl uppercase tracking-[0.2em] text-xs flex items-center justify-center gap-3 active:scale-[0.98]"
                            :class="type === 'income' ? 'bg-emerald-600 hover:bg-emerald-500 text-white shadow-emerald-900/20' : 'bg-red-600 hover:bg-red-500 text-white shadow-red-900/20'">
                        <i :class="type === 'income' ? 'fas fa-save' : 'fas fa-minus-circle'"></i>
                        <span x-text="type === 'income' ? 'Simpan Saldo' : 'Kurangi Saldo'"></span>
                    </button>
                    <p class="text-[9px] text-center text-slate-600 mt-4 uppercase tracking-widest font-black opacity-50">Sistem Pencatatan Histori Otomatis Aktif</p>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Modal Analysis --}}
<div id="analysisModal" class="fixed inset-0 bg-[#0F172A]/80 backdrop-blur-xl z-50 hidden flex items-center justify-center p-4">
    <div class="bg-slate-800 border border-white/10 rounded-[2.5rem] w-full max-w-md p-8 shadow-2xl transform transition-premium max-h-[90vh] overflow-y-auto scrollbar-hide ">
        <div class="flex justify-between items-center mb-8">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 flex items-center justify-center text-indigo-400 border border-indigo-500/20 shadow-lg shadow-indigo-500/10">
                    <i class="fas fa-chart-pie text-xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-black text-white tracking-tighter">Analisis Cepat</h3>
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Financial Insights</p>
                </div>
            </div>
            <button onclick="document.getElementById('analysisModal').classList.add('hidden')" class="w-10 h-10 rounded-full hover:bg-white/5 text-slate-400 hover:text-white transition-premium flex items-center justify-center">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="space-y-6">
            <!-- Avg Income Widget -->
            <div class="bg-slate-900/50 rounded-3xl p-6 border border-white/5 relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 w-16 h-16 bg-emerald-500/5 rounded-full blur-xl group-hover:bg-emerald-500/10 transition-premium"></div>
                <div class="flex justify-between items-center relative z-10">
                    <div>
                        <p class="text-[10px] text-slate-500 uppercase tracking-widest font-black mb-1.5">Rata-rata Pemasukan / Hari</p>
                        <p class="text-2xl font-black text-white tracking-tighter" id="analysisAvgIncome">Rp {{ number_format($avgIncome, 0, ',', '.') }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 flex items-center justify-center text-emerald-400 border border-emerald-500/20 shadow-lg">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                </div>
            </div>

            <!-- Ratio Widget -->
            <div class="bg-slate-900/50 rounded-3xl p-6 border border-white/5">
                <p class="text-[10px] text-slate-500 uppercase tracking-widest font-black mb-4">Rasio Pemasukan vs Pengeluaran</p>
                <div class="w-full bg-slate-800 rounded-full h-4 overflow-hidden flex shadow-inner border border-white/5" id="analysisRatioBar">
                    @php 
                        $total = $totalIncome + $totalExpense;
                        $incPct = $total > 0 ? ($totalIncome / $total) * 100 : 50;
                        $expPct = $total > 0 ? ($totalExpense / $total) * 100 : 50;
                    @endphp
                    <div style="width: {{ $incPct }}%" class="bg-emerald-500 transition-all duration-1000 shadow-[0_0_15px_rgba(16,185,129,0.5)]"></div>
                    <div style="width: {{ $expPct }}%" class="bg-red-500 transition-all duration-1000 shadow-[0_0_15px_rgba(239,68,68,0.5)]"></div>
                </div>
                <div class="flex justify-between items-center mt-3">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span class="text-xs font-black text-emerald-400" id="analysisIncPct">{{ round($incPct) }}% Masuk</span>
                    </div>
                    <div class="flex items-center gap-2 text-right">
                        <span class="text-xs font-black text-red-400" id="analysisExpPct">{{ round($expPct) }}% Keluar</span>
                        <span class="w-2 h-2 rounded-full bg-red-500"></span>
                    </div>
                </div>
            </div>

            <!-- Trend Widget -->
            <div class="bg-slate-900/50 rounded-3xl p-6 border border-white/5">
                <p class="text-[10px] text-slate-500 uppercase tracking-widest font-black mb-4">Tren Performa Bisnis</p>
                <div class="flex items-center gap-5">
                    <div id="analysisTrendIcon" class="w-14 h-14 rounded-2xl {{ $trend == 'up' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20 shadow-emerald-500/10' : ($trend == 'down' ? 'bg-red-500/10 text-red-400 border-red-500/20 shadow-red-500/10' : 'bg-slate-500/10 text-slate-400 border-slate-500/20') }} border flex items-center justify-center text-2xl shadow-lg transition-premium">
                        <i class="fas {{ $trend == 'up' ? 'fa-arrow-trend-up' : ($trend == 'down' ? 'fa-arrow-trend-down' : 'fa-minus') }}"></i>
                    </div>
                    <div>
                        <p class="text-lg font-black text-white tracking-tight" id="analysisTrendText">
                            {{ $trend == 'up' ? 'Meningkat Pesat' : ($trend == 'down' ? 'Perlu Evaluasi' : 'Stabil & Aman') }}
                        </p>
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-1">Vs Periode Sebelumnya</p>
                    </div>
                </div>
            </div>
        </div>
        
        <button onclick="document.getElementById('analysisModal').classList.add('hidden')" class="w-full mt-8 py-4 bg-indigo-600 hover:bg-indigo-500 text-white rounded-2xl font-black text-xs uppercase tracking-widest transition-premium shadow-xl shadow-indigo-500/20 active:scale-95">
            Tutup Dashboard
        </button>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // --- 1. CHART INITIALIZATION (APEXCHARTS) ---
        let chart;
        const initChart = (dates, income, expense, netProfit) => {
            const chartOptions = {
                series: [{
                    name: 'Laba Bersih',
                    data: netProfit,
                    type: 'line'
                }, {
                    name: 'Pemasukan',
                    data: income,
                    type: 'area'
                }, {
                    name: 'Pengeluaran',
                    data: expense,
                    type: 'area'
                }],
                chart: {
                    type: 'line',
                    height: 300,
                    toolbar: { show: false },
                    fontFamily: 'inherit',
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800,
                    }
                },
                colors: ['#3B82F6', '#10B981', '#EF4444'], // Blue, Emerald, Red
                fill: {
                    type: ['solid', 'gradient', 'gradient'],
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.15,
                        opacityTo: 0.01,
                        stops: [0, 100]
                    }
                },
                dataLabels: { enabled: false },
                stroke: {
                    curve: 'smooth',
                    width: [4, 1.5, 1.5]
                },
                xaxis: {
                    categories: dates,
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    labels: {
                        style: { colors: '#64748B', fontSize: '12px' }
                    }
                },
                yaxis: {
                    labels: {
                        style: { colors: '#64748B', fontSize: '12px' },
                        formatter: (value) => {
                            if (value >= 1000000) return 'Rp ' + (value / 1000000).toFixed(1) + 'M';
                            if (value >= 1000) return 'Rp ' + (value / 1000).toFixed(0) + 'K';
                            return 'Rp ' + value;
                        }
                    }
                },
                grid: {
                    borderColor: '#1E293B',
                    strokeDashArray: 4,
                    yaxis: { lines: { show: true } },
                    xaxis: { lines: { show: false } }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'right',
                    labels: { colors: '#94A3B8' }
                },
                tooltip: {
                    theme: 'dark',
                    y: {
                        formatter: function (val) {
                            return "Rp " + new Intl.NumberFormat('id-ID').format(val)
                        }
                    }
                }
            };

            if(chart) {
                chart.destroy();
            }
            chart = new ApexCharts(document.querySelector("#cashflowChart"), chartOptions);
            chart.render();
        };

        // Initial Chart
        initChart({!! json_encode($chartDates) !!}, {!! json_encode($chartIncome) !!}, {!! json_encode($chartExpense) !!}, {!! json_encode($chartNetProfit) !!});

        // --- 2. PAYMENT METHOD TREND CHART ---
        let pmChart;
        const pmLabels = {!! json_encode($pmChartData['labels'] ?? []) !!};
        const pmCash = {!! json_encode($pmChartData['cash'] ?? []) !!};
        const pmQris = {!! json_encode($pmChartData['qris'] ?? []) !!};
        const pmTransfer = {!! json_encode($pmChartData['transfer'] ?? []) !!};
        const pmDebit = {!! json_encode($pmChartData['debit'] ?? []) !!};

        if (document.querySelector("#paymentMethodTrendChart") && pmLabels.length > 0) {
            const pmChartOptions = {
                series: [{
                    name: 'Tunai',
                    data: pmCash
                }, {
                    name: 'QRIS',
                    data: pmQris
                }, {
                    name: 'Transfer',
                    data: pmTransfer
                }, {
                    name: 'Debit',
                    data: pmDebit
                }],
                chart: {
                    type: 'area',
                    height: 250,
                    toolbar: { show: false },
                    fontFamily: 'inherit',
                    animations: { enabled: true, easing: 'easeinout', speed: 800 }
                },
                colors: ['#F59E0B', '#A855F7', '#3B82F6', '#14B8A6'], // Amber, Purple, Blue, Teal
                fill: {
                    type: 'gradient',
                    gradient: { shadeIntensity: 1, opacityFrom: 0.3, opacityTo: 0.05, stops: [0, 100] }
                },
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 2 },
                xaxis: {
                    categories: pmLabels,
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    labels: { style: { colors: '#64748B', fontSize: '10px' } }
                },
                yaxis: {
                    labels: {
                        style: { colors: '#64748B', fontSize: '10px' },
                        formatter: (value) => {
                            if (value >= 1000000) return 'Rp' + (value / 1000000).toFixed(1) + 'M';
                            if (value >= 1000) return 'Rp' + (value / 1000).toFixed(0) + 'K';
                            return value;
                        }
                    }
                },
                grid: {
                    borderColor: '#1E293B',
                    strokeDashArray: 3,
                    yaxis: { lines: { show: true } },
                    xaxis: { lines: { show: false } }
                },
                legend: { show: false },
                tooltip: {
                    theme: 'dark',
                    y: { formatter: function (val) { return "Rp " + new Intl.NumberFormat('id-ID').format(val) } }
                }
            };

            pmChart = new ApexCharts(document.querySelector("#paymentMethodTrendChart"), pmChartOptions);
            pmChart.render();
        }


        // --- 2. AJAX DATA FETCH ---
        const sourceFilter = document.getElementById('sourceFilter');

        const fetchData = async (page = 1) => {
            const source = sourceFilter.value;
            const urlParams = new URLSearchParams(window.location.search);
            const start = urlParams.get('date_from') || '{{ is_object($start) ? $start->format("Y-m-d") : $start }}';
            const end = urlParams.get('date_to') || '{{ is_object($end) ? $end->format("Y-m-d") : $end }}';
            const period = urlParams.get('period') || 'today';

            // Update Browser URL without reload
            let newUrl = `/cashflow?period=${period}&date_from=${start}&date_to=${end}`;
            if (source !== 'all') {
                newUrl += `&source=${source}`;
            }
            window.history.pushState({}, '', newUrl);

            try {
                const response = await fetch(`/cashflow/data?period=${period}&source=${source}&date_from=${start}&date_to=${end}&page=${page}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                
                if(!response.ok) throw new Error('Failed to fetch data');
                
                const data = await response.json();
                
                // Update Summary Cards
                document.getElementById('valTotalIncome').innerText = 'Rp ' + data.summary.totalIncomeFmt;
                document.getElementById('valTotalExpense').innerText = 'Rp ' + data.summary.totalExpenseFmt;
                
                const npEl = document.getElementById('valNetProfit');
                npEl.innerText = (data.summary.netProfitNegative ? '-' : '') + 'Rp ' + data.summary.netProfitFmt;
                npEl.className = `text-2xl font-black tracking-tight ${data.summary.netProfitNegative ? 'text-red-400' : 'text-white'}`;

                // Update Income Breakdown Cards
                if (document.getElementById('valIncomeQris')) document.getElementById('valIncomeQris').innerText = 'Rp ' + data.summary.incomeQrisFmt;
                if (document.getElementById('valIncomeCash')) document.getElementById('valIncomeCash').innerText = 'Rp ' + data.summary.incomeCashFmt;
                if (document.getElementById('valIncomeTransfer')) document.getElementById('valIncomeTransfer').innerText = 'Rp ' + data.summary.incomeTransferFmt;

                // Update Filtered Saldo Cards
                if (document.getElementById('valSaldoLaci')) document.getElementById('valSaldoLaci').innerText = 'Rp ' + data.summary.saldoLaciFmt;
                if (document.getElementById('valSaldoBankSynced')) document.getElementById('valSaldoBankSynced').innerText = 'Rp ' + data.summary.saldoBankSyncedFmt;

                // Update Adjustment Stats
                if (document.getElementById('valAdjIn')) document.getElementById('valAdjIn').innerText = '+ Rp ' + data.summary.totalAdjInFmt;
                if (document.getElementById('valAdjOut')) document.getElementById('valAdjOut').innerText = '- Rp ' + data.summary.totalAdjOutFmt;

                // Update Insights
                document.getElementById('valAvgIncome').innerText = 'Rp ' + data.insights.avgIncomeFmt;
                
                if(document.getElementById('valProfitSummary')) {
                    document.getElementById('valProfitSummary').innerText = data.profitInsights.summary;
                }
                if(document.getElementById('valAvgProfit')) {
                    document.getElementById('valAvgProfit').innerText = 'Rp ' + data.profitInsights.avg_profitFmt;
                }
                if(document.getElementById('valPredictedProfit')) {
                    document.getElementById('valPredictedProfit').innerText = 'Rp ' + data.profitInsights.predicted_profitFmt;
                }
                
                // Top Expenses
                let topExpHtml = '';
                if(data.insights.expenseCategories && data.insights.expenseCategories.length > 0) {
                    data.insights.expenseCategories.slice(0, 3).forEach(cat => {
                        topExpHtml += `<div class="flex justify-between items-center text-sm">
                            <span class="text-slate-300">${cat.category}</span>
                            <span class="text-white font-bold">Rp ${cat.totalFmt}</span>
                        </div>`;
                    });
                } else {
                    topExpHtml = '<div class="text-sm text-slate-500 text-center py-2">Belum ada data</div>';
                }
                document.getElementById('valTopExpenseList').innerHTML = topExpHtml;

                // Biggest Expense
                let bigExpHtml = '<h3 class="text-xs text-slate-400 font-medium uppercase tracking-wider mb-3">Pengeluaran Terbesar</h3>';
                if(data.insights.biggestExpense) {
                    bigExpHtml += `
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full bg-red-500/10 flex items-center justify-center text-red-400 flex-shrink-0 mt-0.5">
                            <i class="fas fa-exclamation-triangle text-xs"></i>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-white">${data.insights.biggestExpense.category}</p>
                            <p class="text-xs text-slate-400">${data.insights.biggestExpense.description}</p>
                            <p class="text-red-400 font-bold mt-1 text-sm">- Rp ${data.insights.biggestExpense.amountFmt}</p>
                        </div>
                    </div>`;
                } else {
                    bigExpHtml += '<div class="text-sm text-slate-500 text-center py-2">Belum ada pengeluaran</div>';
                }
                document.getElementById('valBiggestExpenseContainer').innerHTML = bigExpHtml;

                // Update Analysis Modal
                document.getElementById('analysisAvgIncome').innerText = 'Rp ' + data.insights.avgIncomeFmt;
                
                const total = data.summary.totalIncome + data.summary.totalExpense;
                const incPct = total > 0 ? (data.summary.totalIncome / total) * 100 : 50;
                const expPct = total > 0 ? (data.summary.totalExpense / total) * 100 : 50;
                
                document.getElementById('analysisRatioBar').innerHTML = `
                    <div style="width: ${incPct}%" class="bg-emerald-500 transition-all duration-500"></div>
                    <div style="width: ${expPct}%" class="bg-red-500 transition-all duration-500"></div>
                `;
                document.getElementById('analysisIncPct').innerText = Math.round(incPct) + '%';
                document.getElementById('analysisExpPct').innerText = Math.round(expPct) + '%';
                
                const trendIconContainer = document.getElementById('analysisTrendIcon');
                const trendText = document.getElementById('analysisTrendText');
                
                if(data.insights.trend === 'up') {
                    trendIconContainer.className = "w-10 h-10 rounded-full bg-emerald-500/10 text-emerald-400 flex items-center justify-center";
                    trendIconContainer.innerHTML = '<i class="fas fa-arrow-trend-up"></i>';
                    trendText.innerText = 'Meningkat';
                } else if(data.insights.trend === 'down') {
                    trendIconContainer.className = "w-10 h-10 rounded-full bg-red-500/10 text-red-400 flex items-center justify-center";
                    trendIconContainer.innerHTML = '<i class="fas fa-arrow-trend-down"></i>';
                    trendText.innerText = 'Menurun';
                } else {
                    trendIconContainer.className = "w-10 h-10 rounded-full bg-slate-500/10 text-slate-400 flex items-center justify-center";
                    trendIconContainer.innerHTML = '<i class="fas fa-minus"></i>';
                    trendText.innerText = 'Stabil';
                }

                // Update Chart
                initChart(data.chart.labels, data.chart.income, data.chart.expense, data.chart.netProfit);

                // Animation for balance update
                const laciCard = document.getElementById('valSaldoLaci');
                const bankCard = document.getElementById('valSaldoBankSynced');
                
                if(laciCard) {
                    laciCard.classList.remove('animate-balance-update');
                    void laciCard.offsetWidth; // Trigger reflow
                    laciCard.classList.add('animate-balance-update');
                }
                if(bankCard) {
                    bankCard.classList.remove('animate-balance-update');
                    void bankCard.offsetWidth; // Trigger reflow
                    bankCard.classList.add('animate-balance-update');
                }

                // Update Transactions HTML
                document.getElementById('transactionsContainer').innerHTML = data.transactions;
                
                const pagContainer = document.getElementById('paginationContainer');
                if(data.pagination) {
                    pagContainer.innerHTML = data.pagination;
                    pagContainer.classList.remove('hidden');
                    
                    // Attach event listeners to new pagination links
                    const links = pagContainer.querySelectorAll('a');
                    links.forEach(link => {
                        link.addEventListener('click', function(e) {
                            e.preventDefault();
                            const url = new URL(this.href);
                            const page = url.searchParams.get('page');
                            fetchData(page);
                        });
                    });
                } else {
                    pagContainer.innerHTML = '';
                    pagContainer.classList.add('hidden');
                }

                filterTransactions(); // Re-apply UI text filter if any

            } catch (error) {
                console.error("Error fetching data:", error);
            }
        };



        // Event Listeners for Source Filter
        sourceFilter.addEventListener('change', () => fetchData(1));


        // --- 3. FILTER & SEARCH TRANSACTIONS (UI ONLY) ---
        const filterBtns = document.querySelectorAll('.filter-btn');
        const searchInput = document.getElementById('searchInput');

        function filterTransactions() {
            const activeFilter = document.querySelector('.filter-btn.active')?.dataset.filter || 'all';
            const searchTerm = searchInput.value.toLowerCase();
            const txItems = document.querySelectorAll('.tx-item');
            const txGroups = document.querySelectorAll('.tx-group');

            txItems.forEach(item => {
                const type = item.dataset.type; // income/expense
                const category = item.dataset.category;
                const transactionCategory = item.dataset.transactionCategory;
                const text = item.innerText.toLowerCase();
                
                let matchesFilter = activeFilter === 'all';
                
                if (activeFilter === 'income' || activeFilter === 'expense') {
                    matchesFilter = type === activeFilter;
                } else if (activeFilter === 'adjustment') {
                    // Penyesuaian Kas: Show only outgoing adjustments (reductions), exclude transfers
                    matchesFilter = (transactionCategory === 'adjustment' && type === 'expense' && category !== 'Transfer Internal');
                } else if (activeFilter === 'Input Saldo Manual') {
                    // Input Saldo: Show only incoming entries
                    matchesFilter = (category === 'Input Saldo Manual' && type === 'income');
                } else if (activeFilter === 'Transfer Internal') {
                    // Transfer: Show only incoming entries (targets)
                    matchesFilter = (category === 'Transfer Internal' && type === 'income');
                } else if (activeFilter !== 'all') {
                    matchesFilter = category === activeFilter;
                }

                const matchesSearch = text.includes(searchTerm);

                if (matchesFilter && matchesSearch) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });

            // Hide empty date groups
            txGroups.forEach(group => {
                let current = group.nextElementSibling;
                let hasVisibleItems = false;
                while (current && current.classList.contains('tx-item')) {
                    if (current.style.display !== 'none') {
                        hasVisibleItems = true;
                        break;
                    }
                    current = current.nextElementSibling;
                }
                group.style.display = hasVisibleItems ? 'block' : 'none';
            });

            // Handle Empty State Visibility
            const emptyState = document.getElementById('emptyState');
            if (emptyState) {
                const totalVisible = Array.from(txItems).filter(i => i.style.display !== 'none').length;
                emptyState.style.display = (totalVisible === 0) ? 'flex' : 'none';
            }
        }

        filterBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                filterBtns.forEach(b => {
                    b.classList.remove('active', 'bg-slate-700', 'text-white');
                    b.classList.add('text-slate-400');
                });
                
                e.target.classList.add('active', 'bg-slate-700', 'text-white');
                e.target.classList.remove('text-slate-400');
                
                filterTransactions();
            });
        });

        searchInput.addEventListener('input', filterTransactions);

        // Allow pagination links on first load to use AJAX
        const initialLinks = document.querySelectorAll('#paginationContainer a');
        initialLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const url = new URL(this.href);
                const page = url.searchParams.get('page');
                fetchData(page);
            });
        });

    });

    // --- SYNC FUNCTIONS (PREMIUM SWAL) ---
    async function confirmSyncLaci() {
        const count = {{ $pendingLaciCount ?? 0 }};
        if (count === 0) return;

        const result = await Swal.fire({
            title: 'Konfirmasi Sinkron Laci',
            html: `Konfirmasi bahwa <b>Rp {{ number_format($saldoLaciPending, 0, ',', '.') }}</b> dari <b>${count}</b> transaksi tunai sudah Anda terima dan masukkan ke laci?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Sinkronkan!',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#10b981', // emerald-500
            cancelButtonColor: '#334155',
            background: '#1e293b',
            color: '#f8fafc',
            customClass: {
                popup: 'rounded-[2rem] border border-white/5 shadow-2xl',
                confirmButton: 'rounded-xl px-6 py-3 font-black uppercase tracking-widest text-[10px]',
                cancelButton: 'rounded-xl px-6 py-3 font-black uppercase tracking-widest text-[10px]'
            }
        });

        if (result.isConfirmed) {
            await performSync('{{ route("cashflow.sync-laci") }}');
        }
    }

    async function confirmSyncBank() {
        const count = {{ $pendingBankCount ?? 0 }};
        if (count === 0) return;

        const result = await Swal.fire({
            title: 'Konfirmasi Sinkron Bank',
            html: `Konfirmasi bahwa <b>Rp {{ number_format($saldoBankPending, 0, ',', '.') }}</b> dari <b>${count}</b> transaksi bank (QRIS/Transfer) sudah masuk ke rekening?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Konfirmasi!',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#9333ea', // purple-600
            cancelButtonColor: '#334155',
            background: '#1e293b',
            color: '#f8fafc',
            customClass: {
                popup: 'rounded-[2rem] border border-white/5 shadow-2xl',
                confirmButton: 'rounded-xl px-6 py-3 font-black uppercase tracking-widest text-[10px]',
                cancelButton: 'rounded-xl px-6 py-3 font-black uppercase tracking-widest text-[10px]'
            }
        });

        if (result.isConfirmed) {
            await performSync('{{ route("cashflow.sync-bank") }}');
        }
    }

    async function performSync(url) {
        // Show loading
        Swal.fire({
            title: 'Memproses...',
            text: 'Sedang menyinkronkan data Anda',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            },
            background: '#1e293b',
            color: '#f8fafc'
        });

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
            });
            const data = await response.json();

            if (data.success) {
                Swal.close();
                Toast.fire({
                    icon: 'success',
                    title: data.message
                });
                setTimeout(() => location.reload(), 800);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Sinkronisasi Gagal',
                    text: data.message || 'Terjadi kesalahan saat memproses data.',
                    background: '#1e293b',
                    color: '#f8fafc'
                });
            }
        } catch (e) {
            Swal.fire({
                icon: 'error',
                title: 'Error Sistem',
                text: 'Gagal terhubung ke server. Silakan coba lagi.',
                background: '#1e293b',
                color: '#f8fafc'
            });
        }
    }
</script>


{{-- ============================================================
     MODAL ADJUSTMENT KAS - Koreksi saldo Tunai/Bank
============================================================ --}}
<div id="modalAdjKas" class="fixed inset-0 z-[90] hidden" aria-modal="true" role="dialog">
    <div class="fixed inset-0 bg-slate-950/85 backdrop-blur-lg" onclick="closeModalAdjKas()"></div>
    <div class="relative z-10 flex items-center justify-center min-h-screen p-4">
        <div id="adjKasCard" class="bg-[#0d1526] border border-white/10 rounded-[2.5rem] w-full max-w-md shadow-[0_0_80px_-10px_rgba(245,158,11,0.25)] overflow-hidden max-h-[90vh] overflow-y-auto scrollbar-hide ">
            <div class="relative px-8 pt-8 pb-5 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-amber-500/8 to-transparent pointer-events-none"></div>
                <div class="relative flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-amber-500/15 border border-amber-500/30 flex items-center justify-center text-amber-400">
                            <i class="fas fa-sliders-h text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-white tracking-tighter leading-none">Adjustment Kas</h3>
                            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-1">Koreksi Saldo Tunai / Bank</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeModalAdjKas()"
                        class="w-10 h-10 rounded-full bg-white/5 hover:bg-white/10 text-slate-400 hover:text-white transition-all flex items-center justify-center">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="px-8 pb-8 pt-2">
                <form id="formAdjKas" onsubmit="submitAdjKas(event)">
                    @csrf
                    <input type="hidden" id="adjTypeHidden" name="type" value="expense">
                    <input type="hidden" id="adjSourceHidden" name="source" value="pos_cash">
                    <input type="hidden" name="transaction_date" id="adjDate" value="{{ date('Y-m-d') }}">
                    <div class="space-y-5">
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Jenis Adjustment</p>
                            <div class="grid grid-cols-2 gap-2 bg-slate-900/60 rounded-2xl p-1.5 border border-white/5">
                                <button type="button" id="adjBtnKurangi" onclick="setAdjType('expense')"
                                    class="adj-type-btn py-3 rounded-xl text-[11px] font-black uppercase tracking-widest transition-all flex items-center justify-center gap-2 bg-red-600 text-white shadow-lg">
                                    <i class="fas fa-minus-circle"></i> Kurangi Saldo
                                </button>
                                <button type="button" id="adjBtnTambah" onclick="setAdjType('income')"
                                    class="adj-type-btn py-3 rounded-xl text-[11px] font-black uppercase tracking-widest transition-all flex items-center justify-center gap-2 text-slate-500 hover:text-white">
                                    <i class="fas fa-plus-circle"></i> Tambah Saldo
                                </button>
                            </div>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Sumber Saldo</p>
                            <div class="grid grid-cols-2 gap-2 bg-slate-900/60 rounded-2xl p-1.5 border border-white/5">
                                <button type="button" id="adjBtnLaci" onclick="setAdjSource('pos_cash')"
                                    class="adj-src-btn py-3 rounded-xl text-[11px] font-black uppercase tracking-widest transition-all flex items-center justify-center gap-2 bg-amber-600 text-white shadow-lg">
                                    <i class="fas fa-cash-register text-xs"></i> Tunai / Laci
                                </button>
                                <button type="button" id="adjBtnBank" onclick="setAdjSource('pos_bank')"
                                    class="adj-src-btn py-3 rounded-xl text-[11px] font-black uppercase tracking-widest transition-all flex items-center justify-center gap-2 text-slate-500 hover:text-white">
                                    <i class="fas fa-university text-xs"></i> Saldo Bank
                                </button>
                            </div>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Nominal Koreksi (Rp)</p>
                            <div class="relative group">
                                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 font-black group-focus-within:text-amber-400 transition-colors">Rp</div>
                                <input type="text" id="adjAmountDisplay"
                                    class="w-full bg-slate-800/60 border border-white/10 rounded-2xl pl-12 pr-4 py-5 text-3xl font-black text-white focus:ring-4 focus:ring-amber-500/20 focus:border-amber-500/50 transition-all placeholder-slate-700"
                                    placeholder="0" autocomplete="off" required>
                                <input type="hidden" id="adjAmountRaw" name="amount">
                            </div>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Keterangan</p>
                            <input type="text" name="notes" id="adjNotes"
                                class="w-full bg-slate-800/60 border border-white/10 rounded-2xl px-4 py-3 text-sm text-white focus:ring-2 focus:ring-amber-500/40 transition-all placeholder-slate-600"
                                placeholder="Contoh: Selisih saldo bank 2jt dipindah ke adjustment..." required>
                        </div>
                        <div id="adjInfoBox" class="bg-red-500/8 border border-red-500/20 rounded-2xl p-4">
                            <p id="adjInfoText" class="text-[10px] text-red-300/70 font-medium leading-relaxed">
                                <i class="fas fa-arrow-down mr-2 text-red-400/70"></i>
                                <strong>Kurangi</strong>: Saldo berkurang, selisih dicatat sebagai <em>Adjustment Kas Keluar</em>.
                            </p>
                        </div>
                        <div class="flex flex-col gap-3 pt-1">
                            <button type="submit" id="adjSubmitBtn"
                                class="w-full py-4 rounded-2xl text-[11px] font-black uppercase tracking-widest transition-all active:scale-95 flex items-center justify-center gap-2 bg-red-600 hover:bg-red-500 text-white shadow-xl shadow-red-500/20">
                                <i class="fas fa-minus-circle" id="adjSubmitIcon"></i>
                                <span id="adjSubmitText">Kurangi Saldo &amp; Catat Adjustment</span>
                            </button>
                            <button type="button" onclick="closeModalAdjKas()"
                                class="w-full py-3 text-slate-500 hover:text-slate-300 font-black transition-all uppercase tracking-widest text-[10px]">
                                Batalkan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// ============================================================
// MODAL ADJUSTMENT KAS
// ============================================================
function openModalAdjKas() {
    const modal = document.getElementById('modalAdjKas');
    modal.classList.remove('hidden');
    document.getElementById('adjAmountDisplay').value = '';
    document.getElementById('adjAmountRaw').value = '';
    document.getElementById('adjNotes').value = '';
    document.getElementById('adjDate').value = new Date().toISOString().split('T')[0];
    setAdjType('expense');
    setAdjSource('pos_cash');
    const card = document.getElementById('adjKasCard');
    if (card) {
        card.style.transform = 'scale(0.9) translateY(20px)';
        card.style.opacity = '0';
        card.style.transition = 'all 0.3s cubic-bezier(0.34,1.56,0.64,1)';
        requestAnimationFrame(() => {
            card.style.transform = 'scale(1) translateY(0)';
            card.style.opacity = '1';
        });
    }
    setTimeout(() => document.getElementById('adjAmountDisplay').focus(), 350);
}

function closeModalAdjKas() {
    const modal = document.getElementById('modalAdjKas');
    const card = document.getElementById('adjKasCard');
    if (card) {
        card.style.transform = 'scale(0.95) translateY(10px)';
        card.style.opacity = '0';
        card.style.transition = 'all 0.2s ease';
        setTimeout(() => modal.classList.add('hidden'), 200);
    } else {
        modal.classList.add('hidden');
    }
}

function setAdjType(type) {
    document.getElementById('adjTypeHidden').value = type;
    const btnK = document.getElementById('adjBtnKurangi');
    const btnT = document.getElementById('adjBtnTambah');
    const infoBox = document.getElementById('adjInfoBox');
    const infoText = document.getElementById('adjInfoText');
    const submitBtn = document.getElementById('adjSubmitBtn');
    const submitIcon = document.getElementById('adjSubmitIcon');
    const submitText = document.getElementById('adjSubmitText');
    if (type === 'expense') {
        btnK.className = 'adj-type-btn py-3 rounded-xl text-[11px] font-black uppercase tracking-widest transition-all flex items-center justify-center gap-2 bg-red-600 text-white shadow-lg';
        btnT.className = 'adj-type-btn py-3 rounded-xl text-[11px] font-black uppercase tracking-widest transition-all flex items-center justify-center gap-2 text-slate-500 hover:text-white';
        infoBox.className = 'bg-red-500/8 border border-red-500/20 rounded-2xl p-4';
        infoText.innerHTML = '<i class="fas fa-arrow-down mr-2 text-red-400/70"></i><strong>Kurangi</strong>: Saldo berkurang, selisih dicatat sebagai <em>Adjustment Kas Keluar</em>.';
        submitBtn.className = 'w-full py-4 rounded-2xl text-[11px] font-black uppercase tracking-widest transition-all active:scale-95 flex items-center justify-center gap-2 bg-red-600 hover:bg-red-500 text-white shadow-xl shadow-red-500/20';
        submitIcon.className = 'fas fa-minus-circle';
        submitText.textContent = 'Kurangi Saldo & Catat Adjustment';
    } else {
        btnK.className = 'adj-type-btn py-3 rounded-xl text-[11px] font-black uppercase tracking-widest transition-all flex items-center justify-center gap-2 text-slate-500 hover:text-white';
        btnT.className = 'adj-type-btn py-3 rounded-xl text-[11px] font-black uppercase tracking-widest transition-all flex items-center justify-center gap-2 bg-emerald-600 text-white shadow-lg';
        infoBox.className = 'bg-emerald-500/8 border border-emerald-500/20 rounded-2xl p-4';
        infoText.innerHTML = '<i class="fas fa-arrow-up mr-2 text-emerald-400/70"></i><strong>Tambah</strong>: Saldo bertambah, dicatat sebagai <em>Adjustment Kas Masuk</em>.';
        submitBtn.className = 'w-full py-4 rounded-2xl text-[11px] font-black uppercase tracking-widest transition-all active:scale-95 flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-500 text-white shadow-xl shadow-emerald-500/20';
        submitIcon.className = 'fas fa-plus-circle';
        submitText.textContent = 'Tambah Saldo & Catat Adjustment';
    }
}

function setAdjSource(source) {
    document.getElementById('adjSourceHidden').value = source;
    const btnL = document.getElementById('adjBtnLaci');
    const btnB = document.getElementById('adjBtnBank');
    if (source === 'pos_cash') {
        btnL.className = 'adj-src-btn py-3 rounded-xl text-[11px] font-black uppercase tracking-widest transition-all flex items-center justify-center gap-2 bg-amber-600 text-white shadow-lg';
        btnB.className = 'adj-src-btn py-3 rounded-xl text-[11px] font-black uppercase tracking-widest transition-all flex items-center justify-center gap-2 text-slate-500 hover:text-white';
    } else {
        btnL.className = 'adj-src-btn py-3 rounded-xl text-[11px] font-black uppercase tracking-widest transition-all flex items-center justify-center gap-2 text-slate-500 hover:text-white';
        btnB.className = 'adj-src-btn py-3 rounded-xl text-[11px] font-black uppercase tracking-widest transition-all flex items-center justify-center gap-2 bg-purple-600 text-white shadow-lg';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const display = document.getElementById('adjAmountDisplay');
    const raw = document.getElementById('adjAmountRaw');
    if (display) {
        display.addEventListener('input', function () {
            let val = this.value.replace(/[^0-9]/g, '');
            raw.value = val;
            this.value = val ? new Intl.NumberFormat('id-ID').format(parseInt(val)) : '';
        });
    }
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            const adjModal = document.getElementById('modalAdjKas');
            if (adjModal && !adjModal.classList.contains('hidden')) closeModalAdjKas();
        }
    });
});

async function submitAdjKas(event) {
    event.preventDefault();
    const amount = document.getElementById('adjAmountRaw').value;
    if (!amount || parseInt(amount) <= 0) {
        alert('Masukkan nominal koreksi yang valid!');
        return;
    }
    const btn = document.getElementById('adjSubmitBtn');
    const btnText = document.getElementById('adjSubmitText');
    const origText = btnText.textContent;
    btn.disabled = true;
    btnText.textContent = 'Menyimpan...';
    try {
        const form = document.getElementById('formAdjKas');
        const formData = new FormData(form);
        const response = await fetch('{{ route("cashflow.quick-store") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': formData.get('_token'),
                'Accept': 'application/json'
            },
            body: formData
        });
        if (!response.ok) {
            const data = await response.json().catch(() => ({}));
            throw new Error(data.message || 'Gagal menyimpan adjustment');
        }
        closeModalAdjKas();
        if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Adjustment kas berhasil dicatat.', timer: 2000, showConfirmButton: false, background: '#0d1526', color: '#f8fafc' });
        }
        setTimeout(() => location.reload(), 1600);
    } catch (err) {
        alert('Error: ' + err.message);
        btn.disabled = false;
        btnText.textContent = origText;
    }
}
</script>

    <!-- MODAL TARGET ROI -->

    <div id="modalTargetBEP" class="fixed inset-0 z-[60] hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-black/60 backdrop-blur-md" onclick="document.getElementById('modalTargetBEP').classList.add('hidden')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-slate-800 rounded-[2.5rem] border border-white/10 shadow-2xl sm:my-8 sm:align-middle sm:max-w-md sm:w-full max-h-[90vh] overflow-y-auto scrollbar-hide ">
                <form action="{{ route('cashflow.update-target') }}" method="POST">
                    @csrf
                    <div class="p-10">
                        <div class="flex justify-between items-center mb-10">
                            <div>
                                <h3 class="text-2xl font-black text-white tracking-tighter italic uppercase leading-none">Target ROI</h3>
                                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-1">Konfigurasi Pengembalian Modal</p>
                            </div>
                            <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 flex items-center justify-center text-indigo-400 border border-indigo-500/20">
                                <i class="fas fa-bullseye text-xl"></i>
                            </div>
                        </div>
                        
                        <div class="space-y-6">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Target Balik Modal (Bulan)</label>
                                <div class="relative group">
                                    <input type="number" name="target_payback_months" value="{{ $targetPaybackMonths }}" min="1" max="120" required
                                        class="w-full bg-slate-900/50 border border-white/5 rounded-2xl px-6 py-5 text-white font-black text-3xl focus:border-indigo-500 focus:ring-0 transition-premium text-center group-hover:bg-slate-900">
                                    <div class="absolute inset-y-0 right-6 flex items-center pointer-events-none text-slate-500 font-black italic uppercase text-[10px] tracking-widest">Bulan</div>
                                </div>
                                <div class="mt-6 p-4 rounded-2xl bg-indigo-500/5 border border-indigo-500/10">
                                    <p class="text-[10px] text-indigo-200/60 leading-relaxed font-medium italic">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        Sistem akan otomatis menghitung target laba harian dan bulanan yang harus dicapai berdasarkan total investasi Anda.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-10 flex flex-col gap-3">
                            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-black py-4 rounded-2xl shadow-xl shadow-indigo-500/20 transition-premium uppercase tracking-widest text-[10px] active:scale-95">
                                Simpan Konfigurasi
                            </button>
                            <button type="button" onclick="document.getElementById('modalTargetBEP').classList.add('hidden')" class="w-full py-4 text-slate-500 hover:text-slate-300 font-black transition-premium uppercase tracking-widest text-[10px]">
                                Batalkan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        </div>
    </div>

    <!-- MODAL TARGET OMZET/PROFIT/TRANSAKSI -->
    <div id="targetModal" class="fixed inset-0 bg-[#0F172A]/90 backdrop-blur-md z-[100] hidden flex items-center justify-center p-4">
        <div class="bg-slate-800 rounded-[2.5rem] w-full max-w-md border border-white/10 shadow-[0_0_50px_-12px_rgba(0,0,0,0.5)] overflow-hidden transform transition-all duration-500 relative max-h-[90vh] overflow-y-auto scrollbar-hide ">
            <button type="button" onclick="document.getElementById('targetModal').classList.add('hidden')" class="absolute top-6 right-6 w-10 h-10 rounded-full bg-white/5 hover:bg-white/10 text-slate-400 hover:text-white transition-all flex items-center justify-center z-20">
                <i class="fas fa-times"></i>
            </button>
            <form action="{{ route('settings.targets') }}" method="POST">
                @csrf
                <input type="hidden" name="_method" value="PUT">
                <div class="p-10">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 rounded-2xl bg-blue-500/10 flex items-center justify-center text-blue-400 border border-blue-500/20">
                            <i class="fas fa-bullseye text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl sm:text-2xl font-black text-white tracking-tighter italic uppercase leading-none">Target Bulanan</h3>
                            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-1">Konfigurasi Target Perusahaan</p>
                        </div>
                    </div>
                    
                    <div class="space-y-5 relative z-10">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Target Omzet (Rp)</label>
                            <div class="relative group">
                                <div class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-500 font-black group-focus-within:text-blue-400 transition-colors z-30">Rp</div>
                                <input type="text" class="target-currency-input w-full bg-slate-900/50 border border-white/5 rounded-2xl pl-12 pr-5 py-4 text-white font-black text-lg focus:border-blue-500 focus:ring-0 transition-premium placeholder-slate-600 relative z-20"
                                    value="{{ number_format($targetData['omzet']['target'] ?? 0, 0, '', '') }}" placeholder="0" autocomplete="off" required>
                                <input type="hidden" name="target_omzet" class="target-raw-input" value="{{ $targetData['omzet']['target'] ?? 0 }}">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Target Profit (Rp)</label>
                            <div class="relative group">
                                <div class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-500 font-black group-focus-within:text-blue-400 transition-colors z-30">Rp</div>
                                <input type="text" class="target-currency-input w-full bg-slate-900/50 border border-white/5 rounded-2xl pl-12 pr-5 py-4 text-white font-black text-lg focus:border-blue-500 focus:ring-0 transition-premium placeholder-slate-600 relative z-20"
                                    value="{{ number_format($targetData['profit']['target'] ?? 0, 0, '', '') }}" placeholder="0" autocomplete="off" required>
                                <input type="hidden" name="target_profit" class="target-raw-input" value="{{ $targetData['profit']['target'] ?? 0 }}">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Target Transaksi</label>
                            <input type="number" name="target_transaksi" value="{{ $targetData['transaksi']['target'] ?? 0 }}" required
                                class="w-full bg-slate-900/50 border border-white/5 rounded-2xl px-5 py-4 text-white font-black text-lg focus:border-blue-500 focus:ring-0 transition-premium placeholder-slate-600 relative z-20">
                        </div>
                    </div>

                    <div class="mt-8 flex flex-col gap-3 relative z-10">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-black py-4 rounded-2xl shadow-xl shadow-blue-500/20 transition-premium uppercase tracking-widest text-[10px] active:scale-95 relative z-20">
                            Simpan Target
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.target-currency-input').forEach(input => {
            // Format initial value
            if (input.value) {
                input.value = new Intl.NumberFormat('id-ID').format(parseInt(input.value));
            }
            
            input.addEventListener('input', function() {
                let val = this.value.replace(/[^0-9]/g, '');
                this.nextElementSibling.value = val; // Set the hidden raw input
                this.value = val ? new Intl.NumberFormat('id-ID').format(parseInt(val)) : '';
            });
        });
    });
</script>

@endpush
@endsection


