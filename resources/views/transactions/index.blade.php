@extends('layouts.app')

@section('title', 'Transaksi')
@section('page-title', 'Riwayat Transaksi')

@section('content')
<div x-data="posApp()" x-init="loadPrinterSettings()">
    <div class="space-y-6">
    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
        <div class="bg-slate-800/40 border border-slate-700/50 rounded-2xl p-4 hover:bg-slate-800/60 transition-all shadow-sm">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Omset Hari Ini</p>
            <p class="text-lg font-black text-emerald-400">Rp {{ number_format($todayTotalSales, 0, ',', '.') }}</p>
        </div>
        <div class="bg-slate-800/40 border border-slate-700/50 rounded-2xl p-4 hover:bg-slate-800/60 transition-all shadow-sm flex items-center gap-2 md:gap-4">
            <div class="w-10 h-10 md:w-12 md:h-12 rounded-2xl bg-red-500/10 border border-red-500/20 text-red-400 flex items-center justify-center shrink-0">
                <i class="fas fa-wallet text-lg md:text-xl"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Total Biaya</p>
                <p class="text-base md:text-xl font-black text-red-400 leading-none mb-2 truncate">Rp {{ number_format($todayExpenses, 0, ',', '.') }}</p>
                <div class="space-y-0.5">
                    <div class="flex items-center gap-1.5 text-[8px] md:text-[9px] truncate">
                        <i class="fas fa-money-bill-wave text-emerald-400 w-3 text-center"></i>
                        <span class="text-slate-500 font-semibold">Tunai:</span>
                        <span class="font-bold text-slate-300">Rp {{ number_format($todayCashExpense, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center gap-1.5 text-[8px] md:text-[9px] truncate">
                        <i class="fas fa-university text-blue-400 w-3 text-center"></i>
                        <span class="text-slate-500 font-semibold">Bank:</span>
                        <span class="font-bold text-slate-300">Rp {{ number_format($todayBankExpense, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-slate-800/40 border border-slate-700/50 rounded-2xl p-4 hover:bg-slate-800/60 transition-all shadow-sm">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Pendapatan Bersih</p>
            <p class="text-lg font-black {{ $todayNet >= 0 ? 'text-emerald-400' : 'text-red-400' }}">Rp {{ $todayNet < 0 ? '-' : '' }}{{ number_format(abs($todayNet), 0, ',', '.') }}</p>
        </div>
        {{-- Saldo Laci Card: Sekarang vs Awal Sesi --}}
        <div class="bg-slate-800/40 border border-slate-700/50 rounded-2xl p-4 hover:bg-slate-800/60 transition-all shadow-sm">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Saldo Laci</p>
            @if($activeShift)
                @php
                    $selisih = $saldoLaci - $saldoLaciAwal;
                    $naik = $selisih >= 0;
                @endphp
                <p class="text-lg font-black text-blue-400">Rp {{ number_format($saldoLaci, 0, ',', '.') }}</p>
                <div class="mt-1.5 flex items-center justify-between gap-1">
                    <p class="text-[10px] text-slate-500 truncate">
                        <span class="text-slate-600 font-semibold">Awal sesi:</span>
                        Rp {{ number_format($saldoLaciAwal, 0, ',', '.') }}
                    </p>
                    <span class="text-[10px] font-black px-1.5 py-0.5 rounded-full shrink-0
                        {{ $naik ? 'bg-emerald-500/10 text-emerald-400' : 'bg-red-500/10 text-red-400' }}">
                        {{ $naik ? '+' : '' }}{{ number_format($selisih, 0, ',', '.') }}
                    </span>
                </div>
            @else
                <p class="text-lg font-black text-blue-400">Rp {{ number_format($saldoLaci, 0, ',', '.') }}</p>
                <p class="text-[10px] text-slate-600 mt-1">Tidak ada sesi aktif</p>
            @endif
        </div>

        <div class="bg-slate-800/40 border border-slate-700/50 rounded-2xl p-4 hover:bg-slate-800/60 transition-all shadow-sm">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Total Piutang</p>
            <p class="text-lg font-black text-orange-400">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Breakdown Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-slate-800/40 border border-slate-700/50 rounded-2xl p-4 hover:bg-slate-800/60 transition-all shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Pemasukan QRIS (Hari Ini)</p>
                <p class="text-lg font-black text-purple-400">Rp {{ number_format($todayQris, 0, ',', '.') }}</p>
            </div>
            <div class="w-10 h-10 rounded-full bg-purple-500/10 text-purple-400 flex items-center justify-center text-xl"><i class="fas fa-qrcode"></i></div>
        </div>
        <div class="bg-slate-800/40 border border-slate-700/50 rounded-2xl p-4 hover:bg-slate-800/60 transition-all shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Pemasukan Tunai (Hari Ini)</p>
                <p class="text-lg font-black text-emerald-400">Rp {{ number_format($todayCash, 0, ',', '.') }}</p>
            </div>
            <div class="w-10 h-10 rounded-full bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-xl"><i class="fas fa-money-bill-wave"></i></div>
        </div>
        <div class="bg-slate-800/40 border border-slate-700/50 rounded-2xl p-4 hover:bg-slate-800/60 transition-all shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Pemasukan Transfer (Hari Ini)</p>
                <p class="text-lg font-black text-blue-400">Rp {{ number_format($todayTransfer, 0, ',', '.') }}</p>
            </div>
            <div class="w-10 h-10 rounded-full bg-blue-500/10 text-blue-400 flex items-center justify-center text-xl"><i class="fas fa-building-columns"></i></div>
        </div>
    </div>

    {{-- Filter Pills & Search (Santai Scale Style) --}}
    <div class="space-y-4 bg-slate-800/20 p-6 rounded-[2rem] border border-white/5 shadow-xl">
        {{-- Row 1: Type & Date Filters --}}
        <div class="flex flex-col lg:flex-row justify-between gap-4">
            {{-- Type Filter --}}
            <div class="flex flex-wrap items-center gap-2">
                @php
                    $curType = request('type');
                    $pillBase = 'px-4 py-2 rounded-xl text-[11px] font-black whitespace-nowrap transition-all border inline-flex items-center gap-2 uppercase tracking-wider';
                    $pillActive = 'bg-blue-600 text-white border-blue-500 shadow-lg shadow-blue-900/20';
                    $pillInactive = 'bg-slate-800/60 border-white/5 text-slate-400 hover:bg-slate-700 hover:text-white';
                @endphp
                <a href="{{ route('transactions.index', array_filter(request()->except(['type','page','status','payment_method', 'shift']))) }}" 
                   class="{{ $pillBase }} {{ (!$curType && request('shift') !== 'live') ? $pillActive : $pillInactive }}">
                    <i class="fas fa-list"></i> Semua <span class="bg-white/20 text-white px-1.5 py-0.5 rounded-lg ml-1">{{ $countAll }}</span>
                </a>

                @if($activeShift)
                <a href="{{ route('transactions.index', array_merge(request()->except(['page','status','payment_method']), ['shift' => 'live'])) }}" 
                   class="{{ $pillBase }} {{ request('shift') === 'live' ? 'bg-rose-600 text-white border-rose-500 shadow-lg shadow-rose-900/20' : $pillInactive }}">
                    <i class="fas fa-satellite-dish {{ request('shift') === 'live' ? 'animate-pulse' : '' }}"></i> LIVE SHIFT
                </a>
                @endif
                <a href="{{ route('transactions.index', array_merge(request()->except(['page','status','payment_method']), ['type' => 'penjualan'])) }}" 
                   class="{{ $pillBase }} {{ $curType === 'penjualan' ? $pillActive : $pillInactive }}">
                    <i class="fas fa-arrow-down text-emerald-400"></i> Pemasukan <span class="bg-emerald-500/20 text-emerald-400 px-1.5 py-0.5 rounded-lg ml-1">{{ $countPenjualan }}</span>
                </a>
                <a href="{{ route('transactions.index', array_merge(request()->except(['page','status','payment_method']), ['type' => 'expense'])) }}" 
                   class="{{ $pillBase }} {{ $curType === 'expense' ? 'bg-red-500/20 text-red-400 border-red-500/30' : $pillInactive }}">
                    <i class="fas fa-arrow-up text-red-400"></i> Pengeluaran <span class="bg-red-500/20 text-red-400 px-1.5 py-0.5 rounded-lg ml-1">{{ $countExpense }}</span>
                </a>

                <div class="h-8 w-px bg-white/5 mx-2"></div>
                
                <button onclick="window.openExportModal()" class="w-10 h-10 bg-slate-800 border border-white/5 text-slate-400 rounded-xl hover:bg-slate-700 hover:text-white transition-premium flex items-center justify-center shadow-lg" title="Ekspor Laporan (PDF/Excel/CSV)">
                    <i class="fas fa-file-export"></i>
                </button>
            </div>

            <div class="flex items-center gap-2">
                {{-- Date filter removed as per user request --}}
            </div>
        </div>

        {{-- Row 2: Status & Method Filters --}}
        <div class="flex flex-wrap items-center gap-2 border-t border-white/5 pt-4">
            @php
                $curType = request('type');
                $curStatus = request('status');
                $curMethod = request('payment_method');
                $pillBase = 'px-3 py-1.5 rounded-lg text-[10px] font-black whitespace-nowrap transition-all border inline-flex items-center gap-2 uppercase tracking-widest';
                $pillActive = 'bg-slate-700 text-white border-slate-600';
                $pillInactive = 'bg-slate-800/40 border-white/5 text-slate-500 hover:bg-slate-700 hover:text-white';
            @endphp
            
            @if($curType === 'expense')
                <a href="{{ route('transactions.index', array_filter(request()->except(['payment_method','page']))) }}" 
                   class="{{ $pillBase }} {{ !$curMethod ? $pillActive : $pillInactive }}">Semua Pengeluaran</a>
                
                <a href="{{ route('transactions.index', array_merge(request()->except(['page']), ['payment_method' => 'cash'])) }}" 
                   class="{{ $pillBase }} {{ $curMethod === 'cash' ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30' : $pillInactive }}">
                   <i class="fas fa-wallet"></i> Tunai
                </a>
                <a href="{{ route('transactions.index', array_merge(request()->except(['page']), ['payment_method' => 'bank'])) }}" 
                   class="{{ $pillBase }} {{ $curMethod === 'bank' ? 'bg-blue-500/20 text-blue-400 border-blue-500/30' : $pillInactive }}">
                   <i class="fas fa-university"></i> Bank / Transfer
                </a>
            @else
                <a href="{{ route('transactions.index', array_filter(request()->except(['status','payment_method','page']))) }}" 
                   class="{{ $pillBase }} {{ !$curStatus && !$curMethod ? $pillActive : $pillInactive }}">Semua Pemasukan</a>
                
                <a href="{{ route('transactions.index', array_merge(request()->except(['page']), ['status' => 'piutang', 'payment_method' => ''])) }}" 
                   class="{{ $pillBase }} {{ $curStatus === 'piutang' ? 'bg-orange-500/20 text-orange-400 border-orange-500/30' : $pillInactive }}">
                   <i class="fas fa-clock"></i> Piutang
                </a>
                <a href="{{ route('transactions.index', array_merge(request()->except(['page']), ['status' => 'lunas', 'payment_method' => ''])) }}" 
                   class="{{ $pillBase }} {{ $curStatus === 'lunas' ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30' : $pillInactive }}">
                   <i class="fas fa-check-circle"></i> Lunas (Piutang)
                </a>

                <div class="h-4 w-px bg-white/5 mx-2"></div>

                <a href="{{ route('transactions.index', array_merge(request()->except(['page']), ['payment_method' => 'cash', 'status' => ''])) }}" 
                   class="{{ $pillBase }} {{ $curMethod === 'cash' && !$curStatus ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30' : $pillInactive }}">Tunai</a>
                <a href="{{ route('transactions.index', array_merge(request()->except(['page']), ['payment_method' => 'qris', 'status' => ''])) }}" 
                   class="{{ $pillBase }} {{ $curMethod === 'qris' && !$curStatus ? 'bg-purple-500/20 text-purple-400 border-purple-500/30' : $pillInactive }}">QRIS</a>
                <a href="{{ route('transactions.index', array_merge(request()->except(['page']), ['payment_method' => 'transfer', 'status' => ''])) }}" 
                   class="{{ $pillBase }} {{ $curMethod === 'transfer' && !$curStatus ? 'bg-blue-500/20 text-blue-400 border-blue-500/30' : $pillInactive }}">Transfer</a>
            @endif
        </div>

        {{-- Row 3: Users & Search --}}
        <div class="flex flex-wrap items-center justify-between gap-4 border-t border-white/5 pt-4">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest mr-2">Petugas:</span>
                <a href="{{ route('transactions.index', request()->except(['user_id','page'])) }}" 
                   class="px-3 py-1.5 rounded-lg text-[9px] font-black transition-all {{ !request('user_id') ? 'bg-slate-700 text-white' : 'bg-slate-800/40 border border-white/5 text-slate-500 hover:bg-slate-700 hover:text-white' }}">Semua</a>
                @foreach($users as $u)
                <a href="{{ route('transactions.index', array_merge(request()->except(['page']), ['user_id' => $u->id])) }}" 
                   class="px-3 py-1.5 rounded-lg text-[9px] font-black transition-all {{ request('user_id') == $u->id ? 'bg-slate-700 text-white' : 'bg-slate-800/40 border border-white/5 text-slate-500 hover:bg-slate-700 hover:text-white' }}">{{ $u->name }}</a>
                @endforeach
            </div>

            <div class="flex items-center gap-3">
                <form action="" method="GET" class="flex items-center" onchange="this.submit()">
                    @foreach(request()->except(['per_page', 'page']) as $key => $val)
                        @if(is_array($val))
                            @foreach($val as $v)
                                <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                            @endforeach
                        @elseif($val)
                            <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                        @endif
                    @endforeach
                    <select name="per_page" class="bg-slate-900/60 border border-white/5 text-slate-300 text-[10px] font-black uppercase tracking-widest rounded-xl px-4 py-2 hover:bg-slate-800 transition-all focus:outline-none focus:border-blue-500/50 cursor-pointer h-[38px]">
                        <option value="5" {{ request('per_page') == 5 ? 'selected' : '' }}>5 Baris</option>
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10 Baris</option>
                        <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20 Baris</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 Baris</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 Baris</option>
                    </select>
                </form>

                <form method="GET" class="relative group">
                    @foreach(request()->except(['search','page']) as $key => $val)
                        @if(is_array($val))
                            @foreach($val as $v)
                                <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                            @endforeach
                        @elseif($val)
                            <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                        @endif
                    @endforeach
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 text-xs group-focus-within:text-blue-400 transition-colors"></i>
                    <input type="text" name="search" value="{{ is_array(request('search')) ? '' : request('search') }}" 
                           class="bg-slate-900/60 border border-white/5 rounded-xl pl-10 pr-4 py-2 text-xs text-white placeholder-slate-600 focus:border-blue-500/50 focus:ring-4 focus:ring-blue-500/5 outline-none w-full md:w-64 transition-all h-[38px]" 
                           placeholder="Cari Invoice atau Pelanggan...">
                </form>
            </div>
        </div>
    </div>

    {{-- Detailed Table List --}}
    <div class="bg-slate-800/40 border border-slate-700/50 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-900/50 border-b border-slate-700">
                        <th class="px-4 py-3 text-[10px] font-black uppercase tracking-wider text-slate-400 whitespace-nowrap">No. Pesanan</th>
                        <th class="px-4 py-3 text-[10px] font-black uppercase tracking-wider text-slate-400 whitespace-nowrap">Tanggal</th>
                        <th class="px-4 py-3 text-[10px] font-black uppercase tracking-wider text-slate-400 whitespace-nowrap">Pelanggan / Meja</th>
                        <th class="px-4 py-3 text-[10px] font-black uppercase tracking-wider text-slate-400 whitespace-nowrap">Produk</th>
                        <th class="px-4 py-3 text-[10px] font-black uppercase tracking-wider text-slate-400 text-center whitespace-nowrap">Qty</th>
                        <th class="px-4 py-3 text-[10px] font-black uppercase tracking-wider text-slate-400 text-center whitespace-nowrap">Status</th>
                        <th class="px-4 py-3 text-[10px] font-black uppercase tracking-wider text-slate-400 text-right whitespace-nowrap">Harga Jual</th>
                        <th class="px-4 py-3 text-[10px] font-black uppercase tracking-wider text-slate-400 text-right whitespace-nowrap">Diskon</th>
                        <th class="px-4 py-3 text-[10px] font-black uppercase tracking-wider text-slate-400 text-right whitespace-nowrap">Piutang</th>
                        <th class="px-4 py-3 text-[10px] font-black uppercase tracking-wider text-slate-400 text-right whitespace-nowrap">HPP</th>
                        <th class="px-4 py-3 text-[10px] font-black uppercase tracking-wider text-slate-400 text-right whitespace-nowrap">Gross Profit</th>
                        <th class="px-4 py-3 text-[10px] font-black uppercase tracking-wider text-slate-400 text-right whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/30">
                    @forelse($transactions as $entry)
                        @php
                            $entryType = $entry->type;
                            $model = $entry->model;
                        @endphp

                        @if($entryType === 'penjualan')
                            @php
                                $isPending = $model->status === 'pending';
                                $isCancelled = $model->status === 'cancelled';
                                $isLunas = $model->status === 'completed' && $model->payment_method === 'piutang';
                                $statusColor = $isPending ? 'orange' : ($isCancelled ? 'red' : 'emerald');
                                $statusIcon = $isPending ? 'fa-hourglass-half' : ($isCancelled ? 'fa-times' : 'fa-check');
                                $pmInfo = [
                                    'cash' => ['icon' => 'fa-money-bill-wave', 'color' => 'emerald', 'label' => 'Tunai'],
                                    'transfer' => ['icon' => 'fa-building-columns', 'color' => 'blue', 'label' => 'Transfer'],
                                    'qris' => ['icon' => 'fa-qrcode', 'color' => 'purple', 'label' => 'QRIS'],
                                    'debit' => ['icon' => 'fa-credit-card', 'color' => 'orange', 'label' => 'Debit'],
                                    'piutang' => ['icon' => 'fa-hand-holding-dollar', 'color' => 'orange', 'label' => 'Piutang'],
                                ];
                                $pm = $pmInfo[$model->payment_method] ?? ['icon' => 'fa-wallet', 'color' => 'slate', 'label' => ucfirst($model->payment_method)];
                            @endphp
                            <tr class="hover:bg-slate-800/60 transition-all group {{ $isCancelled ? 'opacity-50' : '' }}">
                                <td class="px-4 py-4">
                                    <p class="text-xs font-black text-white mb-0.5">{{ $model->invoice_number }}</p>
                                    <span class="text-[10px] font-bold text-slate-500 uppercase">{{ $model->user->name }}</span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <p class="text-xs font-bold text-slate-300">{{ $model->created_at->format('d/m/Y') }}</p>
                                    <p class="text-[10px] text-slate-500">{{ $model->created_at->format('H:i') }}</p>
                                </td>
                                <td class="px-4 py-4">
                                    <p class="text-xs font-bold text-slate-300">{{ $model->customer_name ?: 'Umum' }}</p>
                                    @if($model->table_number)
                                        <span class="text-[10px] font-black text-blue-400 uppercase">Meja {{ $model->table_number }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    <div class="max-w-[250px]">
                                        @php $itemCount = $model->items->count(); @endphp
                                        @if($itemCount > 0)
                                            @php $firstItem = $model->items->first(); @endphp
                                            <p class="text-xs font-medium text-slate-300 flex items-center gap-1 flex-wrap">
                                                <span>{{ $firstItem->product_name }}</span>
                                                @if($firstItem->is_custom_price)
                                                    <span class="bg-orange-100 text-orange-600 border border-orange-200 text-[8px] font-black px-1 py-0.5 rounded uppercase" title="Harga Khusus">Khusus</span>
                                                @endif
                                            </p>
                                            <p class="text-[10px] text-slate-500 mt-0.5">
                                                {{ $firstItem->quantity }}x @ Rp {{ number_format($firstItem->is_custom_price ? $firstItem->custom_price : $firstItem->price, 0, ',', '.') }}
                                                @if($itemCount > 1) <span class="text-slate-400 ml-1 font-bold">+{{ $itemCount - 1 }} lainnya</span> @endif
                                            </p>
                                        @else
                                            <p class="text-xs font-medium text-slate-400 truncate">-</p>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span class="text-xs font-black text-white">{{ $model->items->sum('quantity') }}</span>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg bg-{{ $statusColor }}-500/10 text-{{ $statusColor }}-400 border border-{{ $statusColor }}-500/20 text-[10px] font-black uppercase">
                                        {{ $isPending ? 'PIUTANG' : ($isCancelled ? 'BATAL' : ($isLunas ? 'LUNAS' : 'LUNAS')) }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <p class="text-xs font-black text-emerald-400">Rp {{ number_format($model->total, 0, ',', '.') }}</p>
                                    <span class="text-[10px] text-slate-600 font-bold uppercase">{{ $pm['label'] }}</span>
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <p class="text-xs font-bold text-red-400">Rp {{ number_format($model->discount, 0, ',', '.') }}</p>
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <p class="text-xs font-bold text-orange-400">Rp {{ number_format($model->remaining, 0, ',', '.') }}</p>
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <p class="text-xs font-bold text-slate-400">Rp {{ number_format($model->total_cost, 0, ',', '.') }}</p>
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <p class="text-xs font-black text-blue-400">Rp {{ number_format($model->gross_profit, 0, ',', '.') }}</p>
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5 sm:opacity-100 opacity-0 group-hover:opacity-100 transition-opacity">
                                        {{-- Detail --}}
                                        <button onclick="document.getElementById('detail-modal-{{ $model->id }}').classList.remove('hidden')" class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 flex items-center justify-center transition-all" title="Detail"><i class="fas fa-eye text-xs"></i></button>
                                        {{-- Pelunasan --}}
                                        @if($isPending)
                                            <button onclick="document.getElementById('pay-modal-{{ $model->id }}').classList.remove('hidden')" class="w-8 h-8 rounded-lg bg-orange-500/10 hover:bg-orange-500/20 text-orange-400 flex items-center justify-center transition-all" title="Pelunasan Piutang"><i class="fas fa-hand-holding-dollar text-xs"></i></button>
                                        @endif
                                        {{-- Edit --}}
                                        @if(!$isCancelled && auth()->user()->hasPermission('transactions.edit'))
                                            <a href="{{ route('pos.index', ['edit' => $model->id]) }}" class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 flex items-center justify-center transition-all" title="Edit"><i class="fas fa-pencil text-xs"></i></a>
                                        @endif
                                        {{-- Print --}}
                                        <button @click="doPrint('{{ $model->id }}')" class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 flex items-center justify-center transition-all" title="Print Struk POS"><i class="fas fa-print text-xs"></i></button>
                                        {{-- Invoice Generator --}}
                                        <a href="{{ route('invoices.create', ['transaction_id' => $model->id]) }}" class="w-8 h-8 rounded-lg bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 flex items-center justify-center transition-all" title="Buat Invoice (Official)"><i class="fas fa-file-invoice text-xs"></i></a>
                                        {{-- Hapus / Batal --}}
                                        @if(auth()->user()->hasPermission('transactions.delete'))
                                            <form action="{{ route('transactions.cancel', $model) }}" method="POST" class="inline">@csrf
                                                <button type="button" 
                                                        onclick="Swal.fire({
                                                            title: 'Hapus Transaksi?',
                                                            text: 'Transaksi {{ $model->invoice_number }} akan dibatalkan dan stok produk akan dikembalikan.',
                                                            icon: 'warning',
                                                            showCancelButton: true,
                                                            confirmButtonColor: '#ef4444',
                                                            cancelButtonColor: '#64748b',
                                                            confirmButtonText: 'Ya, Hapus!',
                                                            cancelButtonText: 'Batal'
                                                        }).then((result) => {
                                                            if (result.isConfirmed) this.closest('form').submit();
                                                        })"
                                                        class="w-8 h-8 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-400 flex items-center justify-center transition-all" title="Hapus"><i class="fas fa-trash text-xs"></i></button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            
                            {{-- Detail Transaksi Modal (Santai Scale Style) --}}
                            <div id="detail-modal-{{ $model->id }}" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                                <div class="bg-white rounded-3xl w-full max-w-sm shadow-2xl border border-slate-200 max-h-[90vh] overflow-y-auto max-h-[90vh] overflow-y-auto scrollbar-hide ">
                                    <div class="p-5 border-b border-slate-100 flex justify-between items-center sticky top-0 bg-white rounded-t-3xl z-10">
                                        <h3 class="text-lg font-black text-slate-800">Detail Transaksi</h3>
                                        <button onclick="this.closest('.fixed').classList.add('hidden')" class="w-8 h-8 bg-slate-100 text-slate-500 rounded-full hover:bg-slate-200 hover:text-slate-800 transition-colors"><i class="fas fa-times"></i></button>
                                    </div>
                                    <div class="p-5 space-y-4">
                                        {{-- Product list --}}
                                        @foreach($model->items as $item)
                                        <div class="flex justify-between text-sm">
                                            <div>
                                                <div class="flex items-center gap-1.5">
                                                    <p class="font-bold text-slate-800">{{ $item->product_name }}</p>
                                                    @if($item->is_custom_price)
                                                        <span class="bg-orange-100 text-orange-600 border border-orange-200 text-[8px] font-black px-1.5 py-0.5 rounded uppercase" title="Harga Khusus">Khusus</span>
                                                    @endif
                                                </div>
                                                <p class="text-slate-400 text-xs">{{ $item->quantity }}x @ Rp {{ number_format($item->is_custom_price ? $item->custom_price : $item->price, 0, ',', '.') }}</p>
                                            </div>
                                            <p class="font-bold text-slate-700">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</p>
                                        </div>
                                        @endforeach

                                        <hr class="border-slate-100">

                                        {{-- Info rows --}}
                                        <div class="space-y-2 text-sm">
                                            <div class="flex justify-between"><span class="text-slate-500">No. Pesanan</span><span class="font-bold text-slate-700">{{ $model->invoice_number }}</span></div>
                                            <div class="flex justify-between"><span class="text-slate-500">Tanggal</span><span class="font-bold text-slate-700">{{ $model->created_at->translatedFormat('d F Y') }}</span></div>
                                            <div class="flex justify-between"><span class="text-slate-500">Jam</span><span class="font-bold text-slate-700">{{ $model->created_at->format('H:i') }}</span></div>
                                            <div class="flex justify-between"><span class="text-slate-500">Kasir</span><span class="font-bold text-slate-700">{{ $model->user->name }}</span></div>
                                            <div class="flex justify-between"><span class="text-slate-500">Pembayaran</span><span class="font-bold text-slate-700">{{ ucfirst($model->payment_method) }}</span></div>
                                            @if($model->payment_method === 'piutang')
                                            <div class="flex justify-between"><span class="text-slate-500">Status</span>
                                                @if($isPending)<span class="font-bold text-orange-500">Belum Lunas</span>
                                                @else<span class="font-bold text-emerald-500">Lunas</span>@endif
                                            </div>
                                            @if($model->paid_so_far > 0)
                                            <div class="flex justify-between"><span class="text-slate-500">DP Dibayar</span><span class="font-bold text-slate-700">Rp {{ number_format($model->paid_so_far, 0, ',', '.') }}</span></div>
                                            @endif
                                            @endif
                                        </div>

                                        <hr class="border-slate-100">

                                        {{-- Totals --}}
                                        <div class="space-y-2 text-sm">
                                            @if($model->delivery_fee > 0)
                                            <div class="flex justify-between"><span class="text-slate-500">Ongkir{{ $model->delivery_destination ? ' (' . $model->delivery_destination . ')' : '' }}</span><span class="font-medium text-slate-700">Rp {{ number_format($model->delivery_fee, 0, ',', '.') }}</span></div>
                                            @endif
                                            <div class="flex justify-between"><span class="text-slate-500">Subtotal</span><span class="font-medium text-slate-700">Rp {{ number_format($model->subtotal, 0, ',', '.') }}</span></div>
                                            @if($model->discount > 0)
                                            <div class="flex justify-between"><span class="text-slate-500">Diskon</span><span class="font-medium text-red-500">-Rp {{ number_format($model->discount, 0, ',', '.') }}</span></div>
                                            @endif
                                            @if($model->tax > 0)
                                            <div class="flex justify-between"><span class="text-slate-500">Pajak</span><span class="font-medium text-slate-700">Rp {{ number_format($model->tax, 0, ',', '.') }}</span></div>
                                            @endif
                                        </div>

                                        <div class="bg-slate-50 rounded-2xl p-4 flex justify-between items-center border border-slate-100">
                                            <span class="font-black text-slate-700">Total</span>
                                            <span class="text-xl font-black text-emerald-600">Rp {{ number_format($model->total, 0, ',', '.') }}</span>
                                        </div>

                                        {{-- Action buttons --}}
                                        <div class="flex gap-3 pt-2">
                                            <button @click="doPrint('{{ $model->id }}')" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 rounded-xl transition-all text-center text-sm flex items-center justify-center gap-2">
                                                <i class="fas fa-print"></i> Cetak Struk
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Piutang Payment Modal (Santai Scale Style) --}}
                            @if($isPending)
                            <div id="pay-modal-{{ $model->id }}" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" 
                                 x-data="{ 
                                    pelunasanMethod: 'cash', 
                                    pelunasanAmount: {{ $model->remaining }},
                                    get kembalian() { return Math.max(0, this.pelunasanAmount - {{ $model->remaining }}) }
                                 }">
                                <div class="bg-white rounded-3xl w-full max-w-sm shadow-2xl border border-slate-200 max-h-[90vh] overflow-y-auto max-h-[90vh] overflow-y-auto scrollbar-hide ">
                                    {{-- Header --}}
                                    <div class="p-5 border-b border-slate-100 flex justify-between items-center sticky top-0 bg-white rounded-t-3xl z-10">
                                        <h3 class="text-lg font-black text-slate-800">Detail Pelunasan</h3>
                                        <button onclick="this.closest('.fixed').classList.add('hidden')" class="w-8 h-8 bg-slate-100 text-slate-500 rounded-full hover:bg-slate-200 hover:text-slate-800 transition-colors"><i class="fas fa-times"></i></button>
                                    </div>

                                    <form action="{{ route('transactions.pay', $model) }}" method="POST">
                                        @csrf
                                        <div class="p-5 space-y-5">
                                            {{-- Summary --}}
                                            <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4 space-y-2 text-sm">
                                                <div class="flex justify-between">
                                                    <span class="text-slate-500 font-medium">Total Nilai Pesanan:</span>
                                                    <span class="font-bold text-slate-800">Rp {{ number_format($model->total, 0, ',', '.') }}</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="text-slate-500 font-medium">Uang Muka (DP) Dibayar:</span>
                                                    <span class="font-bold text-red-500">- Rp {{ number_format($model->paid_so_far, 0, ',', '.') }}</span>
                                                </div>
                                            </div>

                                            {{-- Nominal Pelunasan --}}
                                            <div class="bg-red-50 border-2 border-red-100 rounded-2xl p-5 text-center">
                                                <p class="text-xs font-black text-red-600 uppercase tracking-wider mb-1">Nominal Pelunasan:</p>
                                                <p class="text-3xl font-black text-red-600 tracking-tight">Rp {{ number_format($model->remaining, 0, ',', '.') }}</p>
                                            </div>

                                            {{-- Metode Pelunasan --}}
                                            <div>
                                                <label class="text-xs font-black text-slate-600 uppercase tracking-wider mb-3 block">Metode Pelunasan</label>
                                                <div class="flex gap-2">
                                                    <button type="button" @click="pelunasanMethod = 'cash'" 
                                                        :class="pelunasanMethod === 'cash' ? 'bg-emerald-500 text-white border-emerald-500 shadow-lg' : 'bg-white text-slate-600 border-slate-200 hover:border-emerald-300'"
                                                        class="flex-1 py-2.5 px-3 border-2 rounded-xl text-sm font-bold transition-all">Tunai</button>
                                                    <button type="button" @click="pelunasanMethod = 'qris'" 
                                                        :class="pelunasanMethod === 'qris' ? 'bg-emerald-500 text-white border-emerald-500 shadow-lg' : 'bg-white text-slate-600 border-slate-200 hover:border-emerald-300'"
                                                        class="flex-1 py-2.5 px-3 border-2 rounded-xl text-sm font-bold transition-all">QRIS</button>
                                                    <button type="button" @click="pelunasanMethod = 'transfer'" 
                                                        :class="pelunasanMethod === 'transfer' ? 'bg-emerald-500 text-white border-emerald-500 shadow-lg' : 'bg-white text-slate-600 border-slate-200 hover:border-emerald-300'"
                                                        class="flex-1 py-2.5 px-3 border-2 rounded-xl text-sm font-bold transition-all">Transfer</button>
                                                </div>
                                                <input type="hidden" name="payment_method" :value="pelunasanMethod">
                                            </div>

                                            {{-- Jumlah Uang Diterima --}}
                                            <div>
                                                <label class="text-xs font-black text-slate-600 uppercase tracking-wider mb-2 block">Jumlah Uang Diterima</label>
                                                <input type="number" name="amount" x-model.number="pelunasanAmount" min="1" max="{{ $model->remaining }}" class="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-4 py-3 text-lg font-black text-slate-800 outline-none focus:border-emerald-500 focus:bg-white transition-colors" required>
                                            </div>

                                            {{-- Kembalian --}}
                                            <div class="flex justify-between items-center bg-slate-50 p-4 rounded-2xl border border-slate-100" x-show="pelunasanMethod === 'cash'">
                                                <span class="text-sm font-bold text-slate-500">Kembalian:</span>
                                                <span class="text-xl font-black text-emerald-600 tracking-tight" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(kembalian)"></span>
                                            </div>

                                            {{-- Catatan --}}
                                            <input type="text" name="notes" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-700 outline-none focus:border-emerald-500 transition-colors" placeholder="Catatan (opsional)">

                                            {{-- Button --}}
                                            <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-black py-4 rounded-xl transition-all shadow-lg shadow-emerald-500/30 active:scale-[0.98] flex items-center justify-center gap-2 text-lg">
                                                <i class="fas fa-check-circle"></i> Konfirmasi Pelunasan
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            @endif
                        @elseif($entryType === 'expense')
                            @php
                                $isBankExpense = in_array($model->source, ['pos_bank', 'transfer', 'bank']);
                            @endphp
                            <tr class="{{ $isBankExpense ? 'bg-blue-500/5 hover:bg-blue-500/10' : 'bg-red-500/5 hover:bg-red-500/10' }} transition-all group">
                                <td class="px-4 py-4">
                                    <p class="text-xs font-black {{ $isBankExpense ? 'text-blue-400' : 'text-red-400' }} mb-0.5">
                                        {{ $isBankExpense ? 'EXP. BANK' : 'EXP. TUNAI' }}
                                    </p>
                                    <span class="text-[10px] font-bold text-slate-500 uppercase">{{ $model->user->name ?? '-' }}</span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <p class="text-xs font-bold text-slate-300">{{ $model->created_at ? $model->created_at->format('d/m/Y') : ($model->transaction_date ? \Carbon\Carbon::parse($model->transaction_date)->format('d/m/Y') : '-') }}</p>
                                    <p class="text-[10px] text-slate-500">{{ $model->created_at ? $model->created_at->format('H:i') : '' }}</p>
                                </td>
                                <td class="px-4 py-4">
                                    <p class="text-xs font-bold text-slate-300">{{ $model->category }}</p>
                                </td>
                                <td class="px-4 py-4" colspan="3">
                                    <p class="text-xs font-medium text-slate-400">{{ $model->description }}</p>
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <p class="text-xs font-black {{ $isBankExpense ? 'text-blue-400' : 'text-red-400' }}">-Rp {{ number_format($model->amount, 0, ',', '.') }}</p>
                                </td>
                                <td class="px-4 py-4 text-right">-</td>
                                <td class="px-4 py-4 text-right">-</td>
                                <td class="px-4 py-4 text-right">-</td>
                                <td class="px-4 py-4 text-right">-</td>
                                <td class="px-4 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5 sm:opacity-100 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button onclick="document.getElementById('expense-detail-modal-{{ $model->id }}').classList.remove('hidden')" class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 flex items-center justify-center transition-all" title="Detail"><i class="fas fa-eye text-xs"></i></button>
                                    </div>
                                </td>
                            </tr>

                            {{-- Expense Detail Modal --}}
                            <div id="expense-detail-modal-{{ $model->id }}" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                                <div class="bg-white rounded-3xl w-full max-w-sm shadow-2xl border border-slate-200 max-h-[90vh] overflow-y-auto scrollbar-hide">
                                    <div class="p-5 border-b border-slate-100 flex justify-between items-center sticky top-0 bg-white rounded-t-3xl z-10">
                                        <h3 class="text-lg font-black text-slate-800">Detail Pengeluaran</h3>
                                        <button onclick="this.closest('.fixed').classList.add('hidden')" class="w-8 h-8 bg-slate-100 text-slate-500 rounded-full hover:bg-slate-200 hover:text-slate-800 transition-colors"><i class="fas fa-times"></i></button>
                                    </div>
                                    <div class="p-5 space-y-4">
                                        @php
                                            $expLabels = \App\Models\Cashflow::sourceLabels();
                                            $expSourceLabel = $expLabels[$model->source] ?? ucfirst($model->source ?? '-');
                                            $expIsBank = in_array($model->source, ['pos_bank', 'transfer', 'bank']);
                                        @endphp

                                        <div class="bg-slate-50 rounded-2xl p-4 flex justify-between items-center border border-slate-100">
                                            <span class="font-black text-slate-700">Nominal</span>
                                            <span class="text-xl font-black text-red-600">-Rp {{ number_format($model->amount, 0, ',', '.') }}</span>
                                        </div>

                                        <div class="space-y-2 text-sm">
                                            <div class="flex justify-between">
                                                <span class="text-slate-500">Tipe</span>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-black uppercase {{ $expIsBank ? 'bg-blue-500/10 text-blue-600 border border-blue-500/20' : 'bg-red-500/10 text-red-600 border border-red-500/20' }}">
                                                    {{ $expIsBank ? 'EXP. BANK' : 'EXP. TUNAI' }}
                                                </span>
                                            </div>
                                            <div class="flex justify-between"><span class="text-slate-500">Tanggal</span><span class="font-bold text-slate-700">{{ $model->transaction_date ? \Carbon\Carbon::parse($model->transaction_date)->translatedFormat('d F Y') : '-' }}</span></div>
                                            <div class="flex justify-between"><span class="text-slate-500">Jam</span><span class="font-bold text-slate-700">{{ $model->transaction_date ? \Carbon\Carbon::parse($model->transaction_date)->format('H:i') : ($model->created_at ? $model->created_at->format('H:i') : '-') }}</span></div>
                                            <div class="flex justify-between"><span class="text-slate-500">Kategori</span><span class="font-bold text-slate-700">{{ $model->category ?: '-' }}</span></div>
                                            <div class="flex justify-between"><span class="text-slate-500">Sumber Dana</span><span class="font-bold text-slate-700">{{ $expSourceLabel }}</span></div>
                                            <div class="flex justify-between"><span class="text-slate-500">Petugas</span><span class="font-bold text-slate-700">{{ $model->user->name ?? '-' }}</span></div>
                                            @if($model->description)
                                            <div class="flex justify-between"><span class="text-slate-500">Deskripsi</span><span class="font-bold text-slate-700 text-right ml-2">{{ $model->description }}</span></div>
                                            @endif
                                            @if($model->notes)
                                            <div class="flex justify-between"><span class="text-slate-500">Catatan</span><span class="font-bold text-slate-700 text-right ml-2">{{ $model->notes }}</span></div>
                                            @endif
                                            @if($model->worksheet)
                                            <div class="flex justify-between"><span class="text-slate-500">Cabang</span><span class="font-bold text-slate-700">{{ $model->worksheet->name }}</span></div>
                                            @endif
                                            @if($model->reference)
                                            <div class="flex justify-between"><span class="text-slate-500">Referensi</span><span class="font-bold text-slate-700">{{ $model->reference }}</span></div>
                                            @endif
                                        </div>

                                        <div class="flex gap-3 pt-2">
                                            <button onclick="this.closest('.fixed').classList.add('hidden')" class="flex-1 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold py-3 rounded-xl transition-all text-center text-sm">
                                                Tutup
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @empty
                        <tr>
                            <td colspan="10" class="py-12 text-center">
                                <div class="w-16 h-16 bg-slate-900/50 rounded-full flex items-center justify-center mx-auto mb-3 shadow-inner">
                                    <i class="fas fa-box-open text-2xl text-slate-600"></i>
                                </div>
                                <h3 class="text-sm font-black text-white mb-1">Belum ada aktivitas</h3>
                                <p class="text-xs font-medium text-slate-500">Tidak ditemukan data untuk filter saat ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    @if($transactions->hasPages())
    <div class="mt-6">
        {{ $transactions->links('pagination::tailwind') }}
    </div>
    @endif

</div>
@endsection

{{-- Hidden Print Iframe --}}
<iframe id="print-iframe" style="display:none;"></iframe>

@push('scripts')
<script>
    function posApp() {
        return {
            printerStatus: 'disconnected',
            printerName: '',
            printerHandle: null,
            connectionMethod: null,
            paperSize: '58mm',
            storeFooter: 'Powered by monodev.id',

            fmt(num) {
                return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(num);
            },

            async loadPrinterSettings() {
                const saved = localStorage.getItem('pos_printer_settings');
                if (saved) {
                    const settings = JSON.parse(saved);
                    this.paperSize = settings.paperSize || '58mm';
                    this.connectionMethod = settings.connectionMethod || null;
                    this.printerName = settings.printerName || '';
                    
                    if (this.connectionMethod === 'usb_direct' && this.printerName && navigator.usb) {
                        try {
                            const devices = await navigator.usb.getDevices();
                            const matching = devices.find(d => d.productName === this.printerName);
                            if (matching) {
                                this.printerHandle = matching;
                                this.printerStatus = 'connected';
                            }
                        } catch (e) { console.error("Auto-reconnect failed:", e); }
                    } else if (this.printerName) {
                        this.printerStatus = 'connected';
                    }
                }
            },

            async printRaw(commands) {
                if (!this.printerHandle) return false;
                const device = this.printerHandle;
                try {
                    if (!device.opened) await device.open();
                    await device.selectConfiguration(1);
                    let interfaceNumber = -1, endpointOut = -1;
                    
                    for (const iface of device.configuration.interfaces) {
                        for (const alt of iface.alternates) {
                            if (alt.interfaceClass === 7) { 
                                interfaceNumber = iface.interfaceNumber;
                                for (const endpoint of alt.endpoints) {
                                    if (endpoint.direction === 'out') { endpointOut = endpoint.endpointNumber; break; }
                                }
                            }
                        }
                        if (interfaceNumber !== -1) break;
                    }

                    if (interfaceNumber === -1 || endpointOut === -1) {
                        for (const iface of device.configuration.interfaces) {
                            for (const alt of iface.alternates) {
                                for (const endpoint of alt.endpoints) {
                                    if (endpoint.direction === 'out') { interfaceNumber = iface.interfaceNumber; endpointOut = endpoint.endpointNumber; break; }
                                }
                                if (interfaceNumber !== -1) break;
                            }
                            if (interfaceNumber !== -1) break;
                        }
                    }

                    if (interfaceNumber === -1) throw new Error("Printer not found");
                    
                    await device.claimInterface(interfaceNumber);
                    await device.transferOut(endpointOut, commands);
                    await device.releaseInterface(interfaceNumber);
                    await device.close();
                    return true;
                } catch (e) {
                    console.error("Direct Print Error:", e);
                    return false;
                }
            },

            async doPrint(transactionId) {
                if (!transactionId) return;

                // Priority: Server Side ESC/POS
                if (this.connectionMethod === 'server_escpos') {
                    try {
                        const res = await fetch(`/pos/print-receipt/${transactionId}`, {
                            method: 'POST',
                            headers: { 
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json' 
                            }
                        });
                        const data = await res.json();
                        if (data.success) {
                            Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
                        } else {
                            Swal.fire({ icon: 'warning', title: 'Peringatan', text: data.message, toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
                        }
                        return;
                    } catch (e) {
                        console.error("Server Print Error:", e);
                        Swal.fire({ icon: 'error', title: 'Gagal', text: 'Gagal menghubungi printer server.', toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
                    }
                }

                if (this.connectionMethod === 'usb_direct' && this.printerHandle) {
                    try {
                        const res = await fetch(`/transactions/${transactionId}`, {
                            headers: { 'Accept': 'application/json' }
                        });
                        const tx = await res.json();
                        
                        const encoder = new TextEncoder();
                        const init = new Uint8Array([0x1B, 0x40]);
                        const center = new Uint8Array([0x1B, 0x61, 0x01]);
                        const left = new Uint8Array([0x1B, 0x61, 0x00]);
                        const boldOn = new Uint8Array([0x1B, 0x45, 0x01]);
                        const boldOff = new Uint8Array([0x1B, 0x45, 0x00]);
                        
                        let commands = [];
                        commands.push(init, center, boldOn);
                        commands.push(encoder.encode(tx.store_name + '\n'));
                        commands.push(boldOff);
                        if (tx.store_address) commands.push(encoder.encode(tx.store_address + '\n'));
                        if (tx.store_phone) commands.push(encoder.encode('Telp: ' + tx.store_phone + '\n'));
                        commands.push(encoder.encode('--------------------------------\n'));
                        
                        commands.push(left);
                        commands.push(encoder.encode('No : ' + tx.invoice_number + '\n'));
                        commands.push(encoder.encode('Tgl: ' + tx.created_at + '\n'));
                        if (tx.customer_name) commands.push(encoder.encode('Plg: ' + tx.customer_name + '\n'));
                        if (tx.customer_phone) {
                            let phone = tx.customer_phone;
                            let maskedHp = phone.length > 4 ? phone.substring(0, phone.length - 4) + '****' : '*'.repeat(phone.length);
                            commands.push(encoder.encode('Hp : ' + maskedHp + '\n'));
                        }
                        commands.push(encoder.encode('Petugas: ' + (tx.user ? tx.user.name : 'Admin') + '\n'));
                        commands.push(encoder.encode('--------------------------------\n'));
                        
                        tx.items.forEach(item => {
                            commands.push(boldOn, encoder.encode(item.product_name + '\n'), boldOff);
                            let qtyPrice = item.quantity + ' x ' + this.fmt(item.price);
                            let sub = this.fmt(item.subtotal);
                            commands.push(encoder.encode(qtyPrice.padEnd(32 - sub.length) + sub + '\n'));
                        });
                        
                        commands.push(encoder.encode('--------------------------------\n'));
                        
                        if (tx.delivery_fee > 0) {
                            let dest = tx.delivery_destination ? ' (' + tx.delivery_destination + ')' : '';
                            let devVal = this.fmt(tx.delivery_fee);
                            commands.push(encoder.encode(('Ongkir' + dest + ':').padEnd(32 - devVal.length) + devVal + '\n'));
                            commands.push(encoder.encode('--------------------------------\n'));
                        }

                        let subVal = this.fmt(tx.subtotal);
                        commands.push(encoder.encode('Subtotal:'.padEnd(32 - subVal.length) + subVal + '\n'));
                        
                        if (tx.discount > 0) {
                            let discVal = '-' + this.fmt(tx.discount);
                            commands.push(encoder.encode('Diskon:'.padEnd(32 - discVal.length) + discVal + '\n'));
                        }

                        let totalVal = this.fmt(tx.total);
                        commands.push(boldOn, encoder.encode('TOTAL:'.padEnd(32 - totalVal.length) + totalVal + '\n'), boldOff);
                        commands.push(encoder.encode('--------------------------------\n'));
                        
                        let paidVal = this.fmt(tx.paid_amount);
                        commands.push(encoder.encode((tx.payment_method + ':').padEnd(32 - paidVal.length) + paidVal + '\n'));
                        let changeVal = this.fmt(tx.change_amount);
                        commands.push(encoder.encode('KEMBALI:'.padEnd(32 - changeVal.length) + changeVal + '\n'));
                        
                        if (tx.notes) {
                            commands.push(encoder.encode('--------------------------------\n'));
                            commands.push(encoder.encode('Catatan:\n' + tx.notes + '\n'));
                        }
                        commands.push(encoder.encode('--------------------------------\n'));
                        
                        commands.push(center);
                        commands.push(encoder.encode('\n' + (tx.store_footer || this.storeFooter) + '\n'));
                        commands.push(encoder.encode('\nPowered by monodev.id\n\n\n\n\n'));
                        
                        const cut = new Uint8Array([0x1D, 0x56, 0x41, 0x03]);
                        commands.push(cut);

                        let totalLen = commands.reduce((acc, c) => acc + c.length, 0);
                        let combined = new Uint8Array(totalLen);
                        let offset = 0;
                        for (const c of commands) {
                            combined.set(c, offset);
                            offset += c.length;
                        }
                        
                        const success = await this.printRaw(combined);
                        if (success) {
                            Swal.fire({ icon: 'success', title: 'Berhasil Cetak!', toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
                            return;
                        }
                    } catch (e) { console.error("Direct Print failed:", e); }
                }

                // Fallback to iframe browser print
                const iframe = document.getElementById('print-iframe');
                const url = `/pos/receipt/${transactionId}?paper=${this.paperSize}`;
                iframe.src = url;
            }
        };
    }
</script>
@endpush


