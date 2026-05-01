@extends('layouts.app')

@section('title', 'Transaksi')
@section('page-title', 'Riwayat Transaksi')
@section('page-subtitle', 'Daftar semua transaksi POS')

@section('content')
<div class="space-y-6">
    {{-- Summary --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="stat-card">
            <div class="w-12 h-12 bg-blue-500/20 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fas fa-receipt text-blue-400 text-lg"></i>
            </div>
            <div>
                <p class="text-xs text-slate-400">Transaksi Hari Ini</p>
                <p class="text-xl font-bold text-white">{{ $todayCount }}</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="w-12 h-12 bg-emerald-500/20 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fas fa-sack-dollar text-emerald-400 text-lg"></i>
            </div>
            <div>
                <p class="text-xs text-slate-400">Penjualan Hari Ini</p>
                <p class="text-xl font-bold text-white">Rp {{ number_format($todayTotal, 0, ',', '.') }}</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="w-12 h-12 bg-purple-500/20 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fas fa-filter text-purple-400 text-lg"></i>
            </div>
            <div>
                <p class="text-xs text-slate-400">Total Filter Aktif</p>
                <p class="text-xl font-bold text-white">Rp {{ number_format($totalFiltered, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    {{-- Filter & List --}}
    <div class="card">
        <div class="p-5 border-b border-slate-700/50 flex flex-col lg:flex-row gap-4 justify-between items-start lg:items-center">
            <form method="GET" class="flex flex-wrap gap-2 w-full lg:w-auto">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm"></i>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-input pl-9 text-sm py-2" placeholder="Cari invoice/pelanggan...">
                </div>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-input text-sm py-2 w-auto">
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-input text-sm py-2 w-auto">
                <select name="payment_method" class="form-input text-sm py-2 w-auto">
                    <option value="">Semua Metode</option>
                    <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Tunai</option>
                    <option value="transfer" {{ request('payment_method') == 'transfer' ? 'selected' : '' }}>Transfer</option>
                    <option value="qris" {{ request('payment_method') == 'qris' ? 'selected' : '' }}>QRIS</option>
                    <option value="debit" {{ request('payment_method') == 'debit' ? 'selected' : '' }}>Debit</option>
                </select>
                <select name="status" class="form-input text-sm py-2 w-auto">
                    <option value="">Semua Status</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Batal</option>
                </select>
                <button type="submit" class="btn-primary py-2 px-3 text-sm"><i class="fas fa-search"></i></button>
                @if(request()->anyFilled(['search', 'date_from', 'date_to', 'payment_method', 'status']))
                    <a href="{{ route('transactions.index') }}" class="btn-secondary py-2 px-3 text-sm flex items-center"><i class="fas fa-times"></i></a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-800/50 border-b border-slate-700">
                        <th class="p-4 table-head">Waktu & Invoice</th>
                        <th class="p-4 table-head">Kasir & Pelanggan</th>
                        <th class="p-4 table-head text-center">Metode</th>
                        <th class="p-4 table-head text-right">Total Transaksi</th>
                        <th class="p-4 table-head text-center">Status</th>
                        <th class="p-4 table-head text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50">
                    @forelse($transactions as $t)
                    <tr class="hover:bg-slate-700/20 transition-colors {{ $t->status === 'cancelled' ? 'opacity-50' : '' }}">
                        <td class="p-4">
                            <p class="font-semibold text-white">{{ $t->invoice_number }}</p>
                            <p class="text-xs text-slate-400">{{ $t->created_at->format('d M Y, H:i') }}</p>
                        </td>
                        <td class="p-4">
                            <p class="text-sm text-slate-300"><i class="fas fa-user-tie mr-1 text-slate-500"></i> {{ $t->user->name }}</p>
                            <p class="text-xs text-slate-500 mt-1"><i class="fas fa-user mr-1"></i> {{ $t->customer_name ?: 'Umum' }}</p>
                        </td>
                        <td class="p-4 text-center">
                            @php
                                $pmLabel = [
                                    'cash' => '<i class="fas fa-money-bill-wave text-emerald-400"></i> Tunai',
                                    'transfer' => '<i class="fas fa-building-columns text-blue-400"></i> Transfer',
                                    'qris' => '<i class="fas fa-qrcode text-purple-400"></i> QRIS',
                                    'debit' => '<i class="fas fa-credit-card text-orange-400"></i> Debit'
                                ];
                            @endphp
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-800 border border-slate-700">
                                {!! $pmLabel[$t->payment_method] !!}
                            </span>
                        </td>
                        <td class="p-4 text-right">
                            <p class="font-bold text-emerald-400">Rp {{ number_format($t->total, 0, ',', '.') }}</p>
                            <p class="text-xs text-slate-500">{{ $t->items->count() }} item</p>
                        </td>
                        <td class="p-4 text-center">
                            @if($t->status === 'completed')
                                <span class="badge-green">Selesai</span>
                            @else
                                <span class="badge-red">Batal</span>
                            @endif
                        </td>
                        <td class="p-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('pos.receipt', $t) }}" target="_blank" class="w-8 h-8 rounded-lg bg-blue-500/20 hover:bg-blue-500/30 text-blue-400 inline-flex items-center justify-center transition-colors" title="Cetak Struk">
                                    <i class="fas fa-print text-xs"></i>
                                </a>
                                <a href="{{ route('transactions.show', $t) }}" class="w-8 h-8 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-300 inline-flex items-center justify-center transition-colors" title="Detail">
                                    <i class="fas fa-eye text-xs"></i>
                                </a>
                                @if($t->status === 'completed' && auth()->user()->isOwner())
                                <form action="{{ route('transactions.cancel', $t) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin membatalkan transaksi ini? Stok akan dikembalikan otomatis.')">
                                    @csrf
                                    <button type="submit" class="w-8 h-8 rounded-lg bg-red-500/20 hover:bg-red-500/30 text-red-400 inline-flex items-center justify-center transition-colors" title="Batalkan Transaksi">
                                        <i class="fas fa-ban text-xs"></i>
                                    </button>
                                </form>
                                @endif
                                @if($t->status === 'cancelled' && auth()->user()->isOwner())
                                <form action="{{ route('transactions.destroy', $t) }}" method="POST" class="inline" onsubmit="return confirm('Hapus permanen transaksi {{ $t->invoice_number }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="w-8 h-8 rounded-lg bg-red-500/20 hover:bg-red-500/40 text-red-400 inline-flex items-center justify-center transition-colors" title="Hapus Permanen">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-slate-500">Tidak ada data transaksi.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($transactions->hasPages())
        <div class="p-4 border-t border-slate-700/50">
            {{ $transactions->links('pagination::tailwind') }}
        </div>
        @endif
    </div>
</div>
@endsection
