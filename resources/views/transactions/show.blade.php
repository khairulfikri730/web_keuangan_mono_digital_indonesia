@extends('layouts.app')

@section('title', 'Detail Transaksi')
@section('page-title', 'Detail Transaksi')
@section('page-subtitle', $transaction->invoice_number)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-4">
        <a href="{{ route('transactions.index') }}" class="btn-secondary text-sm"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
        <div class="flex gap-2">
            @if($transaction->status === 'completed' && auth()->user()->isOwner())
            <form action="{{ route('transactions.cancel', $transaction) }}" method="POST" onsubmit="return confirm('Yakin ingin membatalkan transaksi ini? Stok akan dikembalikan otomatis.')">
                @csrf
                <button type="submit" class="btn-danger text-sm"><i class="fas fa-ban mr-1"></i> Batalkan Transaksi</button>
            </form>
            @endif
            <a href="{{ route('pos.receipt', $transaction) }}" target="_blank" class="btn-primary text-sm"><i class="fas fa-print mr-1"></i> Cetak Struk</a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Info Box --}}
        <div class="md:col-span-1 space-y-6">
            <div class="card p-6 border-t-4 border-blue-500">
                <div class="text-center mb-6">
                    <p class="text-xs text-slate-400 mb-1">Status</p>
                    @if($transaction->status === 'completed')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-emerald-500/20 text-emerald-400">Selesai</span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-red-500/20 text-red-400">Dibatalkan</span>
                    @endif
                </div>

                <div class="space-y-4 text-sm">
                    <div>
                        <p class="text-slate-500 text-xs">Waktu Transaksi</p>
                        <p class="font-medium text-white">{{ $transaction->created_at->format('d M Y, H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs">Kasir</p>
                        <p class="font-medium text-white">{{ $transaction->user->name }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs">Pelanggan</p>
                        <p class="font-medium text-white">{{ $transaction->customer_name ?: 'Umum' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs">Metode Pembayaran</p>
                        <div class="flex items-center gap-2">
                            <p class="font-medium text-white uppercase">{{ $transaction->payment_method }}</p>
                            @if($transaction->status === 'completed' && $transaction->payment_method !== 'piutang' && auth()->user()->isOwner())
                                <button onclick="document.getElementById('modal-edit-payment').classList.remove('hidden')" class="text-xs text-blue-400 hover:text-blue-300"><i class="fas fa-edit"></i> Edit</button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Items Box --}}
        <div class="md:col-span-2">
            <div class="card">
                <div class="p-5 border-b border-slate-700">
                    <h3 class="font-bold text-white"><i class="fas fa-shopping-basket text-blue-400 mr-2"></i>Detail Item</h3>
                </div>
                <div class="p-5 space-y-4">
                    @foreach($transaction->items as $item)
                    <div class="flex items-start justify-between border-b border-slate-700/50 pb-4 last:border-0 last:pb-0">
                        <div>
                            <p class="font-semibold text-white">{{ $item->product_name }}</p>
                            <p class="text-sm text-slate-400">{{ $item->quantity }} x Rp {{ number_format($item->price, 0, ',', '.') }}</p>
                            @if($item->discount > 0)
                            <p class="text-xs text-red-400">Diskon: -Rp {{ number_format($item->discount, 0, ',', '.') }}</p>
                            @endif
                        </div>
                        <p class="font-bold text-emerald-400">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</p>
                    </div>
                    @endforeach
                </div>

                <div class="bg-slate-900/50 p-5 rounded-b-2xl border-t border-slate-700">
                    <div class="space-y-2 mb-4 text-sm">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Subtotal</span>
                            <span class="text-white">Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</span>
                        </div>
                        @if($transaction->discount > 0)
                        <div class="flex justify-between">
                            <span class="text-slate-400">Diskon</span>
                            <span class="text-red-400">-Rp {{ number_format($transaction->discount, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        @if($transaction->tax > 0)
                        <div class="flex justify-between">
                            <span class="text-slate-400">Pajak</span>
                            <span class="text-white">Rp {{ number_format($transaction->tax, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between font-bold text-lg pt-2 border-t border-slate-700/50">
                            <span class="text-white">Total</span>
                            <span class="text-emerald-400">Rp {{ number_format($transaction->total, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    
                    <div class="bg-slate-800 p-4 rounded-xl border border-slate-700 space-y-1 text-sm mt-4">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Dibayar (Tunai)</span>
                            <span class="text-white font-medium">Rp {{ number_format($transaction->paid_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Kembalian</span>
                            <span class="text-white font-medium">Rp {{ number_format($transaction->change_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($transaction->status === 'completed' && $transaction->payment_method !== 'piutang' && auth()->user()->isOwner())
<div id="modal-edit-payment" class="hidden fixed inset-0 bg-slate-900/80 backdrop-blur-sm z-[200] flex items-center justify-center p-4">
    <div class="bg-slate-800 rounded-2xl w-full max-w-sm shadow-2xl border border-slate-700 overflow-hidden">
        <div class="p-5 border-b border-slate-700/50 flex justify-between items-center">
            <h3 class="font-bold text-white"><i class="fas fa-edit text-blue-400 mr-2"></i>Ubah Metode Pembayaran</h3>
            <button onclick="document.getElementById('modal-edit-payment').classList.add('hidden')" class="text-slate-400 hover:text-white"><i class="fas fa-times"></i></button>
        </div>
        <form action="{{ route('transactions.update-payment-method', $transaction) }}" method="POST" class="p-5 space-y-4">
            @csrf
            @method('PUT')
            
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Metode Saat Ini</label>
                <input type="text" value="{{ strtoupper($transaction->payment_method) }}" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-500" disabled>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Metode Baru</label>
                <select name="payment_method" class="w-full bg-slate-900 border border-slate-600 rounded-xl px-4 py-2.5 text-sm font-bold text-white outline-none focus:border-blue-500" required>
                    <option value="cash" {{ $transaction->payment_method === 'cash' ? 'selected' : '' }}>CASH</option>
                    <option value="qris" {{ $transaction->payment_method === 'qris' ? 'selected' : '' }}>QRIS</option>
                    <option value="transfer" {{ $transaction->payment_method === 'transfer' ? 'selected' : '' }}>TRANSFER</option>
                    <option value="debit" {{ $transaction->payment_method === 'debit' ? 'selected' : '' }}>DEBIT</option>
                </select>
            </div>
            
            <div class="pt-2 flex gap-3">
                <button type="button" onclick="document.getElementById('modal-edit-payment').classList.add('hidden')" class="flex-1 bg-slate-700 hover:bg-slate-600 text-white font-bold py-3 rounded-xl transition-colors">Batal</button>
                <button type="submit" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 rounded-xl transition-colors shadow-lg shadow-blue-500/20">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endif

@endsection
