@extends('layouts.app')

@section('title', 'Semua Invoice — MONOFRAME')

@section('page-title', 'Daftar Invoice')

@section('content')
<div class="space-y-6">
       {{-- Filter Bar --}}
    <div class="bg-slate-800/20 p-6 rounded-[2rem] border border-white/5 shadow-xl space-y-4 relative z-[50]">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
            <div class="flex flex-wrap items-center gap-2">
                <div class="bg-slate-900/50 p-1 rounded-xl border border-white/5 flex">
                    <a href="{{ route('invoices.index', array_merge(request()->except('page'), ['status' => 'all'])) }}" class="px-4 py-2 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all {{ request('status', 'all') == 'all' ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/20' : 'text-slate-400 hover:text-white' }}">Semua</a>
                    <a href="{{ route('invoices.index', array_merge(request()->except('page'), ['status' => 'pending'])) }}" class="px-4 py-2 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all {{ request('status') == 'pending' ? 'bg-amber-600 text-white shadow-lg shadow-amber-500/20' : 'text-slate-400 hover:text-white' }}">Menunggu</a>
                    <a href="{{ route('invoices.index', array_merge(request()->except('page'), ['status' => 'partial'])) }}" class="px-4 py-2 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all {{ request('status') == 'partial' ? 'bg-cyan-600 text-white shadow-lg shadow-cyan-500/20' : 'text-slate-400 hover:text-white' }}">DP Aktif</a>
                    <a href="{{ route('invoices.index', array_merge(request()->except('page'), ['status' => 'paid'])) }}" class="px-4 py-2 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all {{ request('status') == 'paid' ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-500/20' : 'text-slate-400 hover:text-white' }}">Lunas</a>
                </div>
                
                <button onclick="window.openExportModal()" class="w-10 h-10 bg-slate-800 border border-white/5 text-slate-400 rounded-xl hover:bg-slate-700 hover:text-white transition-premium flex items-center justify-center shadow-lg" title="Ekspor Laporan (PDF/Excel/CSV)">
                    <i class="fas fa-file-export"></i>
                </button>
            </div>

            <a href="{{ route('invoices.create') }}" class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-3 rounded-2xl font-black uppercase tracking-widest text-[10px] transition-all shadow-xl shadow-blue-500/20 flex items-center gap-2">
                <i class="fas fa-plus-circle"></i> Buat Invoice Baru
            </a>
        </div>

        <div class="flex flex-col lg:flex-row gap-4 pt-4 border-t border-white/5">
            <form method="GET" class="flex-1 flex flex-wrap items-center gap-3">
                @if(request('status') && !is_array(request('status'))) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
                
                @php
                    $startVal = request('date_from');
                    $endVal = request('date_to');
                    if (is_array($startVal)) $startVal = null;
                    if (is_array($endVal)) $endVal = null;
                    $start = $startVal ? \Carbon\Carbon::parse($startVal) : null;
                    $end = $endVal ? \Carbon\Carbon::parse($endVal) : null;
                @endphp
                <input type="hidden" name="date_from" value="{{ $startVal }}">
                <input type="hidden" name="date_to" value="{{ $endVal }}">
                <x-custom-filter :dateFrom="$start" :dateTo="$end" />

                <div class="relative group flex-1 max-w-sm">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 text-[10px] group-focus-within:text-blue-400 transition-colors"></i>
                    <input type="text" name="search" value="{{ is_array(request('search')) ? '' : request('search') }}" 
                           class="w-full bg-slate-900/60 border border-white/5 rounded-xl pl-10 pr-4 py-2.5 text-[11px] text-white font-bold placeholder-slate-600 focus:border-blue-500/50 focus:ring-4 focus:ring-blue-500/5 outline-none transition-all" 
                           placeholder="Cari No. Invoice atau Client...">
                </div>

                <button type="submit" class="bg-slate-700 hover:bg-slate-600 text-white px-4 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                    Cari Data
                </button>
                
                @if(request()->anyFilled(['search', 'date_from', 'date_to', 'status']))
                    <a href="{{ route('invoices.index') }}" class="text-[10px] font-black text-slate-500 hover:text-white uppercase tracking-widest transition-colors">Reset Filter</a>
                @endif
            </form>
        </div>
    </div>

    {{-- Invoice Table --}}
    <div class="bg-slate-800/40 backdrop-blur-md border border-slate-700/50 rounded-3xl overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-900/50 border-b border-slate-700/50">
                        <th class="px-6 py-5 text-[10px] font-black text-slate-500 uppercase tracking-widest">No. Invoice / Tanggal</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-500 uppercase tracking-widest">Client</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-500 uppercase tracking-widest text-right">Total Tagihan</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-500 uppercase tracking-widest text-right">Pembayaran Masuk</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-500 uppercase tracking-widest text-right">Sisa Tagihan</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-500 uppercase tracking-widest text-center">Status</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-500 uppercase tracking-widest text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/30">
                    @forelse($invoices as $invoice)
                    <tr class="hover:bg-slate-700/20 transition-colors group">
                        <td class="px-6 py-4">
                            <span class="text-sm font-black text-white group-hover:text-blue-400 transition-colors">{{ $invoice->invoice_number }}</span>
                            <div class="text-[10px] text-slate-500 font-bold uppercase tracking-tighter">{{ $invoice->date->format('d M Y') }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-bold text-slate-200">{{ $invoice->client_name }}</div>
                            <div class="text-[10px] text-slate-500 uppercase">{{ $invoice->client_company ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="text-sm font-black text-white">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</div>
                            <div class="text-[9px] text-slate-500 font-bold uppercase">Subtotal: Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="text-sm font-black text-emerald-400">Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}</div>
                            <div class="mt-1.5 w-20 h-1 bg-slate-700 rounded-full overflow-hidden ml-auto">
                                <div class="h-full bg-emerald-500" style="width: {{ $invoice->payment_percentage }}%"></div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="text-sm font-black text-red-400">Rp {{ number_format($invoice->balance_remaining, 0, ',', '.') }}</div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @php
                                $statusClasses = [
                                    'pending' => 'bg-amber-500/10 text-amber-500 border-amber-500/20',
                                    'partial' => 'bg-blue-500/10 text-blue-500 border-blue-500/20',
                                    'paid' => 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20',
                                    'overdue' => 'bg-red-500/10 text-red-500 border-red-500/20',
                                ];
                                $statusLabels = [
                                    'pending' => '🟡 Pending',
                                    'partial' => '🔵 DP Dibayar',
                                    'paid' => '🟢 Lunas',
                                    'overdue' => '🔴 Jatuh Tempo',
                                ];
                            @endphp
                            <span class="inline-flex px-3 py-1 rounded-full border text-[10px] font-black uppercase tracking-widest {{ $statusClasses[$invoice->status] ?? $statusClasses['pending'] }}">
                                {{ $statusLabels[$invoice->status] ?? 'Pending' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('invoices.show', $invoice) }}" class="w-8 h-8 rounded-lg bg-slate-700/50 hover:bg-blue-600 text-slate-300 hover:text-white flex items-center justify-center transition-all" title="Lihat & Kelola Pembayaran">
                                    <i class="fas fa-eye text-xs"></i>
                                </a>
                                <a href="{{ route('invoices.edit', $invoice) }}" class="w-8 h-8 rounded-lg bg-slate-700/50 hover:bg-amber-600 text-slate-300 hover:text-white flex items-center justify-center transition-all" title="Edit Invoice">
                                    <i class="fas fa-pencil-alt text-xs"></i>
                                </a>
                                <a href="{{ route('invoices.pdf', $invoice) }}" target="_blank" class="w-8 h-8 rounded-lg bg-slate-700/50 hover:bg-emerald-600 text-slate-300 hover:text-white flex items-center justify-center transition-all" title="Download PDF">
                                    <i class="fas fa-download text-xs"></i>
                                </a>
                                <form action="{{ route('invoices.destroy', $invoice) }}" method="POST" onsubmit="return confirm('Hapus invoice ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="w-8 h-8 rounded-lg bg-slate-700/50 hover:bg-red-600 text-slate-300 hover:text-white flex items-center justify-center transition-all">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center gap-4">
                                <div class="w-16 h-16 rounded-full bg-slate-700/30 flex items-center justify-center text-slate-600">
                                    <i class="fas fa-file-invoice text-3xl"></i>
                                </div>
                                <div>
                                    <p class="text-slate-400 font-bold uppercase tracking-widest text-sm">Belum ada invoice</p>
                                    <p class="text-slate-500 text-xs mt-1">Mulai buat invoice pertama Anda untuk client.</p>
                                </div>
                                <a href="{{ route('invoices.create') }}" class="text-blue-400 font-black uppercase tracking-widest text-[10px] hover:text-blue-300 transition-colors">
                                    Buat Invoice Sekarang <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($invoices->hasPages())
        <div class="px-6 py-4 border-t border-slate-700/50 bg-slate-900/30">
            {{ $invoices->links() }}
        </div>
        @endif
    </div>

    {{-- Stats Row --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-slate-800/40 backdrop-blur-md border border-slate-700/50 rounded-3xl p-6 shadow-xl border-l-blue-500/50">
            <p class="text-[10px] font-black text-blue-500 uppercase tracking-widest mb-1">Total Piutang</p>
            <h3 class="text-2xl font-black text-white">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</h3>
        </div>
        <div class="bg-slate-800/40 backdrop-blur-md border border-slate-700/50 rounded-3xl p-6 shadow-xl border-l-emerald-500/50">
            <p class="text-[10px] font-black text-emerald-500 uppercase tracking-widest mb-1">Total Dibayar</p>
            <h3 class="text-2xl font-black text-white">Rp {{ number_format($totalDibayar, 0, ',', '.') }}</h3>
        </div>
        <div class="bg-slate-800/40 backdrop-blur-md border border-slate-700/50 rounded-3xl p-6 shadow-xl border-l-red-500/50">
            <p class="text-[10px] font-black text-red-500 uppercase tracking-widest mb-1">Total Sisa Tagihan</p>
            <h3 class="text-2xl font-black text-white">Rp {{ number_format($totalSisa, 0, ',', '.') }}</h3>
        </div>
        <div class="bg-slate-800/40 backdrop-blur-md border border-slate-700/50 rounded-3xl p-6 shadow-xl border-l-amber-500/50">
            <p class="text-[10px] font-black text-amber-500 uppercase tracking-widest mb-1">Invoice Menunggu</p>
            <h3 class="text-2xl font-black text-white">{{ $invoiceMenunggu }}</h3>
        </div>
    </div>
</div>
@endsection
