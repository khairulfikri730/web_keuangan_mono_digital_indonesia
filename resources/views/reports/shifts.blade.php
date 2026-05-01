@extends('layouts.app')

@section('title', 'Monitoring Shift')
@section('page-title', 'Dashboard Shift Kasir')
@section('page-subtitle', 'Monitoring operasional dan arus kas kasir')

@section('content')
<div x-data="shiftDashboardApp()" class="flex flex-col gap-6">

    {{-- FILTER BAR MODERN --}}
    <div class="bg-slate-800 rounded-2xl p-5 border border-slate-700/80 shadow-sm relative z-40">
        <form method="GET" class="flex flex-col md:flex-row gap-4 items-end md:items-center w-full">
            <div class="flex-1 w-full grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="col-span-1 md:col-span-2 flex items-center gap-2">
                    <div class="w-full relative">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1 block"><i class="far fa-calendar-alt"></i> Dari Tanggal</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-sm font-bold text-white focus:outline-none focus:border-blue-500 shadow-inner">
                    </div>
                    <span class="text-slate-500 font-bold self-end mb-2">-</span>
                    <div class="w-full relative">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1 block"><i class="far fa-calendar-check"></i> Sampai Tanggal</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-sm font-bold text-white focus:outline-none focus:border-blue-500 shadow-inner">
                    </div>
                </div>
                
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
                <a href="{{ route('reports.shifts') }}" class="py-2.5 px-4 bg-slate-700 hover:bg-slate-600 text-white font-bold rounded-xl transition-colors text-sm border border-slate-600 shadow-sm text-center flex-1 md:flex-none">Reset</a>
                @endif
                <button type="submit" class="py-2.5 px-6 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition-colors shadow-lg shadow-blue-500/20 text-sm flex items-center justify-center gap-2 flex-1 md:flex-none">
                    <i class="fas fa-filter"></i> Terapkan
                </button>
            </div>
        </form>
    </div>

    {{-- SUMMARY SHIFT CARDS --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Shift Aktif --}}
        <div class="bg-slate-800 rounded-2xl p-5 border border-slate-700/80 shadow-sm relative overflow-hidden group hover:-translate-y-1 transition-all duration-300">
            <div class="absolute -right-4 -top-4 w-20 h-20 bg-blue-500/10 rounded-full blur-xl pointer-events-none group-hover:bg-blue-500/20 transition-all"></div>
            <div class="flex items-center gap-4 relative z-10">
                <div class="w-12 h-12 rounded-xl bg-blue-500/20 flex items-center justify-center border border-blue-500/30 shrink-0 text-blue-400 group-hover:bg-blue-500 group-hover:text-white transition-colors shadow-inner">
                    <i class="fas fa-door-open text-xl"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-0.5">Shift Aktif Saat Ini</p>
                    <h3 class="text-xl font-black text-white">{{ $activeShiftsCount }} Kasir</h3>
                    <p class="text-[9px] text-slate-500 mt-1 font-bold">Sedang bertugas</p>
                </div>
            </div>
        </div>

        {{-- Total Kas Masuk --}}
        <div class="bg-slate-800 rounded-2xl p-5 border border-slate-700/80 shadow-sm relative overflow-hidden group hover:-translate-y-1 transition-all duration-300">
            <div class="absolute -right-4 -top-4 w-20 h-20 bg-emerald-500/10 rounded-full blur-xl pointer-events-none group-hover:bg-emerald-500/20 transition-all"></div>
            <div class="flex items-center gap-4 relative z-10">
                <div class="w-12 h-12 rounded-xl bg-emerald-500/20 flex items-center justify-center border border-emerald-500/30 shrink-0 text-emerald-400 group-hover:bg-emerald-500 group-hover:text-white transition-colors shadow-inner">
                    <i class="fas fa-cash-register text-xl"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-0.5">Total Uang Laci (Closed)</p>
                    <h3 class="text-xl font-black text-emerald-400">Rp {{ number_format($totalClosingCash, 0, ',', '.') }}</h3>
                    <p class="text-[9px] text-slate-500 mt-1 font-bold">Kas riil di laci saat shift tutup</p>
                </div>
            </div>
        </div>

        {{-- Penjualan Hari Ini --}}
        <div class="bg-slate-800 rounded-2xl p-5 border border-slate-700/80 shadow-sm relative overflow-hidden group hover:-translate-y-1 transition-all duration-300">
            <div class="absolute -right-4 -top-4 w-20 h-20 bg-purple-500/10 rounded-full blur-xl pointer-events-none group-hover:bg-purple-500/20 transition-all"></div>
            <div class="flex items-center gap-4 relative z-10">
                <div class="w-12 h-12 rounded-xl bg-purple-500/20 flex items-center justify-center border border-purple-500/30 shrink-0 text-purple-400 group-hover:bg-purple-500 group-hover:text-white transition-colors shadow-inner">
                    <i class="fas fa-chart-line text-xl"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-0.5">Penjualan Hari Ini</p>
                    <h3 class="text-xl font-black text-purple-400">Rp {{ number_format($totalSalesToday, 0, ',', '.') }}</h3>
                    <p class="text-[9px] text-slate-500 mt-1 font-bold">Semua transaksi sukses hari ini</p>
                </div>
            </div>
        </div>

        {{-- Selisih Kas --}}
        <div class="bg-slate-800 rounded-2xl p-5 border border-slate-700/80 shadow-sm relative overflow-hidden group hover:-translate-y-1 transition-all duration-300">
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
                    <p class="text-[9px] text-slate-500 mt-1 font-bold">Akumulasi shift selesai</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        {{-- KIRI: REALTIME & INSIGHT (40%) --}}
        <div class="lg:col-span-4 space-y-6">
            
            {{-- CURRENT SHIFT CARD (REALTIME) --}}
            @if($activeShift)
            @php
                $currentExpected = $activeShift->opening_cash + \App\Models\Transaction::where('shift_id', $activeShift->id)->where('payment_method', 'cash')->where('status', 'completed')->sum('total');
                $currentSales = \App\Models\Transaction::where('shift_id', $activeShift->id)->where('status', 'completed')->sum('total');
            @endphp
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

                <div class="space-y-4 relative z-10">
                    <div class="flex justify-between items-center pb-3 border-b border-slate-700/50">
                        <span class="text-xs font-bold text-slate-400">Modal Kas Awal</span>
                        <span class="text-sm font-bold text-slate-200">Rp {{ number_format($activeShift->opening_cash, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center pb-3 border-b border-slate-700/50">
                        <span class="text-xs font-bold text-slate-400">Total Penjualan</span>
                        <span class="text-sm font-black text-emerald-400">Rp {{ number_format($currentSales, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-bold text-slate-400">Estimasi Uang Laci</span>
                        <span class="text-lg font-black text-white">Rp {{ number_format($currentExpected, 0, ',', '.') }}</span>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-700/50 flex gap-2 relative z-10">
                    <a href="{{ route('pos.index') }}" class="flex-1 py-2.5 bg-slate-700 hover:bg-slate-600 text-white font-bold rounded-xl transition-colors shadow-sm text-xs flex items-center justify-center gap-2">
                        <i class="fas fa-cash-register"></i> Ke POS
                    </a>
                    <form action="{{ route('shifts.close', $activeShift) }}" method="POST" class="flex-1" onsubmit="return confirm('Tutup shift ini sekarang?')">
                        @csrf
                        <button type="submit" class="w-full py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl transition-colors shadow-lg shadow-red-500/20 text-xs flex items-center justify-center gap-2">
                            <i class="fas fa-lock"></i> Tutup Shift
                        </button>
                    </form>
                </div>
            </div>
            @else
            <div class="bg-slate-800/50 rounded-2xl p-8 border border-slate-700 border-dashed flex flex-col items-center justify-center text-center">
                <div class="w-16 h-16 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-500 text-2xl mb-4 shadow-inner">
                    <i class="fas fa-bed"></i>
                </div>
                <h3 class="font-black text-white mb-1">Tidak Ada Shift Aktif</h3>
                <p class="text-xs text-slate-400 mb-6">Belum ada kasir yang membuka shift saat ini.</p>
                <form action="{{ route('shifts.open') }}" method="POST" class="w-full max-w-[200px]">
                    @csrf
                    <input type="hidden" name="opening_cash" value="0">
                    <button type="submit" class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition-colors shadow-lg shadow-blue-500/20 text-xs flex items-center justify-center gap-2">
                        <i class="fas fa-key"></i> Buka Shift Baru
                    </button>
                </form>
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
                <button class="px-3 py-1.5 bg-slate-700 hover:bg-slate-600 text-white text-xs font-bold rounded-lg transition-colors border border-slate-600 flex items-center gap-2 shadow-sm">
                    <i class="fas fa-file-export text-emerald-400"></i> Export
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
                                <div class="text-xs font-bold text-slate-500 flex items-center gap-1.5">
                                    <i class="far fa-calendar-alt"></i> {{ $s->opened_at->format('d M Y') }} &nbsp;•&nbsp; 
                                    <i class="far fa-clock"></i> {{ $s->opened_at->format('H:i') }} - {{ $s->closed_at ? $s->closed_at->format('H:i') : 'Skrg' }}
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
                                <p class="text-sm font-black text-emerald-400 mt-0.5">Rp {{ number_format($s->total_sales, 0, ',', '.') }}</p>
                            </div>
                        </div>

                        {{-- Kanan: Hasil & Selisih --}}
                        <div class="flex gap-4 md:gap-6 items-center shrink-0">
                            @if($s->closed_at)
                                @php 
                                    $expected = $s->opening_cash + \App\Models\Transaction::where('shift_id', $s->id)->where('payment_method', 'cash')->where('status', 'completed')->sum('total'); 
                                    $selisih = $s->closing_cash - $expected;
                                @endphp
                                <div class="text-right">
                                    <p class="text-[9px] font-black text-slate-500 uppercase tracking-wider">Kas Laci</p>
                                    <p class="text-sm font-bold text-white mt-0.5">Rp {{ number_format($s->closing_cash, 0, ',', '.') }}</p>
                                </div>
                                <div class="text-right min-w-[80px]">
                                    <p class="text-[9px] font-black text-slate-500 uppercase tracking-wider">Selisih</p>
                                    <div class="px-2 py-0.5 mt-0.5 rounded-md inline-block {{ $selisih == 0 ? 'bg-slate-700/50 text-slate-400' : ($selisih > 0 ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20') }}">
                                        <p class="text-sm font-black">{{ $selisih == 0 ? 'Pas' : ($selisih > 0 ? '+'.number_format($selisih, 0, ',', '.') : '-'.number_format(abs($selisih), 0, ',', '.')) }}</p>
                                    </div>
                                </div>
                            @else
                                <div class="text-right w-full">
                                    <p class="text-[9px] font-black text-blue-400 uppercase tracking-wider animate-pulse">Menunggu shift ditutup</p>
                                </div>
                            @endif
                            <i class="fas fa-chevron-right text-slate-600 group-hover:text-white transition-colors ml-2 hidden md:block"></i>
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

    {{-- MODAL DETAIL SHIFT (SIMULATED) --}}
    <div x-show="isModalOpen" x-transition.opacity x-cloak class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div @click.away="closeModal()" x-show="isModalOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0" class="bg-[#1e293b] rounded-3xl w-full max-w-2xl shadow-2xl border border-slate-700 transform overflow-hidden flex flex-col max-h-[90vh]">
            
            <div class="p-6 border-b border-slate-700/80 flex justify-between items-center bg-slate-800/50 relative overflow-hidden shrink-0">
                <div class="absolute inset-0 opacity-20 pointer-events-none" style="background: radial-gradient(circle at top right, #3b82f6, transparent 70%);"></div>
                <div>
                    <h3 class="text-xl font-black text-white relative z-10 flex items-center gap-2"><i class="fas fa-file-invoice text-blue-400"></i> Detail Laporan Shift</h3>
                    <p class="text-xs font-bold text-slate-400 mt-1">Sistem sedang memuat detail transaksi...</p>
                </div>
                <button @click="closeModal()" class="w-8 h-8 bg-slate-700 hover:bg-slate-600 rounded-full text-slate-400 hover:text-white transition-colors flex items-center justify-center relative z-10"><i class="fas fa-times"></i></button>
            </div>

            <div class="p-8 overflow-y-auto flex flex-col items-center justify-center min-h-[300px]">
                <div class="w-16 h-16 rounded-full bg-blue-500/10 flex items-center justify-center mb-4 border border-blue-500/20">
                    <i class="fas fa-spinner fa-spin text-2xl text-blue-400"></i>
                </div>
                <p class="text-slate-300 font-bold mb-2">Memuat Histori Transaksi</p>
                <p class="text-xs text-slate-500 max-w-sm text-center">Fitur detail per-shift ini dapat dihubungkan via AJAX endpoint di masa mendatang.</p>
                <div class="mt-8 pt-6 border-t border-slate-700/50 w-full flex justify-center">
                    <a href="{{ route('reports.sales') }}" class="py-2.5 px-6 bg-slate-700 hover:bg-slate-600 text-white font-bold rounded-xl transition-colors shadow-sm text-sm flex items-center justify-center gap-2">
                        Lihat Rekap Penjualan
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function shiftDashboardApp() {
    return {
        isModalOpen: false,
        activeShiftId: null,

        openShiftModal(id) {
            this.activeShiftId = id;
            this.isModalOpen = true;
            // Di sini nanti bisa panggil fetch() / axios untuk ambil list transaksi shift tersebut
        },
        
        closeModal() {
            this.isModalOpen = false;
            setTimeout(() => this.activeShiftId = null, 300);
        }
    }
}
</script>
@endsection
