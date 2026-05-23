@extends('layouts.app')

@section('title', 'Detail Invoice â€” MONOFRAME')

@section('page-title')
    <div class="flex items-center gap-2">
        <a href="{{ route('invoices.index') }}" class="text-slate-400 hover:text-white transition-colors"><i class="fas fa-arrow-left text-sm"></i></a>
        <span>Detail Invoice {{ $invoice->invoice_number }}</span>
    </div>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    {{-- Main Detail (Left) --}}
    <div class="lg:col-span-2 space-y-6">
        
        {{-- Status Card --}}
        <div class="bg-slate-800/40 backdrop-blur-md border border-slate-700/50 rounded-3xl p-8 shadow-xl overflow-hidden relative">
            <div class="absolute top-0 right-0 w-32 h-32 bg-blue-600/5 blur-3xl rounded-full -mr-16 -mt-16"></div>
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 relative z-10">
                <div>
                    <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Status Pembayaran</p>
                    <div class="flex items-center gap-3">
                        <h2 class="text-3xl font-black text-white">
                            @if($invoice->status == 'paid') Lunas @elseif($invoice->status == 'partial') DP Aktif @else Menunggu @endif
                        </h2>
                        <span class="text-lg font-bold text-blue-400">({{ $invoice->payment_percentage }}%)</span>
                    </div>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('invoices.pdf', $invoice) }}" target="_blank" class="bg-slate-700 hover:bg-slate-600 text-white px-6 py-3 rounded-2xl text-xs font-black uppercase tracking-widest transition-all">
                        <i class="fas fa-print mr-2"></i> Print / PDF
                    </a>
                    @if($invoice->status != 'paid')
                    <button x-data @click="$dispatch('open-modal', 'add-payment')" class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-3 rounded-2xl text-xs font-black uppercase tracking-widest transition-all shadow-xl shadow-blue-500/20">
                        <i class="fas fa-plus mr-2"></i> Catat Pembayaran
                    </button>
                    @endif
                </div>
            </div>

            {{-- Progress Bar --}}
            <div class="mt-8">
                <div class="flex justify-between text-[10px] font-black uppercase mb-2">
                    <span class="text-slate-500">Progress Pembayaran</span>
                    <span class="text-white">Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }} / Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
                </div>
                <div class="w-full h-3 bg-slate-900/50 rounded-full overflow-hidden border border-slate-700/30">
                    <div class="h-full bg-blue-600 shadow-[0_0_15px_rgba(37,99,235,0.5)] transition-all duration-1000" style="width: {{ $invoice->payment_percentage }}%"></div>
                </div>
            </div>
        </div>

        {{-- Items Table --}}
        <div class="bg-slate-800/40 backdrop-blur-md border border-slate-700/50 rounded-3xl overflow-hidden shadow-xl">
            <div class="px-8 py-6 border-b border-slate-700/50 flex justify-between items-center">
                <h3 class="text-sm font-black text-white uppercase tracking-widest">Item Tagihan</h3>
                <span class="text-xs font-bold text-slate-500">{{ $invoice->items->count() }} Items</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-900/30">
                            <th class="px-8 py-4 text-[10px] font-black text-slate-500 uppercase tracking-widest">Deskripsi</th>
                            <th class="px-8 py-4 text-[10px] font-black text-slate-500 uppercase tracking-widest text-center w-24">Qty</th>
                            <th class="px-8 py-4 text-[10px] font-black text-slate-500 uppercase tracking-widest text-right">Harga Satuan</th>
                            <th class="px-8 py-4 text-[10px] font-black text-slate-500 uppercase tracking-widest text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/30">
                        @foreach($invoice->items as $item)
                        <tr>
                            <td class="px-8 py-5 text-sm font-bold text-white">{{ $item->name }}</td>
                            <td class="px-8 py-5 text-sm text-slate-400 text-center">{{ $item->quantity }}</td>
                            <td class="px-8 py-5 text-sm text-slate-400 text-right">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                            <td class="px-8 py-5 text-sm font-black text-white text-right">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-900/50">
                            <td colspan="3" class="px-8 py-4 text-sm font-black text-slate-500 text-right uppercase tracking-widest">Subtotal</td>
                            <td class="px-8 py-4 text-sm font-black text-white text-right">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @if($invoice->discount > 0)
                        <tr class="bg-slate-900/50">
                            <td colspan="3" class="px-8 py-4 text-sm font-black text-red-500 text-right uppercase tracking-widest">Diskon</td>
                            <td class="px-8 py-4 text-sm font-black text-red-500 text-right">- Rp {{ number_format($invoice->discount, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        <tr class="bg-blue-600/10">
                            <td colspan="3" class="px-8 py-6 text-base font-black text-blue-400 text-right uppercase tracking-widest">Total Akhir</td>
                            <td class="px-8 py-6 text-xl font-black text-blue-400 text-right">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Payment History --}}
        <div class="bg-slate-800/40 backdrop-blur-md border border-slate-700/50 rounded-3xl overflow-hidden shadow-xl">
            <div class="px-8 py-6 border-b border-slate-700/50">
                <h3 class="text-sm font-black text-white uppercase tracking-widest">Riwayat Pembayaran</h3>
            </div>
            <div class="p-8 space-y-6">
                @forelse($invoice->payments as $payment)
                <div class="flex items-center justify-between group">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-slate-900/50 border border-slate-700/50 flex items-center justify-center text-emerald-400">
                            <i class="fas fa-check-double"></i>
                        </div>
                        <div>
                            <p class="text-sm font-black text-white">Rp {{ number_format($payment->amount, 0, ',', '.') }}</p>
                            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">{{ $payment->payment_date->format('d M Y') }} Â· {{ $payment->payment_method }}</p>
                        </div>
                    </div>
                    @if($payment->notes)
                    <div class="text-xs text-slate-500 italic">{{ $payment->notes }}</div>
                    @endif
                </div>
                @empty
                <div class="text-center py-4">
                    <p class="text-xs text-slate-500 uppercase tracking-widest">Belum ada catatan pembayaran</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Sidebar Detail (Right) --}}
    <div class="space-y-6">
        
        {{-- Client Card --}}
        <div class="bg-slate-800/40 backdrop-blur-md border border-slate-700/50 rounded-3xl p-6 shadow-xl">
            <h3 class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-4">Informasi Client</h3>
            <div class="space-y-4">
                <div>
                    <p class="text-[10px] font-black text-slate-600 uppercase tracking-widest">Nama Client</p>
                    <p class="text-sm font-black text-white">{{ $invoice->client_name }}</p>
                </div>
                @if($invoice->client_company)
                <div>
                    <p class="text-[10px] font-black text-slate-600 uppercase tracking-widest">Perusahaan</p>
                    <p class="text-sm font-bold text-slate-300">{{ $invoice->client_company }}</p>
                </div>
                @endif
                @if($invoice->client_email)
                <div>
                    <p class="text-[10px] font-black text-slate-600 uppercase tracking-widest">Email</p>
                    <p class="text-sm text-slate-400">{{ $invoice->client_email }}</p>
                </div>
                @endif
                @if($invoice->client_phone)
                <div>
                    <p class="text-[10px] font-black text-slate-600 uppercase tracking-widest">Telepon / WA</p>
                    <p class="text-sm text-slate-400">{{ $invoice->client_phone }}</p>
                </div>
                @endif
                @if($invoice->client_address)
                <div>
                    <p class="text-[10px] font-black text-slate-600 uppercase tracking-widest">Alamat</p>
                    <p class="text-xs text-slate-400 leading-relaxed">{{ $invoice->client_address }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Metadata Card --}}
        <div class="bg-slate-800/40 backdrop-blur-md border border-slate-700/50 rounded-3xl p-6 shadow-xl">
            <h3 class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-4">Informasi Invoice</h3>
            <div class="space-y-4">
                <div>
                    <p class="text-[10px] font-black text-slate-600 uppercase tracking-widest">No. Invoice</p>
                    <p class="text-sm font-black text-white">{{ $invoice->invoice_number }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-600 uppercase tracking-widest">Tanggal Terbit</p>
                    <p class="text-sm font-bold text-slate-300">{{ $invoice->date->format('d F Y') }}</p>
                </div>
                @if($invoice->due_date)
                <div>
                    <p class="text-[10px] font-black text-slate-600 uppercase tracking-widest">Jatuh Tempo</p>
                    <p class="text-sm font-bold text-slate-300">{{ $invoice->due_date->format('d F Y') }}</p>
                </div>
                @endif
                <div>
                    <p class="text-[10px] font-black text-slate-600 uppercase tracking-widest">Dibuat Oleh</p>
                    <p class="text-sm text-slate-400">{{ $invoice->creator->name }}</p>
                </div>
            </div>
        </div>

        @if($invoice->notes)
        <div class="bg-amber-500/5 border border-amber-500/10 rounded-3xl p-6 shadow-xl">
            <h3 class="text-[10px] font-black text-amber-500 uppercase tracking-widest mb-2">Catatan Internal</h3>
            <p class="text-xs text-amber-200/60 leading-relaxed italic">"{{ $invoice->notes }}"</p>
        </div>
        @endif
    </div>
</div>

{{-- MODAL: CATAT PEMBAYARAN --}}
<div x-data="{ show: false }" x-show="show" @open-modal.window="if ($event.detail === 'add-payment') show = true" @close-modal.window="show = false" class="fixed inset-0 z-[99] flex items-center justify-center p-4" style="display: none;">
    <div x-show="show" x-transition.opacity class="fixed inset-0 bg-slate-900/90 backdrop-blur-sm" @click="show = false"></div>
    <div x-show="show" x-transition.scale.origin.bottom class="relative bg-slate-800 rounded-3xl shadow-2xl border border-slate-700 w-full max-w-md z-10 overflow-hidden max-h-[90vh] overflow-y-auto scrollbar-hide ">
        <div class="px-8 py-6 border-b border-slate-700/50 flex justify-between items-center bg-slate-900/30">
            <h3 class="text-lg font-black text-white uppercase tracking-widest">Catat Pembayaran</h3>
            <button @click="show = false" class="text-slate-400 hover:text-white transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="{{ route('invoices.payments.store', $invoice) }}" method="POST">
            @csrf
            <div class="p-8 space-y-6">
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Nominal Pembayaran (Rp)</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-500 font-bold">Rp</div>
                        <input type="number" name="amount" value="{{ $invoice->balance_remaining }}" max="{{ $invoice->balance_remaining }}" class="w-full bg-slate-900/50 border border-slate-700 rounded-2xl pl-12 pr-4 py-4 text-white text-lg font-black focus:outline-none focus:border-blue-500 transition-all" required>
                    </div>
                    <p class="mt-2 text-[10px] text-slate-500 font-bold uppercase">Sisa Tagihan: Rp {{ number_format($invoice->balance_remaining, 0, ',', '.') }}</p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Metode</label>
                        <select name="payment_method" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-blue-500 transition-all">
                            <option value="Tunai">Tunai</option>
                            <option value="Transfer Bank">Transfer Bank</option>
                            <option value="QRIS">QRIS</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Tanggal</label>
                        <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-blue-500 transition-all" required>
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Catatan Tambahan</label>
                    <input type="text" name="notes" placeholder="Pelunasan Tahap 2, dsb..." class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-blue-500 transition-all">
                </div>
            </div>
            <div class="px-8 py-6 bg-slate-900/30 border-t border-slate-700/50 flex gap-3">
                <button type="button" @click="show = false" class="flex-1 py-3 rounded-2xl text-xs font-black text-slate-400 uppercase tracking-widest hover:bg-slate-700 transition-all">Batal</button>
                <button type="submit" class="flex-[2] bg-blue-600 hover:bg-blue-500 text-white py-3 rounded-2xl text-xs font-black uppercase tracking-widest shadow-xl shadow-blue-500/20 transition-all">Simpan Pembayaran</button>
            </div>
        </form>
    </div>
</div>
@endsection


