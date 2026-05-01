@extends('layouts.app')

@section('title', 'Rekap Penjualan & Margin')
@section('page-title', 'Dashboard Analytics')
@section('page-subtitle', 'Rekap Penjualan & Margin Profit')

@section('content')
<div x-data="analyticsDashboard()" class="flex flex-col gap-6">

    {{-- FILTER BAR MODERN --}}
    <div class="bg-white/5 backdrop-blur-md rounded-2xl p-5 md:p-6 border border-white/5 shadow-sm relative z-40">
        <form method="GET" class="flex flex-col md:flex-row gap-5 items-end md:items-center w-full">
            <div class="flex-1 w-full grid grid-cols-1 md:grid-cols-4 gap-5">
                <div class="col-span-1 md:col-span-2 flex items-center gap-3">
                    <div class="w-full relative group">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1.5 block"><i class="far fa-calendar-alt mr-1"></i> Tanggal Awal</label>
                        <input type="date" name="date_from" value="{{ request('date_from', $dateFrom->format('Y-m-d')) }}" style="color-scheme: dark;" class="w-full bg-[#0f172a] border border-white/10 rounded-xl px-4 py-2.5 text-sm font-bold text-slate-200 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:shadow-[0_0_15px_rgba(59,130,246,0.2)] transition-all placeholder-slate-500/60 group-hover:shadow-sm">
                    </div>
                    <span class="text-slate-600 font-black self-end mb-3">-</span>
                    <div class="w-full relative group">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1.5 block"><i class="far fa-calendar-check mr-1"></i> Tanggal Akhir</label>
                        <input type="date" name="date_to" value="{{ request('date_to', $dateTo->format('Y-m-d')) }}" style="color-scheme: dark;" class="w-full bg-[#0f172a] border border-white/10 rounded-xl px-4 py-2.5 text-sm font-bold text-slate-200 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:shadow-[0_0_15px_rgba(59,130,246,0.2)] transition-all placeholder-slate-500/60 group-hover:shadow-sm">
                    </div>
                </div>
                
                <div class="group relative">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1.5 block"><i class="fas fa-wallet mr-1"></i> Metode Pembayaran</label>
                    <select name="payment_method" style="color-scheme: dark;" class="w-full bg-[#0f172a] border border-white/10 rounded-xl px-4 py-2.5 text-sm font-bold text-slate-200 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:shadow-[0_0_15px_rgba(59,130,246,0.2)] transition-all appearance-none group-hover:shadow-sm">
                        <option value="" class="bg-[#1e293b] hover:bg-slate-700">Semua Metode</option>
                        <option value="cash" class="bg-[#1e293b] hover:bg-slate-700" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Tunai (Cash)</option>
                        <option value="transfer" class="bg-[#1e293b] hover:bg-slate-700" {{ request('payment_method') == 'transfer' ? 'selected' : '' }}>Transfer Bank</option>
                        <option value="qris" class="bg-[#1e293b] hover:bg-slate-700" {{ request('payment_method') == 'qris' ? 'selected' : '' }}>QRIS</option>
                        <option value="debit" class="bg-[#1e293b] hover:bg-slate-700" {{ request('payment_method') == 'debit' ? 'selected' : '' }}>Kartu Debit/Kredit</option>
                    </select>
                    <i class="fas fa-chevron-down absolute right-4 bottom-3 text-slate-500 text-xs pointer-events-none group-hover:text-slate-400 transition-colors"></i>
                </div>

                <div class="group relative">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1.5 block"><i class="fas fa-user-tag mr-1"></i> Kasir</label>
                    <select name="user_id" style="color-scheme: dark;" class="w-full bg-[#0f172a] border border-white/10 rounded-xl px-4 py-2.5 text-sm font-bold text-slate-200 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:shadow-[0_0_15px_rgba(59,130,246,0.2)] transition-all appearance-none group-hover:shadow-sm">
                        <option value="" class="bg-[#1e293b] hover:bg-slate-700">Semua Kasir</option>
                        @foreach($users as $u)
                        <option value="{{ $u->id }}" class="bg-[#1e293b] hover:bg-slate-700" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                    <i class="fas fa-chevron-down absolute right-4 bottom-3 text-slate-500 text-xs pointer-events-none group-hover:text-slate-400 transition-colors"></i>
                </div>
            </div>

            <div class="flex gap-3 shrink-0 w-full md:w-auto mt-4 md:mt-0">
                @if(request()->has('date_from'))
                <a href="{{ route('reports.sales') }}" class="py-2.5 px-5 bg-transparent hover:bg-white/5 text-slate-300 hover:text-white font-bold rounded-xl transition-all text-sm border border-white/10 text-center flex-1 md:flex-none">Reset</a>
                @endif
                <button type="submit" class="py-2.5 px-6 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-500 hover:to-blue-400 text-white font-bold rounded-xl transition-all shadow-[0_5px_15px_rgba(59,130,246,0.3)] hover:shadow-[0_5px_20px_rgba(59,130,246,0.4)] text-sm flex items-center justify-center gap-2 flex-1 md:flex-none transform hover:-translate-y-0.5">
                    <i class="fas fa-filter text-xs"></i> Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    {{-- SUMMARY CARDS --}}
    @php
        $grossSales = $summary->total_sales + $summary->total_discount;
        $marginProfit = $summary->total_sales - $summary->total_cogs;
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Omzet --}}
        <div class="bg-slate-800 rounded-2xl p-5 border border-slate-700/80 shadow-sm relative overflow-hidden group hover:-translate-y-1 transition-all duration-300">
            <div class="absolute -right-4 -top-4 w-20 h-20 bg-blue-500/10 rounded-full blur-xl pointer-events-none group-hover:bg-blue-500/20 transition-all"></div>
            <div class="flex items-center gap-4 relative z-10">
                <div class="w-12 h-12 rounded-xl bg-blue-500/20 flex items-center justify-center border border-blue-500/30 shrink-0 text-blue-400 group-hover:bg-blue-500 group-hover:text-white transition-colors shadow-inner">
                    <i class="fas fa-chart-line text-xl"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-0.5">Total Omzet Kotor</p>
                    <h3 class="text-xl font-black text-white">Rp {{ number_format($grossSales, 0, ',', '.') }}</h3>
                    <p class="text-[9px] text-slate-500 mt-1 font-bold">{{ $summary->total_trx }} Transaksi sukses</p>
                </div>
            </div>
        </div>

        {{-- Total Diskon --}}
        <div class="bg-slate-800 rounded-2xl p-5 border border-slate-700/80 shadow-sm relative overflow-hidden group hover:-translate-y-1 transition-all duration-300">
            <div class="absolute -right-4 -top-4 w-20 h-20 bg-red-500/10 rounded-full blur-xl pointer-events-none group-hover:bg-red-500/20 transition-all"></div>
            <div class="flex items-center gap-4 relative z-10">
                <div class="w-12 h-12 rounded-xl bg-red-500/20 flex items-center justify-center border border-red-500/30 shrink-0 text-red-400 group-hover:bg-red-500 group-hover:text-white transition-colors shadow-inner">
                    <i class="fas fa-tags text-xl"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-0.5">Total Diskon</p>
                    <h3 class="text-xl font-black text-red-400">Rp {{ number_format($summary->total_discount, 0, ',', '.') }}</h3>
                    <p class="text-[9px] text-slate-500 mt-1 font-bold">Potongan harga / voucher</p>
                </div>
            </div>
        </div>

        {{-- Uang Masuk --}}
        <div class="bg-slate-800 rounded-2xl p-5 border border-slate-700/80 shadow-sm relative overflow-hidden group hover:-translate-y-1 transition-all duration-300">
            <div class="absolute -right-4 -top-4 w-20 h-20 bg-emerald-500/10 rounded-full blur-xl pointer-events-none group-hover:bg-emerald-500/20 transition-all"></div>
            <div class="flex items-center gap-4 relative z-10">
                <div class="w-12 h-12 rounded-xl bg-emerald-500/20 flex items-center justify-center border border-emerald-500/30 shrink-0 text-emerald-400 group-hover:bg-emerald-500 group-hover:text-white transition-colors shadow-inner">
                    <i class="fas fa-wallet text-xl"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-0.5">Penjualan Bersih</p>
                    <h3 class="text-xl font-black text-emerald-400">Rp {{ number_format($summary->total_sales, 0, ',', '.') }}</h3>
                    <p class="text-[9px] text-emerald-500/50 mt-1 font-bold">Omzet riil di kasir</p>
                </div>
            </div>
        </div>

        {{-- Margin Profit --}}
        <div class="bg-slate-800 rounded-2xl p-5 border border-slate-700/80 shadow-sm relative overflow-hidden group hover:-translate-y-1 transition-all duration-300">
            <div class="absolute -right-4 -top-4 w-20 h-20 bg-purple-500/10 rounded-full blur-xl pointer-events-none group-hover:bg-purple-500/20 transition-all"></div>
            <div class="flex items-center gap-4 relative z-10">
                <div class="w-12 h-12 rounded-xl bg-purple-500/20 flex items-center justify-center border border-purple-500/30 shrink-0 text-purple-400 group-hover:bg-purple-500 group-hover:text-white transition-colors shadow-inner">
                    <i class="fas fa-coins text-xl"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-0.5">Laba Kotor (Margin)</p>
                    <h3 class="text-xl font-black text-purple-400">Rp {{ number_format($marginProfit, 0, ',', '.') }}</h3>
                    <p class="text-[9px] text-purple-500/50 mt-1 font-bold">Omzet bersih dikurangi HPP</p>
                </div>
            </div>
        </div>
    </div>

    {{-- CHARTS SECTION --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        {{-- LINE CHART --}}
        <div class="lg:col-span-8 bg-slate-800 rounded-2xl p-6 border border-slate-700/80 shadow-sm">
            <h3 class="text-xs font-black text-slate-400 uppercase tracking-wider mb-6 flex items-center gap-2"><i class="fas fa-chart-area text-blue-400"></i> Tren Penjualan Harian</h3>
            <div class="relative h-[300px] w-full">
                <canvas id="salesLineChart"></canvas>
            </div>
        </div>

        {{-- PIE CHART --}}
        <div class="lg:col-span-4 bg-slate-800 rounded-2xl p-6 border border-slate-700/80 shadow-sm">
            <h3 class="text-xs font-black text-slate-400 uppercase tracking-wider mb-6 flex items-center gap-2"><i class="fas fa-chart-pie text-emerald-400"></i> Metode Pembayaran</h3>
            <div class="relative h-[250px] w-full flex items-center justify-center">
                <canvas id="paymentPieChart"></canvas>
            </div>
        </div>

        {{-- BAR CHART KATEGORI --}}
        <div class="lg:col-span-6 bg-slate-800 rounded-2xl p-6 border border-slate-700/80 shadow-sm">
            <h3 class="text-xs font-black text-slate-400 uppercase tracking-wider mb-6 flex items-center gap-2"><i class="fas fa-chart-bar text-amber-400"></i> Omzet per Kategori</h3>
            <div class="relative h-[250px] w-full">
                <canvas id="categoryBarChart"></canvas>
            </div>
        </div>

        {{-- BONUS LEVEL PRO: TOP STATS --}}
        <div class="lg:col-span-6 bg-slate-800 rounded-2xl p-6 border border-slate-700/80 shadow-sm flex flex-col">
            <h3 class="text-xs font-black text-slate-400 uppercase tracking-wider mb-6 flex items-center gap-2"><i class="fas fa-trophy text-yellow-400"></i> Statistik Unggulan</h3>
            
            <div class="space-y-4 flex-1">
                {{-- Top Product --}}
                <div class="flex items-center gap-4 bg-slate-900/50 p-4 rounded-xl border border-slate-700">
                    <div class="w-10 h-10 rounded-full bg-yellow-500/20 text-yellow-500 flex items-center justify-center shrink-0 border border-yellow-500/30"><i class="fas fa-crown"></i></div>
                    <div class="flex-1">
                        <p class="text-[10px] font-bold text-slate-400 uppercase">Produk Paling Laris</p>
                        <h4 class="text-sm font-black text-white truncate">{{ $topProducts->first() ? $topProducts->first()->product_name : 'Belum Ada' }}</h4>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] font-bold text-slate-400 uppercase">Terjual</p>
                        <h4 class="text-sm font-black text-emerald-400">{{ $topProducts->first() ? $topProducts->first()->total_qty : 0 }}</h4>
                    </div>
                </div>

                {{-- Peak Hour --}}
                <div class="flex items-center gap-4 bg-slate-900/50 p-4 rounded-xl border border-slate-700">
                    <div class="w-10 h-10 rounded-full bg-orange-500/20 text-orange-500 flex items-center justify-center shrink-0 border border-orange-500/30"><i class="fas fa-fire"></i></div>
                    <div class="flex-1">
                        <p class="text-[10px] font-bold text-slate-400 uppercase">Jam Paling Ramai</p>
                        <h4 class="text-sm font-black text-white truncate">{{ $peakHours->first() ? sprintf("%02d:00 - %02d:59", $peakHours->first()->hour, $peakHours->first()->hour) : 'Belum Ada' }}</h4>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] font-bold text-slate-400 uppercase">Transaksi</p>
                        <h4 class="text-sm font-black text-orange-400">{{ $peakHours->first() ? $peakHours->first()->count : 0 }}</h4>
                    </div>
                </div>

                {{-- Top Profit Product --}}
                @php
                    $mostProfitable = $topProducts->sortByDesc(function($item) {
                        return $item->total_revenue - $item->total_cost;
                    })->first();
                @endphp
                <div class="flex items-center gap-4 bg-slate-900/50 p-4 rounded-xl border border-slate-700">
                    <div class="w-10 h-10 rounded-full bg-purple-500/20 text-purple-500 flex items-center justify-center shrink-0 border border-purple-500/30"><i class="fas fa-gem"></i></div>
                    <div class="flex-1">
                        <p class="text-[10px] font-bold text-slate-400 uppercase">Produk Paling Untung</p>
                        <h4 class="text-sm font-black text-white truncate">{{ $mostProfitable ? $mostProfitable->product_name : 'Belum Ada' }}</h4>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] font-bold text-slate-400 uppercase">Margin</p>
                        <h4 class="text-sm font-black text-purple-400">Rp {{ $mostProfitable ? number_format($mostProfitable->total_revenue - $mostProfitable->total_cost, 0, ',', '.') : 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- LIST TRANSAKSI (CARD STYLE) --}}
    <div class="bg-slate-800 rounded-2xl border border-slate-700/80 shadow-sm mt-2 overflow-hidden flex flex-col">
        <div class="p-6 border-b border-slate-700/80 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-slate-800/50">
            <h3 class="text-xs font-black text-slate-400 uppercase tracking-wider flex items-center gap-2"><i class="fas fa-list-alt text-slate-300"></i> Detail Transaksi</h3>
            <div class="flex gap-2">
                <button class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 hover:text-white text-xs font-bold rounded-lg transition-colors border border-slate-600 flex items-center gap-2 shadow-sm">
                    <i class="fas fa-file-pdf text-red-400"></i> Export PDF
                </button>
                <button class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 hover:text-white text-xs font-bold rounded-lg transition-colors border border-slate-600 flex items-center gap-2 shadow-sm">
                    <i class="fas fa-file-excel text-emerald-400"></i> Export Excel
                </button>
            </div>
        </div>

        <div class="flex flex-col gap-0">
            <div class="hidden md:grid grid-cols-12 gap-4 px-6 py-4 bg-[#0f172a] border-b border-white/5 shadow-inner">
                <div class="col-span-3 text-[10px] font-black text-slate-300 uppercase tracking-wider">Invoice & Waktu</div>
                <div class="col-span-2 text-[10px] font-black text-slate-300 uppercase tracking-wider">Kasir</div>
                <div class="col-span-2 text-[10px] font-black text-slate-300 uppercase tracking-wider">Metode</div>
                <div class="col-span-2 text-[10px] font-black text-slate-300 uppercase tracking-wider text-right">Kotor / Diskon</div>
                <div class="col-span-2 text-[10px] font-black text-slate-300 uppercase tracking-wider text-right">Total Bersih</div>
                <div class="col-span-1 text-[10px] font-black text-slate-300 uppercase tracking-wider text-center">Aksi</div>
            </div>

            <div class="divide-y divide-slate-700/50">
                @forelse($transactions as $t)
                <div class="group grid grid-cols-1 md:grid-cols-12 gap-4 px-6 py-4 items-center hover:bg-slate-700/30 transition-colors duration-300">
                    <div class="col-span-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-slate-900 border border-slate-700 flex items-center justify-center shrink-0 shadow-inner group-hover:bg-blue-500/20 group-hover:text-blue-400 group-hover:border-blue-500/30 transition-colors">
                                <i class="fas fa-receipt text-slate-500 group-hover:text-blue-400 transition-colors"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-black text-white leading-tight">#{{ $t->invoice_number }}</h4>
                                <p class="text-[10px] font-bold text-slate-500 mt-1">{{ $t->created_at->format('d M Y, H:i') }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-span-2 hidden md:block">
                        <p class="text-xs font-bold text-slate-300">{{ $t->user->name }}</p>
                        <p class="text-[9px] text-slate-500 uppercase">{{ $t->user->role }}</p>
                    </div>

                    <div class="col-span-2 flex items-center">
                        @php
                            $colors = [
                                'cash' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                'transfer' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                'qris' => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
                                'debit' => 'bg-amber-500/10 text-amber-400 border-amber-500/20'
                            ];
                            $icons = ['cash' => 'fa-money-bill-wave', 'transfer' => 'fa-building-columns', 'qris' => 'fa-qrcode', 'debit' => 'fa-credit-card'];
                            $m = $t->payment_method;
                        @endphp
                        <span class="px-2.5 py-1 text-[10px] font-black uppercase tracking-wider rounded-md border {{ $colors[$m] ?? 'bg-slate-700 text-slate-300 border-slate-600' }} flex items-center gap-1.5 shadow-inner">
                            <i class="fas {{ $icons[$m] ?? 'fa-wallet' }}"></i> {{ $m }}
                        </span>
                    </div>

                    <div class="col-span-2 hidden md:block text-right">
                        <p class="text-xs font-bold text-slate-300">Rp {{ number_format($t->subtotal, 0, ',', '.') }}</p>
                        @if($t->discount > 0)
                        <p class="text-[10px] font-bold text-red-400 mt-0.5">- Rp {{ number_format($t->discount, 0, ',', '.') }}</p>
                        @endif
                    </div>

                    <div class="col-span-2 text-right md:text-right flex flex-row md:flex-col justify-between md:justify-center items-center md:items-end w-full">
                        <span class="md:hidden text-[10px] font-black text-slate-400 uppercase">Total Bersih:</span>
                        <p class="text-sm font-black text-emerald-400 bg-emerald-500/10 md:bg-transparent px-2 py-1 md:p-0 rounded border border-emerald-500/20 md:border-transparent">Rp {{ number_format($t->total, 0, ',', '.') }}</p>
                    </div>

                    <div class="col-span-1 flex justify-end items-center">
                        <a href="{{ route('transactions.show', $t) }}" class="w-8 h-8 rounded-lg bg-slate-700 hover:bg-blue-600 text-slate-300 hover:text-white flex items-center justify-center transition-colors shadow-sm" title="Lihat Detail">
                            <i class="fas fa-chevron-right text-xs"></i>
                        </a>
                    </div>
                </div>
                @empty
                <div class="p-12 text-center text-slate-500 flex flex-col items-center">
                    <div class="w-16 h-16 bg-slate-800 rounded-full flex items-center justify-center mb-4"><i class="fas fa-file-invoice text-2xl text-slate-600"></i></div>
                    <p class="font-bold text-white mb-1">Tidak Ada Transaksi</p>
                    <p class="text-sm">Silakan ubah filter tanggal atau kasir.</p>
                </div>
                @endforelse
            </div>
        </div>

        @if($transactions->hasPages())
        <div class="p-4 border-t border-slate-700/80 bg-slate-800/50">
            {{ $transactions->links('pagination::tailwind') }}
        </div>
        @endif
    </div>

</div>

{{-- SCRIPT UNTUK CHART.JS --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    Chart.defaults.color = '#94a3b8';
    Chart.defaults.font.family = "'Inter', 'Nunito', sans-serif";
    
    // Data PHP to JS
    const rawSalesDay = @json($salesPerDay);
    const rawCategory = @json($byCategory);
    const rawPayment = @json($byPayment);

    // LINE CHART: Penjualan Harian
    const ctxLine = document.getElementById('salesLineChart');
    if(ctxLine && rawSalesDay.length > 0) {
        new Chart(ctxLine, {
            type: 'line',
            data: {
                labels: rawSalesDay.map(d => d.date),
                datasets: [{
                    label: 'Omzet Harian (Rp)',
                    data: rawSalesDay.map(d => d.total),
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 3,
                    pointBackgroundColor: '#1e293b',
                    pointBorderColor: '#3b82f6',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleColor: '#fff',
                        bodyColor: '#cbd5e1',
                        borderColor: '#334155',
                        borderWidth: 1,
                        padding: 10,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) label += ': ';
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#334155', drawBorder: false },
                        border: { display: false }
                    },
                    x: {
                        grid: { display: false },
                        border: { display: false }
                    }
                }
            }
        });
    }

    // BAR CHART: Omzet per Kategori
    const ctxBar = document.getElementById('categoryBarChart');
    if(ctxBar && rawCategory.length > 0) {
        new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: rawCategory.map(c => c.category_name),
                datasets: [{
                    label: 'Omzet',
                    data: rawCategory.map(c => c.total),
                    backgroundColor: '#f59e0b',
                    borderRadius: 6,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        callbacks: {
                            label: function(context) {
                                return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.raw);
                            }
                        }
                    }
                },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#334155' }, border: { display: false }, ticks: { display: false } },
                    x: { grid: { display: false }, border: { display: false } }
                }
            }
        });
    }

    // PIE CHART: Metode Pembayaran
    const ctxPie = document.getElementById('paymentPieChart');
    if(ctxPie && rawPayment.length > 0) {
        const colors = { 'cash': '#10b981', 'transfer': '#3b82f6', 'qris': '#a855f7', 'debit': '#f59e0b' };
        new Chart(ctxPie, {
            type: 'doughnut',
            data: {
                labels: rawPayment.map(p => p.payment_method.toUpperCase()),
                datasets: [{
                    data: rawPayment.map(p => p.total),
                    backgroundColor: rawPayment.map(p => colors[p.payment_method] || '#64748b'),
                    borderWidth: 2,
                    borderColor: '#1e293b',
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { position: 'bottom', labels: { color: '#cbd5e1', padding: 20, usePointStyle: true } },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        callbacks: {
                            label: function(context) {
                                return ' ' + new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.raw);
                            }
                        }
                    }
                }
            }
        });
    }
});

function analyticsDashboard() {
    return {
        // Alpine initialization logic if needed
    }
}
</script>
@endsection
