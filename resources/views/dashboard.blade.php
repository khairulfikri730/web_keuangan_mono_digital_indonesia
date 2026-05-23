@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Selamat datang, ' . auth()->user()->name)

@section('content')
<div class="space-y-6">

    {{-- Shift Alert --}}
    @if($activeShift)
        <div class="flex items-center gap-3 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-5 py-4 rounded-2xl">
            <div class="w-2.5 h-2.5 bg-emerald-400 rounded-full animate-pulse"></div>
            <div>
                <p class="font-semibold">Shift Aktif — Dibuka oleh {{ $activeShift->opener->name }}</p>
                <p class="text-sm text-emerald-300/70">Dibuka pada {{ $activeShift->opened_at->format('d M Y, H:i') }}</p>
            </div>
            <a href="{{ route('pos.index') }}" class="ml-auto btn-success whitespace-nowrap"><i class="fas fa-cash-register mr-1"></i> Buka POS</a>
        </div>
    @elseif($productCount > 0)
        <div class="flex items-center gap-3 bg-red-500/15 border border-red-500/30 text-red-400 px-5 py-4 rounded-2xl">
            <i class="fas fa-triangle-exclamation text-xl"></i>
            <div>
                <p class="font-semibold">Shift Belum Dibuka!</p>
                <p class="text-sm text-red-300/70">Buka shift terlebih dahulu agar kasir bisa beroperasi.</p>
            </div>
            @if(auth()->user()->isOwner())
            <a href="{{ route('shifts.index', ['open' => 1]) }}" class="ml-auto btn-primary whitespace-nowrap">Buka Shift</a>
            @endif
        </div>
    @endif

    {{-- Stats Cards --}}
    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <div class="stat-card hover:scale-105 hover:shadow-xl hover:shadow-emerald-900/20 transition-all duration-300 relative overflow-hidden group">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-emerald-500/10 rounded-full blur-2xl group-hover:bg-emerald-500/20 transition-all"></div>
            <div class="w-14 h-14 bg-gradient-to-br from-emerald-500/20 to-emerald-500/5 border border-emerald-500/20 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-inner">
                <i class="fas fa-sack-dollar text-emerald-400 text-xl"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Penjualan Hari Ini</p>
                <p class="text-2xl font-bold text-white">Rp {{ number_format($todaySales, 0, ',', '.') }}</p>
            </div>
        </div>
        <div class="stat-card hover:scale-105 hover:shadow-xl hover:shadow-blue-900/20 transition-all duration-300 relative overflow-hidden group">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-blue-500/10 rounded-full blur-2xl group-hover:bg-blue-500/20 transition-all"></div>
            <div class="w-14 h-14 bg-gradient-to-br from-blue-500/20 to-blue-500/5 border border-blue-500/20 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-inner">
                <i class="fas fa-receipt text-blue-400 text-xl"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Transaksi Hari Ini</p>
                <p class="text-2xl font-bold text-white">{{ $todayTransactions }} <span class="text-sm font-normal text-slate-500">struk</span></p>
            </div>
        </div>
        <div class="stat-card hover:scale-105 hover:shadow-xl hover:shadow-red-900/20 transition-all duration-300 relative overflow-hidden group">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-red-500/10 rounded-full blur-2xl group-hover:bg-red-500/20 transition-all"></div>
            <div class="w-14 h-14 bg-gradient-to-br from-red-500/20 to-red-500/5 border border-red-500/20 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-inner">
                <i class="fas fa-money-bill-wave text-red-400 text-xl"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Pengeluaran Hari Ini</p>
                <p class="text-2xl font-bold text-white">Rp {{ number_format($todayExpenses, 0, ',', '.') }}</p>
            </div>
        </div>
        <div class="stat-card hover:scale-105 hover:shadow-xl hover:shadow-purple-900/20 transition-all duration-300 relative overflow-hidden group">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-purple-500/10 rounded-full blur-2xl group-hover:bg-purple-500/20 transition-all"></div>
            <div class="w-14 h-14 bg-gradient-to-br from-purple-500/20 to-purple-500/5 border border-purple-500/20 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-inner">
                <i class="fas fa-hand-holding-dollar text-purple-400 text-xl"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Pendapatan Bersih Hari Ini</p>
                <p class="text-2xl font-bold text-white">Rp {{ number_format($todayNetProfit, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
        {{-- Chart --}}
        <div class="lg:col-span-2 card p-6 shadow-lg shadow-blue-900/5 relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-64 h-64 bg-blue-500/5 rounded-full blur-3xl group-hover:bg-blue-500/10 transition-all pointer-events-none"></div>
            <div class="flex items-center justify-between mb-6 relative z-10">
                <div>
                    <h3 class="text-lg font-bold text-white">Trend Penjualan</h3>
                    <p class="text-sm text-slate-500">Pergerakan omzet 7 hari terakhir</p>
                </div>
                <div class="w-10 h-10 bg-slate-700/50 rounded-xl flex items-center justify-center">
                    <i class="fas fa-chart-area text-blue-400"></i>
                </div>
            </div>
            <div class="relative z-10 h-[250px] w-full">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        {{-- Low Stock --}}
        <div class="card p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-white">Stok Menipis</h3>
                <span class="badge-red">{{ $lowStockProducts->count() }} produk</span>
            </div>
            @forelse($lowStockProducts as $p)
            <div class="flex items-center gap-3 py-2 border-b border-slate-700/50 last:border-0">
                <div class="w-8 h-8 bg-slate-700 rounded-lg flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-box text-slate-400 text-xs"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-white truncate">{{ $p->name }}</p>
                    <p class="text-xs text-slate-500">{{ $p->unit }}</p>
                </div>
                <span class="text-sm font-bold {{ $p->stock === 0 ? 'text-red-400' : 'text-yellow-400' }}">
                    {{ $p->stock }}
                </span>
            </div>
            @empty
            <p class="text-sm text-slate-500 text-center py-4"><i class="fas fa-check-circle text-emerald-400 mr-1"></i>Semua stok aman</p>
            @endforelse
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
        {{-- Recent Transactions --}}
        <div class="card p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-white">Transaksi Terbaru</h3>
                <a href="{{ route('transactions.index') }}" class="text-xs text-blue-400 hover:text-blue-300">Lihat Semua</a>
            </div>
            <div class="space-y-3">
                @forelse($recentTransactions as $t)
                <div class="flex items-center gap-4 p-4 bg-slate-900/50 border border-slate-700/50 rounded-2xl hover:border-blue-500/50 hover:bg-slate-800 transition-all group cursor-pointer">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500/20 to-blue-500/5 border border-blue-500/20 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                        <i class="fas fa-receipt text-blue-400 text-sm"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-white truncate">{{ $t->invoice_number }}</p>
                        <p class="text-xs text-slate-400 mt-0.5"><i class="far fa-clock mr-1"></i>{{ $t->created_at->format('H:i') }} &nbsp;•&nbsp; <i class="far fa-user mr-1"></i>{{ $t->user->name }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-emerald-400">Rp {{ number_format($t->total, 0, ',', '.') }}</p>
                        <span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $t->status === 'completed' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-red-500/20 text-red-400' }}">{{ ucfirst($t->status) }}</span>
                    </div>
                </div>
                @empty
                <div class="py-8 text-center bg-slate-900/50 rounded-2xl border border-slate-700/50 border-dashed">
                    <i class="fas fa-receipt text-3xl text-slate-600 mb-2"></i>
                    <p class="text-sm text-slate-400">Belum ada transaksi hari ini</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Top Products --}}
        <div class="card p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-white">Produk Terlaris</h3>
                <span class="text-xs text-slate-500">Hari Ini</span>
            </div>
            <div class="space-y-3">
                @forelse($topProducts as $i => $p)
                <div class="flex items-center gap-3">
                    <span class="text-xs font-bold text-slate-600 w-5">{{ $i+1 }}</span>
                    <div class="flex-1">
                        <p class="text-sm text-white">{{ $p->product_name }}</p>
                        <div class="h-1.5 bg-slate-700 rounded-full mt-1">
                            <div class="h-full bg-blue-500 rounded-full" style="width: {{ $topProducts->first() ? ($p->total_qty / $topProducts->first()->total_qty * 100) : 0 }}%"></div>
                        </div>
                    </div>
                    <span class="text-xs text-slate-400">{{ $p->total_qty }} terjual</span>
                </div>
                @empty
                <p class="text-sm text-slate-500 text-center py-4">Belum ada data produk</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('salesChart').getContext('2d');
const chartData = @json($chartData);
new Chart(ctx, {
    type: 'line',
    data: {
        labels: chartData.map(d => d.date),
        datasets: [{
            label: 'Penjualan',
            data: chartData.map(d => d.total),
            borderColor: '#3b82f6',
            backgroundColor: (context) => {
                const ctx = context.chart.ctx;
                const gradient = ctx.createLinearGradient(0, 0, 0, 300);
                gradient.addColorStop(0, 'rgba(59,130,246,0.3)');
                gradient.addColorStop(1, 'rgba(59,130,246,0.0)');
                return gradient;
            },
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#0f172a',
            pointBorderColor: '#3b82f6',
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
                backgroundColor: '#1e293b',
                titleColor: '#94a3b8',
                bodyColor: '#10b981',
                bodyFont: { weight: 'bold' },
                padding: 12,
                borderColor: '#334155',
                borderWidth: 1,
                displayColors: false,
                callbacks: {
                    label: function(context) {
                        return 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                    }
                }
            }
        },
        scales: {
            x: { grid: { display: false }, ticks: { color: '#64748b', font: { size: 12, family: "'Inter', sans-serif" } } },
            y: { 
                grid: { color: '#334155', borderDash: [5, 5] }, 
                border: { display: false },
                ticks: { color: '#64748b', font: { size: 12, family: "'Inter', sans-serif" }, callback: v => 'Rp ' + (v/1000).toFixed(0) + 'k' } 
            }
        },
        interaction: {
            intersect: false,
            mode: 'index',
        },
    }
});
</script>
@endpush
