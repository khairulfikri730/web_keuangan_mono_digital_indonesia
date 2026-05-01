@php
    $currentDate = '';
    $labels = \App\Models\Cashflow::sourceLabels();
@endphp

<div class="relative">
    <div class="absolute left-6 top-2 bottom-2 w-px bg-slate-800 hidden md:block"></div>

    <div class="space-y-6" id="transactionList">
        @forelse($cashflows as $c)
            @php $txDate = $c->transaction_date->format('d M Y'); @endphp

            @if($currentDate != $txDate)
                <div class="md:pl-16 pt-2 first:pt-0 tx-group" data-type="{{ $c->type }}">
                    <h4 class="text-xs font-bold text-slate-500 uppercase tracking-widest sticky top-0 bg-[#111827] py-2 z-10">{{ $txDate }}</h4>
                </div>
                @php $currentDate = $txDate; @endphp
            @endif

            <div class="relative flex flex-col md:flex-row md:items-center gap-4 group p-3 hover:bg-[#1F2937] rounded-xl transition-colors md:pl-16 tx-item" data-type="{{ $c->type }}">
                <div class="hidden md:flex absolute left-4 w-4 h-4 rounded-full border-[3px] border-[#111827] {{ $c->type == 'income' ? 'bg-emerald-500' : 'bg-red-500' }} z-10 shadow-sm"></div>

                <div class="flex items-center gap-4 flex-1">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 {{ $c->type == 'income' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-red-500/10 text-red-400' }}">
                        <i class="fas {{ $c->type == 'income' ? 'fa-arrow-down' : 'fa-arrow-up' }}"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-white">{{ $c->category }}</p>
                        <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                            <span class="text-xs text-slate-400">{{ $c->description }}</span>
                            @php
                                $sourceBadge = $labels[$c->source] ?? $c->source;
                                $sourceColors = [
                                    'pos_cash' => 'bg-yellow-500/10 text-yellow-400',
                                    'pos_bank' => 'bg-blue-500/10 text-blue-400',
                                    'transfer' => 'bg-purple-500/10 text-purple-400',
                                    'pos' => 'bg-blue-500/10 text-blue-400',
                                    'manual' => 'bg-slate-500/10 text-slate-400',
                                ];
                                $badgeColor = $sourceColors[$c->source] ?? 'bg-slate-500/10 text-slate-400';
                            @endphp
                            <span class="px-1.5 py-0.5 rounded text-[9px] uppercase font-bold tracking-wider {{ $badgeColor }}">{{ $sourceBadge }}</span>
                            @if($c->reference)
                                <span class="px-1.5 py-0.5 rounded text-[9px] uppercase font-bold tracking-wider bg-blue-500/10 text-blue-400">POS</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between md:justify-end gap-6 ml-14 md:ml-0">
                    <p class="text-base font-black {{ $c->type == 'income' ? 'text-emerald-400' : 'text-red-400' }}">
                        {{ $c->type == 'income' ? '+' : '-' }} Rp {{ number_format($c->amount, 0, ',', '.') }}
                    </p>

                    <div class="opacity-100 md:opacity-0 group-hover:opacity-100 transition-opacity">
                        @if(!$c->reference)
                            <form action="{{ route('cashflow.destroy', $c) }}" method="POST" onsubmit="return confirm('Hapus data ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="w-8 h-8 rounded-full bg-slate-800 hover:bg-red-500/20 text-slate-400 hover:text-red-400 flex items-center justify-center transition-colors" title="Hapus Data">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="py-16 flex flex-col items-center justify-center text-center" id="emptyState">
                <div class="w-24 h-24 mb-6 opacity-50">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-full h-full text-slate-500">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-white mb-2">Belum ada transaksi</h3>
                <p class="text-sm text-slate-400 max-w-sm mb-6">Belum ada transaksi pada periode ini. Mulai catat pemasukan atau pengeluaran pertama Anda.</p>
                <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="bg-blue-600 hover:bg-blue-500 text-white font-medium px-6 py-2.5 rounded-full transition-all text-sm flex items-center gap-2">
                    <i class="fas fa-plus"></i> Tambah Transaksi
                </button>
            </div>
        @endforelse
    </div>
</div>
