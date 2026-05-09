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
    <div class="bg-slate-800/40 backdrop-blur-xl border border-white/5 rounded-[2rem] p-5 mb-8 flex flex-col lg:flex-row lg:items-center justify-between gap-6 shadow-2xl">
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
            <!-- Period Selector -->
            <div class="flex bg-slate-900/80 rounded-2xl p-1.5 border border-white/5 shadow-inner" id="periodFilterSegment">
                @php
                    $periods = [
                        'today' => 'Hari',
                        'yesterday' => 'Kmrn',
                        'week' => 'Minggu',
                        'month' => 'Bulan',
                        'year' => 'Tahun',
                        'custom' => 'Custom'
                    ];
                @endphp
                @foreach($periods as $val => $label)
                    <button type="button" data-filter="{{ $val }}" 
                        class="period-btn px-4 py-2 text-[10px] font-black uppercase tracking-widest rounded-xl transition-all duration-300 {{ $filter == $val ? 'active text-white bg-blue-600' : 'text-slate-500 hover:text-slate-300' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            <!-- Custom Date Container -->
            <div id="customDateContainer" class="{{ $filter == 'custom' ? 'flex' : 'hidden' }} items-center gap-2 bg-slate-900/80 rounded-2xl px-3 py-1.5 border border-white/5">
                <input type="date" id="customStart" value="{{ is_array($start) ? '' : $start }}" class="bg-transparent border-none text-white text-xs font-bold focus:ring-0 w-32">
                <span class="text-slate-600 font-black">/</span>
                <input type="date" id="customEnd" value="{{ is_array($end) ? '' : $end }}" class="bg-transparent border-none text-white text-xs font-bold focus:ring-0 w-32">
                <button type="button" id="applyCustomBtn" class="w-8 h-8 bg-blue-600 hover:bg-blue-500 text-white rounded-lg flex items-center justify-center transition-premium">
                    <i class="fas fa-check text-xs"></i>
                </button>
            </div>

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
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6 mb-8">
        <!-- Modal Investasi -->
        <div class="glass-card grad-blue rounded-[2.5rem] p-7 transition-premium group relative overflow-hidden">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-blue-500/10 rounded-full blur-2xl group-hover:bg-blue-500/20 transition-premium"></div>
            <div class="flex justify-between items-start mb-4">
                <div class="w-12 h-12 rounded-2xl bg-blue-500/10 flex items-center justify-center text-blue-400 border border-blue-500/20 group-hover:bg-blue-600 group-hover:text-white transition-premium shadow-lg">
                    <i class="fas fa-vault text-lg"></i>
                </div>
                <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Modal Investasi</span>
            </div>
            <h3 class="text-2xl font-black text-white tracking-tighter leading-none mb-2">Rp {{ number_format($totalInvestment, 0, ',', '.') }}</h3>
            <p class="text-[9px] font-bold text-slate-500 uppercase tracking-tighter flex items-center gap-1.5">
                <i class="fas fa-chart-pie text-blue-400"></i>
                Total Modal & Beban
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
                    <span class="inline-block mt-1 text-[8px] font-black bg-emerald-500/20 text-emerald-400 px-2 py-0.5 rounded-full uppercase tracking-widest">✓ Synced</span>
                </div>
            </div>
            <h3 class="text-2xl font-black text-white tracking-tight transition-all duration-500" id="valSaldoBankSynced">Rp {{ number_format($saldoBankSynced, 0, ',', '.') }}</h3>
            <div class="flex items-center gap-1.5 text-[9px] font-black text-purple-400 uppercase tracking-tighter">
                <i class="fas fa-check-circle"></i>
                Rekening Bisnis
            </div>
        </div>

        <!-- Adjustment Kas (NEW) -->
        <div class="glass-card grad-amber rounded-[2.5rem] p-7 transition-premium group relative overflow-hidden bg-slate-900/40 border-amber-500/20">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-amber-500/10 rounded-full blur-2xl group-hover:bg-amber-500/20 transition-premium"></div>
            <div class="flex justify-between items-start mb-4">
                <div class="w-12 h-12 rounded-2xl bg-amber-500/20 flex items-center justify-center text-amber-400 border border-amber-500/30 group-hover:bg-amber-600 group-hover:text-white transition-premium shadow-xl">
                    <i class="fas fa-sliders-h text-lg"></i>
                </div>
                <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Adjustment Kas</span>
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
                Koreksi & Audit
            </div>
        </div>
    </div>

    <!-- 2.5 ACTION COMMAND CENTER -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Unified Sync Status -->
        <div class="lg:col-span-2 bg-slate-800/40 backdrop-blur-xl border border-white/5 rounded-[2.5rem] p-8 shadow-2xl relative overflow-hidden group">
            <div class="absolute -right-10 -top-10 w-40 h-40 bg-blue-500/5 rounded-full blur-3xl"></div>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-8 relative z-10">
                <div class="space-y-4 flex-1">
                    <h4 class="text-[10px] font-black text-slate-500 uppercase tracking-widest flex items-center gap-3">
                        <span class="w-2 h-4 bg-blue-500 rounded-full"></span>
                        Status Sinkronisasi Kas
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                    <p class="text-[10px] font-bold text-blue-100 uppercase tracking-widest opacity-80">Setoran Laci → Bank</p>
                </div>
                <button onclick="document.getElementById('transferModal').classList.remove('hidden')" 
                    class="w-full mt-8 py-4 bg-white text-blue-700 rounded-2xl font-black text-xs uppercase tracking-widest transition-premium shadow-xl hover:shadow-white/20 active:scale-95">
                    Buat Transfer Baru
                </button>
            </div>
        </div>
    </div>

    <!-- Pemasukan Berdasarkan Metode Pembayaran -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-[#111827] rounded-2xl p-5 border border-slate-700/50 relative overflow-hidden group hover:bg-[#1F2937] transition-colors flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Pemasukan QRIS/Bank</p>
                <h3 class="text-2xl font-black text-purple-400 tracking-tight" id="valIncomeQris">Rp {{ number_format($incomeQris, 0, ',', '.') }}</h3>
            </div>
            <div class="w-12 h-12 rounded-full bg-purple-500/10 flex items-center justify-center text-purple-400 text-2xl">
                <i class="fas fa-qrcode"></i>
            </div>
        </div>
        <div class="bg-[#111827] rounded-2xl p-5 border border-slate-700/50 relative overflow-hidden group hover:bg-[#1F2937] transition-colors flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Pemasukan Tunai</p>
                <h3 class="text-2xl font-black text-emerald-400 tracking-tight" id="valIncomeCash">Rp {{ number_format($incomeCash, 0, ',', '.') }}</h3>
            </div>
            <div class="w-12 h-12 rounded-full bg-emerald-500/10 flex items-center justify-center text-emerald-400 text-2xl">
                <i class="fas fa-money-bill-wave"></i>
            </div>
        </div>
        <div class="bg-[#111827] rounded-2xl p-5 border border-slate-700/50 relative overflow-hidden group hover:bg-[#1F2937] transition-colors flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Pemasukan Transfer</p>
                <h3 class="text-2xl font-black text-blue-400 tracking-tight" id="valIncomeTransfer">Rp {{ number_format($incomeTransfer, 0, ',', '.') }}</h3>
            </div>
            <div class="w-12 h-12 rounded-full bg-blue-500/10 flex items-center justify-center text-blue-400 text-2xl">
                <i class="fas fa-building-columns"></i>
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
        <!-- LEFT (70%): Grafik Cashflow -->
        <div class="w-full lg:w-[70%] bg-[#111827] border border-slate-700/50 rounded-2xl p-5">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-bold text-white">Grafik Cashflow</h2>
            </div>
            <div class="w-full h-[300px]" id="cashflowChart"></div>
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

            <!-- Insight 2: Top Kategori Pengeluaran -->
            <div class="bg-[#111827] border border-slate-700/50 rounded-2xl p-4">
                <h3 class="text-xs text-slate-400 font-medium uppercase tracking-wider mb-3">Top Pengeluaran (Kategori)</h3>
                <div class="space-y-3" id="valTopExpenseList">
                    @forelse($expenseByCategory->take(3) as $cat)
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-300">{{ $cat->category }}</span>
                            <span class="text-white font-bold">Rp {{ number_format($cat->total, 0, ',', '.') }}</span>
                        </div>
                    @empty
                        <div class="text-sm text-slate-500 text-center py-2">Belum ada data</div>
                    @endforelse
                </div>
            </div>

            <!-- Insight 3: Transaksi Terbesar Hari Ini -->
            <div class="bg-[#111827] border border-slate-700/50 rounded-2xl p-4" id="valBiggestExpenseContainer">
                <h3 class="text-xs text-slate-400 font-medium uppercase tracking-wider mb-3">Pengeluaran Terbesar</h3>
                @if($biggestExpense)
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-red-500/10 flex items-center justify-center text-red-400 flex-shrink-0 mt-0.5">
                        <i class="fas fa-exclamation-triangle text-xs"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-white">{{ $biggestExpense->category }}</p>
                        <p class="text-xs text-slate-400">{{ $biggestExpense->description }}</p>
                        <p class="text-red-400 font-bold mt-1 text-sm">- Rp {{ number_format($biggestExpense->amount, 0, ',', '.') }}</p>
                    </div>
                </div>
                @else
                <div class="text-sm text-slate-500 text-center py-2">Belum ada pengeluaran</div>
                @endif
            </div>

        </div>
    </div>

    <!-- 6. TRANSACTION LIST (TIMELINE STYLE) -->
    <div class="bg-[#111827] border border-slate-700/50 rounded-2xl p-5 mb-6">
        <!-- 7. FILTER & SEARCH -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <h2 class="text-lg font-bold text-white">Riwayat Transaksi</h2>
            
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <!-- Search Input -->
                <div class="relative w-full sm:w-64">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                    <input type="text" id="searchInput" placeholder="Cari transaksi..." class="w-full bg-[#0F172A] border border-slate-700 rounded-full pl-9 pr-4 py-2 text-sm text-slate-300 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors">
                </div>
                
                <!-- Filter Pills -->
                <div class="flex bg-[#0F172A] border border-slate-700 rounded-full p-1 shrink-0 overflow-x-auto scrollbar-hide max-w-full">
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
    <div class="bg-[#111827] rounded-2xl w-full max-w-md p-6 border border-slate-700 shadow-2xl transform scale-100 transition-transform">
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
                        <option value="laci_to_bank">Laci (Kasir) → Bank Rekening</option>
                        <option value="bank_to_laci">Bank Rekening → Laci (Kasir)</option>
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
    <div class="bg-[#111827] rounded-2xl w-full max-w-md p-6 border border-slate-700 shadow-2xl transform scale-100 transition-transform">
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
    <div class="bg-[#111827] rounded-[2.5rem] w-full max-w-md border border-white/10 shadow-[0_0_50px_-12px_rgba(0,0,0,0.5)] overflow-hidden transform transition-all duration-500"
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
    <div class="bg-slate-800 border border-white/10 rounded-[2.5rem] w-full max-w-md p-8 shadow-2xl transform transition-premium">
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
        const initChart = (dates, income, expense) => {
            const chartOptions = {
                series: [{
                    name: 'Pemasukan',
                    data: income
                }, {
                    name: 'Pengeluaran',
                    data: expense
                }],
                chart: {
                    type: 'area',
                    height: 300,
                    toolbar: { show: false },
                    fontFamily: 'inherit',
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800,
                    }
                },
                colors: ['#10B981', '#EF4444'], // Emerald & Red
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.4,
                        opacityTo: 0.05,
                        stops: [0, 100]
                    }
                },
                dataLabels: { enabled: false },
                stroke: {
                    curve: 'smooth',
                    width: 2
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
        initChart({!! json_encode($chartDates) !!}, {!! json_encode($chartIncome) !!}, {!! json_encode($chartExpense) !!});


        // --- 2. AJAX DATA FETCH ---
        let currentFilter = '{{ $filter }}';
        const sourceFilter = document.getElementById('sourceFilter');
        const customStart = document.getElementById('customStart');
        const customEnd = document.getElementById('customEnd');
        const customDateContainer = document.getElementById('customDateContainer');

        const fetchData = async (page = 1) => {
            const source = sourceFilter.value;
            const start = customStart.value;
            const end = customEnd.value;

            // Update Browser URL without reload
            let newUrl = `/cashflow?filter=${currentFilter}`;
            if (currentFilter === 'custom' && start && end) {
                newUrl += `&start=${start}&end=${end}`;
            }
            if (source !== 'all') {
                newUrl += `&source=${source}`;
            }
            window.history.pushState({}, '', newUrl);

            try {
                const response = await fetch(`/cashflow/data?filter=${currentFilter}&source=${source}&start=${start}&end=${end}&page=${page}`, {
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
                initChart(data.chart.labels, data.chart.income, data.chart.expense);

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

        // Segmented Period Buttons Logic
        const periodBtns = document.querySelectorAll('.period-btn');
        periodBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                const filterVal = btn.dataset.filter;
                currentFilter = filterVal;

                // Update styling
                periodBtns.forEach(b => {
                    b.classList.remove('active', 'text-white', 'bg-blue-600');
                    b.classList.add('text-slate-500', 'hover:text-slate-300');
                });
                
                btn.classList.remove('text-slate-500', 'hover:text-slate-300');
                btn.classList.add('active', 'text-white', 'bg-blue-600');

                if (filterVal === 'custom') {
                    customDateContainer.classList.remove('hidden');
                    customDateContainer.classList.add('flex');
                    // Do not fetch immediately, let user pick dates
                } else {
                    customDateContainer.classList.add('hidden');
                    customDateContainer.classList.remove('flex');
                    fetchData(1);
                }
            });
        });

        document.getElementById('applyCustomBtn').addEventListener('click', () => {
            if (customStart.value && customEnd.value) {
                fetchData(1);
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Rentang Tanggal Kosong',
                    text: 'Silakan pilih rentang tanggal mulai dan akhir.',
                    background: '#1e293b',
                    color: '#f8fafc',
                    confirmButtonColor: '#3b82f6'
                });
            }
        });

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
    <!-- MODAL TARGET ROI -->
    <div id="modalTargetBEP" class="fixed inset-0 z-[60] hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-black/60 backdrop-blur-md" onclick="document.getElementById('modalTargetBEP').classList.add('hidden')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-slate-800 rounded-[2.5rem] border border-white/10 shadow-2xl sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
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

@endpush
@endsection
