@extends('layouts.app')

@section('title', 'Detail Shift')
@section('page-title', 'Laporan Detail Shift')
@section('page-subtitle', 'Waktu: ' . $shift->opened_at->format('d M Y, H:i') . ' s/d ' . ($shift->closed_at ? $shift->closed_at->format('d M Y, H:i') : 'Sekarang'))

@section('content')
<div class="space-y-6">
    <a href="{{ route('shifts.index') }}" class="btn-secondary text-sm inline-flex items-center"><i class="fas fa-arrow-left mr-2"></i> Kembali</a>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stat-card">
            <div class="w-12 h-12 bg-slate-700 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fas fa-cash-register text-slate-300 text-lg"></i>
            </div>
            <div>
                <p class="text-xs text-slate-400">Kas Awal</p>
                <p class="text-lg font-bold text-white">Rp {{ number_format($shift->opening_cash, 0, ',', '.') }}</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="w-12 h-12 bg-blue-500/20 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fas fa-shopping-cart text-blue-400 text-lg"></i>
            </div>
            <div>
                <p class="text-xs text-slate-400">Total Penjualan</p>
                <p class="text-lg font-bold text-white">Rp {{ number_format($shift->total_sales, 0, ',', '.') }}</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="w-12 h-12 bg-emerald-500/20 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fas fa-money-bills text-emerald-400 text-lg"></i>
            </div>
            <div>
                <p class="text-xs text-slate-400">Uang Laci (Fisik)</p>
                <p class="text-lg font-bold text-white">{{ $shift->closed_at ? 'Rp ' . number_format($shift->closing_cash, 0, ',', '.') : '-' }}</p>
            </div>
        </div>
        <div class="stat-card">
            @php 
                $expected = $expectedCash; 
                $selisih = $shift->closed_at ? ($shift->closing_cash - $expected) : 0;
            @endphp
            <div class="w-12 h-12 {{ $selisih == 0 ? 'bg-slate-700 text-slate-300' : ($selisih > 0 ? 'bg-emerald-500/20 text-emerald-400' : 'bg-red-500/20 text-red-400') }} rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fas fa-scale-balanced text-lg"></i>
            </div>
            <div>
                <p class="text-xs text-slate-400">Selisih Kas Tunai</p>
                <p class="text-lg font-bold {{ $selisih == 0 ? 'text-white' : ($selisih > 0 ? 'text-emerald-400' : 'text-red-400') }}">
                    {{ $shift->closed_at ? 'Rp ' . number_format($selisih, 0, ',', '.') : '-' }}
                </p>
            </div>
        </div>
    </div>

    <div class="card p-5">
        <h3 class="font-bold text-white mb-4">Transaksi Selama Shift</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-800/50 border-b border-slate-700">
                        <th class="p-3 table-head">Waktu</th>
                        <th class="p-3 table-head">Invoice</th>
                        <th class="p-3 table-head">Metode</th>
                        <th class="p-3 table-head text-right">Total</th>
                        <th class="p-3 table-head text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50 text-sm">
                    @forelse($transactions as $t)
                    <tr class="hover:bg-slate-700/20 {{ $t->status === 'cancelled' ? 'opacity-50' : '' }}">
                        <td class="p-3 text-white">{{ $t->created_at->format('H:i:s') }}</td>
                        <td class="p-3 text-blue-400"><a href="{{ route('transactions.show', $t) }}" class="hover:underline">{{ $t->invoice_number }}</a></td>
                        <td class="p-3 uppercase">{{ $t->payment_method }}</td>
                        <td class="p-3 text-right text-emerald-400 font-semibold">Rp {{ number_format($t->total, 0, ',', '.') }}</td>
                        <td class="p-3 text-center">
                            @if($t->status === 'completed') <span class="text-emerald-400">OK</span> @else <span class="text-red-400">Batal</span> @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="p-5 text-center text-slate-500">Tidak ada transaksi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $transactions->links('pagination::tailwind') }}</div>
    </div>
</div>
@endsection
