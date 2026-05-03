@extends('layouts.app')

@section('title', 'Transaksi')
@section('page-title', 'Riwayat Transaksi')

@section('content')
<div class="space-y-6">
    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-slate-800/40 border border-slate-700/50 rounded-2xl p-4 hover:bg-slate-800/60 transition-all shadow-sm">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Omset Hari Ini</p>
            <p class="text-lg font-black text-emerald-400">Rp {{ number_format($todayTotalSales, 0, ',', '.') }}</p>
        </div>
        <div class="bg-slate-800/40 border border-slate-700/50 rounded-2xl p-4 hover:bg-slate-800/60 transition-all shadow-sm">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Pengeluaran</p>
            <p class="text-lg font-black text-red-400">Rp {{ number_format($todayExpenses, 0, ',', '.') }}</p>
        </div>
        <div class="bg-slate-800/40 border border-slate-700/50 rounded-2xl p-4 hover:bg-slate-800/60 transition-all shadow-sm">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Pendapatan Bersih</p>
            <p class="text-lg font-black {{ $todayNet >= 0 ? 'text-emerald-400' : 'text-red-400' }}">Rp {{ number_format(abs($todayNet), 0, ',', '.') }}</p>
        </div>
        <div class="bg-slate-800/40 border border-slate-700/50 rounded-2xl p-4 hover:bg-slate-800/60 transition-all shadow-sm">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Saldo Laci</p>
            <p class="text-lg font-black text-blue-400">Rp {{ number_format($saldoLaci, 0, ',', '.') }}</p>
        </div>
        <div class="bg-slate-800/40 border border-slate-700/50 rounded-2xl p-4 hover:bg-slate-800/60 transition-all shadow-sm">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Saldo Bank</p>
            <p class="text-lg font-black text-purple-400">Rp {{ number_format($saldoBank, 0, ',', '.') }}</p>
        </div>
        <div class="bg-slate-800/40 border border-slate-700/50 rounded-2xl p-4 hover:bg-slate-800/60 transition-all shadow-sm">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Total Piutang</p>
            <p class="text-lg font-black text-orange-400">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Filter Pills (Santai Scale Style) --}}
    <div class="space-y-3">
        {{-- Status & Method Pills --}}
        <div class="flex flex-wrap gap-2">
            @php
                $curStatus = request('status');
                $curMethod = request('payment_method');
                $isActive = fn($s, $m) => request('status') == $s && request('payment_method') == $m;
                $pillBase = 'px-4 py-2 rounded-full text-sm font-bold whitespace-nowrap transition-all border inline-flex items-center gap-2';
                $pillActive = 'bg-slate-700 text-white border-slate-600 shadow-lg';
                $pillInactive = 'bg-slate-800/40 border-slate-700/50 text-slate-400 hover:bg-slate-700 hover:text-white';
            @endphp
            <a href="{{ route('transactions.index', array_filter(request()->except(['status','payment_method','page']))) }}" 
               class="{{ $pillBase }} {{ !$curStatus && !$curMethod ? $pillActive : $pillInactive }}">
                <i class="fas fa-th-large text-xs"></i> Semua <span class="bg-slate-600 text-white text-[10px] font-black px-1.5 py-0.5 rounded-full">{{ $countAll }}</span>
            </a>
            <a href="{{ route('transactions.index', array_merge(request()->except(['page']), ['status' => 'piutang', 'payment_method' => ''])) }}" 
               class="{{ $pillBase }} {{ $curStatus === 'piutang' ? 'bg-orange-500/20 text-orange-400 border-orange-500/30 shadow-lg' : $pillInactive }}">
                <i class="fas fa-hourglass-half text-xs"></i> Piutang <span class="bg-orange-500/20 text-orange-400 text-[10px] font-black px-1.5 py-0.5 rounded-full">{{ $countPiutang }}</span>
            </a>
            <a href="{{ route('transactions.index', array_merge(request()->except(['page']), ['status' => 'lunas', 'payment_method' => ''])) }}" 
               class="{{ $pillBase }} {{ $curStatus === 'lunas' ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30 shadow-lg' : $pillInactive }}">
                <i class="fas fa-check-circle text-xs"></i> Lunas <span class="bg-emerald-500/20 text-emerald-400 text-[10px] font-black px-1.5 py-0.5 rounded-full">{{ $countLunas }}</span>
            </a>
            <span class="border-l border-slate-700 mx-1"></span>
            <a href="{{ route('transactions.index', array_merge(request()->except(['page']), ['payment_method' => 'cash', 'status' => ''])) }}" 
               class="{{ $pillBase }} {{ $curMethod === 'cash' && !$curStatus ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30 shadow-lg' : $pillInactive }}">
                <i class="fas fa-money-bill-wave text-xs"></i> Tunai <span class="text-[10px] font-black bg-slate-700/50 px-1.5 py-0.5 rounded-full">{{ $countCash }}</span>
            </a>
            <a href="{{ route('transactions.index', array_merge(request()->except(['page']), ['payment_method' => 'qris', 'status' => ''])) }}" 
               class="{{ $pillBase }} {{ $curMethod === 'qris' && !$curStatus ? 'bg-purple-500/20 text-purple-400 border-purple-500/30 shadow-lg' : $pillInactive }}">
                <i class="fas fa-qrcode text-xs"></i> QRIS <span class="text-[10px] font-black bg-slate-700/50 px-1.5 py-0.5 rounded-full">{{ $countQris }}</span>
            </a>
            <a href="{{ route('transactions.index', array_merge(request()->except(['page']), ['payment_method' => 'transfer', 'status' => ''])) }}" 
               class="{{ $pillBase }} {{ $curMethod === 'transfer' && !$curStatus ? 'bg-blue-500/20 text-blue-400 border-blue-500/30 shadow-lg' : $pillInactive }}">
                <i class="fas fa-building-columns text-xs"></i> Transfer <span class="text-[10px] font-black bg-slate-700/50 px-1.5 py-0.5 rounded-full">{{ $countTransfer }}</span>
            </a>
            @if($countDebit > 0)
            <a href="{{ route('transactions.index', array_merge(request()->except(['page']), ['payment_method' => 'debit', 'status' => ''])) }}" 
               class="{{ $pillBase }} {{ $curMethod === 'debit' && !$curStatus ? 'bg-orange-500/20 text-orange-400 border-orange-500/30 shadow-lg' : $pillInactive }}">
                <i class="fas fa-credit-card text-xs"></i> Debit <span class="text-[10px] font-black bg-slate-700/50 px-1.5 py-0.5 rounded-full">{{ $countDebit }}</span>
            </a>
            @endif
        </div>

        {{-- Kasir Pills + Search --}}
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-slate-500 text-sm font-bold mr-1"><i class="fas fa-user mr-1"></i> Kasir:</span>
            <a href="{{ route('transactions.index', request()->except(['user_id','page'])) }}" 
               class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ !request('user_id') ? 'bg-slate-600 text-white' : 'bg-slate-800/40 border border-slate-700/50 text-slate-400 hover:bg-slate-700 hover:text-white' }}">Semua Kasir</a>
            @foreach($users as $u)
            <a href="{{ route('transactions.index', array_merge(request()->except(['page']), ['user_id' => $u->id])) }}" 
               class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ request('user_id') == $u->id ? 'bg-slate-600 text-white' : 'bg-slate-800/40 border border-slate-700/50 text-slate-400 hover:bg-slate-700 hover:text-white' }}">{{ $u->name }}</a>
            @endforeach
            <span class="border-l border-slate-700 mx-1"></span>
            <form method="GET" class="relative">
                @foreach(request()->except(['search','page']) as $key => $val)
                    @if($val) <input type="hidden" name="{{ $key }}" value="{{ $val }}"> @endif
                @endforeach
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}" 
                       class="bg-slate-800/40 border border-slate-700/50 rounded-lg pl-8 pr-3 py-1.5 text-xs text-white placeholder-slate-500 focus:border-blue-500 outline-none w-48" 
                       placeholder="Cari invoice...">
            </form>
        </div>
    </div>

    {{-- Cards List --}}
    <div class="flex flex-col gap-4">
        @forelse($transactions as $entry)
            @php
                $entryType = $entry->type;
                $model = $entry->model;
            @endphp

            @if($entryType === 'penjualan')
                {{-- PENJUALAN CARD --}}
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
                <div class="bg-slate-800/40 border border-slate-700/50 rounded-2xl p-5 hover:bg-slate-800/80 hover:-translate-y-0.5 hover:shadow-xl transition-all duration-300 group {{ $isCancelled ? 'opacity-60' : '' }}">
                    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                        <div class="flex items-start gap-4 flex-1">
                            <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0 bg-{{ $statusColor }}-500/10 text-{{ $statusColor }}-400 border border-{{ $statusColor }}-500/20">
                                <i class="fas {{ $statusIcon }} text-lg"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="text-base font-black text-white">{{ $model->invoice_number }}</h3>
                                    <span class="text-[10px] font-bold text-slate-400"><i class="far fa-clock mr-0.5"></i>{{ $model->created_at->format('d M Y, H:i') }}</span>
                                </div>
                                <div class="flex flex-wrap items-center gap-2 text-xs">
                                    <span class="text-slate-300 bg-slate-900/40 px-2 py-0.5 rounded font-medium"><i class="fas fa-user-tie text-blue-400 mr-1"></i>{{ $model->user->name }}</span>
                                    <span class="text-slate-400"><i class="fas fa-user text-slate-500 mr-1"></i>{{ $model->customer_name ?: 'Umum' }}</span>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-{{ $pm['color'] }}-500/10 text-{{ $pm['color'] }}-400 border border-{{ $pm['color'] }}-500/20 font-bold">
                                        <i class="fas {{ $pm['icon'] }} text-[10px]"></i> {{ $pm['label'] }}
                                    </span>
                                    @if($isPending)
                                        <span class="px-2 py-0.5 rounded-lg bg-orange-500/10 text-orange-400 border border-orange-500/20 font-bold">PIUTANG</span>
                                    @elseif($isLunas)
                                        <span class="px-2 py-0.5 rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-bold">LUNAS</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="text-right">
                                <p class="text-lg font-black text-emerald-400">+Rp {{ number_format($model->total, 0, ',', '.') }}</p>
                                @if($isPending)
                                    <p class="text-xs font-bold text-orange-400">Sisa: Rp {{ number_format($model->remaining, 0, ',', '.') }}</p>
                                @elseif($isLunas)
                                    <p class="text-xs font-bold text-emerald-400">{{ $model->items->count() }} item · Lunas</p>
                                @else
                                    <p class="text-xs text-slate-500">{{ $model->items->count() }} item</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-1.5 bg-slate-900/50 p-1 rounded-xl border border-slate-700/50">
                                {{-- Detail (mata) --}}
                                <button onclick="document.getElementById('detail-modal-{{ $model->id }}').classList.remove('hidden')" class="w-9 h-9 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 inline-flex items-center justify-center transition-all text-sm" title="Detail"><i class="fas fa-eye"></i></button>
                                {{-- Edit (pensil) --}}
                                @if(!$isCancelled && auth()->user()->isOwner())
                                <a href="{{ route('transactions.show', $model) }}" class="w-9 h-9 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 inline-flex items-center justify-center transition-all text-sm" title="Edit"><i class="fas fa-pencil"></i></a>
                                @endif
                                {{-- Invoice (doc) --}}
                                <a href="{{ route('pos.receipt', $model) }}" target="_blank" class="w-9 h-9 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 inline-flex items-center justify-center transition-all text-sm" title="Invoice"><i class="fas fa-file-invoice"></i></a>
                                {{-- Print --}}
                                <a href="{{ route('pos.receipt', $model) }}" target="_blank" onclick="setTimeout(()=>{let w=window.open(this.href);w.onload=()=>w.print()},100);return false;" class="w-9 h-9 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 inline-flex items-center justify-center transition-all text-sm" title="Print"><i class="fas fa-print"></i></a>
                                {{-- Pelunasan (piutang only) --}}
                                @if($isPending && auth()->user()->isOwner())
                                    <button onclick="document.getElementById('pay-modal-{{ $model->id }}').classList.remove('hidden')" class="w-9 h-9 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 inline-flex items-center justify-center transition-all text-sm" title="Bayar Piutang"><i class="fas fa-hand-holding-dollar"></i></button>
                                @endif
                                {{-- Hapus (sampah) --}}
                                @if(auth()->user()->isOwner())
                                <form action="{{ route('transactions.cancel', $model) }}" method="POST" class="inline" onsubmit="return confirm('Hapus transaksi {{ $model->invoice_number }}?')">@csrf
                                    <button type="submit" class="w-9 h-9 rounded-lg bg-slate-800 hover:bg-red-500/20 text-slate-400 hover:text-red-400 inline-flex items-center justify-center transition-all text-sm" title="Hapus"><i class="fas fa-trash"></i></button>
                                </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Detail Transaksi Modal (Santai Scale Style) --}}
                <div id="detail-modal-{{ $model->id }}" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                    <div class="bg-white rounded-3xl w-full max-w-sm shadow-2xl border border-slate-200 max-h-[90vh] overflow-y-auto">
                        <div class="p-5 border-b border-slate-100 flex justify-between items-center sticky top-0 bg-white rounded-t-3xl z-10">
                            <h3 class="text-lg font-black text-slate-800">Detail Transaksi</h3>
                            <button onclick="this.closest('.fixed').classList.add('hidden')" class="w-8 h-8 bg-slate-100 text-slate-500 rounded-full hover:bg-slate-200 hover:text-slate-800 transition-colors"><i class="fas fa-times"></i></button>
                        </div>
                        <div class="p-5 space-y-4">
                            {{-- Product list --}}
                            @foreach($model->items as $item)
                            <div class="flex justify-between text-sm">
                                <div>
                                    <p class="font-bold text-slate-800">{{ $item->product_name }}</p>
                                    <p class="text-slate-400 text-xs">{{ $item->quantity }}x @ Rp {{ number_format($item->price, 0, ',', '.') }}</p>
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
                                <a href="{{ route('pos.receipt', $model) }}" target="_blank" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 rounded-xl transition-all text-center text-sm flex items-center justify-center gap-2">
                                    <i class="fas fa-print"></i> Cetak Struk
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Piutang Payment Modal (Santai Scale Style) --}}
                @if($isPending && auth()->user()->isOwner())
                <div id="pay-modal-{{ $model->id }}" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" 
                     x-data="{ 
                        pelunasanMethod: 'cash', 
                        pelunasanAmount: {{ $model->remaining }},
                        get kembalian() { return Math.max(0, this.pelunasanAmount - {{ $model->remaining }}) }
                     }">
                    <div class="bg-white rounded-3xl w-full max-w-sm shadow-2xl border border-slate-200 max-h-[90vh] overflow-y-auto">
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
                {{-- EXPENSE CARD --}}
                <div class="bg-slate-800/40 border border-slate-700/50 rounded-2xl p-5 hover:bg-slate-800/80 hover:-translate-y-0.5 hover:shadow-xl transition-all duration-300 group">
                    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                        <div class="flex items-start gap-4 flex-1">
                            <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0 bg-red-500/10 text-red-400 border border-red-500/20">
                                <i class="fas fa-arrow-down text-lg"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="text-base font-black text-white">{{ $model->description }}</h3>
                                    <span class="text-[10px] font-bold text-slate-400"><i class="far fa-clock mr-0.5"></i>{{ $model->created_at->format('d M Y, H:i') }}</span>
                                </div>
                                <div class="flex flex-wrap items-center gap-2 text-xs">
                                    <span class="text-slate-300 bg-slate-900/40 px-2 py-0.5 rounded font-medium"><i class="fas fa-user-tie text-blue-400 mr-1"></i>{{ $model->user->name }}</span>
                                    <span class="px-2 py-0.5 rounded-lg bg-red-500/10 text-red-400 border border-red-500/20 font-bold">EXPENSE</span>
                                    <span class="text-slate-500">{{ $model->category }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-black text-red-400">-Rp {{ number_format($model->amount, 0, ',', '.') }}</p>
                            <p class="text-xs text-slate-500">Pengeluaran Kasir</p>
                        </div>
                    </div>
                </div>

            @endif
        @empty
            <div class="bg-slate-800/40 border border-slate-700/50 rounded-2xl p-12 text-center flex flex-col items-center justify-center shadow-sm">
                <div class="w-20 h-20 bg-slate-900/50 rounded-full flex items-center justify-center mb-4 shadow-inner">
                    <i class="fas fa-box-open text-4xl text-slate-600"></i>
                </div>
                <h3 class="text-xl font-black text-white mb-2">Belum ada aktivitas</h3>
                <p class="text-sm font-medium text-slate-400">Tidak ditemukan data untuk filter saat ini.</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($transactions->hasPages())
    <div class="mt-6">
        {{ $transactions->links('pagination::tailwind') }}
    </div>
    @endif

</div>
@endsection
