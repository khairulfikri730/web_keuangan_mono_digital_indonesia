@php
    $currentDate = '';
    $labels = \App\Models\Cashflow::sourceLabels();
@endphp

<div class="relative">
    <!-- Timeline Vertical Line -->
    <div class="absolute left-6 md:left-8 top-4 bottom-4 w-1 bg-gradient-to-b from-blue-600/20 via-slate-800 to-transparent hidden md:block"></div>

    <div class="space-y-4" id="transactionList">
        @forelse($cashflows as $c)
            @php $txDate = $c->transaction_date->isoFormat('dddd, D MMMM YYYY'); @endphp

            @if($currentDate != $txDate)
                <div class="md:pl-20 pt-6 first:pt-0 tx-group" data-type="{{ $c->type }}">
                    <h4 class="inline-block px-4 py-1.5 rounded-full text-[10px] font-black text-slate-400 bg-slate-800/50 border border-white/5 uppercase tracking-[0.2em] sticky top-0 backdrop-blur-md z-10 shadow-lg">
                        <i class="far fa-calendar-alt mr-2 text-blue-500"></i>{{ $txDate }}
                    </h4>
                </div>
                @php $currentDate = $txDate; @endphp
            @endif

            <div class="relative flex flex-col md:flex-row md:items-center gap-5 group p-5 bg-slate-800/20 border border-white/5 hover:bg-slate-800/60 rounded-[1.5rem] transition-premium md:pl-20 tx-item shadow-sm hover:shadow-xl" data-type="{{ $c->type }}" data-category="{{ $c->category }}" data-transaction-category="{{ $c->transaction_category }}">
                <!-- Dot Marker -->
                @php
                    $dotColor = $c->transaction_category == 'adjustment' ? 'bg-amber-500 shadow-[0_0_10px_rgba(245,158,11,0.5)]' : ($c->type == 'income' ? 'bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.5)]' : 'bg-red-500 shadow-[0_0_10px_rgba(239,68,68,0.5)]');
                @endphp
                <div class="hidden md:flex absolute left-[1.85rem] w-4 h-4 rounded-full border-[3px] border-[#0f172a] {{ $dotColor }} z-10 transition-transform group-hover:scale-125"></div>

                <div class="flex items-center gap-5 flex-1">
                    @php
                        $iconBg = $c->transaction_category == 'adjustment' ? 'bg-amber-500/10 text-amber-400 group-hover:bg-amber-500 group-hover:text-white' : ($c->type == 'income' ? 'bg-emerald-500/10 text-emerald-400 group-hover:bg-emerald-500 group-hover:text-white' : 'bg-red-500/10 text-red-400 group-hover:bg-red-500 group-hover:text-white');
                        
                        $icon = ($c->type == 'income' ? 'fa-arrow-down' : 'fa-arrow-up');
                        if ($c->transaction_category == 'adjustment') {
                            if (str_contains(strtolower($c->category), 'audit')) $icon = 'fa-shield-alt';
                            elseif (str_contains(strtolower($c->category), 'transfer')) $icon = 'fa-exchange-alt';
                            elseif (str_contains(strtolower($c->category), 'koreksi') || str_contains(strtolower($c->category), 'manual')) $icon = 'fa-sliders-h';
                            else $icon = 'fa-balance-scale';
                        }
                    @endphp
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center shrink-0 transition-premium {{ $iconBg }} shadow-inner">
                        <i class="fas {{ $icon }} text-xl"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-base font-black text-white tracking-tight">{{ $c->category }}</p>
                        <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                            <span class="text-xs text-slate-400 font-medium truncate max-w-[200px]" title="{{ $c->description }}">{{ $c->description }}</span>
                            @php
                                $sourceBadge = $labels[$c->source] ?? $c->source;
                                $sourceColors = [
                                    'pos_cash' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                                    'pos_bank' => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
                                    'transfer' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                    'pos' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                    'manual' => 'bg-slate-500/10 text-slate-400 border-slate-500/20',
                                ];
                                $badgeColor = $sourceColors[$c->source] ?? 'bg-slate-500/10 text-slate-400 border-slate-500/20';
                                
                                // Category Specific Badges
                                $catBadge = '';
                                if ($c->transaction_category == 'adjustment') {
                                    $catBadge = '<span class="px-2 py-0.5 rounded-lg text-[9px] uppercase font-black tracking-widest bg-amber-500/10 text-amber-400 border border-amber-500/20 shadow-lg shadow-amber-900/20"><i class="fas fa-shield-check mr-1.5"></i>ADJUSTMENT</span>';
                                } elseif ($c->category == 'Penjualan') {
                                    $catBadge = '<span class="px-2 py-0.5 rounded-lg text-[9px] uppercase font-black tracking-widest bg-emerald-500/10 text-emerald-400 border border-emerald-500/20"><i class="fas fa-shopping-cart mr-1.5"></i>Penjualan</span>';
                                } elseif ($c->category == 'Transfer Internal') {
                                    $catBadge = '<span class="px-2 py-0.5 rounded-lg text-[9px] uppercase font-black tracking-widest bg-indigo-500/10 text-indigo-400 border border-indigo-500/20"><i class="fas fa-exchange-alt mr-1.5"></i>Transfer</span>';
                                }
                            @endphp
                            {!! $catBadge !!}
                             <span class="px-2 py-0.5 rounded-lg text-[9px] uppercase font-black tracking-widest {{ $c->type == 'income' ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-900/20' : 'bg-red-600 text-white shadow-lg shadow-red-900/20' }}">
                                 {{ $c->type == 'income' ? 'Masuk' : 'Keluar' }}
                             </span>
                             <span class="px-2 py-0.5 rounded-lg text-[9px] uppercase font-black tracking-widest border {{ $badgeColor }}">{{ $sourceBadge }}</span>
                             @if($c->worksheet)
                                 <span class="px-2 py-0.5 rounded-lg text-[9px] uppercase font-black tracking-widest bg-slate-800 text-slate-500 border border-white/5" title="Cabang / Worksheet"><i class="fas fa-store-alt mr-1.5"></i>{{ $c->worksheet->name }}</span>
                             @endif
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between md:justify-end gap-8 ml-16 md:ml-0">
                    <div class="text-right">
                        <p class="text-xl font-black {{ $c->type == 'income' ? 'text-emerald-400' : 'text-red-400' }} tracking-tighter">
                            {{ $c->type == 'income' ? '+' : '-' }} Rp {{ number_format($c->amount, 0, ',', '.') }}
                        </p>
                        <p class="text-[9px] font-bold text-slate-500 uppercase tracking-widest mt-1">{{ $c->transaction_date->format('H:i') }} • {{ strtoupper($c->type) }}</p>
                    </div>

                    <div class="opacity-100 md:opacity-0 group-hover:opacity-100 transition-premium flex items-center gap-2">
                        @if(!$c->reference || str_starts_with($c->reference, 'TRF-'))
                            <button type="button" 
                                onclick="window.dispatchEvent(new CustomEvent('open-edit', { detail: {{ json_encode($c->only(['id', 'type', 'source', 'category', 'amount', 'description', 'transaction_date'])) }} }))"
                                class="w-10 h-10 rounded-xl bg-slate-800 hover:bg-blue-600 text-slate-400 hover:text-white flex items-center justify-center transition-premium shadow-lg" title="Edit Data">
                                <i class="fas fa-pen text-xs"></i>
                            </button>
                            <form action="{{ route('cashflow.destroy', $c) }}" method="POST" class="delete-form">
                                @csrf @method('DELETE')
                                <button type="button" onclick="confirmDelete(this)" class="w-10 h-10 rounded-xl bg-slate-800 hover:bg-red-600 text-slate-400 hover:text-white flex items-center justify-center transition-premium shadow-lg" title="Hapus Data">
                                    <i class="fas fa-trash text-sm"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="py-24 flex flex-col items-center justify-center text-center glass-card rounded-[3rem]" id="emptyState">
                <div class="w-24 h-24 mb-8 bg-slate-800/50 rounded-full flex items-center justify-center text-slate-700 shadow-inner">
                    <i class="fas fa-receipt text-4xl"></i>
                </div>
                <h3 class="text-xl font-black text-white mb-2 uppercase tracking-tight">Belum ada transaksi</h3>
                <p class="text-sm text-slate-500 max-w-xs mb-8 font-medium">Belum ada aktivitas keuangan yang tercatat pada periode ini.</p>
                <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="bg-blue-600 hover:bg-blue-500 text-white font-black px-8 py-3.5 rounded-2xl transition-premium shadow-xl shadow-blue-900/30 text-[10px] uppercase tracking-widest">
                    Mulai Catat Sekarang
                </button>
            </div>
        @endforelse
    </div>
</div>

<script>
    function confirmDelete(btn) {
        Swal.fire({
            title: 'Hapus Transaksi?',
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#334155',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            background: '#1e293b',
            color: '#f8fafc',
            customClass: {
                popup: 'rounded-[2rem] border border-white/5 shadow-2xl',
                confirmButton: 'rounded-xl px-6 py-3 font-black uppercase tracking-widest text-[10px]',
                cancelButton: 'rounded-xl px-6 py-3 font-black uppercase tracking-widest text-[10px]'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                btn.closest('form').submit();
            }
        });
    }
</script>
