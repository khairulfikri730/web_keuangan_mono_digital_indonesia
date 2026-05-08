@extends('layouts.app')

@section('title', 'Laporan Laba Rugi Enterprise')
@section('page-title', 'Financial Analytics')
@section('page-subtitle', 'Analisis Mendalam Performa Bisnis')

@section('content')
<div class="flex flex-col gap-6" x-data="financialDashboard()">
    
    {{-- 1. PREMIUM HEADER --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-2">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-3xl font-black text-white tracking-tight">Financial Report</h1>
                <div class="px-3 py-1 rounded-full text-[10px] font-black tracking-widest border {{ $health == 'healthy' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : ($health == 'warning' ? 'bg-amber-500/10 text-amber-400 border-amber-500/20' : 'bg-red-500/10 text-red-400 border-red-500/20') }}">
                    <i class="fas fa-circle mr-1 animate-pulse"></i>
                    {{ strtoupper($health == 'healthy' ? 'Bisnis Sehat' : ($health == 'warning' ? 'Waspada' : 'Kritis')) }}
                </div>
            </div>
            <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mt-1">Laporan Laba Rugi & Analisis ROI • {{ date('F Y', mktime(0, 0, 0, $month, 1)) }}</p>
        </div>
        
        <div class="flex flex-wrap items-center gap-3">
            {{-- Filter Bar --}}
            <form id="filterForm" method="GET" class="flex bg-slate-800/80 backdrop-blur-md rounded-2xl p-1.5 border border-slate-700/50 shadow-xl">
                <select name="month" class="bg-transparent border-none text-xs font-black text-slate-300 focus:ring-0 cursor-pointer pr-8" onchange="this.form.submit()">
                    @for($m=1; $m<=12; $m++)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }} class="bg-slate-800">{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                    @endfor
                </select>
                <div class="w-px h-4 bg-slate-700 self-center mx-2"></div>
                <select name="year" class="bg-transparent border-none text-xs font-black text-slate-300 focus:ring-0 cursor-pointer pr-8" onchange="this.form.submit()">
                    @for($y=date('Y'); $y>=2020; $y--)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }} class="bg-slate-800">{{ $y }}</option>
                    @endfor
                </select>
            </form>

            <button onclick="window.openExportModal()" class="flex items-center gap-2 px-5 py-3 bg-blue-600 hover:bg-blue-500 text-white rounded-2xl font-black text-xs transition-premium shadow-lg shadow-blue-600/20 transform hover:-translate-y-1">
                <i class="fas fa-file-export"></i>
                EXPORT REPORT
            </button>
        </div>
    </div>

    {{-- 2. KPI SUMMARY BAR --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Revenue --}}
        <div class="bg-slate-800/40 backdrop-blur-xl border border-white/5 rounded-3xl p-5 shadow-xl relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-blue-500/10 rounded-full blur-2xl group-hover:bg-blue-500/20 transition-all"></div>
            <div class="relative z-10">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.15em] mb-1">Total Omzet</p>
                <div class="flex items-baseline gap-2">
                    <h3 class="text-2xl font-black text-white">Rp{{ number_format($income, 0, ',', '.') }}</h3>
                    <span class="text-[10px] font-bold {{ $growth['income'] >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                        <i class="fas {{ $growth['income'] >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }} mr-0.5"></i>{{ abs($growth['income']) }}%
                    </span>
                </div>
            </div>
        </div>

        {{-- Net Profit --}}
        <div class="bg-slate-800/40 backdrop-blur-xl border border-white/5 rounded-3xl p-5 shadow-xl relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-emerald-500/10 rounded-full blur-2xl group-hover:bg-emerald-500/20 transition-all"></div>
            <div class="relative z-10">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.15em] mb-1">Laba Bersih</p>
                <div class="flex items-baseline gap-2">
                    <h3 class="text-2xl font-black text-white">Rp{{ number_format($profit, 0, ',', '.') }}</h3>
                    <span class="text-[10px] font-bold {{ $growth['profit'] >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                        <i class="fas {{ $growth['profit'] >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }} mr-0.5"></i>{{ abs($growth['profit']) }}%
                    </span>
                </div>
            </div>
        </div>

        {{-- Transaction Count --}}
        <div class="bg-slate-800/40 backdrop-blur-xl border border-white/5 rounded-3xl p-5 shadow-xl relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-purple-500/10 rounded-full blur-2xl group-hover:bg-purple-500/20 transition-all"></div>
            <div class="relative z-10">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.15em] mb-1">Total Transaksi</p>
                <div class="flex items-baseline gap-2">
                    <h3 class="text-2xl font-black text-white">{{ number_format($transactionCount, 0, ',', '.') }}</h3>
                    <span class="text-[10px] font-bold text-slate-500">Sales</span>
                </div>
            </div>
        </div>

        {{-- Net Margin --}}
        <div class="bg-slate-800/40 backdrop-blur-xl border border-white/5 rounded-3xl p-5 shadow-xl relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-amber-500/10 rounded-full blur-2xl group-hover:bg-amber-500/20 transition-all"></div>
            <div class="relative z-10">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.15em] mb-1">Net Margin %</p>
                <div class="flex items-baseline gap-2">
                    <h3 class="text-2xl font-black text-white">{{ round($margins['net']) }}%</h3>
                    <div class="w-16 h-1.5 bg-slate-700 rounded-full self-center ml-2 overflow-hidden">
                        <div class="h-full {{ $margins['net'] > 20 ? 'bg-emerald-500' : ($margins['net'] > 10 ? 'bg-amber-500' : 'bg-red-500') }}" style="width: {{ min(100, $margins['net']) }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. MAIN ANALYTICS GRID --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        {{-- LEFT COLUMN (8 Cols) --}}
        <div class="lg:col-span-8 space-y-6">
            
            {{-- Trend Chart --}}
            <div class="bg-slate-800/40 backdrop-blur-xl border border-white/5 rounded-[40px] p-8 shadow-2xl relative overflow-hidden">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h3 class="text-xl font-black text-white tracking-tight">Tren Laba Bersih</h3>
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mt-1">Performa Bulanan {{ $year }}</p>
                    </div>
                    <div class="flex gap-2">
                        <div class="flex items-center gap-1.5">
                            <div class="w-3 h-3 rounded-full bg-blue-500"></div>
                            <span class="text-[10px] font-black text-slate-400 uppercase">Omzet</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                            <span class="text-[10px] font-black text-slate-400 uppercase">Profit</span>
                        </div>
                    </div>
                </div>
                
                <div class="h-80 relative">
                    <canvas id="profitTrendChart"></canvas>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Income Breakdown --}}
                <div class="bg-slate-800/40 backdrop-blur-xl border border-white/5 rounded-[40px] p-8 shadow-2xl group">
                    <h3 class="text-lg font-black text-white mb-6 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-emerald-500/20 flex items-center justify-center text-emerald-400 shadow-lg shadow-emerald-500/10">
                            <i class="fas fa-arrow-down-wide-short"></i>
                        </div>
                        Pemasukan & HPP
                    </h3>
                    
                    <div class="space-y-6">
                        <div>
                            <div class="flex justify-between items-end mb-2">
                                <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Penjualan POS</span>
                                <span class="text-sm font-black text-white">Rp{{ number_format($salesTotal, 0, ',', '.') }}</span>
                            </div>
                            <div class="h-2 w-full bg-slate-900 rounded-full overflow-hidden">
                                <div class="h-full bg-blue-500 rounded-full" style="width: 100%"></div>
                            </div>
                        </div>
                        
                        <div>
                            <div class="flex justify-between items-end mb-2">
                                <span class="text-xs font-black text-slate-400 uppercase tracking-widest">HPP (Modal Barang)</span>
                                <span class="text-sm font-black text-red-400">- Rp{{ number_format($cogs, 0, ',', '.') }}</span>
                            </div>
                            <div class="h-2 w-full bg-slate-900 rounded-full overflow-hidden">
                                <div class="h-full bg-red-500/50 rounded-full" style="width: {{ $salesTotal > 0 ? ($cogs / $salesTotal) * 100 : 0 }}%"></div>
                            </div>
                            <p class="text-[10px] font-bold text-slate-500 mt-1.5 uppercase">{{ round($salesTotal > 0 ? ($cogs / $salesTotal) * 100 : 0) }}% Kontribusi dari Omzet</p>
                        </div>

                        <div class="pt-4 border-t border-slate-700/50 mt-4">
                            <div class="flex justify-between items-center">
                                <span class="text-xs font-black text-emerald-400 uppercase tracking-[0.2em]">Gross Profit</span>
                                <span class="text-xl font-black text-emerald-400">Rp{{ number_format($grossProfit, 0, ',', '.') }}</span>
                            </div>
                            <p class="text-[10px] font-bold text-slate-500 mt-1 uppercase">Margin Kotor: {{ round($margins['gross']) }}%</p>
                        </div>
                    </div>
                </div>

                {{-- Expense Breakdown --}}
                <div class="bg-slate-800/40 backdrop-blur-xl border border-white/5 rounded-[40px] p-8 shadow-2xl">
                    <h3 class="text-lg font-black text-white mb-6 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-red-500/20 flex items-center justify-center text-red-400 shadow-lg shadow-red-500/10">
                            <i class="fas fa-receipt"></i>
                        </div>
                        Biaya Operasional
                    </h3>

                    <div class="space-y-5">
                        @foreach($expenseDetails->take(4) as $exp)
                        @php $ratio = $expense > 0 ? ($exp->total / $expense) * 100 : 0; @endphp
                        <div>
                            <div class="flex justify-between items-center mb-1.5">
                                <span class="text-xs font-bold text-slate-300 truncate w-32">{{ $exp->category }}</span>
                                <span class="text-xs font-black text-white">Rp{{ number_format($exp->total, 0, ',', '.') }}</span>
                            </div>
                            <div class="h-1.5 w-full bg-slate-900 rounded-full overflow-hidden">
                                <div class="h-full bg-amber-500 rounded-full" style="width: {{ $ratio }}%"></div>
                            </div>
                        </div>
                        @endforeach
                        
                        @if($expenseDetails->count() > 4)
                        <div class="text-center pt-2">
                            <button class="text-[10px] font-black text-blue-400 hover:text-blue-300 uppercase tracking-widest">+ {{ $expenseDetails->count() - 4 }} Kategori Lainnya</button>
                        </div>
                        @endif

                        <div class="pt-4 border-t border-slate-700/50 mt-4">
                            <div class="flex justify-between items-center">
                                <span class="text-xs font-black text-slate-400 uppercase tracking-[0.2em]">Total Pengeluaran</span>
                                <span class="text-xl font-black text-white">Rp{{ number_format($expense, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN (4 Cols) --}}
        <div class="lg:col-span-4 space-y-6">
            
            {{-- AI INSIGHTS CARD --}}
            <div class="bg-gradient-to-br from-indigo-600 to-blue-700 rounded-[40px] p-8 shadow-2xl shadow-blue-900/20 relative overflow-hidden border border-white/10 group">
                <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-white/10 rounded-full blur-3xl group-hover:scale-125 transition-transform duration-700"></div>
                <div class="absolute -left-10 -top-10 w-40 h-40 bg-blue-400/10 rounded-full blur-3xl group-hover:scale-125 transition-transform duration-700"></div>
                
                <h3 class="text-lg font-black text-white mb-6 flex items-center gap-3 relative z-10">
                    <i class="fas fa-brain animate-pulse text-white/80"></i>
                    Insight Bisnis & AI
                </h3>

                <div class="space-y-4 relative z-10">
                    @foreach($insights as $insight)
                    <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/5 hover:bg-white/15 transition-colors cursor-default">
                        <div class="flex items-center gap-2 mb-1">
                            <i class="fas {{ $insight['type'] == 'success' ? 'fa-check-circle text-emerald-300' : ($insight['type'] == 'warning' ? 'fa-exclamation-circle text-amber-300' : 'fa-info-circle text-blue-200') }} text-xs"></i>
                            <h4 class="text-[11px] font-black text-white uppercase tracking-wider">{{ $insight['title'] }}</h4>
                        </div>
                        <p class="text-[10px] leading-relaxed text-blue-50/80 font-medium">{{ $insight['text'] }}</p>
                    </div>
                    @endforeach
                </div>

                <div class="mt-6 pt-4 border-t border-white/10 relative z-10 text-center">
                    <p class="text-[9px] font-bold text-blue-100/50 uppercase tracking-widest italic">Dianalisis secara otomatis berdasarkan tren data terbaru</p>
                </div>
            </div>

            {{-- ROI & BEP PROGRESS --}}
            <div class="bg-slate-800/40 backdrop-blur-xl border border-white/5 rounded-[40px] p-8 shadow-2xl relative overflow-hidden">
                <h3 class="text-lg font-black text-white mb-6 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-amber-500/20 flex items-center justify-center text-amber-400 shadow-lg shadow-amber-500/10">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    Analisis ROI & BEP
                </h3>

                @php 
                    $bepProgress = $totalCapital > 0 ? min(100, ($allTimeNetProfit / $totalCapital) * 100) : 0;
                @endphp

                <div class="flex flex-col items-center text-center mb-6">
                    <div class="relative w-32 h-32 mb-4">
                        <svg class="w-full h-full transform -rotate-90">
                            <circle cx="64" cy="64" r="58" stroke="currentColor" stroke-width="12" fill="transparent" class="text-slate-700/50" />
                            <circle cx="64" cy="64" r="58" stroke="currentColor" stroke-width="12" fill="transparent" 
                                class="{{ $bepProgress >= 100 ? 'text-emerald-500' : 'text-blue-500' }} transition-all duration-1000" 
                                style="stroke-dasharray: 364.4; stroke-dashoffset: {{ 364.4 - (364.4 * $bepProgress / 100) }}" />
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-2xl font-black text-white">{{ round($bepProgress) }}%</span>
                            <span class="text-[8px] font-black text-slate-500 uppercase tracking-widest">MENUJU BEP</span>
                        </div>
                    </div>
                    
                    <p class="text-xs font-bold text-slate-300">
                        {{ $bepProgress >= 100 ? 'SELAMAT! MODAL SUDAH KEMBALI' : 'Bisnis Anda dalam jalur balik modal.' }}
                    </p>
                </div>

                <div class="space-y-4 pt-4 border-t border-slate-700/50">
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] font-black text-slate-500 uppercase">Modal Investasi</span>
                        <span class="text-xs font-black text-white">Rp{{ number_format($totalCapital, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] font-black text-slate-500 uppercase">Sisa Target Profit</span>
                        <span class="text-xs font-black text-red-400">Rp{{ number_format($remainingToPayback, 0, ',', '.') }}</span>
                    </div>
                    @if($paybackPeriodMonths)
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] font-black text-slate-500 uppercase">Estimasi Selesai</span>
                        <span class="text-xs font-black text-emerald-400">{{ ceil($paybackPeriodMonths) }} Bulan Lagi</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- TOP PRODUCTS --}}
            <div class="bg-slate-800/40 backdrop-blur-xl border border-white/5 rounded-[40px] p-8 shadow-2xl">
                <h3 class="text-lg font-black text-white mb-6 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-purple-500/20 flex items-center justify-center text-purple-400 shadow-lg shadow-purple-500/10">
                        <i class="fas fa-crown"></i>
                    </div>
                    Top Profit Products
                </h3>

                <div class="space-y-4">
                    @foreach($topProducts as $idx => $prod)
                    <div class="flex items-center gap-3 group">
                        <div class="w-8 h-8 rounded-lg bg-slate-900 border border-white/5 flex items-center justify-center text-[10px] font-black text-slate-500 group-hover:text-emerald-400 transition-colors">
                            #{{ $idx + 1 }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-white truncate">{{ $prod->product_name }}</p>
                            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">{{ $prod->total_qty }} Unit Terjual</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-black text-emerald-400">Rp{{ number_format($prod->profit, 0, ',', '.') }}</p>
                            <p class="text-[9px] font-bold text-slate-600 uppercase">Gross Profit</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
    function financialDashboard() {
        return {
            init() {
                this.renderChart();
            },
            renderChart() {
                const ctx = document.getElementById('profitTrendChart').getContext('2d');
                const trendData = @json($trendData);
                
                const labels = trendData.map(d => d.month);
                const profitData = trendData.map(d => d.profit);
                const incomeData = trendData.map(d => d.income);

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Omzet',
                                data: incomeData,
                                borderColor: 'rgba(59, 130, 246, 1)',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                borderWidth: 3,
                                fill: true,
                                tension: 0.4,
                                pointBackgroundColor: '#0f172a',
                                pointBorderWidth: 2,
                                pointRadius: 4,
                                pointHoverRadius: 6
                            },
                            {
                                label: 'Profit',
                                data: profitData,
                                borderColor: 'rgba(16, 185, 129, 1)',
                                backgroundColor: 'transparent',
                                borderWidth: 3,
                                fill: false,
                                tension: 0.4,
                                pointBackgroundColor: '#0f172a',
                                pointBorderWidth: 2,
                                pointRadius: 4,
                                pointHoverRadius: 6
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: 'rgba(255, 255, 255, 0.05)', drawBorder: false },
                                ticks: {
                                    color: 'rgba(148, 163, 184, 0.5)',
                                    font: { size: 10, weight: 'bold' },
                                    callback: function(value) {
                                        if (value >= 1000000) return (value / 1000000).toFixed(1) + 'jt';
                                        return value;
                                    }
                                }
                            },
                            x: {
                                grid: { display: false },
                                ticks: {
                                    color: 'rgba(148, 163, 184, 0.8)',
                                    font: { size: 10, weight: 'black' }
                                }
                            }
                        }
                    }
                });
            }
        }
    }
</script>
@endpush
@endsection
