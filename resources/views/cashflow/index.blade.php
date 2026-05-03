@extends('layouts.app')

@section('title', 'Cashflow Dashboard')
@section('page-title', 'Arus Kas')
@section('page-subtitle', 'Pantau pemasukan & pengeluaran bisnis secara real-time')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<style>
    /* Dark Theme Utilities */
    .bg-slate-900 { background-color: #0F172A; }
    .bg-slate-800 { background-color: #111827; }
    .hover\:bg-slate-700:hover { background-color: #1F2937; }
    /* Hide scrollbar for timeline */
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endpush

@section('content')
<div class="space-y-6 text-slate-200">
    
    <!-- 1. HEADER SECTION (VERSI BARU) -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-transparent mb-6">
        <div>
            <h1 class="text-3xl font-black text-white tracking-tight">Arus Kas</h1>
            <p class="text-sm text-slate-400 mt-1">Pantau pemasukan & pengeluaran bisnis secara real-time</p>
        </div>
        
            <!-- Segmented Period Buttons -->
            <div class="flex bg-[#111827] rounded-full p-1 shadow-sm border border-slate-700/50" id="periodFilterSegment">
                @php
                    $periods = [
                        'today' => 'Hari Ini',
                        'yesterday' => 'Kemarin',
                        'week' => 'Minggu Ini',
                        'month' => 'Bulan Ini',
                        'year' => 'Tahun Ini',
                        'custom' => 'Custom'
                    ];
                @endphp
                @foreach($periods as $val => $label)
                    <button type="button" data-filter="{{ $val }}" class="period-btn px-4 py-1.5 text-xs font-medium rounded-full transition-all {{ $filter == $val ? 'active text-white bg-blue-600 shadow' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            <!-- Custom Date Inputs (Hidden unless custom is selected) -->
            <div id="customDateContainer" class="{{ $filter == 'custom' ? 'flex' : 'hidden' }} items-center gap-2">
                <input type="date" id="customStart" value="{{ $start }}" class="bg-[#111827] border border-slate-700/50 rounded-full px-3 py-1.5 text-slate-300 text-xs focus:outline-none focus:border-blue-500">
                <span class="text-slate-500 text-xs">—</span>
                <input type="date" id="customEnd" value="{{ $end }}" class="bg-[#111827] border border-slate-700/50 rounded-full px-3 py-1.5 text-slate-300 text-xs focus:outline-none focus:border-blue-500">
                <button type="button" id="applyCustomBtn" class="bg-blue-600 hover:bg-blue-500 text-white rounded-full w-7 h-7 flex items-center justify-center text-xs transition-colors shadow" title="Terapkan">
                    <i class="fas fa-check"></i>
                </button>
            </div>

            <select id="sourceFilter" class="bg-[#111827] border border-slate-700/50 rounded-full px-4 py-2.5 text-slate-300 text-sm focus:outline-none focus:border-blue-500 appearance-none shadow-sm cursor-pointer hover:bg-[#1F2937] transition-colors">
                <option value="all" {{ $source == 'all' ? 'selected' : '' }}>Semua Sumber</option>
                <option value="pos_cash" {{ $source == 'pos_cash' ? 'selected' : '' }}>Tunai</option>
                <option value="pos_bank" {{ $source == 'pos_bank' ? 'selected' : '' }}>Bank/QRIS</option>
                <option value="transfer" {{ $source == 'transfer' ? 'selected' : '' }}>Transfer Kasir ke Bank</option>
            </select>
            
            <button onclick="document.getElementById('analysisModal').classList.remove('hidden')" class="bg-indigo-600 hover:bg-indigo-500 text-white font-medium px-4 py-2.5 rounded-full transition-all text-sm flex items-center gap-2">
                <i class="fas fa-chart-pie"></i> Analisis
            </button>

            <a href="{{ route('cashflow.export', ['filter' => $filter, 'source' => $source, 'start' => $start, 'end' => $end]) }}" id="exportBtn" class="bg-slate-700 hover:bg-slate-600 text-white font-medium px-4 py-2.5 rounded-full transition-all text-sm flex items-center gap-2">
                <i class="fas fa-file-export"></i> Ekspor
            </a>

            <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="bg-blue-600 hover:bg-blue-500 text-white font-medium px-4 py-2.5 rounded-full transition-all shadow-lg shadow-blue-900/20 active:scale-95 text-sm whitespace-nowrap flex items-center gap-2">
                <i class="fas fa-plus"></i> Transaksi
            </button>
        </div>
    </div>

    <!-- 2. SUMMARY STRIP -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Modal Usaha -->
        <div class="bg-[#111827] rounded-2xl p-5 border border-slate-700/50 relative overflow-hidden group hover:bg-[#1F2937] transition-colors">
            <div class="absolute right-0 top-0 w-32 h-32 bg-indigo-500/5 rounded-bl-full group-hover:bg-indigo-500/10 transition-colors"></div>
            <div class="flex justify-between items-start mb-2">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Modal Usaha</p>
                <div class="w-8 h-8 rounded-full bg-indigo-500/10 flex items-center justify-center text-indigo-400">
                    <i class="fas fa-vault text-sm"></i>
                </div>
            </div>
            @php
                $modalUsaha = 0;
                if ($activeWorksheet) {
                    $modalUsaha = $activeWorksheet->initial_balance;
                } elseif ($activeWorksheetId === 'all' && isset($userWorksheets)) {
                    $modalUsaha = $userWorksheets->sum('initial_balance');
                }
            @endphp
            <h3 class="text-3xl font-black text-white tracking-tight" id="valModalUsaha">Rp {{ number_format($modalUsaha, 0, ',', '.') }}</h3>
            <div class="mt-2 flex items-center gap-1.5 text-xs text-indigo-400 font-medium">
                <i class="fas fa-building"></i>
                <span>Saldo Awal Bisnis</span>
            </div>
        </div>
        <!-- Pemasukan -->
        <div class="bg-[#111827] rounded-2xl p-5 border border-slate-700/50 relative overflow-hidden group hover:bg-[#1F2937] transition-colors">
            <div class="absolute right-0 top-0 w-32 h-32 bg-emerald-500/5 rounded-bl-full group-hover:bg-emerald-500/10 transition-colors"></div>
            <div class="flex justify-between items-start mb-2">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Pemasukan</p>
                <div class="w-8 h-8 rounded-full bg-emerald-500/10 flex items-center justify-center text-emerald-400">
                    <i class="fas fa-arrow-down text-sm"></i>
                </div>
            </div>
            <h3 class="text-3xl font-black text-white tracking-tight" id="valTotalIncome">Rp {{ number_format($totalIncome, 0, ',', '.') }}</h3>
            <div class="mt-2 flex items-center gap-1.5 text-xs text-emerald-400 font-medium">
                <i class="fas fa-trend-up"></i>
                <span>Trend Positif</span>
            </div>
        </div>

        <!-- Pengeluaran -->
        <div class="bg-[#111827] rounded-2xl p-5 border border-slate-700/50 relative overflow-hidden group hover:bg-[#1F2937] transition-colors">
            <div class="absolute right-0 top-0 w-32 h-32 bg-red-500/5 rounded-bl-full group-hover:bg-red-500/10 transition-colors"></div>
            <div class="flex justify-between items-start mb-2">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Pengeluaran</p>
                <div class="w-8 h-8 rounded-full bg-red-500/10 flex items-center justify-center text-red-400">
                    <i class="fas fa-arrow-up text-sm"></i>
                </div>
            </div>
            <h3 class="text-3xl font-black text-white tracking-tight" id="valTotalExpense">Rp {{ number_format($totalExpense, 0, ',', '.') }}</h3>
            <div class="mt-2 flex items-center gap-1.5 text-xs text-red-400 font-medium">
                <i class="fas fa-trend-down"></i>
                <span>Pantau Pengeluaran</span>
            </div>
        </div>

        <!-- Saldo Bersih -->
        <div class="bg-[#111827] rounded-2xl p-5 border border-slate-700/50 relative overflow-hidden group hover:bg-[#1F2937] transition-colors">
            <div class="absolute right-0 top-0 w-32 h-32 bg-blue-500/5 rounded-bl-full group-hover:bg-blue-500/10 transition-colors"></div>
            <div class="flex justify-between items-start mb-2">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Saldo Bersih</p>
                <div class="w-8 h-8 rounded-full bg-blue-500/10 flex items-center justify-center text-blue-400">
                    <i class="fas fa-wallet text-sm"></i>
                </div>
            </div>
            <h3 class="text-3xl font-black {{ $netProfit >= 0 ? 'text-white' : 'text-red-400' }} tracking-tight" id="valNetProfit">
                {{ $netProfit < 0 ? '-' : '' }}Rp {{ number_format(abs($netProfit), 0, ',', '.') }}
            </h3>
            <div class="mt-2 flex items-center gap-1.5 text-xs text-blue-400 font-medium">
                <i class="fas fa-chart-pie"></i>
                <span>Net Profit</span>
            </div>
        </div>
    </div>

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
                    <p class="text-lg font-bold text-white mt-0.5" id="valAvgIncome">Rp {{ number_format($avgIncome, 0, ',', '.') }}</p>
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
                <div class="flex bg-[#0F172A] border border-slate-700 rounded-full p-1 shrink-0">
                    <button class="filter-btn active px-3 py-1 text-xs font-medium rounded-full bg-slate-700 text-white transition-all" data-filter="all">Semua</button>
                    <button class="filter-btn px-3 py-1 text-xs font-medium rounded-full text-slate-400 hover:text-slate-200 transition-all" data-filter="income">Masuk</button>
                    <button class="filter-btn px-3 py-1 text-xs font-medium rounded-full text-slate-400 hover:text-slate-200 transition-all" data-filter="expense">Keluar</button>
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

{{-- Modal Add --}}
<div id="addModal" class="fixed inset-0 bg-[#0F172A]/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-[#111827] rounded-2xl w-full max-w-md p-6 border border-slate-700 shadow-2xl transform scale-100 transition-transform">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-bold text-white">Tambah Transaksi</h3>
            <button onclick="document.getElementById('addModal').classList.add('hidden')" class="text-slate-400 hover:text-white transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form action="{{ route('cashflow.store') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1.5 uppercase tracking-wider">Tanggal</label>
                    <input type="date" name="transaction_date" value="{{ date('Y-m-d') }}" class="w-full bg-[#0F172A] border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required>
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1.5 uppercase tracking-wider">Jenis Transaksi</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="cursor-pointer relative">
                            <input type="radio" name="type" value="income" class="peer sr-only">
                            <div class="w-full text-center px-4 py-2.5 bg-[#0F172A] border border-slate-700 rounded-xl text-sm font-medium text-slate-400 peer-checked:bg-emerald-500/10 peer-checked:border-emerald-500/50 peer-checked:text-emerald-400 transition-all">
                                Pemasukan
                            </div>
                        </label>
                        <label class="cursor-pointer relative">
                            <input type="radio" name="type" value="expense" class="peer sr-only" checked>
                            <div class="w-full text-center px-4 py-2.5 bg-[#0F172A] border border-slate-700 rounded-xl text-sm font-medium text-slate-400 peer-checked:bg-red-500/10 peer-checked:border-red-500/50 peer-checked:text-red-400 transition-all">
                                Pengeluaran
                            </div>
                        </label>
                    </div>
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1.5 uppercase tracking-wider">Sumber Dana</label>
                    <select name="source" class="w-full bg-[#0F172A] border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required>
                        <option value="pos_cash">Tunai</option>
                        <option value="pos_bank">Bank / QRIS</option>
                        <option value="transfer">Transfer Kasir ke Bank</option>
                        <option value="manual">Lainnya (Manual)</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1.5 uppercase tracking-wider">Kategori</label>
                    <input type="text" name="category" class="w-full bg-[#0F172A] border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required placeholder="Cth: Listrik, Gaji, ATK...">
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1.5 uppercase tracking-wider">Nominal (Rp)</label>
                    <input type="number" name="amount" class="w-full bg-[#0F172A] border border-slate-700 rounded-xl px-4 py-2.5 text-lg font-bold text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 placeholder-slate-600" required min="1" placeholder="0">
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1.5 uppercase tracking-wider">Catatan</label>
                    <textarea name="description" rows="2" class="w-full bg-[#0F172A] border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required placeholder="Detail transaksi..."></textarea>
                </div>
                
                <div class="pt-4 mt-6 border-t border-slate-700">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 rounded-xl transition-colors shadow-lg shadow-blue-500/20">
                        Simpan Transaksi
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Modal Analysis --}}
<div id="analysisModal" class="fixed inset-0 bg-[#0F172A]/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-[#111827] rounded-2xl w-full max-w-md p-6 border border-slate-700 shadow-2xl transform scale-100 transition-transform">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-bold text-white"><i class="fas fa-chart-pie text-indigo-400 mr-2"></i> Analisis Cepat</h3>
            <button onclick="document.getElementById('analysisModal').classList.add('hidden')" class="text-slate-400 hover:text-white transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="space-y-4">
            <div class="bg-[#0F172A] rounded-xl p-4 border border-slate-700/50 flex justify-between items-center">
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Rata-rata Pemasukan / Hari</p>
                    <p class="text-xl font-black text-white mt-1" id="analysisAvgIncome">Rp {{ number_format($avgIncome, 0, ',', '.') }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-emerald-500/10 flex items-center justify-center text-emerald-400">
                    <i class="fas fa-calendar-day"></i>
                </div>
            </div>

            <div class="bg-[#0F172A] rounded-xl p-4 border border-slate-700/50">
                <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold mb-3">Rasio Pemasukan vs Pengeluaran</p>
                <div class="w-full bg-slate-800 rounded-full h-3 overflow-hidden flex" id="analysisRatioBar">
                    @php 
                        $total = $totalIncome + $totalExpense;
                        $incPct = $total > 0 ? ($totalIncome / $total) * 100 : 50;
                        $expPct = $total > 0 ? ($totalExpense / $total) * 100 : 50;
                    @endphp
                    <div style="width: {{ $incPct }}%" class="bg-emerald-500 transition-all duration-500"></div>
                    <div style="width: {{ $expPct }}%" class="bg-red-500 transition-all duration-500"></div>
                </div>
                <div class="flex justify-between items-center mt-2 text-xs">
                    <span class="text-emerald-400 font-bold" id="analysisIncPct">{{ round($incPct) }}%</span>
                    <span class="text-red-400 font-bold" id="analysisExpPct">{{ round($expPct) }}%</span>
                </div>
            </div>

            <div class="bg-[#0F172A] rounded-xl p-4 border border-slate-700/50">
                <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold mb-3">Tren Pemasukan</p>
                <div class="flex items-center gap-3">
                    <div id="analysisTrendIcon" class="w-10 h-10 rounded-full {{ $trend == 'up' ? 'bg-emerald-500/10 text-emerald-400' : ($trend == 'down' ? 'bg-red-500/10 text-red-400' : 'bg-slate-500/10 text-slate-400') }} flex items-center justify-center">
                        <i class="fas {{ $trend == 'up' ? 'fa-arrow-trend-up' : ($trend == 'down' ? 'fa-arrow-trend-down' : 'fa-minus') }}"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-white" id="analysisTrendText">
                            {{ $trend == 'up' ? 'Meningkat' : ($trend == 'down' ? 'Menurun' : 'Stabil') }}
                        </p>
                        <p class="text-xs text-slate-400">Dibandingkan periode sebelumnya</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
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
        const exportBtn = document.getElementById('exportBtn');
        const customStart = document.getElementById('customStart');
        const customEnd = document.getElementById('customEnd');
        const customDateContainer = document.getElementById('customDateContainer');

        const fetchData = async (page = 1) => {
            const source = sourceFilter.value;
            const start = customStart.value;
            const end = customEnd.value;

            // Update Export URL
            exportBtn.href = `/cashflow/export?filter=${currentFilter}&source=${source}&start=${start}&end=${end}`;

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
                npEl.className = `text-3xl font-black tracking-tight ${data.summary.netProfitNegative ? 'text-red-400' : 'text-white'}`;

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
                    b.classList.remove('active', 'text-white', 'bg-blue-600', 'shadow');
                    b.classList.add('text-slate-400', 'hover:text-white', 'hover:bg-slate-800');
                });
                
                btn.classList.remove('text-slate-400', 'hover:text-white', 'hover:bg-slate-800');
                btn.classList.add('active', 'text-white', 'bg-blue-600', 'shadow');

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
                alert('Silakan pilih rentang tanggal mulai dan akhir.');
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

            txItems.forEach(item => {
                const type = item.dataset.type;
                const text = item.innerText.toLowerCase();
                
                const matchesFilter = activeFilter === 'all' || type === activeFilter;
                const matchesSearch = text.includes(searchTerm);

                if (matchesFilter && matchesSearch) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
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
</script>
@endpush
@endsection
