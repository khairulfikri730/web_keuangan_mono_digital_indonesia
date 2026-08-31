@extends('layouts.app')

@section('title', 'Dashboard Overview')

@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-6 pb-20">

    {{-- Header Content & Filter Bar --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-2">
        <div>
            <h2 class="text-2xl font-black text-white tracking-wide uppercase">Dashboard Overview</h2>
            <p class="text-sm text-slate-400 mt-1">Sistem Pemantauan Operasional Enterprise</p>
        </div>
        
        <form id="filter-form" method="GET" class="flex items-center gap-2 bg-slate-900/60 p-1.5 rounded-xl border border-white/5 backdrop-blur-md overflow-x-auto">
            @if(auth()->user()->isOwner())
                <button type="submit" name="filter" value="today" class="px-5 py-2 rounded-lg text-xs font-bold uppercase tracking-wider transition-all {{ $filter == 'today' ? 'bg-cyan-500/20 text-cyan-400 border border-cyan-500/30 shadow-[0_0_15px_rgba(6,182,212,0.2)]' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Hari</button>
                <button type="submit" name="filter" value="week" class="px-5 py-2 rounded-lg text-xs font-bold uppercase tracking-wider transition-all {{ $filter == 'week' ? 'bg-cyan-500/20 text-cyan-400 border border-cyan-500/30 shadow-[0_0_15px_rgba(6,182,212,0.2)]' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Minggu</button>
                <button type="submit" name="filter" value="month" class="px-5 py-2 rounded-lg text-xs font-bold uppercase tracking-wider transition-all {{ $filter == 'month' ? 'bg-cyan-500/20 text-cyan-400 border border-cyan-500/30 shadow-[0_0_15px_rgba(6,182,212,0.2)]' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Bulan</button>
                
                <div class="w-px h-6 bg-slate-700/50 mx-1"></div>
                
                <a href="{{ route('schedules.attendances.index') }}" class="px-4 py-2 bg-emerald-600/20 text-emerald-400 border border-emerald-500/50 rounded-lg text-xs font-bold uppercase tracking-wider hover:bg-emerald-600 hover:text-white transition-colors flex items-center">
                    <i class="fas fa-camera mr-2"></i> Absensi
                </a>
            @else
                <button type="button" class="px-5 py-2 rounded-lg text-xs font-bold uppercase tracking-wider transition-all bg-cyan-500/20 text-cyan-400 border border-cyan-500/30 shadow-[0_0_15px_rgba(6,182,212,0.2)]">Hari Ini</button>
                
                @if(auth()->user()->role === 'kasir' || auth()->user()->role === 'crew')
                <a href="{{ route('schedules.attendances.index') }}" class="px-4 py-2 bg-blue-600/20 text-blue-400 border border-blue-500/50 rounded-lg text-xs font-bold uppercase tracking-wider hover:bg-blue-600 hover:text-white transition-colors flex items-center">
                    <i class="fas fa-camera mr-2"></i> Absensi
                </a>
                @endif
            @endif
        </form>
    </div>

    @if(auth()->user()->isOwner())
        {{-- ========================================================= --}}
        {{-- SECTION 1: TOP KPI CARDS (PHASE 2) --}}
        {{-- ========================================================= --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @php
                $kpis = [
                    ['title' => 'Total Omzet', 'data' => $kpiData['omzet'], 'icon' => 'fa-wallet', 'color' => 'blue'],
                    ['title' => 'Total Transaksi', 'data' => $kpiData['trx'], 'icon' => 'fa-receipt', 'color' => 'purple'],
                    ['title' => 'Total Pengeluaran', 'data' => $kpiData['expense'], 'icon' => 'fa-money-bill-wave', 'color' => 'red'],
                    ['title' => 'Profit Bersih', 'data' => $kpiData['profit'], 'icon' => 'fa-chart-pie', 'color' => 'emerald'],
                ];
            @endphp
            @foreach($kpis as $kpi)
            <div class="bg-slate-900/60 border border-white/5 rounded-2xl p-5 backdrop-blur-xl relative overflow-hidden group hover:border-{{ $kpi['color'] }}-500/40 transition-all duration-300 shadow-lg hover:shadow-[0_0_30px_rgba(var(--color-{{ $kpi['color'] }}-500),0.1)] flex flex-col justify-between">
                <div class="absolute -right-10 -top-10 w-32 h-32 bg-{{ $kpi['color'] }}-500/10 rounded-full blur-[40px] pointer-events-none group-hover:bg-{{ $kpi['color'] }}-500/20 transition-colors"></div>
                <div class="flex justify-between items-start relative z-10 mb-4">
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ $kpi['title'] }}</p>
                        <h3 class="text-2xl font-black text-white mt-1">{{ $kpi['title'] === 'Total Transaksi' ? number_format($kpi['data']['total']) : 'Rp ' . number_format($kpi['data']['total'], 0, ',', '.') }}</h3>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-{{ $kpi['color'] }}-500/20 flex items-center justify-center text-{{ $kpi['color'] }}-400 group-hover:scale-110 transition-transform">
                        <i class="fas {{ $kpi['icon'] }} text-lg"></i>
                    </div>
                </div>
                <div class="flex items-center justify-between relative z-10">
                    <div class="flex items-center gap-1.5 text-xs font-bold {{ $kpi['data']['growth'] >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                        <i class="fas {{ $kpi['data']['growth'] >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                        <span>{{ abs(round($kpi['data']['growth'], 1)) }}%</span>
                        <span class="text-[9px] text-slate-500 font-normal uppercase tracking-widest ml-1">vs lalu</span>
                    </div>
                    <div class="w-16 h-8">
                        <canvas class="sparkline" data-color="{{ $kpi['data']['growth'] >= 0 ? '#10b981' : '#ef4444' }}" data-points="{{ json_encode($kpi['data']['sparkline']) }}"></canvas>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- ========================================================= --}}
        {{-- SECTION 2: SYSTEM HEALTH & QUICK ACTIONS --}}
        {{-- ========================================================= --}}
        <div class="flex flex-col lg:flex-row gap-4 items-stretch">
            {{-- Quick Actions --}}
            <div class="flex-1 flex items-center gap-2 overflow-x-auto pb-2 lg:pb-0 scrollbar-hide">
                <a href="{{ route('pos.index') }}" class="flex items-center gap-2 px-5 py-3 bg-blue-500/10 hover:bg-blue-500/20 border border-blue-500/30 rounded-xl text-blue-400 hover:text-blue-300 font-bold text-xs uppercase tracking-widest transition-all group whitespace-nowrap shadow-[0_0_15px_rgba(59,130,246,0.1)]">
                    <i class="fas fa-cash-register group-hover:scale-110 transition-transform"></i> Buka POS
                </a>
                <a href="{{ route('cashflow.index') }}" class="flex items-center gap-2 px-5 py-3 bg-slate-800/50 hover:bg-slate-800 border border-white/5 rounded-xl text-slate-300 hover:text-white font-bold text-xs uppercase tracking-widest transition-all group whitespace-nowrap">
                    <i class="fas fa-money-bill-wave group-hover:text-red-400 transition-colors"></i> Pengeluaran
                </a>
                <a href="{{ route('shifts.index') }}" class="flex items-center gap-2 px-5 py-3 bg-slate-800/50 hover:bg-slate-800 border border-white/5 rounded-xl text-slate-300 hover:text-white font-bold text-xs uppercase tracking-widest transition-all group whitespace-nowrap">
                    <i class="fas fa-clock group-hover:text-emerald-400 transition-colors"></i> Shift
                </a>
            </div>
        </div>

        {{-- ========================================================= --}}
        {{-- SECTION 3: PRIORITY ALERTS & FORECAST AI --}}
        {{-- ========================================================= --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Priority Alerts (Takes 2 Cols) --}}
            <div class="lg:col-span-2 bg-slate-900/60 border border-white/5 rounded-2xl p-6 backdrop-blur-xl relative overflow-hidden group">
                <div class="flex justify-between items-start mb-4 relative z-10">
                    <div>
                        <h3 class="font-black text-white uppercase tracking-wider text-sm flex items-center gap-2">
                            <i class="fas fa-exclamation-circle text-orange-400"></i> Priority Alerts
                        </h3>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 relative z-10">
                    @foreach($alerts as $alert)
                        @php
                            $bg = match($alert['type']) { 'critical' => 'from-red-500/20 to-red-900/10 border-red-500/30 shadow-[0_0_20px_rgba(239,68,68,0.15)]', 'warning' => 'from-orange-500/20 to-orange-900/10 border-orange-500/30', default => 'from-blue-500/10 to-slate-900/50 border-blue-500/20' };
                            $text = match($alert['type']) { 'critical' => 'text-red-400', 'warning' => 'text-orange-400', default => 'text-blue-400' };
                        @endphp
                        <div class="bg-gradient-to-r {{ $bg }} border rounded-xl p-4 flex items-start gap-4">
                            <div class="mt-1">
                                <i class="fas {{ $alert['icon'] }} {{ $text }} text-xl {{ $alert['type'] == 'critical' ? 'animate-pulse' : '' }}"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-white mb-1">{{ $alert['title'] }}</h4>
                                <p class="text-xs text-slate-300 leading-relaxed">{{ $alert['message'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Forecast AI --}}
            <div class="bg-gradient-to-br from-indigo-900/40 via-purple-900/20 to-slate-900/60 border border-indigo-500/30 rounded-2xl p-6 backdrop-blur-xl shadow-[0_0_30px_rgba(99,102,241,0.1)] relative overflow-hidden group">
                <div class="absolute -right-10 -bottom-10 w-32 h-32 bg-indigo-500/20 rounded-full blur-[40px] pointer-events-none group-hover:bg-indigo-500/30 transition-all duration-1000"></div>
                
                <div class="flex items-center gap-3 mb-6 relative z-10">
                    <div class="w-8 h-8 rounded-lg bg-indigo-500/20 flex items-center justify-center text-indigo-400">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-white uppercase tracking-wider text-sm">AI Insight & Forecast</h3>
                        <p class="text-[9px] font-bold text-indigo-300 uppercase tracking-widest mt-0.5">Business Intelligence</p>
                    </div>
                </div>
                
                <div class="space-y-4 relative z-10 mb-5">
                    @foreach($aiForecast['insights'] as $insight)
                    <div class="flex items-start gap-2">
                        <i class="fas fa-magic text-indigo-400 mt-1 text-xs"></i>
                        <p class="text-xs text-slate-300 leading-relaxed">{{ $insight }}</p>
                    </div>
                    @endforeach
                </div>

                <div class="grid grid-cols-2 gap-3 relative z-10 border-t border-indigo-500/20 pt-4">
                    <div class="bg-slate-950/50 border border-white/5 p-3 rounded-xl flex justify-between items-center group-hover:border-indigo-500/20 transition-colors">
                        <div>
                            <p class="text-[9px] text-slate-400 uppercase tracking-widest font-bold mb-1">Est. Besok</p>
                            <p class="text-xs font-black text-indigo-400">Rp {{ number_format($aiForecast['tomorrow'], 0, ',', '.') }}</p>
                        </div>
                        <i class="fas fa-bolt text-indigo-500/50 text-lg"></i>
                    </div>
                    <div class="bg-slate-950/50 border border-white/5 p-3 rounded-xl flex justify-between items-center group-hover:border-indigo-500/20 transition-colors">
                        <div>
                            <p class="text-[9px] text-slate-400 uppercase tracking-widest font-bold mb-1">Est. Mgg Depan</p>
                            <p class="text-xs font-black text-purple-400">Rp {{ number_format($aiForecast['next_week'], 0, ',', '.') }}</p>
                        </div>
                        <i class="fas fa-chart-line text-purple-500/50 text-lg"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================================= --}}
        {{-- SECTION 4: MAIN CHARTS & HEATMAP (OPTIMIZED) --}}
        {{-- ========================================================= --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main Area Chart (Lower Height) --}}
            <div class="lg:col-span-2 bg-slate-900/60 border border-white/5 rounded-2xl p-6 backdrop-blur-xl relative overflow-hidden group">
                <div class="flex justify-between items-start mb-4 relative z-10">
                    <div>
                        <h3 class="font-black text-white uppercase tracking-wider text-sm">Trend Penjualan {{ $filter == 'month' ? 'Bulan Ini' : ($filter == 'week' ? 'Minggu Ini' : '7 Hari Terakhir') }}</h3>
                        <p class="text-xs text-slate-500 mt-1">Pergerakan omzet bisnis secara kumulatif</p>
                    </div>
                    <div class="px-3 py-1 bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 rounded-lg text-[10px] font-bold uppercase tracking-widest">Area Chart</div>
                </div>
                <div class="relative z-10 h-[220px] w-full">
                    <canvas id="salesAreaChart"></canvas>
                </div>
            </div>

            {{-- Peak Hours Heatmap (Upgraded Color) --}}
            <div class="bg-slate-900/60 border border-white/5 rounded-2xl p-6 backdrop-blur-xl relative overflow-hidden group flex flex-col">
                <div class="flex justify-between items-start mb-4 relative z-10">
                    <div>
                        <h3 class="font-black text-white uppercase tracking-wider text-sm flex items-center gap-2">
                            <i class="fas fa-fire text-orange-400"></i> Heatmap Transaksi
                        </h3>
                    </div>
                </div>
                
                @if($busiestHour && $busiestHour->total_trx > 0)
                <div class="mb-4 relative z-10 grid grid-cols-2 gap-4">
                    <div class="bg-slate-950/50 rounded-xl p-3 border border-white/5 text-center shadow-inner">
                        <p class="text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-1">Peak Time</p>
                        <p class="text-lg font-black text-orange-400 drop-shadow-[0_0_5px_rgba(249,115,22,0.5)]">{{ str_pad($busiestHour->hour, 2, '0', STR_PAD_LEFT) }}:00</p>
                    </div>
                    <div class="bg-slate-950/50 rounded-xl p-3 border border-white/5 text-center shadow-inner">
                        <p class="text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-1">Peak Omzet</p>
                        <p class="text-xs font-bold text-white mt-2">Rp {{ number_format($busiestHour->total_sales, 0, ',', '.') }}</p>
                    </div>
                </div>
                
                {{-- HTML Heatmap V2 --}}
                <div class="relative z-10 mt-auto">
                    <div class="flex justify-between text-[8px] text-slate-500 font-bold mb-2 px-1 border-b border-white/5 pb-1">
                        <span>06:00</span>
                        <span>12:00</span>
                        <span>18:00</span>
                        <span>23:00</span>
                    </div>
                    <div class="flex gap-1 h-16 items-end">
                        @php $maxTrx = max($peakChartTrx) ?: 1; @endphp
                        @for($h=6; $h<=23; $h++)
                            @php
                                $val = $peakChartTrx[$h];
                                $intensity = $val / $maxTrx;
                                $height = max(15, $intensity * 100);
                                if($intensity == 0) { $bg = 'bg-slate-800/30'; $border = 'border-white/5'; }
                                elseif($intensity < 0.3) { $bg = 'bg-gradient-to-t from-orange-900/50 to-orange-500/30'; $border = 'border-orange-500/20'; }
                                elseif($intensity < 0.7) { $bg = 'bg-gradient-to-t from-orange-600 to-orange-400'; $border = 'border-orange-400 shadow-[0_0_8px_rgba(249,115,22,0.5)]'; }
                                else { $bg = 'bg-gradient-to-t from-orange-500 to-yellow-400'; $border = 'border-yellow-400 shadow-[0_0_15px_rgba(250,204,21,0.8)] animate-pulse'; }
                            @endphp
                            <div class="flex-1 flex flex-col justify-end group/heat relative cursor-crosshair">
                                <div class="w-full rounded-sm border {{ $border }} {{ $bg }} transition-all duration-300 hover:brightness-150" style="height: {{ $height }}%"></div>
                                
                                {{-- Tooltip --}}
                                <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 opacity-0 group-hover/heat:opacity-100 transition-opacity pointer-events-none z-50 w-max bg-slate-800 text-white text-[10px] py-1 px-2 rounded shadow-lg border border-white/10 text-center">
                                    <p class="font-bold text-orange-400 mb-0.5">{{ str_pad($h, 2, '0', STR_PAD_LEFT) }}:00</p>
                                    <p>{{ $val }} Trx</p>
                                    <p class="text-slate-400">Rp {{ number_format($peakChartData[$h]/1000, 0) }}k</p>
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>
                @else
                <div class="py-12 text-center relative z-10 border border-dashed border-white/5 rounded-xl my-auto">
                    <i class="fas fa-bed text-2xl text-slate-700 mb-3"></i>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-widest">Belum ada transaksi</p>
                </div>
                @endif
            </div>
        </div>

        {{-- ========================================================= --}}
        {{-- SECTION 5: LIVE ACTIVITY FEED & FINANCIAL INSIGHT --}}
        {{-- ========================================================= --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- Realtime Activity Feed (2 Cols) --}}
            <div class="lg:col-span-2 bg-slate-900/60 border border-white/5 rounded-2xl p-6 backdrop-blur-xl flex flex-col h-full max-h-[400px]">
                <div class="flex justify-between items-center mb-6 flex-shrink-0 border-b border-white/5 pb-4">
                    <div>
                        <h3 class="font-black text-white uppercase tracking-wider text-sm">Realtime Activity</h3>
                        <p class="text-[10px] text-slate-500 uppercase tracking-widest mt-1 font-bold">Live System Logs</p>
                    </div>
                    <span class="px-2 py-1 bg-emerald-500/20 text-emerald-400 text-[10px] font-bold uppercase tracking-widest rounded shadow-[0_0_10px_rgba(16,185,129,0.2)]">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block mr-1 animate-pulse"></span> Auto Sync
                    </span>
                </div>
                <div class="flex-1 overflow-y-auto pr-2 space-y-4 custom-scrollbar">
                    @forelse($liveActivity as $act)
                        <div class="flex items-start gap-4 group/feed hover:bg-slate-800/30 p-2 rounded-xl transition-colors cursor-pointer animate-fade-in-up">
                            <div class="w-10 h-10 rounded-full {{ $act->bg }} flex items-center justify-center {{ $act->color }} group-hover/feed:scale-110 transition-transform shadow-lg">
                                <i class="fas {{ $act->icon }}"></i>
                            </div>
                            <div class="flex-1 pt-1">
                                <p class="text-sm font-bold text-white mb-0.5">{{ $act->title }}</p>
                                <p class="text-xs text-slate-400">{{ $act->desc }}</p>
                                <p class="text-[9px] text-slate-500 font-bold uppercase tracking-widest mt-2 flex items-center gap-2">
                                    <i class="far fa-clock"></i> {{ $act->time->diffForHumans() }} 
                                    <span class="w-1 h-1 rounded-full bg-slate-600"></span> 
                                    <i class="fas fa-user-circle"></i> {{ $act->user }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-10">
                            <p class="text-sm text-slate-500">Belum ada aktivitas hari ini.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Mini Financial Insight --}}
            <div class="bg-slate-900/60 border border-white/5 rounded-2xl p-6 backdrop-blur-xl">
                <h3 class="font-black text-white uppercase tracking-wider text-sm mb-6 border-b border-white/5 pb-4">Financial Insight</h3>
                
                <div class="space-y-6">
                    {{-- Gross Margin --}}
                    <div>
                        <div class="flex justify-between items-end mb-2">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Gross Margin (Est)</p>
                            <p class="text-sm font-black text-white">{{ $financialInsight['gross_margin'] }}%</p>
                        </div>
                        <div class="h-1.5 w-full bg-slate-800 rounded-full overflow-hidden">
                            <div class="h-full bg-cyan-400 rounded-full" style="width: {{ $financialInsight['gross_margin'] }}%"></div>
                        </div>
                    </div>
                    
                    {{-- ROI --}}
                    <div>
                        <div class="flex justify-between items-end mb-2">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">ROI (Return on Investment)</p>
                            <p class="text-sm font-black {{ $financialInsight['roi'] > 0 ? 'text-emerald-400' : 'text-slate-400' }}">{{ number_format($financialInsight['roi'], 1) }}%</p>
                        </div>
                        <div class="h-1.5 w-full bg-slate-800 rounded-full overflow-hidden">
                            <div class="h-full bg-emerald-400 rounded-full" style="width: {{ min(100, max(0, $financialInsight['roi'])) }}%"></div>
                        </div>
                    </div>

                    {{-- Break Even --}}
                    <div class="bg-slate-950/50 p-4 rounded-xl border border-white/5 mt-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-500/10 flex items-center justify-center text-blue-400">
                                <i class="fas fa-bullseye"></i>
                            </div>
                            <div>
                                <p class="text-[9px] font-bold text-slate-500 uppercase tracking-widest">Estimasi Break Even</p>
                                <p class="text-sm font-black text-white mt-0.5">{{ $financialInsight['break_even'] < 99 ? number_format($financialInsight['break_even'], 1) . ' Bulan' : 'Belum Tersedia' }}</p>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Cashflow Health --}}
                    <div class="bg-slate-950/50 p-4 rounded-xl border border-white/5">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg {{ $financialInsight['health'] > 50 ? 'bg-emerald-500/10 text-emerald-400' : 'bg-orange-500/10 text-orange-400' }} flex items-center justify-center">
                                <i class="fas fa-heartbeat"></i>
                            </div>
                            <div>
                                <p class="text-[9px] font-bold text-slate-500 uppercase tracking-widest">Cashflow Health</p>
                                <p class="text-sm font-black {{ $financialInsight['health'] > 50 ? 'text-emerald-400' : 'text-orange-400' }} mt-0.5">{{ $financialInsight['health'] > 50 ? 'Sangat Sehat' : 'Perlu Perhatian' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================================= --}}
        {{-- SECTION 6: TARGET, CREW LIVE, & BRANCH PERF --}}
        {{-- ========================================================= --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- Target & Progress Bisnis (Takes 2 columns) --}}
            <div class="lg:col-span-2 bg-slate-900/60 border border-white/5 rounded-2xl p-6 backdrop-blur-xl flex flex-col justify-between group relative overflow-hidden">
                <div class="absolute -right-20 -top-20 w-64 h-64 bg-cyan-500/5 rounded-full blur-[60px] pointer-events-none group-hover:bg-cyan-500/10 transition-all duration-1000"></div>
                
                <div class="flex justify-between items-start mb-6 relative z-10">
                    <div>
                        <h3 class="font-black text-white uppercase tracking-wider text-sm flex items-center gap-2">
                            <i class="fas fa-bullseye text-cyan-400"></i> Target & Pencapaian
                        </h3>
                    </div>
                </div>

                <div class="space-y-6 relative z-10">
                    {{-- Daily --}}
                    <div>
                        <div class="flex justify-between text-xs mb-2">
                            <span class="font-bold text-slate-400 uppercase tracking-widest">Harian</span>
                            <div class="flex gap-2 items-center">
                                <span class="font-bold text-white">Rp {{ number_format($targetData['daily']['achieved'], 0, ',', '.') }}</span>
                                <span class="text-slate-600">/</span>
                                <span class="text-slate-500">Rp {{ number_format($targetData['daily']['target'], 0, ',', '.') }}</span>
                            </div>
                        </div>
                        <div class="h-2 w-full bg-slate-950 rounded-full overflow-hidden border border-white/5 relative group-hover:border-white/10 transition-colors">
                            <div class="h-full bg-gradient-to-r from-blue-600 to-cyan-400 rounded-full shadow-[0_0_10px_rgba(6,182,212,0.5)] relative" style="width: {{ $targetData['daily']['percentage'] }}%">
                                @if($targetData['daily']['percentage'] >= 100) <div class="absolute inset-0 bg-white/20 animate-pulse"></div> @endif
                            </div>
                        </div>
                    </div>
                    
                    {{-- Weekly --}}
                    <div>
                        <div class="flex justify-between text-xs mb-2">
                            <span class="font-bold text-slate-400 uppercase tracking-widest">Mingguan</span>
                            <div class="flex gap-2 items-center">
                                <span class="font-bold text-white">Rp {{ number_format($targetData['weekly']['achieved'], 0, ',', '.') }}</span>
                                <span class="text-slate-600">/</span>
                                <span class="text-slate-500">Rp {{ number_format($targetData['weekly']['target'], 0, ',', '.') }}</span>
                            </div>
                        </div>
                        <div class="h-2 w-full bg-slate-950 rounded-full overflow-hidden border border-white/5 relative group-hover:border-white/10 transition-colors">
                            <div class="h-full bg-gradient-to-r from-indigo-600 to-blue-400 rounded-full shadow-[0_0_10px_rgba(59,130,246,0.5)] relative" style="width: {{ $targetData['weekly']['percentage'] }}%">
                                @if($targetData['weekly']['percentage'] >= 100) <div class="absolute inset-0 bg-white/20 animate-pulse"></div> @endif
                            </div>
                        </div>
                    </div>

                    {{-- Monthly --}}
                    <div>
                        <div class="flex justify-between text-xs mb-2">
                            <span class="font-bold text-slate-400 uppercase tracking-widest">Bulanan</span>
                            <div class="flex gap-2 items-center">
                                <span class="font-bold text-white">Rp {{ number_format($targetData['monthly']['achieved'], 0, ',', '.') }}</span>
                                <span class="text-slate-600">/</span>
                                <span class="text-slate-500">Rp {{ number_format($targetData['monthly']['target'], 0, ',', '.') }}</span>
                            </div>
                        </div>
                        <div class="h-2 w-full bg-slate-950 rounded-full overflow-hidden border border-white/5 relative group-hover:border-white/10 transition-colors">
                            <div class="h-full bg-gradient-to-r from-purple-600 to-indigo-400 rounded-full shadow-[0_0_10px_rgba(99,102,241,0.5)] relative" style="width: {{ $targetData['monthly']['percentage'] }}%">
                                @if($targetData['monthly']['percentage'] >= 100) <div class="absolute inset-0 bg-white/20 animate-pulse"></div> @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Crew Live Feed (Upgraded) --}}
            <div class="bg-slate-900/60 border border-white/5 rounded-2xl p-6 backdrop-blur-xl flex flex-col h-full max-h-[400px]">
                <div class="flex justify-between items-center mb-6 flex-shrink-0">
                    <div>
                        <h3 class="font-black text-white uppercase tracking-wider text-sm">Crew & Shift Live</h3>
                        <p class="text-[10px] text-slate-500 uppercase tracking-widest mt-1 font-bold">Realtime POS Monitor</p>
                    </div>
                    <span class="px-2 py-1 bg-blue-500/20 text-blue-400 text-[10px] font-bold uppercase tracking-widest rounded shadow-[0_0_10px_rgba(59,130,246,0.2)]">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-400 inline-block mr-1 animate-pulse"></span> Live
                    </span>
                </div>
                
                <div class="flex-1 overflow-y-auto pr-2 space-y-3 custom-scrollbar">
                    @forelse($activeShiftsList as $liveShift)
                        <div class="bg-slate-950/50 rounded-xl p-4 border border-white/5 flex flex-col gap-3 hover:border-blue-500/30 transition-colors">
                            <div class="flex items-center gap-3 border-b border-white/5 pb-2">
                                <div class="relative">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-cyan-400 p-[2px] shadow-[0_0_10px_rgba(6,182,212,0.3)]">
                                        <div class="w-full h-full bg-slate-900 rounded-full flex items-center justify-center text-white font-black text-xs uppercase">
                                            {{ substr($liveShift->opener->name, 0, 2) }}
                                        </div>
                                    </div>
                                    <div class="absolute -bottom-1 -right-1 w-3.5 h-3.5 bg-emerald-500 border-2 border-slate-950 rounded-full shadow-[0_0_8px_rgba(16,185,129,0.8)] animate-pulse"></div>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-bold text-white mb-0.5">{{ $liveShift->opener->name }}</p>
                                    <p class="text-[9px] text-slate-400 uppercase tracking-widest font-bold"><i class="fas fa-map-marker-alt text-blue-400 mr-1"></i> {{ $liveShift->worksheet->name ?? 'Cabang Utama' }}</p>
                                </div>
                                <div class="text-right">
                                    @php 
                                        $minutes = $liveShift->opened_at->diffInMinutes(now());
                                        $h = floor($minutes / 60);
                                        $m = $minutes % 60;
                                        $durationText = $h > 0 ? "{$h}j {$m}m" : "{$m} menit";
                                    @endphp
                                    <p class="text-[10px] font-black text-emerald-400"><i class="far fa-clock"></i> {{ $durationText }}</p>
                                </div>
                            </div>
                            <div class="flex justify-between items-end">
                                <div>
                                    <p class="text-[9px] text-slate-500 uppercase tracking-widest font-bold">Omzet Saat Ini</p>
                                    <p class="text-sm font-black text-cyan-400 mt-0.5">Rp {{ number_format($liveShift->current_sales, 0, ',', '.') }}</p>
                                </div>
                                @if($liveShift->last_trx)
                                <div class="text-right">
                                    <p class="text-[9px] text-slate-500 uppercase tracking-widest font-bold">Trx Terakhir</p>
                                    <p class="text-xs font-bold text-white mt-0.5">{{ $liveShift->last_trx->created_at->diffForHumans() }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="py-12 text-center h-full flex flex-col items-center justify-center border border-dashed border-white/5 rounded-xl">
                            <div class="w-16 h-16 bg-emerald-500/5 rounded-full flex items-center justify-center text-emerald-500 mx-auto mb-4 border border-emerald-500/10">
                                <i class="fas fa-power-off text-2xl"></i>
                            </div>
                            <p class="text-sm font-bold text-white mb-1">POS Sedang Offline</p>
                            <p class="text-xs text-slate-500">Tidak ada shift yang terbuka saat ini</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ========================================================= --}}
        {{-- ESTIMASI GAJI CREW BULAN INI --}}
        {{-- ========================================================= --}}
        <div id="salary-section" class="bg-slate-900/60 border border-white/5 rounded-2xl p-6 backdrop-blur-xl">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5">
                <div>
                    <h3 class="font-black text-white uppercase tracking-wider text-sm flex items-center gap-2">
                        <i class="fas fa-money-check-alt text-emerald-400"></i>
                        Estimasi Penggajian Bulan {{ $salaryMonth->translatedFormat('F Y') }}
                    </h3>
                    <p class="text-[10px] text-slate-500 mt-1 uppercase tracking-widest">Estimasi berdasarkan jumlah shift & data payroll yang tercatat</p>
                </div>
                <div class="flex items-center gap-3 flex-wrap">
                    {{-- Total grand --}}
                    <div class="bg-emerald-500/10 border border-emerald-500/20 rounded-xl px-4 py-2 text-right">
                        <p class="text-[9px] text-emerald-400 font-bold uppercase tracking-widest">Total Estimasi</p>
                        <p class="text-lg font-black text-white">Rp {{ number_format($salaryGrandTotal, 0, ',', '.') }}</p>
                    </div>
                    {{-- Filter user --}}
                    <form id="salary-filter-form" method="GET" action="{{ route('dashboard') }}#salary-section" class="flex items-center gap-2">
                        <input type="hidden" name="filter" value="{{ $filter }}">
                        <select name="salary_user_id" onchange="document.getElementById('salary-filter-form').submit()" class="bg-slate-800 border border-slate-700 text-white text-xs rounded-xl px-3 py-2 focus:outline-none focus:border-emerald-500 min-w-[160px]">
                            <option value="">-- Semua Crew --</option>
                            @foreach($allCrewUsers as $cu)
                            <option value="{{ $cu->id }}" {{ $salaryFilterUserId == $cu->id ? 'selected' : '' }}>
                                {{ $cu->name }} ({{ $cu->role }})
                            </option>
                            @endforeach
                        </select>
                        @if($salaryFilterUserId)
                        <a href="{{ route('dashboard', ['filter' => $filter]) }}#salary-section" class="text-slate-400 hover:text-white text-xs px-2 py-2 rounded-lg hover:bg-slate-800 transition-colors" title="Reset filter">
                            <i class="fas fa-times"></i>
                        </a>
                        @endif
                    </form>
                </div>
            </div>

            @if($crewSalaryEstimations->isEmpty())
            <div class="py-10 text-center border border-dashed border-white/5 rounded-xl">
                <i class="fas fa-users-slash text-2xl text-slate-600 mb-2"></i>
                <p class="text-sm text-slate-500">Tidak ada data crew aktif untuk ditampilkan.</p>
            </div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-slate-300">
                    <thead>
                        <tr class="border-b border-white/5">
                            <th class="text-left px-3 py-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Nama</th>
                            <th class="text-left px-3 py-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Role</th>
                            <th class="text-center px-3 py-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Shift</th>
                            <th class="text-right px-3 py-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Komisi Shift</th>
                            <th class="text-right px-3 py-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Tunjangan</th>
                            <th class="text-right px-3 py-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Lembur+Bonus</th>
                            <th class="text-right px-3 py-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Potongan</th>
                            <th class="text-right px-3 py-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Gaji Kotor</th>
                            <th class="text-right px-3 py-2 text-[10px] font-bold text-emerald-400 uppercase tracking-widest">Take-Home</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach($crewSalaryEstimations as $est)
                        <tr class="hover:bg-slate-800/40 transition-colors group">
                            <td class="px-3 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-slate-700 flex items-center justify-center text-[10px] font-black text-slate-300 flex-shrink-0">
                                        {{ strtoupper(substr($est['user']->name, 0, 2)) }}
                                    </div>
                                    <span class="font-bold text-white">{{ $est['user']->name }}</span>
                                </div>
                            </td>
                            <td class="px-3 py-3">
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-widest {{ $est['user']->role === 'kasir' ? 'bg-blue-500/20 text-blue-400' : 'bg-slate-700 text-slate-400' }}">
                                    {{ $est['user']->role }}
                                </span>
                            </td>
                            <td class="px-3 py-3 text-center">
                                <span class="font-black text-white">{{ $est['shifts'] }}</span>
                                <span class="text-slate-500 text-[9px]"> shift</span>
                            </td>
                            <td class="px-3 py-3 text-right text-slate-300">Rp {{ number_format($est['komisi'], 0, ',', '.') }}</td>
                            <td class="px-3 py-3 text-right text-slate-300">Rp {{ number_format($est['allowance'], 0, ',', '.') }}</td>
                            <td class="px-3 py-3 text-right text-blue-400">Rp {{ number_format($est['lembur'] + $est['bonus'], 0, ',', '.') }}</td>
                            <td class="px-3 py-3 text-right text-red-400">
                                {{ $est['kasbon'] > 0 ? '- Rp ' . number_format($est['kasbon'], 0, ',', '.') : '-' }}
                            </td>
                            <td class="px-3 py-3 text-right text-slate-300 font-bold">Rp {{ number_format($est['gross'], 0, ',', '.') }}</td>
                            <td class="px-3 py-3 text-right">
                                <span class="font-black text-emerald-400 text-sm">Rp {{ number_format($est['net'], 0, ',', '.') }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-emerald-500/30 bg-emerald-500/5">
                            <td colspan="8" class="px-3 py-3 text-right text-[10px] font-bold text-emerald-400 uppercase tracking-widest">Total Estimasi Bayar</td>
                            <td class="px-3 py-3 text-right font-black text-emerald-400 text-base">Rp {{ number_format($salaryGrandTotal, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @endif
        </div>

    @elseif(auth()->user()->isKasir())
        {{-- ========================================================= --}}
        {{-- CASHIER WORKSTATION DASHBOARD --}}
        {{-- ========================================================= --}}
        <div class="space-y-6">
            
            {{-- 1. Status Kasir Aktif & Buka POS --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Status Card --}}
                <div class="md:col-span-2 bg-slate-900/60 border border-white/5 rounded-2xl p-6 backdrop-blur-xl relative overflow-hidden flex flex-col justify-center">
                    <div class="absolute -right-10 -top-10 w-40 h-40 bg-blue-500/10 rounded-full blur-[50px] pointer-events-none"></div>
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-10">
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                @if($activeShift)
                                    <span class="relative flex h-3 w-3">
                                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                      <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                                    </span>
                                    <span class="text-emerald-400 font-bold uppercase tracking-widest text-xs">Shift Aktif</span>
                                @else
                                    <span class="relative flex h-3 w-3">
                                      <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                                    </span>
                                    <span class="text-red-400 font-bold uppercase tracking-widest text-xs">Shift Terkunci</span>
                                @endif
                            </div>
                            <h2 class="text-2xl font-black text-white mb-1">Kasir: {{ auth()->user()->name }}</h2>
                            @if($activeShift)
                                <p class="text-sm text-slate-400 font-medium flex items-center gap-2">
                                    <i class="far fa-clock text-blue-400"></i>
                                    <span id="live-shift-clock" data-start="{{ $activeShift->opened_at->toIso8601String() }}">00:00:00</span> berjalan
                                </p>
                            @else
                                <p class="text-sm text-slate-500">Tunggu Owner/Admin membuka shift</p>
                            @endif
                        </div>
                        
                        <div class="flex gap-4">
                            <div class="bg-slate-950/50 rounded-xl p-3 border border-white/5 min-w-[120px]">
                                <p class="text-[10px] text-slate-500 uppercase tracking-widest font-bold mb-1">Transaksi</p>
                                <p class="text-xl font-black text-white">{{ number_format($todayTransactions) }}</p>
                            </div>
                            <div class="bg-slate-950/50 rounded-xl p-3 border border-white/5 min-w-[140px]">
                                <p class="text-[10px] text-slate-500 uppercase tracking-widest font-bold mb-1">Penjualan</p>
                                <p class="text-xl font-black text-emerald-400">Rp {{ number_format($todaySales/1000, 0, ',', '.') }}k</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Shortcut Buka POS --}}
                @if($activeShift)
                    <div class="bg-gradient-to-br from-blue-600 to-cyan-500 border border-blue-400/50 shadow-[0_0_30px_rgba(6,182,212,0.4)] rounded-2xl p-6 flex flex-col justify-center items-center text-center cursor-pointer hover:from-blue-500 hover:to-cyan-400 hover:scale-[1.02] transition-all relative overflow-hidden group" onclick="window.location.href='{{ route('pos.index') }}'">
                        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10 mix-blend-overlay"></div>
                        <i class="fas fa-cash-register text-4xl text-white mb-3 drop-shadow-[0_0_10px_rgba(255,255,255,0.5)] relative z-10 group-hover:scale-110 transition-transform"></i>
                        <h3 class="font-black text-white uppercase tracking-widest text-sm relative z-10">Buka POS Kasir</h3>
                        <p class="text-[10px] text-blue-100 font-bold uppercase tracking-widest mt-1 relative z-10 opacity-80">Mulai layani pelanggan</p>
                    </div>
                @else
                    <div class="bg-slate-800/50 border border-slate-700 rounded-2xl p-6 flex flex-col justify-center items-center text-center relative overflow-hidden opacity-75">
                        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-5 mix-blend-overlay"></div>
                        <div class="w-16 h-16 bg-slate-900 rounded-full flex items-center justify-center text-slate-500 mb-3 shadow-inner relative z-10 border border-slate-700">
                            <i class="fas fa-lock text-2xl"></i>
                        </div>
                        <h3 class="font-black text-slate-400 uppercase tracking-widest text-sm relative z-10">Tunggu Shift Dibuka</h3>
                    </div>
                @endif
            </div>

            {{-- 2. Quick Stats Mini (Horizontal Scrollable) --}}
            <div class="flex overflow-x-auto gap-4 pb-2 scrollbar-hide custom-scrollbar">
                <div class="min-w-[200px] flex-1 bg-slate-900/60 border border-white/5 rounded-xl p-4 flex items-center gap-3 backdrop-blur-xl group hover:border-purple-500/30 transition-colors">
                    <div class="w-10 h-10 rounded-lg bg-purple-500/10 text-purple-400 flex items-center justify-center border border-purple-500/20 group-hover:bg-purple-500/20 transition-colors"><i class="fas fa-credit-card"></i></div>
                    <div>
                        <p class="text-[10px] text-slate-500 uppercase tracking-widest font-bold">Metode Terfavorit</p>
                        <p class="text-sm font-black text-white mt-0.5">{{ strtoupper($topPayment) }}</p>
                    </div>
                </div>
                <div class="min-w-[200px] flex-1 bg-slate-900/60 border border-white/5 rounded-xl p-4 flex items-center gap-3 backdrop-blur-xl group hover:border-blue-500/30 transition-colors">
                    <div class="w-10 h-10 rounded-lg bg-blue-500/10 text-blue-400 flex items-center justify-center border border-blue-500/20 group-hover:bg-blue-500/20 transition-colors"><i class="fas fa-box-open"></i></div>
                    <div>
                        <p class="text-[10px] text-slate-500 uppercase tracking-widest font-bold">Produk Terlaris</p>
                        <p class="text-sm font-black text-white mt-0.5 truncate max-w-[120px]" title="{{ $topProduct }}">{{ $topProduct }}</p>
                    </div>
                </div>
                <div class="min-w-[200px] flex-1 bg-slate-900/60 border border-white/5 rounded-xl p-4 flex items-center gap-3 backdrop-blur-xl group hover:border-emerald-500/30 transition-colors">
                    <div class="w-10 h-10 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center border border-emerald-500/20 group-hover:bg-emerald-500/20 transition-colors"><i class="fas fa-bullseye"></i></div>
                    <div>
                        <p class="text-[10px] text-slate-500 uppercase tracking-widest font-bold">Target Tercapai</p>
                        <p class="text-sm font-black text-emerald-400 mt-0.5">{{ number_format($targetPercentage, 1) }}%</p>
                    </div>
                </div>
                <div class="min-w-[200px] flex-1 bg-slate-900/60 border border-white/5 rounded-xl p-4 flex items-center gap-3 backdrop-blur-xl group hover:border-amber-500/30 transition-colors">
                    <div class="w-10 h-10 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center border border-amber-500/20 group-hover:bg-amber-500/20 transition-colors"><i class="fas fa-clock"></i></div>
                    <div>
                        <p class="text-[10px] text-slate-500 uppercase tracking-widest font-bold">Trx Terakhir</p>
                        <p class="text-sm font-black text-white mt-0.5">{{ $recentTransactions->first() ? $recentTransactions->first()->created_at->diffForHumans() : '-' }}</p>
                    </div>
                </div>
            </div>

            {{-- 3. Detailed Financial Metrics --}}
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mt-6">
                <div class="bg-slate-900/60 border border-white/5 rounded-xl p-4 backdrop-blur-xl hover:border-emerald-500/30 transition-colors">
                    <p class="text-[10px] text-slate-500 uppercase tracking-widest font-bold mb-1">Omset Hari Ini</p>
                    <p class="text-lg font-black text-emerald-400">Rp {{ number_format($todaySales, 0, ',', '.') }}</p>
                </div>
                <div class="bg-slate-900/60 border border-white/5 rounded-xl p-4 backdrop-blur-xl hover:border-red-500/30 transition-colors">
                    <p class="text-[10px] text-slate-500 uppercase tracking-widest font-bold mb-1">Total Biaya</p>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-red-500/10 text-red-400 flex items-center justify-center border border-red-500/20">
                            <i class="fas fa-wallet text-xs"></i>
                        </div>
                        <div>
                            <p class="text-lg font-black text-red-400">Rp {{ number_format($totalBiaya, 0, ',', '.') }}</p>
                            <p class="text-[8px] text-slate-500 uppercase font-bold mt-0.5">
                                <i class="fas fa-money-bill text-emerald-400 mr-1"></i>Tunai: Rp {{ number_format($totalBiayaTunai, 0, ',', '.') }} 
                                <span class="mx-1">•</span> 
                                <i class="fas fa-university text-blue-400 mr-1"></i>Bank: Rp {{ number_format($totalBiayaBank, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-900/60 border border-white/5 rounded-xl p-4 backdrop-blur-xl hover:border-emerald-500/30 transition-colors">
                    <p class="text-[10px] text-slate-500 uppercase tracking-widest font-bold mb-1">Pendapatan Bersih</p>
                    <p class="text-lg font-black text-emerald-400">Rp {{ number_format($pendapatanBersih, 0, ',', '.') }}</p>
                </div>
                <div class="bg-slate-900/60 border border-white/5 rounded-xl p-4 backdrop-blur-xl hover:border-blue-500/30 transition-colors">
                    <p class="text-[10px] text-slate-500 uppercase tracking-widest font-bold mb-1">Saldo Laci</p>
                    <p class="text-lg font-black text-blue-400">Rp {{ number_format($saldoLaci, 0, ',', '.') }}</p>
                    <div class="flex justify-between items-center mt-1">
                        <p class="text-[8px] text-slate-500 uppercase">Awal shift: Rp {{ number_format($awalShift, 0, ',', '.') }}</p>
                        <p class="text-[8px] text-emerald-400 font-bold">+{{ number_format($saldoLaci - $awalShift, 0, ',', '.') }}</p>
                    </div>
                </div>
                <div class="bg-slate-900/60 border border-white/5 rounded-xl p-4 backdrop-blur-xl hover:border-amber-500/30 transition-colors">
                    <p class="text-[10px] text-slate-500 uppercase tracking-widest font-bold mb-1">Total Piutang</p>
                    <p class="text-lg font-black text-amber-500">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                <div class="bg-slate-900/60 border border-white/5 rounded-xl p-4 flex justify-between items-center backdrop-blur-xl hover:border-purple-500/30 transition-colors">
                    <div>
                        <p class="text-[10px] text-slate-500 uppercase tracking-widest font-bold mb-1">Pemasukan QRIS (Hari Ini)</p>
                        <p class="text-lg font-black text-purple-400">Rp {{ number_format($pemasukanQris, 0, ',', '.') }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-purple-500/10 text-purple-400 flex items-center justify-center border border-purple-500/20">
                        <i class="fas fa-qrcode"></i>
                    </div>
                </div>
                <div class="bg-slate-900/60 border border-white/5 rounded-xl p-4 flex justify-between items-center backdrop-blur-xl hover:border-emerald-500/30 transition-colors">
                    <div>
                        <p class="text-[10px] text-slate-500 uppercase tracking-widest font-bold mb-1">Pemasukan Tunai (Hari Ini)</p>
                        <p class="text-lg font-black text-emerald-400">Rp {{ number_format($pemasukanTunai, 0, ',', '.') }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center border border-emerald-500/20">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                </div>
                <div class="bg-slate-900/60 border border-white/5 rounded-xl p-4 flex justify-between items-center backdrop-blur-xl hover:border-blue-500/30 transition-colors">
                    <div>
                        <p class="text-[10px] text-slate-500 uppercase tracking-widest font-bold mb-1">Pemasukan Transfer (Hari Ini)</p>
                        <p class="text-lg font-black text-blue-400">Rp {{ number_format($pemasukanTransfer, 0, ',', '.') }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-blue-500/10 text-blue-400 flex items-center justify-center border border-blue-500/20">
                        <i class="fas fa-university"></i>
                    </div>
                </div>
            </div>

            {{-- 4. Target Harian --}}
            <div class="grid grid-cols-1 gap-6 mt-6">
                {{-- Target Harian Progress --}}
                <div class="bg-slate-900/60 border border-white/5 rounded-2xl p-6 backdrop-blur-xl relative overflow-hidden">
                    @php
                        $targetColor = $targetPercentage < 30 ? 'blue' : ($targetPercentage < 70 ? 'cyan' : ($targetPercentage >= 100 ? 'yellow' : 'emerald'));
                        $motivationalText = "Semangat 🚀 Kejar target penjualan hari ini.";
                        if($targetPercentage >= 100) $motivationalText = "Target harian berhasil dicapai 🏆 Luar biasa!";
                        elseif($targetPercentage >= 80) $motivationalText = "Hampir selesai 🎯 Sedikit lagi mencapai target.";
                        elseif($targetPercentage >= 50) $motivationalText = "Bagus 🔥 Target mulai tercapai.";
                    @endphp
                    
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="font-black text-slate-400 uppercase tracking-widest text-xs mb-1">Target Penjualan Hari Ini</h3>
                            <p class="text-sm font-medium text-slate-300">{{ $motivationalText }}</p>
                        </div>
                        <div class="text-right">
                            <h2 class="text-2xl font-black text-white">Rp {{ number_format($todaySales, 0, ',', '.') }}</h2>
                            <p class="text-[10px] text-slate-500 uppercase tracking-widest font-bold">dari Rp {{ number_format($targetDaily, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    <div class="h-4 w-full bg-slate-950 rounded-full overflow-hidden relative border border-white/5 mb-3">
                        <div class="absolute top-0 left-0 h-full bg-{{ $targetColor }}-500 transition-all duration-1000 rounded-full shadow-[0_0_15px_rgba(var(--tw-colors-{{ $targetColor }}-500),0.6)]" style="width: {{ $targetPercentage }}%"></div>
                        @if($targetPercentage >= 100)
                            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/30 to-transparent -translate-x-full animate-[shimmer_2s_infinite]"></div>
                        @endif
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">
                            Sisa: Rp {{ number_format(max(0, $targetDaily - $todaySales), 0, ',', '.') }}
                        </p>
                        <p class="text-xs text-{{ $targetColor }}-400 font-black tracking-widest">{{ number_format($targetPercentage, 1) }}% TERCAPAI</p>
                    </div>
                </div>

                </div>
            </div>

            {{-- 4. Notifikasi, Transaksi, & Timeline --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                {{-- Notifikasi & Transaksi --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Notifikasi Operasional --}}
                    @if($lowStockCount > 0)
                    <div class="flex gap-3 overflow-x-auto pb-2 scrollbar-hide">
                        <div class="flex-shrink-0 flex items-center gap-2 bg-amber-500/10 border border-amber-500/20 text-amber-400 px-3 py-2 rounded-lg text-xs font-bold">
                            <i class="fas fa-exclamation-triangle"></i> {{ $lowStockCount }} Stok Menipis
                        </div>
                    </div>
                    @endif

                    {{-- Transaksi Terakhir --}}
                    <div class="bg-slate-900/60 border border-white/5 rounded-2xl p-6 backdrop-blur-xl">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-black text-white uppercase tracking-wider text-sm">Transaksi Terakhir</h3>
                            <a href="{{ route('transactions.index') }}" class="text-[10px] font-bold text-blue-400 hover:text-blue-300 uppercase tracking-widest">Lihat Semua</a>
                        </div>
                        
                        @if($recentTransactions->count() > 0)
                            <div class="space-y-3">
                                @foreach($recentTransactions as $t)
                                <div class="flex items-center justify-between p-3 bg-slate-950/50 rounded-xl border border-white/5 hover:border-blue-500/30 hover:bg-slate-900 transition-all group">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400 group-hover:scale-110 transition-transform">
                                            <i class="fas fa-receipt"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-white">{{ $t->invoice_number }}</p>
                                            <p class="text-xs text-slate-500 mt-0.5"><i class="far fa-clock mr-1"></i>{{ $t->created_at->format('H:i') }} • <span class="font-bold text-slate-400">{{ strtoupper($t->payment_method) }}</span></p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-black text-emerald-400">Rp {{ number_format($t->total, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            {{-- Empty State --}}
                            <div class="py-12 text-center border border-dashed border-white/5 rounded-xl bg-slate-950/30">
                                <div class="w-16 h-16 bg-blue-500/10 rounded-full flex items-center justify-center mx-auto mb-4 text-blue-400 shadow-[0_0_15px_rgba(59,130,246,0.2)]">
                                    <i class="fas fa-shopping-basket text-2xl"></i>
                                </div>
                                <h3 class="text-sm font-black text-white uppercase tracking-widest mb-1">Belum Ada Transaksi Hari Ini 🚀</h3>
                                <p class="text-xs text-slate-500 max-w-xs mx-auto">Mulai transaksi pertama Anda sekarang dengan membuka POS.</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Timeline Aktivitas Shift --}}
                <div class="bg-slate-900/60 border border-white/5 rounded-2xl p-6 backdrop-blur-xl">
                    <h3 class="font-black text-white uppercase tracking-wider text-sm mb-6">Aktivitas Shift</h3>
                    <div class="relative border-l border-white/10 ml-3 space-y-6">
                        @foreach($activities as $act)
                            <div class="relative pl-6">
                                <div class="absolute -left-3.5 top-0 w-7 h-7 {{ $act->bg }} rounded-full flex items-center justify-center border border-slate-900">
                                    <i class="fas {{ $act->icon }} text-[10px] {{ $act->color }}"></i>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-white">{{ $act->title }}</h4>
                                    @if(isset($act->desc))
                                        <p class="text-sm font-black {{ $act->color }} mt-0.5">{{ $act->desc }}</p>
                                    @endif
                                    <p class="text-[10px] text-slate-500 mt-1"><i class="far fa-clock mr-1"></i>{{ Carbon\Carbon::parse($act->time)->format('H:i') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>

    @else
        {{-- ========================================================= --}}
        {{-- CREW BIASA DASHBOARD --}}
        {{-- ========================================================= --}}
        <div class="space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Profile & Status --}}
                <div class="md:col-span-2 bg-slate-900/60 border border-white/5 rounded-2xl p-6 backdrop-blur-xl relative overflow-hidden flex flex-col justify-center">
                    <div class="absolute -right-10 -top-10 w-40 h-40 bg-blue-500/10 rounded-full blur-[50px] pointer-events-none"></div>
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-10">
                        <div>
                            <h2 class="text-2xl font-black text-white mb-1">Halo, {{ auth()->user()->name }}</h2>
                            <p class="text-sm text-slate-400 font-medium">Selamat datang di dashboard personal Anda.</p>
                        </div>
                        
                        <div class="flex gap-4">
                            <div class="bg-slate-950/50 rounded-xl p-3 border border-white/5 min-w-[120px] text-center">
                                <p class="text-[10px] text-slate-500 uppercase tracking-widest font-bold mb-1">Target Kehadiran</p>
                                <p class="text-xl font-black {{ $performanceScore >= 80 ? 'text-emerald-400' : ($performanceScore >= 50 ? 'text-amber-400' : 'text-red-400') }}">{{ $performanceScore }}%</p>
                            </div>
                            <div class="bg-slate-950/50 rounded-xl p-3 border border-white/5 min-w-[120px] text-center">
                                <p class="text-[10px] text-slate-500 uppercase tracking-widest font-bold mb-1">Shift Selesai</p>
                                <p class="text-xl font-black text-blue-400">{{ $completedShiftsCount }} <span class="text-xs text-slate-500">/ {{ $totalShiftsThisMonth }}</span></p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Shortcut Jadwal --}}
                <div class="bg-gradient-to-br from-emerald-600 to-teal-500 border border-emerald-400/50 shadow-[0_0_30px_rgba(16,185,129,0.4)] rounded-2xl p-6 flex flex-col justify-center items-center text-center cursor-pointer hover:from-emerald-500 hover:to-teal-400 hover:scale-[1.02] transition-all relative overflow-hidden group" onclick="window.location.href='{{ route('schedules.index') }}'">
                    <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10 mix-blend-overlay"></div>
                    <i class="fas fa-calendar-alt text-4xl text-white mb-3 drop-shadow-[0_0_10px_rgba(255,255,255,0.5)] relative z-10 group-hover:scale-110 transition-transform"></i>
                    <h3 class="font-black text-white uppercase tracking-widest text-sm relative z-10">Lihat Jadwal</h3>
                    <p class="text-[10px] text-emerald-100 font-bold uppercase tracking-widest mt-1 relative z-10 opacity-80">Atur penukaran shift</p>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-slate-900/60 border border-white/5 rounded-xl p-4 backdrop-blur-xl">
                    <p class="text-[10px] text-slate-500 uppercase tracking-widest font-bold mb-1">Total Pendapatan</p>
                    <p class="text-lg font-black text-emerald-400">Rp {{ number_format($totalKotor, 0, ',', '.') }}</p>
                    <p class="text-[9px] text-slate-400 mt-1">Gaji & Komisi Shift</p>
                </div>
                <div class="bg-slate-900/60 border border-white/5 rounded-xl p-4 backdrop-blur-xl">
                    <p class="text-[10px] text-slate-500 uppercase tracking-widest font-bold mb-1">Tambahan</p>
                    <p class="text-lg font-black text-blue-400">Rp {{ number_format($lembur + $motret + $bonus, 0, ',', '.') }}</p>
                    <p class="text-[9px] text-slate-400 mt-1">Lembur, Motret, Bonus</p>
                </div>
                <div class="bg-slate-900/60 border border-white/5 rounded-xl p-4 backdrop-blur-xl">
                    <p class="text-[10px] text-slate-500 uppercase tracking-widest font-bold mb-1">Kasbon / Potongan</p>
                    <p class="text-lg font-black text-red-400">Rp {{ number_format($kasbon, 0, ',', '.') }}</p>
                    <p class="text-[9px] text-slate-400 mt-1">Dari sistem payroll</p>
                </div>
                <div class="bg-gradient-to-br from-emerald-900/40 to-slate-900/60 border border-emerald-500/30 rounded-xl p-4 backdrop-blur-xl">
                    <p class="text-[10px] text-emerald-500 uppercase tracking-widest font-bold mb-1">Estimasi Bersih</p>
                    <p class="text-xl font-black text-white">Rp {{ number_format($totalBersih, 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Jadwal Mendatang (Semua Crew di Lokasi) --}}
                <div class="lg:col-span-2 bg-slate-900/60 border border-white/5 rounded-2xl p-6 backdrop-blur-xl">
                    <h3 class="font-black text-white uppercase tracking-wider text-sm mb-4">Jadwal Shift Lokasi Anda (7 Hari)</h3>
                    @if($locationSchedules->count() > 0)
                        <div class="space-y-4">
                            @foreach($locationSchedules as $date => $assignments)
                            <div class="bg-slate-950/50 p-4 rounded-xl border {{ $date == Carbon\Carbon::today()->format('Y-m-d') ? 'border-cyan-500/30 shadow-[0_0_15px_rgba(6,182,212,0.1)]' : 'border-white/5' }}">
                                <div class="flex justify-between items-center mb-3 border-b border-white/5 pb-2">
                                    <h4 class="text-xs font-bold {{ $date == Carbon\Carbon::today()->format('Y-m-d') ? 'text-cyan-400' : 'text-white' }} flex items-center gap-2">
                                        <i class="fas fa-calendar-day"></i> {{ Carbon\Carbon::parse($date)->translatedFormat('l, d M Y') }}
                                        @if($date == Carbon\Carbon::today()->format('Y-m-d'))
                                            <span class="px-2 py-0.5 bg-cyan-500/20 text-cyan-400 text-[9px] rounded uppercase tracking-widest">Hari Ini</span>
                                        @endif
                                    </h4>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    @foreach($assignments as $asgn)
                                    <div class="flex items-center gap-3 p-2 rounded-lg {{ $asgn->user_id == auth()->id() ? 'bg-emerald-500/10 border border-emerald-500/20' : 'bg-slate-900/50' }}">
                                        <div class="w-8 h-8 rounded-full {{ $asgn->user_id == auth()->id() ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-800 text-slate-400' }} flex flex-shrink-0 items-center justify-center font-black text-[10px]">
                                            {{ substr($asgn->user->name, 0, 2) }}
                                        </div>
                                        <div class="flex-1 overflow-hidden">
                                            <p class="text-xs font-bold text-white truncate">{{ $asgn->user->name }} {!! $asgn->user_id == auth()->id() ? '<span class="text-emerald-400">(Anda)</span>' : '' !!}</p>
                                            <p class="text-[9px] text-slate-500 tracking-widest uppercase mt-0.5">{{ $asgn->shift->name }} • {{ $asgn->shift->start_time }} - {{ $asgn->shift->end_time }}</p>
                                        </div>
                                        <div class="text-[9px] font-bold text-slate-500 uppercase px-2 py-1 bg-slate-950 rounded">
                                            {{ $asgn->shift->location->name ?? '-' }}
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-8 text-center border border-dashed border-white/5 rounded-xl">
                            <i class="fas fa-bed text-2xl text-slate-600 mb-2"></i>
                            <p class="text-sm font-bold text-slate-400">Tidak ada jadwal 7 hari kedepan</p>
                        </div>
                    @endif
                </div>
                
                {{-- Info / Pengumuman --}}
                <div class="bg-slate-900/60 border border-white/5 rounded-2xl p-6 backdrop-blur-xl">
                    <h3 class="font-black text-white uppercase tracking-wider text-sm mb-4">Informasi Penting</h3>
                    <div class="bg-blue-500/10 border border-blue-500/20 rounded-xl p-4 flex gap-3 mb-4">
                        <i class="fas fa-info-circle text-blue-400 mt-1"></i>
                        <div>
                            <p class="text-sm font-bold text-white">Panduan Penukaran Shift</p>
                            <p class="text-xs text-slate-400 mt-1">Untuk menukar shift, silakan masuk ke menu <strong class="text-slate-300">Jadwal Kerja</strong> lalu klik shift Anda dan pilih opsi penukaran. Pastikan penukaran disetujui minimal 1 hari sebelum shift dimulai.</p>
                        </div>
                    </div>
                    <div class="bg-amber-500/10 border border-amber-500/20 rounded-xl p-4 flex gap-3">
                        <i class="fas fa-exclamation-triangle text-amber-400 mt-1"></i>
                        <div>
                            <p class="text-sm font-bold text-white">Performa Kehadiran</p>
                            <p class="text-xs text-slate-400 mt-1">Gaji dan komisi Anda sangat bergantung pada tingkat kehadiran. Pastikan untuk selalu hadir pada shift yang telah ditentukan atau ajukan tukar shift jika berhalangan.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
    
    {{-- INSERT CREW FINANCIAL (KASIR ROLE) --}}
    @if(isset($crewFinancial))
    <div class="mt-6 border-t border-white/5 pt-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-black text-white uppercase tracking-wider text-sm flex items-center gap-2">
                <i class="fas fa-user-circle text-orange-400"></i> Performa Pribadi Saya Bulan Ini
            </h3>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <!-- Pendapatan Kotor -->
            <div class="bg-gradient-to-br from-emerald-900/40 to-emerald-800/20 border border-emerald-700/50 rounded-2xl p-4 flex flex-col justify-between">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <p class="text-[10px] text-emerald-400 font-bold uppercase tracking-wider mb-1">Gaji Kotor</p>
                        <h3 class="text-lg font-black text-white">Rp {{ number_format($crewFinancial['totalKotor'], 0, ',', '.') }}</h3>
                    </div>
                    <div class="w-8 h-8 rounded-full bg-emerald-500/20 flex items-center justify-center border border-emerald-500/30 text-emerald-400">
                        <i class="fas fa-money-bill-wave text-xs"></i>
                    </div>
                </div>
                <p class="text-[10px] text-emerald-400/80">Termasuk gaji pokok & tambahan</p>
            </div>

            <!-- Potongan Kasbon -->
            <div class="bg-gradient-to-br from-red-900/40 to-red-800/20 border border-red-700/50 rounded-2xl p-4">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <p class="text-[10px] text-red-400 font-bold uppercase tracking-wider mb-1">Potongan</p>
                        <h3 class="text-lg font-black text-white">Rp {{ number_format($crewFinancial['kasbon'], 0, ',', '.') }}</h3>
                    </div>
                    <div class="w-8 h-8 rounded-full bg-red-500/20 flex items-center justify-center border border-red-500/30 text-red-400">
                        <i class="fas fa-hand-holding-usd text-xs"></i>
                    </div>
                </div>
                @if(isset($crewFinancial['payroll']) && $crewFinancial['payroll']->deduction_note)
                    <p class="text-[10px] text-red-400/80 italic">{{ $crewFinancial['payroll']->deduction_note }}</p>
                @else
                    <p class="text-[10px] text-red-400/80">Total kasbon ditarik</p>
                @endif
            </div>

            <!-- Lembur -->
            <div class="bg-gradient-to-br from-orange-900/40 to-orange-800/20 border border-orange-700/50 rounded-2xl p-4">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <p class="text-[10px] text-orange-400 font-bold uppercase tracking-wider mb-1">Lembur</p>
                        <h3 class="text-lg font-black text-white">Rp {{ number_format($crewFinancial['lembur'], 0, ',', '.') }}</h3>
                    </div>
                    <div class="w-8 h-8 rounded-full bg-orange-500/20 flex items-center justify-center border border-orange-500/30 text-orange-400">
                        <i class="fas fa-clock text-xs"></i>
                    </div>
                </div>
                @if(isset($crewFinancial['payroll']) && $crewFinancial['payroll']->overtime_fee_note)
                    <p class="text-[10px] text-orange-400/80 italic">{{ $crewFinancial['payroll']->overtime_fee_note }}</p>
                @else
                    <p class="text-[10px] text-orange-400/80">Tambahan lembur</p>
                @endif
            </div>

            <!-- Bonus -->
            <div class="bg-gradient-to-br from-purple-900/40 to-purple-800/20 border border-purple-700/50 rounded-2xl p-4">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <p class="text-[10px] text-purple-400 font-bold uppercase tracking-wider mb-1">Bonus</p>
                        <h3 class="text-lg font-black text-white">Rp {{ number_format($crewFinancial['bonus'], 0, ',', '.') }}</h3>
                    </div>
                    <div class="w-8 h-8 rounded-full bg-purple-500/20 flex items-center justify-center border border-purple-500/30 text-purple-400">
                        <i class="fas fa-gift text-xs"></i>
                    </div>
                </div>
                @if(isset($crewFinancial['payroll']) && $crewFinancial['payroll']->bonus_note)
                    <p class="text-[10px] text-purple-400/80 italic">{{ $crewFinancial['payroll']->bonus_note }}</p>
                @else
                    <p class="text-[10px] text-purple-400/80">Bonus/reward ekstra</p>
                @endif
            </div>

            <!-- Pendapatan Bersih -->
            <div class="bg-gradient-to-br from-blue-900/40 to-blue-800/20 border border-blue-700/50 rounded-2xl p-4">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <p class="text-[10px] text-blue-400 font-bold uppercase tracking-wider mb-1">Total Diterima</p>
                        <h3 class="text-lg font-black text-white">Rp {{ number_format($crewFinancial['totalBersih'], 0, ',', '.') }}</h3>
                    </div>
                    <div class="w-8 h-8 rounded-full bg-blue-500/20 flex items-center justify-center border border-blue-500/30 text-blue-400">
                        <i class="fas fa-wallet text-xs"></i>
                    </div>
                </div>
                <p class="text-[10px] text-blue-400/80">Take-home pay bulan ini</p>
                @if(isset($crewFinancial['payroll']))
                    <div class="mt-2 text-[10px] font-bold px-2 py-1 rounded {{ $crewFinancial['payroll']->is_finalized ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-500/20 text-slate-400' }}">
                        Status: {{ $crewFinancial['payroll']->is_finalized ? 'Gaji Pas (Selesai)' : 'Sedang Proses' }}
                    </div>
                @endif
            </div>

            <!-- Kehadiran -->
            <div class="bg-slate-800/80 border border-slate-700 rounded-2xl p-4 relative overflow-hidden group">
                <div class="flex justify-between items-start mb-2 relative z-10">
                    <div>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1">Shift Selesai</p>
                        <h3 class="text-lg font-black text-white">
                            {{ $crewFinancial['completed_shifts'] }}<span class="text-sm text-slate-500 font-medium">/{{ $crewFinancial['total_shifts'] }}</span>
                        </h3>
                    </div>
                    <div class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center border border-slate-600 text-slate-300">
                        <i class="fas fa-user-check text-xs"></i>
                    </div>
                </div>
                @php $pct = $crewFinancial['total_shifts'] > 0 ? ($crewFinancial['completed_shifts'] / $crewFinancial['total_shifts'] * 100) : 0; @endphp
                <div class="h-1.5 w-full bg-slate-900 rounded-full mt-2 relative z-10 overflow-hidden border border-slate-700">
                    <div class="h-full bg-slate-400 rounded-full" style="width: {{ $pct }}%"></div>
                </div>
                <div class="mt-3 relative z-10">
                    <a href="{{ route('schedules.payrolls.export-pdf', ['month' => Carbon\Carbon::now()->format('Y-m')]) }}" class="w-full flex items-center justify-center gap-1.5 px-3 py-1.5 bg-red-500/20 hover:bg-red-500/40 text-red-400 border border-red-500/30 rounded-lg text-[10px] font-bold uppercase tracking-widest transition-colors">
                        <i class="fas fa-file-pdf"></i> Slip Gaji
                    </a>
                </div>
            </div>
        </div>
        
        @if(isset($crewFinancial['payroll']) && $crewFinancial['payroll']->notes)
            <div class="mt-4 bg-slate-800/80 border border-slate-700 p-4 rounded-xl relative overflow-hidden">
                <div class="absolute top-0 left-0 w-1 h-full bg-blue-500"></div>
                <p class="text-xs font-bold text-slate-400 mb-1"><i class="fas fa-sticky-note mr-1 text-blue-400"></i> Catatan Keseluruhan</p>
                <p class="text-sm text-slate-200 italic">{{ $crewFinancial['payroll']->notes }}</p>
            </div>
        @endif
    </div>
    <div class="mt-8 border-t border-white/5 pt-6">
        <h3 class="font-black text-white uppercase tracking-wider text-sm flex items-center gap-2 mb-4">
            <i class="fas fa-calendar-week text-blue-400"></i> Jadwal Shift Pribadi
        </h3>

        {{-- Filter Mode --}}
        <div class="bg-slate-800/80 border border-slate-700 rounded-2xl p-4 mb-4">
            <form action="{{ route('dashboard') }}" method="GET" class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Mode Tampilan</label>
                    <div class="flex rounded-xl overflow-hidden border border-slate-700">
                        <button type="submit" name="view" value="daily" class="px-4 py-2 text-xs font-bold transition-colors {{ $scheduleViewMode === 'daily' ? 'bg-blue-600 text-white' : 'bg-slate-900 text-slate-400 hover:text-white' }}">
                            <i class="fas fa-calendar-day mr-1"></i>Harian
                        </button>
                        <button type="submit" name="view" value="weekly" class="px-4 py-2 text-xs font-bold transition-colors border-x border-slate-700 {{ $scheduleViewMode === 'weekly' ? 'bg-blue-600 text-white' : 'bg-slate-900 text-slate-400 hover:text-white' }}">
                            <i class="fas fa-calendar-week mr-1"></i>Mingguan
                        </button>
                        <button type="submit" name="view" value="monthly" class="px-4 py-2 text-xs font-bold transition-colors {{ $scheduleViewMode === 'monthly' ? 'bg-blue-600 text-white' : 'bg-slate-900 text-slate-400 hover:text-white' }}">
                            <i class="fas fa-calendar-alt mr-1"></i>Bulanan
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Tanggal</label>
                    <input type="date" name="date" value="{{ $scheduleDate }}" class="bg-slate-900 border border-slate-700 text-white text-sm rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none w-[160px]">
                </div>
                <div>
                    <button type="submit" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white text-sm font-bold rounded-xl transition-colors h-[38px] flex items-center gap-2">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
            </form>
        </div>
        
        <div class="bg-slate-800/80 border border-slate-700 rounded-2xl p-4">
            <div class="overflow-x-auto pb-8 min-h-[300px]">
                <table class="w-full text-xs text-slate-300 border-collapse">
                    <thead>
                        <tr class="bg-slate-900/50">
                            <th class="px-3 py-2 text-left font-bold text-slate-400 border border-slate-700 sticky left-0 bg-slate-900 z-10 min-w-[120px]">Shift</th>
                            @foreach($scheduleDates as $dt)
                            @php $dtCarbon = \Carbon\Carbon::parse($dt); @endphp
                            <th class="px-2 py-2 text-center font-bold border border-slate-700 min-w-[110px] {{ $dt === now()->format('Y-m-d') ? 'bg-blue-500/10 text-blue-400' : 'text-slate-400' }}">
                                <div>{{ $dtCarbon->translatedFormat('D') }}</div>
                                <div class="text-[10px]">{{ $dtCarbon->format('d/m') }}</div>
                            </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($scheduleLocations as $loc)
                        @foreach($loc->shifts as $shift)
                        <tr class="hover:bg-slate-700/20">
                            <td class="px-3 py-2 border border-slate-700 sticky left-0 bg-slate-800 z-10">
                                <div class="flex items-center gap-1.5">
                                    <div class="w-2 h-2 rounded-full" style="background:{{ $shift->color }}"></div>
                                    <div>
                                        <div class="font-bold text-white text-[11px]">{{ $shift->name }}</div>
                                        <div class="text-[9px] text-slate-500">{{ $loc->name }} · {{ substr($shift->start_time,0,5) }}-{{ substr($shift->end_time,0,5) }}</div>
                                    </div>
                                </div>
                            </td>
                            @foreach($scheduleDates as $dt)
                            @php $cellAsgn = $scheduleAssignments->where('schedule_shift_id', $shift->id)->filter(fn($a) => $a->date->format('Y-m-d') === $dt); @endphp
                            <td class="px-1 py-1 border border-slate-700/50 align-top {{ $dt === now()->format('Y-m-d') ? 'bg-blue-500/5' : '' }}">
                                @foreach($cellAsgn as $ca)
                                <div x-data="{ pop: false }" class="relative mb-0.5 group">
                                    <div @click="pop = !pop" class="px-1.5 py-1 rounded cursor-pointer text-[9px] font-bold flex items-center gap-0.5 transition-all hover:ring-1 hover:ring-slate-500
                                        {{ $ca->isClosed() 
                                            ? ($ca->closed_at_time ? 'bg-blue-500/10 text-blue-400 border border-blue-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20')
                                            : 'text-white' }}"
                                        style="{{ $ca->isOpen() ? 'background:' . $shift->color . '33; border-left: 2px solid ' . $shift->color : '' }}"
                                        title="{{ $ca->isClosed() ? 'CLOSE - ' . $ca->closed_by . ': ' . $ca->closed_reason : 'OPEN' }}">
                                        @if($ca->isClosed())
                                            <div class="flex flex-col items-center leading-tight">
                                                <span>{{ Str::limit($ca->user->name ?? '?', 8) }}</span>
                                                <span class="text-[7px] font-normal opacity-80 mt-0.5"><i class="fas fa-{{ $ca->closed_at_time ? 'clock' : 'ban' }} mr-0.5"></i>{{ $ca->closed_at_time ? 'Selesai ' . substr($ca->closed_at_time, 0, 5) : 'Close' }}</span>
                                            </div>
                                        @else
                                            {{ Str::limit($ca->user->name ?? '?', 8) }}
                                        @endif
                                    </div>
                                    {{-- Action Popover --}}
                                    <div x-show="pop" x-transition.scale.origin.top @click.outside="pop = false" class="absolute z-30 top-full left-1/2 -translate-x-1/2 mt-1 bg-slate-800 border border-slate-600 rounded-xl shadow-2xl p-2 min-w-[140px]" style="display:none;">
                                        <div class="text-[9px] text-slate-400 px-2 py-1 border-b border-slate-700 mb-1 truncate"><b class="text-white">{{ $ca->user->name ?? '?' }}</b> · {{ \Carbon\Carbon::parse($dt)->translatedFormat('D d/m') }}</div>
                                        @if($ca->isClosed())
                                        <div class="text-[8px] text-red-400 px-2 py-0.5 mb-1"><i class="fas fa-lock mr-0.5"></i>{{ $ca->closed_by }} · {{ $ca->closed_reason }}</div>
                                        @endif
                                        <div class="flex flex-col gap-1">
                                            @if($ca->user_id == auth()->id() && $ca->isOpen())
                                                @if($ca->swap_status === 'pending')
                                                    <span class="text-[9px] text-orange-400 px-2 italic"><i class="fas fa-hourglass-half mr-1"></i>Menunggu Persetujuan</span>
                                                @elseif(\Carbon\Carbon::parse($dt)->startOfDay()->gte(\Carbon\Carbon::today()))
                                                    <button type="button" @click="pop=false; $dispatch('open-modal', 'swap-request-{{ $ca->id }}')" class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-[10px] font-bold text-orange-400 hover:bg-orange-500/10 transition-colors w-full text-left">
                                                        <i class="fas fa-exchange-alt w-3 text-center"></i> Ajukan Tukar Shift
                                                    </button>
                                                @endif
                                            @else
                                                <span class="text-[9px] text-slate-500 px-2 italic">Aksi tidak tersedia</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                        @endforeach
                        @if($scheduleLocations->isEmpty())
                        <tr>
                            <td colspan="{{ count($scheduleDates) + 1 }}" class="px-3 py-8 text-center text-slate-500 italic">
                                Belum ada jadwal shift untuk minggu ini.
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

@if(auth()->user()->role === 'kasir')
    @php
        $assignments = $scheduleAssignments;
        $locations = $scheduleLocations;
        $users = $activeUsers ?? collect();
    @endphp
    @include('schedules._modals_actions')
@endif
@endsection

@push('styles')
<style>
    /* Custom Scrollbar */
    .custom-scrollbar::-webkit-scrollbar { width: 4px; height: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: rgba(255, 255, 255, 0.02); border-radius: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.1); border-radius: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(255, 255, 255, 0.2); }
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in-up { animation: fadeInUp 0.5s ease-out forwards; }
</style>
@endpush

@push('scripts')
@if(auth()->user()->isOwner())
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
Chart.defaults.color = '#64748b';
Chart.defaults.font.family = "'Inter', sans-serif";

// 1. Area Chart (Trend Penjualan)
const salesCtx = document.getElementById('salesAreaChart');
if(salesCtx) {
    const ctx = salesCtx.getContext('2d');
    const chartData = @json($chartData ?? []);
    if(chartData.length > 0) {
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(6, 182, 212, 0.4)');
        gradient.addColorStop(1, 'rgba(6, 182, 212, 0.0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.map(d => d.date),
                datasets: [{
                    label: 'Omzet',
                    data: chartData.map(d => d.total),
                    borderColor: '#06b6d4',
                    backgroundColor: gradient,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#0f172a',
                    pointBorderColor: '#06b6d4',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.9)',
                        titleColor: '#cbd5e1',
                        bodyColor: '#22d3ee',
                        bodyFont: { weight: 'bold' },
                        padding: 12,
                        borderColor: 'rgba(6, 182, 212, 0.3)',
                        borderWidth: 1,
                        displayColors: false,
                        callbacks: {
                            label: function(context) { return 'Rp ' + context.parsed.y.toLocaleString('id-ID'); }
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                    y: { 
                        grid: { color: 'rgba(51, 65, 85, 0.2)', borderDash: [5, 5] }, 
                        border: { display: false },
                        ticks: { font: { size: 10 }, callback: v => 'Rp ' + (v/1000).toFixed(0) + 'k' } 
                    }
                },
                interaction: { intersect: false, mode: 'index' },
            }
        });
    }
}

// 2. Mini Sparklines Rendering
document.addEventListener("DOMContentLoaded", function() {
    const sparklines = document.querySelectorAll('.sparkline');
    sparklines.forEach(canvas => {
        const points = JSON.parse(canvas.dataset.points);
        const color = canvas.dataset.color;
        const ctx = canvas.getContext('2d');
        
        // Setup canvas resolution
        canvas.width = canvas.offsetWidth * 2;
        canvas.height = canvas.offsetHeight * 2;
        ctx.scale(2, 2);
        
        const width = canvas.offsetWidth;
        const height = canvas.offsetHeight;
        
        if (!points || points.length === 0) return;
        
        const max = Math.max(...points) || 1; // avoid div by 0
        const min = Math.min(...points);
        const range = max - min || 1;
        
        ctx.beginPath();
        points.forEach((val, i) => {
            const x = (i / (points.length - 1)) * width;
            // Pad 2px top and bottom
            const y = height - 2 - (((val - min) / range) * (height - 4));
            
            if (i === 0) ctx.moveTo(x, y);
            else ctx.lineTo(x, y);
        });
        
        ctx.strokeStyle = color;
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        ctx.stroke();
    });
});
</script>
@else
<script>
document.addEventListener("DOMContentLoaded", function() {
    const clockEl = document.getElementById('live-shift-clock');
    if(clockEl) {
        const startStr = clockEl.dataset.start;
        const startTime = new Date(startStr).getTime();
        
        setInterval(() => {
            const now = new Date().getTime();
            const diff = now - startTime;
            
            if(diff > 0) {
                const hours = Math.floor(diff / (1000 * 60 * 60));
                const mins = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                const secs = Math.floor((diff % (1000 * 60)) / 1000);
                
                clockEl.textContent = 
                    String(hours).padStart(2, '0') + ':' + 
                    String(mins).padStart(2, '0') + ':' + 
                    String(secs).padStart(2, '0');
            }
        }, 1000);
    }
});
</script>
@endif
@endpush
