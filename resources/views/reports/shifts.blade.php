@extends('layouts.app')

@section('title', 'Monitoring Shift')
@section('page-title', 'Dashboard Shift Kasir')
@section('page-subtitle', 'Monitoring operasional dan arus kas kasir')

@section('content')
<div x-data="shiftDashboardApp()" class="flex flex-col gap-6">

    {{-- FILTER BAR MODERN --}}
    <div class="bg-slate-800 rounded-2xl p-5 border border-slate-700/80 shadow-sm relative z-[50]">
        <form method="GET" id="filter-form" class="flex flex-col gap-4 w-full">
            <input type="hidden" name="date_from" value="{{ request('date_from') }}">
            <input type="hidden" name="date_to" value="{{ request('date_to') }}">
            <input type="hidden" name="period" value="{{ request('period', 'today') }}">
            {{-- PERIOD SELECTOR --}}
            <div class="flex items-center gap-3 mb-2 flex-wrap">
                <x-custom-filter :dateFrom="request('date_from')" :dateTo="request('date_to')" />

                @if($activeShift)
                <a href="{{ request()->url() }}?shift=live" 
                        class="px-5 py-2.5 text-[10px] font-black rounded-full transition-all duration-300 {{ (request('shift') == 'live') ? 'bg-rose-600 text-white shadow-lg shadow-rose-900/20' : 'text-rose-400 border border-rose-500/30 hover:bg-rose-500 hover:text-white bg-rose-500/10' }}">
                    LIVE
                </a>
                @endif
            </div>

            @if(auth()->user()->isOwner())
            <div class="flex flex-col md:flex-row gap-4 items-end md:items-center w-full mt-4 md:mt-0">
                <div class="flex-1 w-full grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1 block"><i class="fas fa-traffic-light"></i> Status Shift</label>
                        <select name="status" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-sm font-bold text-white focus:outline-none focus:border-blue-500 shadow-inner appearance-none">
                            <option value="">Semua Status</option>
                            <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Aktif (Berjalan)</option>
                            <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Selesai (Ditutup)</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1 block"><i class="fas fa-user-tag"></i> Kasir Bertugas</label>
                        <select name="user_id" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-sm font-bold text-white focus:outline-none focus:border-blue-500 shadow-inner appearance-none">
                            <option value="">Semua Kasir</option>
                            @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex gap-2 shrink-0 w-full md:w-auto mt-4 md:mt-0">
                    @if(request()->has('date_from'))
                    <a href="{{ request()->url() }}" class="py-2.5 px-4 bg-slate-700 hover:bg-slate-600 text-white font-bold rounded-xl transition-colors text-sm border border-slate-600 shadow-sm text-center flex-1 md:flex-none">Reset</a>
                    @endif
                    <button type="submit" class="py-2.5 px-6 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition-colors shadow-lg shadow-blue-500/20 text-sm flex items-center justify-center gap-2 flex-1 md:flex-none">
                        <i class="fas fa-filter"></i> Terapkan
                    </button>
                </div>
            </div>
            @endif
        </form>
    </div>

    {{-- SUMMARY SHIFT CARDS --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        {{-- Shift Aktif --}}
        <div class="bg-slate-800 rounded-2xl p-5 border border-slate-700/80 shadow-sm relative overflow-hidden group hover:-translate-y-1 transition-all duration-300">
            <div class="absolute -right-4 -top-4 w-20 h-20 bg-blue-500/10 rounded-full blur-xl pointer-events-none group-hover:bg-blue-500/20 transition-all"></div>
            <div class="flex items-center gap-4 relative z-10">
                <div class="w-12 h-12 rounded-xl bg-blue-500/20 flex items-center justify-center border border-blue-500/30 shrink-0 text-blue-400 group-hover:bg-blue-500 group-hover:text-white transition-colors shadow-inner">
                    <i class="fas fa-door-open text-xl"></i>
                </div>
                <div class="flex-1">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-0.5">Total Shift</p>
                    <h3 class="text-xl font-black text-white">{{ $activeShiftsCount + $closedShifts->count() }} Shift</h3>
                    <div class="mt-1.5 space-y-0.5">
                        <p class="text-[9px] text-slate-500 font-bold flex items-center gap-1">
                            <i class="fas fa-door-open text-[7px] text-blue-400"></i>
                            Aktif: <span class="text-slate-400">{{ $activeShiftsCount }}</span>
                        </p>
                        <p class="text-[9px] text-slate-500 font-bold flex items-center gap-1">
                            <i class="fas fa-door-closed text-[7px] text-slate-400"></i>
                            Selesai: <span class="text-slate-400">{{ $closedShifts->count() }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Penjualan --}}
        <div class="bg-slate-800 rounded-2xl p-5 border border-slate-700/80 shadow-sm relative overflow-hidden group hover:-translate-y-1 transition-all duration-300">
            <div class="absolute -right-4 -top-4 w-20 h-20 bg-purple-500/10 rounded-full blur-xl pointer-events-none group-hover:bg-purple-500/20 transition-all"></div>
            <div class="flex items-center gap-4 relative z-10">
                <div class="w-12 h-12 rounded-xl bg-purple-500/20 flex items-center justify-center border border-purple-500/30 shrink-0 text-purple-400 group-hover:bg-purple-500 group-hover:text-white transition-colors shadow-inner">
                    <i class="fas fa-chart-line text-xl"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-0.5">Total Omset</p>
                    <h3 class="text-xl font-black text-purple-400">Rp {{ number_format($totalSalesToday, 0, ',', '.') }}</h3>
                    <p class="text-[9px] text-slate-500 mt-1 font-bold">Periode terpilih</p>
                </div>
            </div>
        </div>

        {{-- Pengeluaran --}}
        <div class="bg-slate-800 rounded-2xl p-5 border border-slate-700/80 shadow-sm relative overflow-hidden group hover:-translate-y-1 transition-all duration-300">
            <div class="absolute -right-4 -top-4 w-20 h-20 bg-red-500/10 rounded-full blur-xl pointer-events-none group-hover:bg-red-500/20 transition-all"></div>
            <div class="flex items-center gap-4 relative z-10">
                <div class="w-12 h-12 rounded-xl bg-red-500/20 flex items-center justify-center border border-red-500/30 shrink-0 text-red-400 group-hover:bg-red-600 group-hover:text-white transition-colors shadow-inner">
                    <i class="fas fa-wallet text-xl"></i>
                </div>
                <div class="flex-1">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-0.5">Total Biaya</p>
                    <h3 class="text-xl font-black text-red-400">Rp {{ number_format($totalExpensesToday, 0, ',', '.') }}</h3>
                    <div class="mt-1.5 space-y-0.5">
                        <p class="text-[9px] text-slate-500 font-bold flex items-center gap-1">
                            <i class="fas fa-money-bill-wave text-[7px] text-emerald-500"></i>
                            Tunai: <span class="text-slate-400">Rp {{ number_format($cashExpensesToday, 0, ',', '.') }}</span>
                        </p>
                        <p class="text-[9px] text-slate-500 font-bold flex items-center gap-1">
                            <i class="fas fa-building-columns text-[7px] text-blue-400"></i>
                            Bank: <span class="text-slate-400">Rp {{ number_format($bankExpensesToday, 0, ',', '.') }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Laba Bersih --}}
        <div class="bg-slate-800 rounded-2xl p-5 border border-slate-700/80 shadow-sm relative overflow-hidden group hover:-translate-y-1 transition-all duration-300">
            <div class="absolute -right-4 -top-4 w-20 h-20 bg-emerald-500/10 rounded-full blur-xl pointer-events-none group-hover:bg-emerald-500/20 transition-all"></div>
            <div class="flex items-center gap-4 relative z-10">
                <div class="w-12 h-12 rounded-xl bg-emerald-500/20 flex items-center justify-center border border-emerald-500/30 shrink-0 text-emerald-400 group-hover:bg-emerald-500 group-hover:text-white transition-colors shadow-inner">
                    <i class="fas fa-sack-dollar text-xl"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-0.5">Pendapatan Bersih</p>
                    <h3 class="text-xl font-black text-emerald-400">Rp {{ number_format($netProfitToday, 0, ',', '.') }}</h3>
                    <p class="text-[9px] text-slate-500 mt-1 font-bold">Keuntungan riil</p>
                </div>
            </div>
        </div>

        {{-- Selisih Kas --}}
        <div @click="showDiscrepancyModal = true" class="bg-slate-800 rounded-2xl p-5 border border-slate-700/80 shadow-sm relative overflow-hidden group hover:-translate-y-1 hover:border-amber-500/30 transition-all duration-300 cursor-pointer">
            <div class="absolute -right-4 -top-4 w-20 h-20 {{ $totalDiscrepancy < 0 ? 'bg-red-500/10' : 'bg-amber-500/10' }} rounded-full blur-xl pointer-events-none transition-all"></div>
            <div class="flex items-center gap-4 relative z-10">
                <div class="w-12 h-12 rounded-xl {{ $totalDiscrepancy < 0 ? 'bg-red-500/20 text-red-400 border-red-500/30' : 'bg-amber-500/20 text-amber-400 border-amber-500/30' }} flex items-center justify-center border shrink-0 transition-colors shadow-inner">
                    <i class="fas fa-scale-unbalanced text-xl"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-0.5">Total Selisih Kas</p>
                    <h3 class="text-xl font-black {{ $totalDiscrepancy < 0 ? 'text-red-400' : ($totalDiscrepancy > 0 ? 'text-emerald-400' : 'text-slate-300') }}">
                        {{ $totalDiscrepancy < 0 ? '-' : ($totalDiscrepancy > 0 ? '+' : '') }} Rp {{ number_format(abs($totalDiscrepancy), 0, ',', '.') }}
                    </h3>
                    <p class="text-[9px] text-slate-500 mt-1 font-bold flex items-center gap-1">
                        Akumulasi shift selesai
                        <i class="fas fa-info-circle text-[8px] opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        {{-- KIRI: REALTIME & INSIGHT (40%) --}}
        <div class="lg:col-span-4 space-y-6">
            
            {{-- CURRENT SHIFT CARD (REALTIME) --}}
            @if($activeShift)
            <div class="bg-gradient-to-br from-blue-900/40 to-slate-800 rounded-2xl p-6 border border-blue-500/30 shadow-[0_0_15px_rgba(59,130,246,0.1)] relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>
                
                <div class="flex justify-between items-start mb-6 relative z-10">
                    <div>
                        <span class="px-2.5 py-1 text-[9px] font-black text-blue-400 uppercase tracking-wider rounded-md border border-blue-500/30 bg-blue-500/10 flex items-center gap-1.5 shadow-inner w-fit mb-2">
                            <i class="fas fa-circle text-[6px] animate-pulse"></i> LIVE SHIFT
                        </span>
                        <h3 class="font-black text-white text-lg">{{ $activeShift->opener->name }}</h3>
                        <p class="text-xs text-slate-400 mt-0.5"><i class="far fa-clock"></i> Dimulai: {{ $activeShift->opened_at->format('H:i') }} ({{ $activeShift->opened_at->diffForHumans() }})</p>
                    </div>
                </div>

                @php
                    // Compute expected laci from shift-only data (opening + sales - cash-out)
                    $shiftOnlyExpected = $activeShift->opening_cash + $currentSales - $currentCashExpenses;
                    // Net non-POS movements (transfers, adjustments) = actual laci - shift-only expected
                    $nonPosNet = $currentExpected - $shiftOnlyExpected;
                @endphp
                <div class="space-y-3 relative z-10">
                    {{-- Modal Awal --}}
                    <div class="flex justify-between items-center pb-2 border-b border-slate-700/50">
                        <span class="text-xs font-bold text-slate-400"><i class="fas fa-play-circle text-blue-400 mr-1"></i>Modal Kas Awal</span>
                        <span class="text-sm font-bold text-slate-200">Rp {{ number_format($activeShift->opening_cash, 0, ',', '.') }}</span>
                    </div>
                    {{-- Penjualan Tunai --}}
                    <div class="flex justify-between items-center pb-2 border-b border-slate-700/50">
                        <span class="text-xs font-bold text-slate-400"><i class="fas fa-plus text-emerald-400 mr-1"></i>Penjualan Tunai</span>
                        <span class="text-sm font-black text-emerald-400">+ Rp {{ number_format($currentSales, 0, ',', '.') }}</span>
                    </div>
                    {{-- Pengeluaran Cash --}}
                    @if($currentCashExpenses > 0)
                    <div class="flex justify-between items-center pb-2 border-b border-slate-700/50">
                        <span class="text-xs font-bold text-slate-400"><i class="fas fa-minus text-red-400 mr-1"></i>Pengeluaran Tunai</span>
                        <span class="text-sm font-bold text-red-400">- Rp {{ number_format($currentCashExpenses, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    {{-- Non-POS Movements (Transfers / Adjustments) --}}
                    @if($laciMovements->count() > 0)
                    <div class="pb-2 border-b border-slate-700/50">
                        <div class="flex justify-between items-center mb-1.5">
                            <span class="text-xs font-bold text-amber-400"><i class="fas fa-exchange-alt mr-1"></i>Transfer / Penyesuaian</span>
                            <span class="text-sm font-bold {{ $nonPosNet >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                                {{ $nonPosNet >= 0 ? '+' : '-' }} Rp {{ number_format(abs($nonPosNet), 0, ',', '.') }}
                            </span>
                        </div>
                        {{-- Itemized list --}}
                        <div class="space-y-1 pl-2 border-l-2 border-amber-500/30 ml-1">
                            @foreach($laciMovements as $mv)
                            <div class="flex justify-between items-center">
                                <span class="text-[10px] text-slate-500 truncate max-w-[55%]">
                                    {{ $mv->description ?: $mv->category }}
                                </span>
                                <span class="text-[10px] font-bold {{ $mv->type === 'income' ? 'text-emerald-400' : 'text-red-400' }}">
                                    {{ $mv->type === 'income' ? '+' : '-' }} Rp {{ number_format($mv->amount, 0, ',', '.') }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    {{-- Estimasi Total Laci --}}
                    <div class="flex justify-between items-center pt-1">
                        <span class="text-xs font-black text-slate-300">Estimasi Uang Laci</span>
                        <span class="text-lg font-black text-white">Rp {{ number_format($currentExpected, 0, ',', '.') }}</span>
                    </div>
                    @if($laciMovements->count() > 0 || $currentCashExpenses > 0)
                    <div class="bg-amber-500/5 border border-amber-500/20 rounded-lg px-3 py-2 flex items-start gap-2">
                        <i class="fas fa-info-circle text-amber-400 text-[10px] mt-0.5 shrink-0"></i>
                        <p class="text-[10px] text-amber-300/80 font-medium leading-relaxed">
                            Saldo berubah dari modal awal karena
                            @if($currentCashExpenses > 0 && $laciMovements->count() > 0)
                                pengeluaran tunai dan transfer/penyesuaian saldo selama sesi ini.
                            @elseif($currentCashExpenses > 0)
                                ada pengeluaran tunai selama sesi ini.
                            @else
                                ada transfer atau penyesuaian saldo selama sesi ini.
                            @endif
                        </p>
                    </div>
                    @endif
                </div>

                <div class="mt-6 pt-4 border-t border-slate-700/50 flex gap-2 relative z-10">
                    <a href="{{ route('pos.index') }}" class="flex-1 py-2.5 bg-slate-700 hover:bg-slate-600 text-white font-bold rounded-xl transition-colors shadow-sm text-xs flex items-center justify-center gap-2">
                        <i class="fas fa-cash-register"></i> Ke POS
                    </a>
                    <button @click="showCloseModal = true" class="flex-1 py-3 bg-gradient-to-r from-red-600 to-rose-500 hover:from-rose-500 hover:to-red-600 text-white font-black rounded-xl transition-all shadow-xl shadow-red-500/20 hover:shadow-red-500/40 hover:scale-[1.02] active:scale-95 text-xs flex items-center justify-center gap-2 uppercase tracking-wider">
                        <i class="fas fa-lock text-[10px]"></i> Tutup Shift
                    </button>
                </div>
            </div>
            @else
            <div class="bg-slate-800/50 rounded-2xl p-8 border border-slate-700 border-dashed flex flex-col items-center justify-center text-center">
                <div class="w-16 h-16 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-500 text-2xl mb-4 shadow-inner">
                    <i class="fas fa-bed"></i>
                </div>
                <h3 class="font-black text-white mb-1">Tidak Ada Shift Aktif</h3>
                <p class="text-xs text-slate-400 mb-6">Belum ada kasir yang membuka shift saat ini.</p>
                <button @click="showOpenModal = true" class="py-4 px-10 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-500 hover:to-blue-400 text-white font-black rounded-2xl transition-all shadow-xl shadow-blue-500/25 hover:shadow-blue-500/40 hover:scale-[1.02] active:scale-95 flex items-center justify-center gap-3 group uppercase tracking-[0.1em] text-xs">
                    <i class="fas fa-plus-circle group-hover:rotate-90 transition-transform duration-500 text-lg"></i> 
                    BUKA SHIFT BARU
                </button>
            </div>
            @endif

            {{-- INSIGHT LEVEL PRO --}}
            <div class="bg-slate-800 rounded-2xl p-6 border border-slate-700/80 shadow-sm space-y-5">
                <h3 class="text-xs font-black text-slate-400 uppercase tracking-wider flex items-center gap-2"><i class="fas fa-bolt text-yellow-400"></i> Insight Operasional</h3>
                
                {{-- Kasir Produktif --}}
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-emerald-500/20 text-emerald-500 flex items-center justify-center shrink-0 border border-emerald-500/30"><i class="fas fa-medal"></i></div>
                    <div class="flex-1">
                        <p class="text-[9px] font-bold text-slate-400 uppercase">Kasir Terbaik Hari Ini</p>
                        <h4 class="text-sm font-black text-white">{{ $bestCashier ? $bestCashier->name : 'Belum Ada' }}</h4>
                    </div>
                    @if($bestCashier)
                    <div class="text-right">
                        <span class="text-xs font-black text-emerald-400">Rp {{ number_format($bestCashier->total, 0, ',', '.') }}</span>
                    </div>
                    @endif
                </div>

                {{-- Shift Omzet Tertinggi --}}
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-purple-500/20 text-purple-500 flex items-center justify-center shrink-0 border border-purple-500/30"><i class="fas fa-rocket"></i></div>
                    <div class="flex-1">
                        <p class="text-[9px] font-bold text-slate-400 uppercase">Shift Omzet Tertinggi</p>
                        <h4 class="text-sm font-black text-white">{{ $highestShift ? $highestShift->opener->name : 'Belum Ada' }}</h4>
                        <p class="text-[9px] font-medium text-slate-500">{{ $highestShift ? $highestShift->opened_at->format('d/m/Y') : '' }}</p>
                    </div>
                </div>

                {{-- Rata-rata Selisih --}}
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full {{ $avgDiscrepancy < 0 ? 'bg-red-500/20 text-red-500 border-red-500/30' : 'bg-slate-700 text-slate-400 border-slate-600' }} flex items-center justify-center shrink-0 border"><i class="fas fa-scale-balanced"></i></div>
                    <div class="flex-1">
                        <p class="text-[9px] font-bold text-slate-400 uppercase">Rata-rata Selisih Kas</p>
                        <h4 class="text-sm font-black {{ $avgDiscrepancy < 0 ? 'text-red-400' : 'text-slate-300' }}">Rp {{ number_format(abs($avgDiscrepancy), 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>

        </div>

        {{-- KANAN: LIST SHIFT (60%) --}}
        <div class="lg:col-span-8 bg-slate-800 rounded-2xl border border-slate-700/80 shadow-sm overflow-hidden flex flex-col">
            <div class="p-5 border-b border-slate-700/80 flex justify-between items-center bg-slate-800/50">
                <h3 class="text-xs font-black text-slate-400 uppercase tracking-wider flex items-center gap-2"><i class="fas fa-list text-slate-300"></i> Riwayat Shift</h3>
                <button onclick="window.openExportModal()" class="w-10 h-10 bg-slate-800 border border-white/5 text-slate-400 rounded-2xl hover:bg-slate-700 hover:text-white transition-premium flex items-center justify-center shadow-lg" title="Ekspor Laporan (PDF/Excel/CSV)">
                    <i class="fas fa-file-export"></i>
                </button>
            </div>

            <div class="flex flex-col gap-0 divide-y divide-slate-700/50">
                @forelse($shifts as $s)
                <div class="group px-6 py-5 hover:bg-slate-700/20 transition-colors duration-300 cursor-pointer" @click="openShiftModal({{ $s->id }})">
                    <div class="flex flex-col md:flex-row justify-between md:items-center gap-4">
                        
                        {{-- Kiri: Identitas --}}
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl {{ $s->status === 'open' ? 'bg-blue-500/20 border-blue-500/30 text-blue-400 shadow-[0_0_10px_rgba(59,130,246,0.2)]' : 'bg-slate-900 border-slate-700 text-slate-500' }} border flex items-center justify-center shrink-0">
                                <i class="fas {{ $s->status === 'open' ? 'fa-door-open' : 'fa-lock' }}"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <h4 class="text-base font-black text-white">{{ $s->opener->name }}</h4>
                                    @if($s->status === 'open')
                                        <span class="px-2 py-0.5 text-[8px] font-black uppercase tracking-wider rounded-md bg-blue-500/20 text-blue-400 border border-blue-500/30">Aktif</span>
                                    @else
                                        <span class="px-2 py-0.5 text-[8px] font-black uppercase tracking-wider rounded-md bg-slate-700 text-slate-400 border border-slate-600">Selesai</span>
                                    @endif
                                </div>
                                <div class="text-xs font-bold text-slate-500 flex flex-wrap items-center gap-1.5 mt-1">
                                    <span class="flex items-center gap-1.5"><i class="far fa-calendar-alt"></i> {{ $s->opened_at->format('d M Y') }}</span>
                                    <span class="text-slate-700">•</span>
                                    <span class="flex items-center gap-1.5"><i class="far fa-clock"></i> {{ $s->opened_at->format('H:i') }} - {{ $s->closed_at ? $s->closed_at->format('H:i') : 'Skrg' }}</span>
                                    <span class="ml-1 px-1.5 py-0.5 rounded-md bg-slate-900/50 text-[9px] font-black uppercase tracking-wider text-blue-400/80 border border-slate-700/50">{{ $s->getDuration() }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Tengah: Uang --}}
                        <div class="flex gap-6 lg:justify-center shrink-0">
                            <div>
                                <p class="text-[9px] font-black text-slate-500 uppercase tracking-wider">Kas Awal</p>
                                <p class="text-sm font-bold text-slate-300 mt-0.5">Rp {{ number_format($s->opening_cash, 0, ',', '.') }}</p>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-slate-500 uppercase tracking-wider">Penjualan</p>
                                @php
                                    $rowSales = $s->status === 'open' 
                                        ? \App\Models\Transaction::withoutGlobalScopes()->where('shift_id', $s->id)->where('status', 'completed')->sum('total')
                                        : $s->total_sales;
                                @endphp
                                <p class="text-sm font-black text-emerald-400 mt-0.5">Rp {{ number_format($rowSales, 0, ',', '.') }}</p>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-slate-500 uppercase tracking-wider">Pengeluaran</p>
                                @php
                                    $rowCashExpenses = $s->status === 'open'
                                        ? \App\Models\Cashflow::withoutGlobalScopes()->where('shift_id', $s->id)->where('type', 'expense')->where('source', 'pos_cash')->sum('amount')
                                        : ($s->cash_expenses ?? 0);
                                    $rowBankExpenses = $s->status === 'open'
                                        ? \App\Models\Cashflow::withoutGlobalScopes()->where('shift_id', $s->id)->where('type', 'expense')->whereIn('source', ['pos_bank', 'transfer'])->sum('amount')
                                        : ($s->bank_expenses ?? 0);
                                @endphp
                                <div class="mt-0.5 space-y-0.5">
                                    @if($rowCashExpenses > 0 || $rowBankExpenses == 0)
                                    <p class="text-[11px] font-bold text-red-400 leading-tight">
                                        <i class="fas fa-money-bill-wave text-[9px] w-3 text-center"></i> Rp {{ number_format($rowCashExpenses, 0, ',', '.') }}
                                    </p>
                                    @endif
                                    @if($rowBankExpenses > 0)
                                    <p class="text-[11px] font-bold text-blue-400 leading-tight">
                                        <i class="fas fa-university text-[9px] w-3 text-center"></i> Rp {{ number_format($rowBankExpenses, 0, ',', '.') }}
                                    </p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Kanan: Hasil & Selisih --}}
                        <div class="flex gap-4 md:gap-6 items-center shrink-0">
                            @if($s->closed_at)
                                @php 
                                    $rowCashSales = \App\Models\Transaction::withoutGlobalScopes()->where('shift_id', $s->id)->where('payment_method', 'cash')->where('status', 'completed')->sum('total');
                                    $expected = $s->opening_cash + $rowCashSales - $rowCashExpenses; 
                                    $selisih = $s->closing_cash - $expected;
                                @endphp
                                <div class="text-right">
                                    <p class="text-[9px] font-black text-slate-500 uppercase tracking-wider">Kas Laci</p>
                                    <p class="text-sm font-bold text-white mt-0.5">Rp {{ number_format($s->closing_cash, 0, ',', '.') }}</p>
                                </div>
                                <div class="text-right min-w-[80px]">
                                    <p class="text-[9px] font-black text-slate-500 uppercase tracking-wider">Selisih</p>
                                    <div class="px-2 py-0.5 mt-0.5 rounded-md inline-block {{ abs($selisih) < 1 ? 'bg-slate-700/50 text-slate-400' : ($selisih > 0 ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20') }}">
                                        <p class="text-sm font-black">{{ abs($selisih) < 1 ? 'Pas' : ($selisih > 0 ? '+'.number_format($selisih, 0, ',', '.') : '-'.number_format(abs($selisih), 0, ',', '.')) }}</p>
                                    </div>
                                </div>
                            @else
                                <div class="text-right w-full">
                                    <p class="text-[9px] font-black text-blue-400 uppercase tracking-wider animate-pulse">Menunggu shift ditutup</p>
                                </div>
                            @endif
                            <div class="flex items-center gap-2 ml-4 md:ml-6">
                                @if(auth()->user()->isOwner())
                                <button @click.stop="openEditModalFor({{ $s->id }})" class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-400 hover:bg-blue-600 hover:text-white flex items-center justify-center transition-colors shadow-sm" title="Edit Shift">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('shifts.destroy', $s->id) }}" method="POST" class="m-0" id="delete-shift-{{ $s->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" @click.stop="confirmDelete('{{ $s->id }}')" class="w-8 h-8 rounded-lg bg-red-500/10 text-red-400 hover:bg-red-600 hover:text-white flex items-center justify-center transition-colors shadow-sm" title="Hapus Shift">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
                @empty
                <div class="p-12 text-center text-slate-500 flex flex-col items-center">
                    <div class="w-16 h-16 bg-slate-800 rounded-full flex items-center justify-center mb-4"><i class="fas fa-box-open text-2xl text-slate-600"></i></div>
                    <p class="font-bold text-white mb-1">Tidak Ada Data Shift</p>
                    <p class="text-sm">Filter saat ini tidak mengembalikan hasil apapun.</p>
                </div>
                @endforelse
            </div>

            @if($shifts->hasPages())
            <div class="p-4 border-t border-slate-700/80 bg-slate-800/50">
                {{ $shifts->links('pagination::tailwind') }}
            </div>
            @endif
        </div>
    </div>

    {{-- MODAL DETAIL SHIFT --}}
    <div x-show="isModalOpen" x-transition.opacity x-cloak class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div @click.away="closeModal()" x-show="isModalOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="bg-[#1e293b] rounded-3xl w-full max-w-2xl shadow-2xl border border-slate-700 overflow-hidden flex flex-col max-h-[90vh]">
            <div class="p-6 border-b border-slate-700/80 flex justify-between items-center bg-slate-800/50 shrink-0">
                <h3 class="text-lg font-black text-white flex items-center gap-2"><i class="fas fa-file-invoice text-blue-400"></i> Detail Shift</h3>
                <button @click="closeModal()" class="w-8 h-8 bg-slate-700 hover:bg-slate-600 rounded-full text-slate-400 hover:text-white transition-colors flex items-center justify-center"><i class="fas fa-times"></i></button>
            </div>
            <div class="p-6 overflow-y-auto">
                <template x-if="detailLoading"><div class="flex flex-col items-center py-12"><i class="fas fa-spinner fa-spin text-2xl text-blue-400 mb-3"></i><p class="text-slate-400 text-sm">Memuat...</p></div></template>
                <template x-if="!detailLoading && detailData">
                    <div class="space-y-6">
                        {{-- Alert Info --}}
                        <div class="bg-emerald-500/10 border border-emerald-500/20 rounded-xl p-3 flex items-start gap-2 text-emerald-400">
                            <i class="fas fa-check-circle mt-0.5"></i>
                            <p class="text-xs font-medium">Nominal penjualan diambil dari Rekap Penjualan (shifts).</p>
                        </div>

                        {{-- General Info --}}
                        <div class="bg-slate-800 rounded-xl border border-slate-700/50 p-4 space-y-3">
                            <div class="flex justify-between items-center border-b border-slate-700/50 pb-2">
                                <span class="text-xs font-medium text-slate-400">Kasir</span>
                                <span class="text-sm font-bold text-white" x-text="detailData.opener"></span>
                            </div>
                            <div class="flex justify-between items-center border-b border-slate-700/50 pb-2">
                                <span class="text-xs font-medium text-slate-400">Status</span>
                                <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-md tracking-wider border" :class="detailData.status=='open'?'bg-blue-500/20 text-blue-400 border-blue-500/30':'bg-slate-700 text-slate-400 border-slate-600'" x-text="detailData.status=='open'?'Aktif':'Selesai'"></span>
                            </div>
                            <div class="flex justify-between items-center border-b border-slate-700/50 pb-2">
                                <span class="text-xs font-medium text-slate-400">Shift Dibuka</span>
                                <span class="text-xs font-bold text-white" x-text="detailData.opened_at"></span>
                            </div>
                            <div class="flex justify-between items-center border-b border-slate-700/50 pb-2">
                                <span class="text-xs font-medium text-slate-400">Shift Ditutup</span>
                                <span class="text-xs font-bold text-white" x-text="detailData.closed_at"></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-xs font-medium text-slate-400">Durasi</span>
                                <span class="text-xs font-bold text-white" x-text="detailData.duration"></span>
                            </div>
                        </div>

                        {{-- Penjualan Section --}}
                        <div>
                            <h4 class="text-xs font-black text-emerald-400 uppercase flex items-center gap-2 mb-2 tracking-wider"><i class="fas fa-chart-line"></i> Penjualan</h4>
                            <div class="bg-slate-800 rounded-xl border border-slate-700/50 p-4 space-y-3">
                                <div class="flex justify-between items-center border-b border-slate-700/50 pb-2">
                                    <span class="text-xs font-medium text-slate-400">Total Transaksi</span>
                                    <span class="text-sm font-black text-white" x-text="detailData.total_transactions"></span>
                                </div>
                                <div class="flex justify-between items-center border-b border-slate-700/50 pb-2">
                                    <span class="text-xs font-medium text-slate-400">Total Penjualan</span>
                                    <span class="text-sm font-black text-white" x-text="'Rp '+Number(detailData.total_sales).toLocaleString('id-ID')"></span>
                                </div>
                                <div class="flex justify-between items-center border-b border-slate-700/50 pb-2">
                                    <span class="text-xs font-medium text-slate-400">Cash</span>
                                    <span class="text-sm font-bold text-emerald-400" x-text="'Rp '+Number(detailData.cash_sales).toLocaleString('id-ID')"></span>
                                </div>
                                <div class="flex justify-between items-center border-b border-slate-700/50 pb-2">
                                    <span class="text-xs font-medium text-slate-400">QRIS</span>
                                    <span class="text-sm font-bold text-white" x-text="'Rp '+Number(detailData.qris_sales).toLocaleString('id-ID')"></span>
                                </div>
                                <div class="flex justify-between items-center border-b border-slate-700/50 pb-2">
                                    <span class="text-xs font-medium text-slate-400">Transfer</span>
                                    <span class="text-sm font-bold text-white" x-text="'Rp '+Number(detailData.transfer_sales).toLocaleString('id-ID')"></span>
                                </div>
                                <div class="flex justify-between items-center" x-show="detailData.debit_sales > 0">
                                    <span class="text-xs font-medium text-slate-400">Debit</span>
                                    <span class="text-sm font-bold text-white" x-text="'Rp '+Number(detailData.debit_sales).toLocaleString('id-ID')"></span>
                                </div>
                            </div>
                        </div>

                        {{-- Arus Kas Section --}}
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <h4 class="text-xs font-black text-blue-400 uppercase flex items-center gap-2 tracking-wider"><i class="fas fa-money-bill-wave"></i> Arus Kas</h4>
                                @if(auth()->user()->isOwner())
                                <button @click="openEditModal()" class="text-[10px] font-bold text-blue-400 hover:text-white bg-blue-500/10 hover:bg-blue-600 border border-blue-500/20 px-2 py-1 rounded-md transition-colors flex items-center gap-1.5"><i class="fas fa-edit"></i> Edit Angka</button>
                                @endif
                            </div>
                            <div class="bg-slate-800 rounded-xl border border-slate-700/50 p-4 space-y-3">
                                <div class="flex justify-between items-center border-b border-slate-700/50 pb-2">
                                    <span class="text-xs font-medium text-slate-400">Modal Awal</span>
                                    <span class="text-sm font-bold text-white" x-text="'Rp '+Number(detailData.opening_cash).toLocaleString('id-ID')"></span>
                                </div>
                                <div class="flex justify-between items-center border-b border-slate-700/50 pb-2">
                                    <span class="text-xs font-medium text-slate-400">+ Penjualan Cash</span>
                                    <span class="text-sm font-bold text-emerald-400" x-text="'+ Rp '+Number(detailData.cash_sales).toLocaleString('id-ID')"></span>
                                </div>
                                <div class="flex justify-between items-center border-b border-slate-700/50 pb-2">
                                    <span class="text-xs font-medium text-slate-400">- Pengeluaran Cash</span>
                                    <span class="text-sm font-bold text-red-400" x-text="'- Rp '+Number(detailData.cash_expenses).toLocaleString('id-ID')"></span>
                                </div>
                                <div class="flex justify-between items-center border-b border-slate-700/50 pb-2">
                                    <span class="text-xs font-black text-slate-300">Expected Cash</span>
                                    <span class="text-sm font-black text-white" x-text="'Rp '+Number(detailData.expected_cash).toLocaleString('id-ID')"></span>
                                </div>
                                <div class="flex justify-between items-center border-b border-slate-700/50 pb-2">
                                    <span class="text-xs font-medium text-slate-400">Uang di Laci</span>
                                    <span class="text-sm font-bold text-white" x-text="detailData.status=='closed' ? 'Rp '+Number(detailData.closing_cash).toLocaleString('id-ID') : 'Belum ditutup'"></span>
                                </div>
                                <div class="flex justify-between items-center pb-2 border-b border-slate-700/50">
                                    <span class="text-xs font-black text-slate-300">Selisih</span>
                                    <span class="text-sm font-black" :class="(detailData.discrepancy||0)<0?'text-red-400':((detailData.discrepancy||0)>0?'text-emerald-400':'text-slate-400')" x-text="detailData.status=='open' ? '-' : (detailData.discrepancy==0 ? 'Pas (Rp 0)' : ((detailData.discrepancy>0?'+':'') + 'Rp ' + Number(Math.abs(detailData.discrepancy)).toLocaleString('id-ID')))"></span>
                                </div>
                                <div class="flex justify-between items-start pt-1">
                                    <span class="text-[10px] font-medium text-slate-500">Catatan</span>
                                    <span class="text-[10px] text-slate-400 text-right max-w-[60%]" x-text="detailData.notes || '-'"></span>
                                </div>
                            </div>
                        </div>

                        {{-- Danger Zone (Hapus Shift) --}}
                        @if(auth()->user()->isOwner())
                        <div class="bg-red-500/10 border border-red-500/20 rounded-xl p-4 mt-6">
                            <h4 class="text-sm font-black text-red-400 flex items-center gap-2 mb-1"><i class="fas fa-exclamation-triangle"></i> Hapus Shift Ini</h4>
                            <p class="text-xs text-red-400/80 mb-4 font-medium">Aksi ini tidak dapat dibatalkan.</p>
                            
                            <div class="bg-red-950/30 rounded-lg p-3 text-[10px] space-y-2 mb-4">
                                <p class="font-bold text-red-300">Apa yang akan terjadi:</p>
                                <div class="flex gap-2 text-red-400/80">
                                    <i class="fas fa-minus-circle mt-0.5 shrink-0"></i>
                                    <p><strong class="text-red-300">Terhapus:</strong> data shift ini (modal awal, cash out, selisih kas, catatan).</p>
                                </div>
                                <div class="flex gap-2 text-emerald-400/80">
                                    <i class="fas fa-check-circle mt-0.5 shrink-0 text-emerald-400"></i>
                                    <p><strong class="text-emerald-400">TIDAK terhapus:</strong> data Rekap Penjualan, transaksi, dan cashflow yang sudah tersinkronisasi. Semua data penjualan tetap aman.</p>
                                </div>
                            </div>

                            <form :action="'/shifts/' + activeShiftId" method="POST" onsubmit="return confirm('Yakin ingin menghapus shift ini? Peringatan: Shift yang memiliki transaksi terkait mungkin tidak dapat dihapus.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl transition-colors text-sm shadow-lg shadow-red-500/20 flex items-center justify-center gap-2">
                                    <i class="fas fa-trash"></i> Hapus Shift
                                </button>
                            </form>
                        </div>
                        @endif
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- MODAL EDIT SHIFT --}}
    <div x-show="showEditModal" x-transition.opacity x-cloak class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm z-[60] flex items-center justify-center p-4">
        <div @click.away="showEditModal=false" class="bg-[#1e293b] rounded-3xl w-full max-w-md shadow-2xl border border-slate-700 p-6">
            <h3 class="text-lg font-black text-white mb-1 flex items-center gap-2"><i class="fas fa-edit text-blue-400"></i> Edit Shift</h3>
            <p class="text-xs text-slate-400 mb-5">Perbarui data kas awal dan akhir shift.</p>
            <form :action="'/shifts/' + activeShiftId" method="POST">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1">Kas Awal (Rp)</label>
                        <div class="relative"><span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 font-bold">Rp</span>
                            <input type="number" name="opening_cash" x-model="editOpening" required min="0" class="w-full bg-slate-900 border border-slate-700 rounded-xl pl-12 pr-4 py-3 text-white font-bold text-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                        </div>
                    </div>
                    <template x-if="detailData?.status == 'closed'">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1">Kas Laci Saat Ditutup (Rp)</label>
                            <div class="relative"><span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 font-bold">Rp</span>
                                <input type="number" name="closing_cash" x-model="editClosing" required min="0" class="w-full bg-slate-900 border border-slate-700 rounded-xl pl-12 pr-4 py-3 text-white font-bold text-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                            </div>

                            {{-- Live Calculator Info --}}
                            <div class="bg-slate-800 rounded-xl p-4 border border-slate-700/50 space-y-3 mt-4 shadow-inner">
                                <div class="border-b border-slate-700/50 pb-3 mb-2">
                                    <h4 class="text-[10px] font-black text-blue-400 uppercase tracking-wider mb-2"><i class="fas fa-calculator mr-1"></i> Kalkulasi Selisih Kas (Live)</h4>
                                    
                                    <div class="bg-slate-900/50 rounded-lg p-2 space-y-1.5 border border-slate-700/30">
                                        <div class="flex justify-between items-center">
                                            <span class="text-[10px] font-medium text-slate-500">Total Penjualan Keseluruhan</span>
                                            <span class="text-[10px] font-bold text-slate-400" x-text="'Rp '+Number(detailData?.total_sales||0).toLocaleString('id-ID')"></span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-[10px] font-medium text-slate-500">QRIS</span>
                                            <span class="text-[10px] font-bold text-blue-400/80" x-text="'Rp '+Number(detailData?.qris_sales||0).toLocaleString('id-ID')"></span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-[10px] font-medium text-slate-500">Transfer</span>
                                            <span class="text-[10px] font-bold text-blue-400/80" x-text="'Rp '+Number(detailData?.transfer_sales||0).toLocaleString('id-ID')"></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-slate-400">Kas Awal</span>
                                    <span class="text-sm font-bold text-slate-300" x-text="'Rp '+Number(editOpening || 0).toLocaleString('id-ID')"></span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-slate-400">+ Penjualan Cash</span>
                                    <span class="text-sm font-bold text-emerald-400" x-text="'+Rp '+Number(detailData?.cash_sales||0).toLocaleString('id-ID')"></span>
                                </div>
                                <div class="flex justify-between items-center border-b border-slate-700/50 pb-2">
                                    <span class="text-xs text-slate-400">- Pengeluaran Cash</span>
                                    <span class="text-sm font-bold text-red-400" x-text="'-Rp '+Number(detailData?.cash_expenses||0).toLocaleString('id-ID')"></span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-xs font-bold text-slate-300">Expected Cash</span>
                                    <span class="text-sm font-black text-white" x-text="'Rp '+Number(editExpected).toLocaleString('id-ID')"></span>
                                </div>
                                <div class="flex justify-between items-center pt-2 border-t border-slate-700/50 mt-1">
                                    <span class="text-xs font-black text-slate-300">Estimasi Selisih</span>
                                    <div class="px-2 py-1 rounded-md" :class="editDiscrepancy < 0 ? 'bg-red-500/10 text-red-400' : (editDiscrepancy > 0 ? 'bg-emerald-500/10 text-emerald-400' : 'bg-slate-700 text-slate-300')">
                                        <span class="text-sm font-black" x-text="editDiscrepancy == 0 ? 'Pas (Rp 0)' : ((editDiscrepancy>0?'+':'') + 'Rp ' + Number(Math.abs(editDiscrepancy)).toLocaleString('id-ID'))"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1">Catatan</label>
                        <textarea name="notes" :value="detailData?.notes" rows="2" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 text-white text-sm focus:outline-none focus:border-blue-500"></textarea>
                    </div>
                    <div class="flex gap-2 pt-2">
                        <button type="button" @click="showEditModal=false" class="flex-1 py-2.5 bg-slate-700 hover:bg-slate-600 text-white font-bold rounded-xl text-sm">Batal</button>
                        <button type="submit" class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-sm shadow-lg shadow-blue-500/20"><i class="fas fa-save mr-1"></i>Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL TUTUP SHIFT --}}
    @if($activeShift)
        @include('components.modals.tutup-shift')
    @endif

    {{-- MODAL BUKA SHIFT --}}
    @if(!$activeShift)
        @include('components.modals.buka-shift')
    @endif

    {{-- MODAL BREAKDOWN SELISIH --}}
    <div x-show="showDiscrepancyModal" 
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6"
         x-cloak>
        <div x-show="showDiscrepancyModal" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="showDiscrepancyModal = false"
             class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm"></div>

        <div x-show="showDiscrepancyModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4"
             class="bg-slate-900 border border-slate-700 w-full max-w-2xl rounded-3xl shadow-2xl relative z-10 overflow-hidden">
            
            <div class="p-6 border-b border-slate-800 flex justify-between items-center bg-gradient-to-r from-slate-900 to-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center border border-amber-500/20">
                        <i class="fas fa-list-check"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-white">Rincian Selisih Kas</h3>
                        <p class="text-[10px] text-slate-500 uppercase tracking-widest font-bold">Daftar shift yang berkontribusi</p>
                    </div>
                </div>
                <button @click="showDiscrepancyModal = false" class="w-8 h-8 rounded-full hover:bg-slate-700 flex items-center justify-center text-slate-400 transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="p-6 max-h-[60vh] overflow-y-auto custom-scrollbar">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-[10px] font-black text-slate-500 uppercase tracking-widest border-b border-slate-800">
                            <th class="pb-3 px-2">Kasir / Waktu</th>
                            <th class="pb-3 px-2 text-right">Hitungan Sistem</th>
                            <th class="pb-3 px-2 text-right">Uang Laci</th>
                            <th class="pb-3 px-2 text-right">Selisih</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/50">
                        @php $hasDiscrepancy = false; @endphp
                        @if(isset($closedShifts))
                            @foreach($closedShifts as $s)
                                @if(abs($s->discrepancy) > 0.1)
                                    @php 
                                        $hasDiscrepancy = true; 
                                        // Redo the calculation to be explicit in the UI
                                        $cashSales = \App\Models\Transaction::withoutGlobalScopes()->where('shift_id', $s->id)->where('payment_method', 'cash')->where('status', 'completed')->sum('total');
                                        $cashExpenses = \App\Models\Cashflow::withoutGlobalScopes()->where('shift_id', $s->id)->where('type', 'expense')->where('source', 'pos_cash')->sum('amount');
                                        $expected = $s->opening_cash + $cashSales - $cashExpenses;
                                    @endphp
                                    <tr class="group hover:bg-slate-800/30 transition-colors">
                                        <td class="py-4 px-2">
                                            <p class="text-sm font-bold text-slate-200">{{ $s->opener->name ?? 'Kasir' }}</p>
                                            <p class="text-[9px] text-slate-500">{{ $s->opened_at->format('d M Y H:i') }}</p>
                                        </td>
                                        <td class="py-4 px-2 text-right">
                                            <div class="text-[10px] text-slate-400 space-y-0.5">
                                                <p>Awal: {{ number_format($s->opening_cash, 0, ',', '.') }}</p>
                                                <p>+ Jual: {{ number_format($cashSales, 0, ',', '.') }}</p>
                                                <p>- Keluar: {{ number_format($cashExpenses, 0, ',', '.') }}</p>
                                                <p class="font-bold border-t border-slate-700 pt-0.5 text-slate-300">= {{ number_format($expected, 0, ',', '.') }}</p>
                                            </div>
                                        </td>
                                        <td class="py-4 px-2 text-right">
                                            <p class="text-sm font-black text-white">Rp {{ number_format($s->closing_cash, 0, ',', '.') }}</p>
                                        </td>
                                        <td class="py-4 px-2 text-right">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-black {{ $s->discrepancy > 0 ? 'bg-emerald-500/10 text-emerald-400' : 'bg-red-500/10 text-red-400' }}">
                                                {{ $s->discrepancy > 0 ? '+' : '-' }} {{ number_format(abs($s->discrepancy), 0, ',', '.') }}
                                            </span>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        @endif
                        
                        @if(!$hasDiscrepancy)
                            <tr>
                                <td colspan="2" class="py-12 text-center">
                                    <div class="w-12 h-12 bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-3">
                                        <i class="fas fa-check-circle text-emerald-500"></i>
                                    </div>
                                    <p class="text-sm font-bold text-slate-400">Semua shift sudah pas!</p>
                                    <p class="text-[10px] text-slate-600 mt-1">Tidak ada selisih yang ditemukan dalam periode ini.</p>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="p-6 bg-slate-900/50 border-t border-slate-800 flex justify-between items-center">
                <span class="text-sm font-bold text-slate-400">Total Akumulasi</span>
                <span class="text-xl font-black {{ $totalDiscrepancy > 0 ? 'text-emerald-400' : ($totalDiscrepancy < 0 ? 'text-red-400' : 'text-slate-300') }}">
                    {{ $totalDiscrepancy > 0 ? '+' : ($totalDiscrepancy < 0 ? '-' : '') }} Rp {{ number_format(abs($totalDiscrepancy), 0, ',', '.') }}
                </span>
            </div>
        </div>
    </div>
</div>

<script>
function setQuickPeriod(p) {
    document.getElementById('period_input').value = p;
    if (p === 'custom') {
        window.showCustomDates = true;
        // Kita tidak langsung submit jika custom, agar user bisa pilih tanggal
        return;
    }
    document.getElementById('filter-form').submit();
}

function shiftDashboardApp() {
    return {
        isModalOpen: false,
        activeShiftId: null,
        detailLoading: false,
        detailData: null,
        showCloseModal: false,
        showOpenModal: {{ request('open') && !$activeShift ? 'true' : 'false' }},
        showEditModal: false,
        showDiscrepancyModal: false,

        editOpening: 0,
        editClosing: 0,

        get editExpected() {
            if (!this.detailData) return 0;
            return Number(this.editOpening) + Number(this.detailData.cash_sales) - Number(this.detailData.cash_expenses);
        },

        get editDiscrepancy() {
            return Number(this.editClosing) - this.editExpected;
        },

        openEditModal() {
            if (this.detailData) {
                this.editOpening = this.detailData.opening_cash || 0;
                this.editClosing = this.detailData.closing_cash || 0;
            }
            this.showEditModal = true;
        },

        openEditModalFor(id) {
            this.activeShiftId = id;
            this.detailLoading = true;
            this.detailData = null;
            this.showEditModal = true;
            fetch('/shifts/' + id + '/summary')
                .then(r => r.json())
                .then(d => { 
                    this.detailData = d; 
                    this.editOpening = d.opening_cash || 0;
                    this.editClosing = d.closing_cash || 0;
                    this.detailLoading = false; 
                })
                .catch(() => { this.detailLoading = false; });
        },

        openShiftModal(id) {
            this.activeShiftId = id;
            this.isModalOpen = true;
            this.detailLoading = true;
            this.detailData = null;
            fetch('/shifts/' + id + '/summary')
                .then(r => r.json())
                .then(d => { 
                    this.detailData = d; 
                    this.editOpening = d.opening_cash || 0;
                    this.editClosing = d.closing_cash || 0;
                    this.detailLoading = false; 
                })
                .catch(() => { this.detailLoading = false; });
        },
        closeModal() {
            this.isModalOpen = false;
            setTimeout(() => { this.activeShiftId = null; this.detailData = null; }, 300);
        }
    }
}

function confirmDelete(id) {
    Swal.fire({
        title: 'Hapus Shift?',
        text: 'Data shift beserta semua transaksi dan pengeluaran di dalamnya akan dihapus permanen!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#334155',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        background: '#1e293b',
        color: '#f8fafc',
        customClass: {
            popup: 'rounded-3xl border border-slate-700',
            confirmButton: 'rounded-xl font-bold px-6 py-2.5',
            cancelButton: 'rounded-xl font-bold px-6 py-2.5'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-shift-' + id).submit();
        }
    });
}
</script>
@endsection
