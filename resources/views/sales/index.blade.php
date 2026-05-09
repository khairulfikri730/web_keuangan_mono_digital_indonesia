@extends('layouts.app')

@section('title', 'Rekap Penjualan & Margin')
@section('page-title', 'Rekap Penjualan & Margin')
@section('page-subtitle', 'Pantau performa penjualan dan keuntungan bisnis Anda')

@section('content')
<div class="flex flex-col gap-6">

    {{-- FILTER BAR MODERN --}}
    <div class="bg-[#111827] rounded-xl p-5 border border-white/5 shadow-sm relative z-40">
        <form method="GET" action="{{ route('sales.index') }}" class="flex flex-col md:flex-row gap-4 items-end md:items-center w-full">
            <div class="flex-1 w-full grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="col-span-1 md:col-span-2 flex items-center gap-2">
                    <div class="w-full relative group">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1 block"><i class="far fa-calendar-alt"></i> Tanggal Awal</label>
                        <input type="date" name="date_from" value="{{ is_array(request('date_from')) ? $dateFrom->format('Y-m-d') : request('date_from', $dateFrom->format('Y-m-d')) }}" style="color-scheme: dark;" class="w-full bg-[#0F172A] border border-white/10 rounded-xl px-3 py-2.5 text-sm font-bold text-[#E5E7EB] focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 shadow-inner transition-all placeholder-slate-500">
                    </div>
                    <span class="text-slate-600 font-black self-end mb-3">-</span>
                    <div class="w-full relative group">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1 block"><i class="far fa-calendar-check"></i> Tanggal Akhir</label>
                        <input type="date" name="date_to" value="{{ is_array(request('date_to')) ? $dateTo->format('Y-m-d') : request('date_to', $dateTo->format('Y-m-d')) }}" style="color-scheme: dark;" class="w-full bg-[#0F172A] border border-white/10 rounded-xl px-3 py-2.5 text-sm font-bold text-[#E5E7EB] focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 shadow-inner transition-all placeholder-slate-500">
                    </div>
                </div>
                
                <div class="relative group">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1 block"><i class="fas fa-wallet"></i> Metode Pembayaran</label>
                    <select name="payment_method" style="color-scheme: dark;" class="w-full bg-[#0F172A] border border-white/10 rounded-xl px-3 py-2.5 text-sm font-bold text-[#E5E7EB] focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 shadow-inner appearance-none transition-all">
                        <option value="" class="bg-[#1F2937]">Semua Metode</option>
                        <option value="cash" class="bg-[#1F2937]" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Tunai (Cash)</option>
                        <option value="transfer" class="bg-[#1F2937]" {{ request('payment_method') == 'transfer' ? 'selected' : '' }}>Transfer Bank</option>
                        <option value="qris" class="bg-[#1F2937]" {{ request('payment_method') == 'qris' ? 'selected' : '' }}>QRIS</option>
                        <option value="debit" class="bg-[#1F2937]" {{ request('payment_method') == 'debit' ? 'selected' : '' }}>Kartu Debit/Kredit</option>
                    </select>
                    <i class="fas fa-chevron-down absolute right-3 bottom-3.5 text-slate-500 text-xs pointer-events-none"></i>
                </div>

                <div class="relative group">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1 block"><i class="fas fa-user-tag"></i> Kasir</label>
                    <select name="user_id" style="color-scheme: dark;" class="w-full bg-[#0F172A] border border-white/10 rounded-xl px-3 py-2.5 text-sm font-bold text-[#E5E7EB] focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 shadow-inner appearance-none transition-all">
                        <option value="" class="bg-[#1F2937]">Semua Kasir</option>
                        @foreach($kasirUsers as $user)
                        <option value="{{ $user->id }}" class="bg-[#1F2937]" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                    <i class="fas fa-chevron-down absolute right-3 bottom-3.5 text-slate-500 text-xs pointer-events-none"></i>
                </div>
            </div>

            <div class="flex gap-2 shrink-0 w-full md:w-auto mt-4 md:mt-0">
                @if(request()->anyFilled(['date_from', 'date_to', 'payment_method', 'user_id']))
                <a href="{{ route('sales.index') }}" class="py-2.5 px-4 bg-transparent hover:bg-white/5 text-[#E5E7EB] font-bold rounded-xl transition-colors text-sm border border-white/10 text-center flex-1 md:flex-none">Reset</a>
                @endif
                <button type="submit" class="py-2.5 px-6 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-500 hover:to-blue-400 text-white font-bold rounded-xl transition-all shadow-[0_4px_14px_rgba(59,130,246,0.3)] text-sm flex items-center justify-center gap-2 flex-1 md:flex-none">
                    <i class="fas fa-filter"></i> Terapkan
                </button>
            </div>
        </form>
    </div>

    {{-- SUMMARY CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
        {{-- Omzet --}}
        <div class="bg-[#111827] p-5 rounded-xl border border-white/5 relative overflow-hidden group hover:-translate-y-1 hover:shadow-[0_8px_30px_rgba(59,130,246,0.15)] transition-all duration-300">
            <div class="absolute -right-4 -top-4 w-20 h-20 bg-blue-500/10 rounded-full blur-xl pointer-events-none"></div>
            <div class="flex items-center gap-4 relative z-10">
                <div class="w-14 h-14 bg-blue-500/20 rounded-xl flex items-center justify-center flex-shrink-0 text-blue-400 text-xl border border-blue-500/20">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Total Omzet (Kotor)</p>
                    <p class="text-2xl font-black text-[#E5E7EB]">Rp {{ number_format($totalOmzetKotor, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
        
        {{-- Diskon --}}
        <div class="bg-[#111827] p-5 rounded-xl border border-white/5 relative overflow-hidden group hover:-translate-y-1 hover:shadow-[0_8px_30px_rgba(239,68,68,0.15)] transition-all duration-300">
            <div class="absolute -right-4 -top-4 w-20 h-20 bg-red-500/10 rounded-full blur-xl pointer-events-none"></div>
            <div class="flex items-center gap-4 relative z-10">
                <div class="w-14 h-14 bg-red-500/20 rounded-xl flex items-center justify-center flex-shrink-0 text-red-400 text-xl border border-red-500/20">
                    <i class="fas fa-tags"></i>
                </div>
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Total Diskon</p>
                    <p class="text-2xl font-black text-red-400">Rp {{ number_format($totalDiskon, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        {{-- Profit --}}
        <div class="bg-[#111827] p-5 rounded-xl border border-white/5 relative overflow-hidden group hover:-translate-y-1 hover:shadow-[0_8px_30px_rgba(16,185,129,0.15)] transition-all duration-300">
            <div class="absolute -right-4 -top-4 w-20 h-20 bg-emerald-500/10 rounded-full blur-xl pointer-events-none"></div>
            <div class="flex items-center gap-4 relative z-10">
                <div class="w-14 h-14 bg-emerald-500/20 rounded-xl flex items-center justify-center flex-shrink-0 text-emerald-400 text-xl border border-emerald-500/20">
                    <i class="fas fa-money-bill-trend-up"></i>
                </div>
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Margin Profit (Bersih)</p>
                    <p class="text-2xl font-black text-emerald-400">Rp {{ number_format($totalProfit, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        {{-- Laci --}}
        <div class="bg-[#111827] p-5 rounded-xl border border-white/5 relative overflow-hidden group hover:-translate-y-1 hover:shadow-[0_8px_30px_rgba(249,115,22,0.15)] transition-all duration-300">
            <div class="absolute -right-4 -top-4 w-20 h-20 bg-orange-500/10 rounded-full blur-xl pointer-events-none"></div>
            <div class="flex items-center gap-4 relative z-10">
                <div class="w-14 h-14 bg-orange-500/20 rounded-xl flex items-center justify-center flex-shrink-0 text-orange-400 text-xl border border-orange-500/20">
                    <i class="fas fa-wallet"></i>
                </div>
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Uang Laci (Tunai)</p>
                    <p class="text-2xl font-black text-orange-400">Rp {{ number_format($uangLaci, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- CHARTS SECTION --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {{-- LINE CHART --}}
        <div class="lg:col-span-8 bg-[#111827] rounded-xl p-5 border border-white/5 shadow-sm">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xs font-black text-slate-400 uppercase tracking-wider flex items-center gap-2"><i class="fas fa-chart-area text-blue-400"></i> Tren Penjualan Harian</h3>
                <button class="text-slate-500 hover:text-white transition-colors text-xs"><i class="fas fa-ellipsis-v"></i></button>
            </div>
            <div class="relative h-[280px] w-full">
                <canvas id="salesLineChart"></canvas>
            </div>
        </div>

        {{-- PIE CHART --}}
        <div class="lg:col-span-4 bg-[#111827] rounded-xl p-5 border border-white/5 shadow-sm">
            <h3 class="text-xs font-black text-slate-400 uppercase tracking-wider mb-4 flex items-center gap-2"><i class="fas fa-chart-pie text-emerald-400"></i> Metode Pembayaran</h3>
            <div class="relative h-[280px] w-full flex items-center justify-center">
                <canvas id="paymentPieChart"></canvas>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        {{-- KIRI: TOP PRODUK --}}
        <div class="lg:col-span-4 bg-[#111827] rounded-xl p-5 border border-white/5 shadow-sm">
            <h3 class="text-xs font-black text-slate-400 uppercase tracking-wider mb-5 flex items-center gap-2"><i class="fas fa-crown text-amber-400"></i> Top 3 Produk Terlaris</h3>
            <div class="space-y-4">
                @forelse($topProducts as $idx => $tp)
                <div class="flex items-center gap-3 bg-[#0F172A] p-3 rounded-lg border border-white/5">
                    <div class="w-8 h-8 rounded bg-slate-800 text-slate-400 font-black flex items-center justify-center text-xs border border-slate-700">#{{ $idx + 1 }}</div>
                    <div class="flex-1 min-w-0">
                        <h4 class="text-sm font-bold text-[#E5E7EB] truncate">{{ $tp->product_name }}</h4>
                    </div>
                    <div class="text-right shrink-0">
                        <span class="px-2 py-1 bg-blue-500/10 text-blue-400 border border-blue-500/20 rounded text-xs font-black">{{ $tp->total_qty }} Terjual</span>
                    </div>
                </div>
                @empty
                <div class="text-center py-6 text-slate-500 text-sm">Tidak ada data produk terjual.</div>
                @endforelse
            </div>
            <div class="mt-6 pt-4 border-t border-white/5">
                <div class="relative h-[180px] w-full">
                    <canvas id="productBarChart"></canvas>
                </div>
            </div>
        </div>

        {{-- KANAN: MODERN LIST TRANSAKSI --}}
        <div class="lg:col-span-8 bg-[#111827] rounded-xl border border-white/5 shadow-sm overflow-hidden flex flex-col">
            <div class="p-5 border-b border-white/5 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-[#111827]">
                <h3 class="text-xs font-black text-slate-400 uppercase tracking-wider flex items-center gap-2"><i class="fas fa-list-alt text-slate-300"></i> Detail Transaksi</h3>
                <div class="flex gap-2">
                    <button class="px-3 py-1.5 bg-[#0F172A] hover:bg-[#1F2937] text-slate-300 text-xs font-bold rounded transition-colors border border-white/5 flex items-center gap-2 shadow-sm">
                        <i class="fas fa-file-pdf text-red-400"></i> PDF
                    </button>
                    <button class="px-3 py-1.5 bg-[#0F172A] hover:bg-[#1F2937] text-slate-300 text-xs font-bold rounded transition-colors border border-white/5 flex items-center gap-2 shadow-sm">
                        <i class="fas fa-file-excel text-emerald-400"></i> Excel
                    </button>
                </div>
            </div>

            <div class="flex flex-col gap-0 bg-[#0F172A]">
                <div class="hidden md:grid grid-cols-12 gap-4 px-6 py-3 bg-[#111827]/50 border-b border-white/5 shadow-inner">
                    <div class="col-span-3 text-[10px] font-black text-slate-400 uppercase tracking-wider">Invoice</div>
                    <div class="col-span-2 text-[10px] font-black text-slate-400 uppercase tracking-wider">Kasir</div>
                    <div class="col-span-2 text-[10px] font-black text-slate-400 uppercase tracking-wider">Metode</div>
                    <div class="col-span-2 text-[10px] font-black text-slate-400 uppercase tracking-wider text-right">Diskon</div>
                    <div class="col-span-2 text-[10px] font-black text-slate-400 uppercase tracking-wider text-right">Total Bersih</div>
                    <div class="col-span-1 text-[10px] font-black text-slate-400 uppercase tracking-wider text-center">Aksi</div>
                </div>

                <div class="divide-y divide-white/5">
                    @forelse($transactions as $t)
                    {{-- Row Utama --}}
                    <div class="group grid grid-cols-1 md:grid-cols-12 gap-4 px-6 py-4 items-center hover:bg-[#1F2937] transition-colors duration-200">
                        <div class="col-span-3">
                            <h4 class="text-sm font-black text-blue-400">{{ $t->invoice_number }}</h4>
                            <p class="text-[10px] font-medium text-slate-500 mt-0.5">{{ $t->created_at->format('d M Y, H:i') }}</p>
                        </div>
                        
                        <div class="col-span-2 hidden md:block">
                            <p class="text-xs font-bold text-[#E5E7EB]">{{ $t->user->name }}</p>
                        </div>

                        <div class="col-span-2 flex items-center">
                            @php
                                $colors = [
                                    'cash' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                    'transfer' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                    'qris' => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
                                    'debit' => 'bg-amber-500/10 text-amber-400 border-amber-500/20'
                                ];
                                $m = $t->payment_method;
                            @endphp
                            <span class="px-2 py-1 text-[9px] font-black uppercase tracking-wider rounded border {{ $colors[$m] ?? 'bg-slate-800 text-slate-300 border-slate-700' }}">
                                {{ $m }}
                            </span>
                        </div>

                        <div class="col-span-2 hidden md:block text-right">
                            <p class="text-xs font-bold {{ $t->discount > 0 ? 'text-red-400' : 'text-slate-500' }}">Rp {{ number_format($t->discount, 0, ',', '.') }}</p>
                        </div>

                        <div class="col-span-2 text-right md:text-right flex justify-between md:justify-end items-center w-full">
                            <span class="md:hidden text-[10px] font-black text-slate-500 uppercase">Total:</span>
                            <p class="text-sm font-black text-emerald-400">Rp {{ number_format($t->total, 0, ',', '.') }}</p>
                        </div>

                        <div class="col-span-1 flex justify-end md:justify-center items-center">
                            <button type="button" onclick="toggleDetail('detail-{{ $t->id }}')" class="w-8 h-8 rounded-full bg-[#111827] hover:bg-blue-600 text-slate-400 hover:text-white flex items-center justify-center transition-colors border border-white/5" title="Lihat Detail">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                        </div>
                    </div>
                    
                    {{-- Row Detail --}}
                    <div id="detail-{{ $t->id }}" class="hidden bg-[#0a0f18] px-6 py-4 border-l-2 border-blue-500 shadow-inner">
                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-wider mb-3">Item Transaksi</p>
                        <div class="space-y-2">
                            @foreach($t->items as $item)
                            <div class="flex justify-between items-center bg-[#111827] p-2 rounded border border-white/5">
                                <div class="flex items-center gap-3">
                                    <div class="w-6 h-6 rounded bg-slate-800 text-slate-400 flex items-center justify-center text-[10px] font-bold">{{ $item->quantity }}x</div>
                                    <div>
                                        <p class="text-xs font-bold text-[#E5E7EB]">{{ $item->product_name }}</p>
                                        <p class="text-[9px] text-slate-500">Modal: Rp {{ number_format($item->cost_price, 0, ',', '.') }} | Jual: Rp {{ number_format($item->price, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs font-bold text-[#E5E7EB]">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</p>
                                    <p class="text-[9px] font-bold text-emerald-400">Profit: Rp {{ number_format($item->subtotal - ($item->cost_price * $item->quantity), 0, ',', '.') }}</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @empty
                    <div class="p-12 text-center text-slate-500 flex flex-col items-center">
                        <div class="w-14 h-14 bg-[#111827] rounded-full flex items-center justify-center mb-3 border border-white/5"><i class="fas fa-receipt text-xl text-slate-600"></i></div>
                        <p class="font-bold text-[#E5E7EB] mb-1">Data Kosong</p>
                        <p class="text-xs">Tidak ada transaksi pada periode ini.</p>
                    </div>
                    @endforelse
                </div>
            </div>

            @if($transactions->hasPages())
            <div class="p-4 border-t border-white/5 bg-[#111827]">
                {{ $transactions->links('pagination::tailwind') }}
            </div>
            @endif
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function toggleDetail(id) {
        const el = document.getElementById(id);
        if(el.classList.contains('hidden')) {
            el.classList.remove('hidden');
        } else {
            el.classList.add('hidden');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        Chart.defaults.color = '#94a3b8';
        Chart.defaults.font.family = "'Inter', 'Nunito', sans-serif";
        
        const rawSalesDay = @json($salesPerDay);
        const rawPayment = @json($byPayment);
        const rawProducts = @json($topProducts);

        // LINE CHART: Penjualan Harian
        const ctxLine = document.getElementById('salesLineChart');
        if(ctxLine && rawSalesDay.length > 0) {
            new Chart(ctxLine, {
                type: 'line',
                data: {
                    labels: rawSalesDay.map(d => d.date),
                    datasets: [{
                        label: 'Omzet',
                        data: rawSalesDay.map(d => d.total),
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 3,
                        pointBackgroundColor: '#0F172A',
                        pointBorderColor: '#3b82f6',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' }, border: { display: false } },
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
                        borderWidth: 0,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '75%',
                    plugins: {
                        legend: { position: 'bottom', labels: { padding: 20, usePointStyle: true } }
                    }
                }
            });
        }

        // BAR CHART: Top Products
        const ctxBar = document.getElementById('productBarChart');
        if(ctxBar && rawProducts.length > 0) {
            new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: rawProducts.map(p => p.product_name.substring(0, 10) + '...'),
                    datasets: [{
                        label: 'Terjual',
                        data: rawProducts.map(p => p.total_qty),
                        backgroundColor: '#f59e0b',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { display: false },
                        x: { grid: { display: false }, border: { display: false }, ticks: { font: { size: 10 } } }
                    }
                }
            });
        }
    });
</script>
@endsection
