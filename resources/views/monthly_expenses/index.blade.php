@extends('layouts.app')
@section('title', 'Biaya & Pengeluaran')

@section('content')
<div x-data="expenseDashboard()" class="flex flex-col gap-6 font-sans antialiased text-slate-300">

    <!-- MAIN NAVIGATION TABS -->
    <div class="flex border-b border-slate-700/50 mb-2 overflow-x-auto hide-scrollbar">
        <a href="{{ route('cashflow.index') }}" class="px-6 py-3 text-slate-500 hover:text-slate-300 font-bold tracking-wide transition-colors whitespace-nowrap">Semua Arus Kas</a>
        <a href="{{ route('sales.index') }}" class="px-6 py-3 text-slate-500 hover:text-slate-300 font-bold tracking-wide transition-colors whitespace-nowrap">Pemasukan (Sales)</a>
        <a href="{{ route('monthly_expenses.index') }}" class="px-6 py-3 border-b-2 border-emerald-500 text-emerald-500 font-black tracking-wide whitespace-nowrap">Pengeluaran (Biaya)</a>
    </div>

    <!-- TOP HEADER -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-white tracking-tight">Dashboard Pengeluaran</h2>
            <p class="text-slate-400 text-sm mt-1">Kelola dan pantau semua pengeluaran dan biaya bisnis Anda</p>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-3">
            <form id="dateFilterForm" method="GET" class="flex items-center gap-2 bg-slate-800 border border-slate-700 rounded-xl p-1">
                @php
                    $activePeriod = request('period', 'month');
                    $today = now()->format('Y-m-d');
                    $startOfWeek = now()->startOfWeek()->format('Y-m-d');
                    $startOfMonth = now()->startOfMonth()->format('Y-m-d');
                    $startOfYear = now()->startOfYear()->format('Y-m-d');
                @endphp
                <input type="hidden" name="date_from" id="date_from" value="{{ $dateFrom->format('Y-m-d') }}">
                <input type="hidden" name="date_to" id="date_to" value="{{ $dateTo->format('Y-m-d') }}">
                <input type="hidden" name="period" id="period" value="{{ $activePeriod }}">
                
                <button type="button" @click="setPeriod('today', '{{ $today }}', '{{ $today }}')" :class="period === 'today' ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-400 hover:text-slate-200'" class="px-3 py-1.5 text-[10px] font-black rounded-lg transition-all">HARI INI</button>
                <button type="button" @click="setPeriod('week', '{{ $startOfWeek }}', '{{ $today }}')" :class="period === 'week' ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-400 hover:text-slate-200'" class="px-3 py-1.5 text-[10px] font-black rounded-lg transition-all">MINGGUAN</button>
                <button type="button" @click="setPeriod('month', '{{ $startOfMonth }}', '{{ $today }}')" :class="period === 'month' ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-400 hover:text-slate-200'" class="px-3 py-1.5 text-[10px] font-black rounded-lg transition-all">BULANAN</button>
                <button type="button" @click="setPeriod('year', '{{ $startOfYear }}', '{{ $today }}')" :class="period === 'year' ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-400 hover:text-slate-200'" class="px-3 py-1.5 text-[10px] font-black rounded-lg transition-all">TAHUNAN</button>
            </form>

            <button onclick="window.openExportModal()" class="w-11 h-11 bg-slate-800 border border-white/5 text-slate-400 rounded-2xl hover:bg-slate-700 hover:text-white transition-premium flex items-center justify-center shadow-lg" title="Ekspor Laporan (PDF/Excel/CSV)">
                <i class="fas fa-file-export"></i>
            </button>
            <a href="{{ route('monthly_expenses.create') }}" class="bg-blue-600 hover:bg-blue-500 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg shadow-blue-500/30 transition-all flex items-center justify-center gap-2 text-sm">
                <i class="fas fa-plus"></i> Catat Pengeluaran
            </a>
        </div>
    </div>

    <!-- MAIN CONTENT GRID -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- LEFT: MAIN LIST & CARDS (8 COLS) -->
        <div class="lg:col-span-9 space-y-6">
            
            <!-- SUMMARY CARDS ROW 1: THE BIG THREE -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- OMZET -->
                <div class="bg-slate-800/80 p-5 rounded-2xl border border-white/5 shadow-xl hover:border-blue-500/30 transition-all group backdrop-blur-xl">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-400 group-hover:bg-blue-600 group-hover:text-white transition-all">
                            <i class="fas fa-chart-line text-xl"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Total Omzet</p>
                            <h3 class="text-2xl font-black text-white">Rp {{ number_format($totalOmzet ?? 0, 0, ',', '.') }}</h3>
                        </div>
                    </div>
                </div>

                <!-- PENGELUARAN -->
                <div @click="showDetailModal = true" class="bg-slate-800/80 p-5 rounded-2xl border border-white/5 shadow-xl hover:border-red-500/50 hover:bg-slate-700/80 transition-all group backdrop-blur-xl cursor-pointer">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 rounded-xl bg-red-500/10 flex items-center justify-center text-red-400 group-hover:bg-red-600 group-hover:text-white transition-all shadow-lg shadow-red-900/20">
                            <i class="fas fa-wallet text-xl"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Total Pengeluaran</p>
                                <span class="bg-red-500/10 text-red-400 text-[8px] font-black px-1.5 py-0.5 rounded border border-red-500/20">KLIK DETAIL</span>
                            </div>
                            <h3 class="text-2xl font-black text-white">Rp {{ number_format($systemTotalExpense ?? 0, 0, ',', '.') }}</h3>
                        </div>
                    </div>
                </div>

                <!-- LABA BERSIH -->
                <div class="bg-gradient-to-br from-emerald-600 to-emerald-900 p-5 rounded-2xl border border-emerald-500/30 shadow-xl shadow-emerald-500/20 hover:shadow-emerald-500/40 transition-all group">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center text-white transition-all">
                            <i class="fas fa-sack-dollar text-xl"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-emerald-200 uppercase tracking-widest">Laba Bersih</p>
                            <h3 class="text-2xl font-black text-white">Rp {{ number_format($labaBersih ?? 0, 0, ',', '.') }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SUMMARY CARDS ROW 2: KATEGORI PENGELUARAN -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <!-- OPERASIONAL -->
                <div class="bg-slate-800/80 p-4 rounded-xl border border-white/5 shadow-md flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-500/10 flex items-center justify-center text-blue-400">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <div>
                        <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest">Operasional</p>
                        <p class="text-sm font-black text-white">Rp {{ number_format($summary->operasional_total ?? 0, 0, ',', '.') }}</p>
                    </div>
                </div>

                <!-- CONSUMABLE -->
                <div class="bg-slate-800/80 p-4 rounded-xl border border-white/5 shadow-md flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-400">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <div>
                        <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest">Consumable</p>
                        <p class="text-sm font-black text-white">Rp {{ number_format($summary->consumable_total ?? 0, 0, ',', '.') }}</p>
                    </div>
                </div>

                <!-- BAHAN BAKU -->
                <div class="bg-slate-800/80 p-4 rounded-xl border border-white/5 shadow-md flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-purple-500/10 flex items-center justify-center text-purple-400">
                        <i class="fas fa-cube"></i>
                    </div>
                    <div>
                        <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest">Bahan Baku</p>
                        <p class="text-sm font-black text-white">Rp {{ number_format($summary->bahan_baku_total ?? 0, 0, ',', '.') }}</p>
                    </div>
                </div>

                <!-- VARIABEL -->
                <div class="bg-slate-800/80 p-4 rounded-xl border border-white/5 shadow-md flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-amber-500/10 flex items-center justify-center text-amber-400">
                        <i class="fas fa-car"></i>
                    </div>
                    <div>
                        <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest">Variabel</p>
                        <p class="text-sm font-black text-white">Rp {{ number_format($summary->variabel_total ?? 0, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <!-- TABLE CONTAINER -->
            <div class="bg-slate-800/40 rounded-[2.5rem] border border-white/5 shadow-2xl overflow-hidden backdrop-blur-xl">
                <div class="px-8 py-6 flex justify-between items-center border-b border-white/5">
                    <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest flex items-center gap-3">
                        <span class="w-2 h-6 bg-blue-500 rounded-full"></span>
                        Riwayat Pengeluaran
                    </h4>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-900/40">
                            <tr class="text-[10px] font-black text-slate-500 uppercase tracking-widest border-b border-white/5">
                                <th class="px-8 py-5">Nama & Item</th>
                                <th class="px-8 py-5 text-center">Qty</th>
                                <th class="px-8 py-5">Jenis</th>
                                <th class="px-8 py-5">Tanggal & Waktu</th>
                                <th class="px-8 py-5 text-right">Nominal</th>
                                <th class="px-8 py-5 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @forelse($expenses as $expense)
                            <tr class="group hover:bg-white/5 transition-colors">
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-4">
                                        @php
                                            $icon = 'fa-receipt'; $color = 'slate';
                                            if($expense->expense_type == 'operasional') { $icon = 'fa-bolt'; $color = 'blue'; }
                                            elseif($expense->expense_type == 'consumable') { $icon = 'fa-box-open'; $color = 'emerald'; }
                                            elseif($expense->expense_type == 'bahan_baku') { $icon = 'fa-cube'; $color = 'purple'; }
                                            elseif($expense->expense_type == 'variabel') { $icon = 'fa-car'; $color = 'amber'; }
                                        @endphp
                                        <div class="w-10 h-10 rounded-xl bg-{{$color}}-500/10 flex items-center justify-center text-{{$color}}-400 border border-{{$color}}-500/20 group-hover:bg-{{$color}}-600 group-hover:text-white transition-all shadow-lg">
                                            <i class="fas {{ $icon }}"></i>
                                        </div>
                                        <div>
                                            <p class="font-black text-white text-sm">{{ $expense->expense_name }}</p>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <span class="text-[9px] font-black text-slate-500 uppercase tracking-tighter bg-slate-900/60 px-1.5 rounded">{{ $expense->sub_category ?: 'Tanpa Detail' }}</span>
                                                <span class="text-[9px] font-black text-slate-500 uppercase flex items-center gap-1">
                                                    <i class="fas {{ in_array(strtolower($expense->payment_method), ['tunai', 'cash']) ? 'fa-wallet text-emerald-400' : 'fa-university text-blue-400' }} text-[8px]"></i>
                                                    {{ $expense->payment_method ?: 'Tunai' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-5 text-center">
                                    <span class="text-xs font-black text-slate-300">{{ $expense->quantity ?? 0 }}</span>
                                    <span class="text-[9px] font-bold text-slate-500 uppercase ml-0.5">{{ $expense->unit ?? '' }}</span>
                                </td>
                                <td class="px-8 py-5">
                                    <span class="px-3 py-1.5 rounded-lg text-[9px] font-black uppercase tracking-wider bg-{{$color}}-500/10 text-{{$color}}-400 border border-{{$color}}-500/20">
                                        {{ str_replace('_', ' ', $expense->expense_type) }}
                                    </span>
                                </td>
                                <td class="px-8 py-5">
                                    <p class="text-xs font-black text-slate-300">{{ \Carbon\Carbon::parse($expense->expense_date)->translatedFormat('d M Y') }}</p>
                                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-tighter">{{ \Carbon\Carbon::parse($expense->created_at)->format('H:i') }}</p>
                                </td>
                                <td class="px-8 py-5 text-right font-black text-white text-sm">
                                    Rp {{ number_format($expense->usage_amount, 0, ',', '.') }}
                                </td>
                                <td class="px-8 py-5 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('monthly_expenses.edit', $expense->id) }}" class="w-8 h-8 rounded-lg bg-slate-900 text-slate-500 hover:bg-blue-600 hover:text-white flex items-center justify-center transition-all">
                                            <i class="fas fa-edit text-[10px]"></i>
                                        </a>
                                        <form action="{{ route('monthly_expenses.destroy', $expense->id) }}" method="POST" onsubmit="return confirm('Hapus pengeluaran ini? Data di Cashflow juga akan terhapus.')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="w-8 h-8 rounded-lg bg-slate-900 text-slate-500 hover:bg-red-600 hover:text-white flex items-center justify-center transition-all">
                                                <i class="fas fa-trash text-[10px]"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-8 py-16 text-center">
                                    <div class="w-20 h-20 bg-slate-800/50 rounded-full flex items-center justify-center mx-auto mb-4 border border-white/5 shadow-inner text-slate-500">
                                        <i class="fas fa-receipt text-3xl"></i>
                                    </div>
                                    <p class="text-xs font-black text-slate-500 uppercase tracking-widest">Belum ada catatan pengeluaran</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($expenses->hasPages())
                <div class="px-8 py-6 border-t border-white/5 bg-slate-900/20">
                    {{ $expenses->links() }}
                </div>
                @endif
            </div>
        </div>

        <!-- RIGHT SIDE: ANALYTICS (3 COLS) -->
        <div class="lg:col-span-3 space-y-8">
            
            <!-- TOP PENGELUARAN BULAN INI -->
            <div class="bg-slate-800/40 backdrop-blur-md rounded-[2.5rem] border border-white/5 p-8 shadow-xl">
                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-8 text-center flex items-center justify-center gap-3">
                    <span class="w-6 h-px bg-slate-700"></span>
                    Top Pengeluaran
                    <span class="w-6 h-px bg-slate-700"></span>
                </h4>
                <div class="space-y-6">
                    @forelse($topExpenses ?? [] as $index => $top)
                    <div class="relative">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <p class="text-[10px] font-black text-slate-500 uppercase">#{{ $index + 1 }} Item</p>
                                <p class="text-xs font-black text-white line-clamp-1">{{ $top->expense_name }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs font-black text-white">Rp {{ number_format($top->total_amount, 0, ',', '.') }}</p>
                                <p class="text-[9px] font-bold text-slate-500">{{ $top->trans_count }} Transaksi</p>
                            </div>
                        </div>
                        <div class="w-full h-1.5 bg-slate-900 rounded-full overflow-hidden">
                            <div class="h-full bg-blue-600 rounded-full" style="width: {{ $top->percentage }}%"></div>
                        </div>
                    </div>
                    @empty
                    <p class="text-[10px] text-slate-600 text-center py-4 italic">Belum ada data</p>
                    @endforelse
                </div>
            </div>

            <!-- RINGKASAN JENIS CHART -->
            <div class="bg-slate-800/40 backdrop-blur-md rounded-[2.5rem] border border-white/5 p-8 shadow-xl">
                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-8 text-center">Analisis Proporsi</h4>
                
                @php
                    $tot = $summary->total ?: 1; // avoid div by 0
                    $pctOp = round(($summary->operasional_total / $tot) * 100);
                    $pctCo = round(($summary->consumable_total / $tot) * 100);
                    $pctBb = round(($summary->bahan_baku_total / $tot) * 100);
                    $pctVa = round(($summary->variabel_total / $tot) * 100);
                @endphp
                
                <div class="relative w-32 h-32 mx-auto mb-8 flex items-center justify-center">
                    <canvas id="expenseDoughnut"></canvas>
                    <div class="absolute flex flex-col items-center pointer-events-none">
                        <span class="text-[8px] font-black text-slate-500 uppercase tracking-widest">Total</span>
                        <span class="text-[10px] font-black text-white">100%</span>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="flex items-center justify-between text-[11px]">
                        <div class="flex items-center gap-3">
                            <div class="w-2.5 h-2.5 rounded-sm bg-blue-500"></div>
                            <span class="text-slate-400 font-bold uppercase tracking-wider">Operasional</span>
                        </div>
                        <span class="text-white font-black">{{ $pctOp }}%</span>
                    </div>
                    <div class="flex items-center justify-between text-[11px]">
                        <div class="flex items-center gap-3">
                            <div class="w-2.5 h-2.5 rounded-sm bg-emerald-500"></div>
                            <span class="text-slate-400 font-bold uppercase tracking-wider">Consumable</span>
                        </div>
                        <span class="text-white font-black">{{ $pctCo }}%</span>
                    </div>
                    <div class="flex items-center justify-between text-[11px]">
                        <div class="flex items-center gap-3">
                            <div class="w-2.5 h-2.5 rounded-sm bg-purple-500"></div>
                            <span class="text-slate-400 font-bold uppercase tracking-wider">Bahan Baku</span>
                        </div>
                        <span class="text-white font-black">{{ $pctBb }}%</span>
                    </div>
                    <div class="flex items-center justify-between text-[11px]">
                        <div class="flex items-center gap-3">
                            <div class="w-2.5 h-2.5 rounded-sm bg-amber-500"></div>
                            <span class="text-slate-400 font-bold uppercase tracking-wider">Variabel</span>
                        </div>
                        <span class="text-white font-black">{{ $pctVa }}%</span>
                    </div>
                </div>
            </div>

            <!-- RINCIAN JENIS BIAYA CHART -->
            <div class="bg-slate-800/40 backdrop-blur-md rounded-3xl border border-white/5 p-6 shadow-xl relative overflow-hidden">
                <div class="flex justify-between items-center mb-6">
                    <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Rincian Jenis Biaya</h4>
                    <a href="{{ route('expense_categories.index') }}" class="text-[9px] font-black text-blue-400 hover:text-blue-300 uppercase tracking-tighter">Master Data <i class="fas fa-chevron-right ml-1"></i></a>
                </div>
                <div class="h-64 w-full">
                    <canvas id="subCategoryChart"></canvas>
                </div>
            </div>

            <!-- GRAFIK HARIAN (Kecil) -->
            <div class="bg-slate-800/20 backdrop-blur-md rounded-3xl border border-white/5 p-4 shadow-xl relative overflow-hidden">
                <h4 class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-3">Tren Harian</h4>
                <div class="h-20 w-full">
                    <canvas id="expenseLineChart"></canvas>
                </div>
            </div>
            
        </div>
    </div>

    <!-- EXPENSE DETAIL MODAL -->
    <div x-show="showDetailModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md"
         style="display: none;">
        
        <div @click.away="showDetailModal = false" class="bg-slate-900 border border-slate-800 w-full max-w-2xl max-h-[90vh] rounded-[2.5rem] overflow-hidden shadow-2xl flex flex-col">
            <!-- Modal Header -->
            <div class="px-8 py-6 border-b border-slate-800 flex justify-between items-center bg-slate-900/50">
                <div>
                    <h3 class="text-xl font-black text-white">Rincian Pengeluaran Sistem</h3>
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-1">
                        Periode: {{ $dateFrom->format('d M') }} - {{ $dateTo->format('d M Y') }}
                    </p>
                </div>
                <button @click="showDetailModal = false" class="w-10 h-10 rounded-xl bg-slate-800 text-slate-400 hover:bg-red-600 hover:text-white transition-all flex items-center justify-center">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Modal Content -->
            <div class="p-8 overflow-y-auto hide-scrollbar flex-1 space-y-8">
                
                <!-- 1. Breakdown by Category -->
                <div>
                    <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4 flex items-center gap-3">
                        <span class="w-2 h-4 bg-blue-500 rounded-full"></span>
                        Berdasarkan Kategori
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($fullBreakdown as $item)
                        <div class="bg-slate-800/50 border border-slate-700/50 p-4 rounded-2xl flex justify-between items-center">
                            <div>
                                <p class="text-[10px] font-black text-slate-500 uppercase">{{ $item->category }}</p>
                                <p class="text-xs font-bold text-slate-400">{{ $item->count }} Transaksi</p>
                            </div>
                            <p class="text-sm font-black text-white">Rp {{ number_format($item->total, 0, ',', '.') }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- 2. Top Individual Expenses -->
                <div>
                    <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4 flex items-center gap-3">
                        <span class="w-2 h-4 bg-red-500 rounded-full"></span>
                        10 Pengeluaran Terbesar
                    </h4>
                    <div class="bg-slate-950/40 border border-slate-800 rounded-2xl overflow-hidden">
                        <table class="w-full text-left">
                            <thead class="bg-slate-900">
                                <tr class="text-[9px] font-black text-slate-500 uppercase tracking-widest">
                                    <th class="px-6 py-4">Keterangan</th>
                                    <th class="px-6 py-4">Kategori</th>
                                    <th class="px-6 py-4 text-right">Nominal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800">
                                @foreach($topSystemExpenses as $expense)
                                <tr class="hover:bg-white/5 transition-colors">
                                    <td class="px-6 py-4">
                                        <p class="text-xs font-bold text-white line-clamp-1">{{ $expense->description }}</p>
                                        <p class="text-[9px] text-slate-500 mt-0.5">{{ \Carbon\Carbon::parse($expense->transaction_date)->format('d M Y') }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-[9px] font-black text-slate-500 uppercase bg-slate-900 px-1.5 py-0.5 rounded">{{ $expense->category }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <p class="text-xs font-black text-white">Rp {{ number_format($expense->amount, 0, ',', '.') }}</p>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 3. Total Highlight -->
                <div class="bg-gradient-to-r from-red-600/20 to-red-900/20 border border-red-500/20 p-6 rounded-3xl flex justify-between items-center">
                    <div>
                        <p class="text-xs font-black text-red-400 uppercase tracking-widest">Total Keseluruhan</p>
                        <p class="text-[10px] text-red-400/60 font-bold mt-1 italic">*Sudah mengecualikan transfer internal & refund</p>
                    </div>
                    <p class="text-2xl font-black text-white">Rp {{ number_format($systemTotalExpense, 0, ',', '.') }}</p>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-8 py-6 bg-slate-900/80 border-t border-slate-800 flex justify-center">
                <button @click="showDetailModal = false" class="px-8 py-3 bg-slate-800 hover:bg-slate-700 text-white rounded-2xl font-black text-xs uppercase tracking-widest transition-all">
                    Tutup Rincian
                </button>
            </div>
        </div>
    </div>
</div>

<!-- SCRIPTS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('expenseDashboard', () => ({
            period: '{{ $activePeriod }}',
            showDetailModal: false,
            setPeriod(period, from, to) {
                this.period = period;
                document.getElementById('period').value = period;
                document.getElementById('date_from').value = from;
                document.getElementById('date_to').value = to;
                document.getElementById('dateFilterForm').submit();
            }
        }));
    });

    document.addEventListener('DOMContentLoaded', function() {
        Chart.defaults.color = '#94a3b8';
        Chart.defaults.font.family = "'Inter', 'Nunito', sans-serif";

        // Doughnut Chart
        const ctxDoughnut = document.getElementById('expenseDoughnut');
        if(ctxDoughnut) {
            new Chart(ctxDoughnut, {
                type: 'doughnut',
                data: {
                    labels: ['Operasional', 'Consumable', 'Bahan Baku', 'Variabel'],
                    datasets: [{
                        data: [{{ $summary->operasional_total ?: 0 }}, {{ $summary->consumable_total ?: 0 }}, {{ $summary->bahan_baku_total ?: 0 }}, {{ $summary->variabel_total ?: 0 }}],
                        backgroundColor: ['#3b82f6', '#10b981', '#a855f7', '#f59e0b'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: { cutout: '75%', plugins: { legend: { display: false } } }
            });
        }

        // Sub Category Chart (Horizontal Bar)
        const ctxSub = document.getElementById('subCategoryChart');
        const subCatData = @json($expenseBySubCategory);
        if(ctxSub && subCatData.length > 0) {
            new Chart(ctxSub, {
                type: 'bar',
                data: {
                    labels: subCatData.map(d => d.sub_category || 'Lainnya'),
                    datasets: [{
                        label: 'Total Biaya',
                        data: subCatData.map(d => d.total),
                        backgroundColor: function(context) {
                            const colors = ['#3b82f6', '#10b981', '#a855f7', '#f59e0b', '#ef4444', '#6366f1', '#06b6d4', '#ec4899'];
                            return colors[context.dataIndex % colors.length];
                        },
                        borderRadius: 8,
                        barThickness: 12
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return ' Rp ' + context.raw.toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    scales: {
                        x: { display: false, grid: { display: false } },
                        y: { 
                            grid: { display: false },
                            ticks: { 
                                font: { size: 9, weight: 'bold' },
                                padding: 10
                            }
                        }
                    }
                }
            });
        }

        // Line Chart
        const ctxLine = document.getElementById('expenseLineChart');
        const rawChartData = @json($chartData);
        if(ctxLine && rawChartData.length > 0) {
            new Chart(ctxLine, {
                type: 'line',
                data: {
                    labels: rawChartData.map(d => d.date),
                    datasets: [{
                        label: 'Pengeluaran',
                        data: rawChartData.map(d => d.total),
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 2,
                        pointRadius: 0
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { display: false },
                        y: { display: false, min: 0 }
                    }
                }
            });
        }
    });
</script>
<style>
    .hide-scrollbar::-webkit-scrollbar { display: none; }
    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endsection
