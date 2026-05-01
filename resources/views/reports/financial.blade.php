@extends('layouts.app')

@section('title', 'Laporan Keuangan')
@section('page-title', 'Laporan Laba Rugi')
@section('page-subtitle', 'Ringkasan performa keuangan bulanan')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">
    <div class="card p-5 border-b border-slate-700/50 flex justify-center bg-slate-800/80 backdrop-blur-md relative z-20">
        <form method="GET" class="flex gap-3 relative">
            <div class="relative">
                <select name="month" class="w-40 bg-slate-900/50 border border-slate-700 rounded-full pl-5 pr-10 py-2.5 text-white text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 appearance-none shadow-inner cursor-pointer font-medium" onchange="this.form.submit()">
                    @for($m=1; $m<=12; $m++)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                    @endfor
                </select>
                <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-500 text-xs pointer-events-none"></i>
            </div>
            <div class="relative">
                <select name="year" class="w-32 bg-slate-900/50 border border-slate-700 rounded-full pl-5 pr-10 py-2.5 text-white text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 appearance-none shadow-inner cursor-pointer font-medium" onchange="this.form.submit()">
                    @for($y=date('Y'); $y>=2020; $y--)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
                <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-500 text-xs pointer-events-none"></i>
            </div>
        </form>
    </div>

    <div class="card overflow-hidden shadow-2xl relative z-10 border border-slate-700/80">
        {{-- Header / Highlight --}}
        <div class="relative p-10 text-center border-b border-slate-700/80 overflow-hidden {{ $profit >= 0 ? 'bg-gradient-to-b from-emerald-900/40 to-slate-800' : 'bg-gradient-to-b from-red-900/40 to-slate-800' }}">
            <div class="absolute inset-0 w-full h-full">
                <div class="absolute left-1/2 top-0 -translate-x-1/2 w-96 h-64 blur-[100px] rounded-full pointer-events-none {{ $profit >= 0 ? 'bg-emerald-500/20' : 'bg-red-500/20' }}"></div>
            </div>
            
            <div class="relative z-10">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-3xl mb-4 shadow-lg border {{ $profit >= 0 ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30 shadow-emerald-500/20' : 'bg-red-500/20 text-red-400 border-red-500/30 shadow-red-500/20' }}">
                    <i class="fas {{ $profit >= 0 ? 'fa-chart-line' : 'fa-chart-line-down' }} text-3xl"></i>
                </div>
                <p class="text-slate-400 font-bold mb-2 uppercase tracking-[0.2em] text-sm">Laba Bersih Bulan Ini</p>
                <h2 class="text-5xl md:text-6xl font-black tracking-tight {{ $profit >= 0 ? 'text-emerald-400' : 'text-red-400' }}" style="text-shadow: 0 4px 20px {{ $profit >= 0 ? 'rgba(52,211,153,0.3)' : 'rgba(248,113,113,0.3)' }};">
                    {{ $profit >= 0 ? '+' : '-' }}Rp {{ number_format(abs($profit), 0, ',', '.') }}
                </h2>
                <div class="mt-4 inline-flex items-center px-4 py-1.5 rounded-full text-sm font-bold tracking-wider {{ $profit >= 0 ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20' }}">
                    {{ $profit >= 0 ? 'PROFIT' : 'LOSS' }}
                </div>
            </div>
        </div>

        <div class="p-6 md:p-10 bg-slate-800/50">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                
                {{-- Pemasukan Utama --}}
                <div class="bg-slate-900/80 p-6 rounded-3xl border border-slate-700/60 shadow-lg relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/5 blur-3xl group-hover:bg-emerald-500/10 transition-all"></div>
                    
                    <div class="flex items-center gap-3 border-b border-slate-700/80 pb-4 mb-5 relative z-10">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/20 flex items-center justify-center border border-emerald-500/20 flex-shrink-0">
                            <i class="fas fa-arrow-down text-emerald-400"></i>
                        </div>
                        <div>
                            <h4 class="text-white font-bold text-lg">Pemasukan</h4>
                            <p class="text-xs text-slate-400">Detail pendapatan & laba kotor</p>
                        </div>
                    </div>
                    
                    <div class="space-y-4 text-sm md:text-base relative z-10">
                        <div class="flex justify-between items-center text-slate-300">
                            <span class="flex items-center gap-2"><i class="fas fa-cash-register text-slate-500 w-4"></i> Penjualan (POS)</span>
                            <span class="font-bold">Rp {{ number_format($salesTotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center text-slate-400">
                            <span class="flex items-center gap-2"><i class="fas fa-box-open text-slate-500 w-4"></i> HPP (Modal Barang)</span>
                            <span class="font-bold">- Rp {{ number_format($cogs, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center text-white font-bold pt-4 border-t border-slate-700/80 border-dashed">
                            <span class="flex items-center gap-2"><i class="fas fa-percentage text-emerald-400 w-4"></i> Laba Kotor Penjualan</span>
                            <span class="text-emerald-400">Rp {{ number_format($grossProfit, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center text-slate-300 pt-2">
                            <span class="flex items-center gap-2"><i class="fas fa-plus-circle text-slate-500 w-4"></i> Pemasukan Lainnya</span>
                            <span class="font-bold text-emerald-400">+ Rp {{ number_format($income - $salesTotal, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Pengeluaran --}}
                <div class="bg-slate-900/80 p-6 rounded-3xl border border-slate-700/60 shadow-lg relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-red-500/5 blur-3xl group-hover:bg-red-500/10 transition-all"></div>
                    
                    <div class="flex items-center gap-3 border-b border-slate-700/80 pb-4 mb-5 relative z-10">
                        <div class="w-10 h-10 rounded-xl bg-red-500/20 flex items-center justify-center border border-red-500/20 flex-shrink-0">
                            <i class="fas fa-arrow-up text-red-400"></i>
                        </div>
                        <div>
                            <h4 class="text-white font-bold text-lg">Pengeluaran</h4>
                            <p class="text-xs text-slate-400">Beban operasional bisnis</p>
                        </div>
                    </div>
                    
                    <div class="space-y-4 text-sm md:text-base relative z-10">
                        <div class="flex justify-between items-center text-slate-300">
                            <span class="flex items-center gap-2"><i class="fas fa-file-invoice-dollar text-slate-500 w-4"></i> Beban Operasional</span>
                            <span class="font-bold text-red-400">- Rp {{ number_format($expense, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Laba Bersih Footer --}}
            <div class="mt-8 rounded-3xl p-6 sm:p-8 flex flex-col sm:flex-row justify-between items-center border shadow-xl relative overflow-hidden {{ $profit >= 0 ? 'bg-gradient-to-r from-emerald-900/40 to-slate-900 border-emerald-500/30' : 'bg-gradient-to-r from-red-900/40 to-slate-900 border-red-500/30' }}">
                <div class="absolute -right-10 -top-10 w-40 h-40 blur-3xl rounded-full pointer-events-none {{ $profit >= 0 ? 'bg-emerald-500/10' : 'bg-red-500/10' }}"></div>
                <div class="flex items-center gap-4 relative z-10 mb-4 sm:mb-0">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-inner {{ $profit >= 0 ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-red-500/20 text-red-400 border border-red-500/30' }}">
                        <i class="fas fa-wallet text-xl"></i>
                    </div>
                    <div>
                        <span class="text-sm font-bold text-slate-400 uppercase tracking-widest block mb-1">Total Laba Bersih</span>
                        <span class="text-xs text-slate-500">Pendapatan - Pengeluaran</span>
                    </div>
                </div>
                <div class="relative z-10 text-center sm:text-right">
                    <span class="text-3xl sm:text-4xl font-black tracking-tight {{ $profit >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                        {{ $profit >= 0 ? '+' : '-' }}Rp {{ number_format(abs($profit), 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
