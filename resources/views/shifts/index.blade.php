@extends('layouts.app')

@section('title', 'Manajemen Shift')
@section('page-title', 'Manajemen Shift Kasir')
@section('page-subtitle', 'Buka dan tutup sesi shift harian')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Form Buka/Tutup Shift --}}
    <div class="lg:col-span-1" x-data="{ showOpenModal: {{ request('open') ? 'true' : 'false' }} }">
        <div class="card p-6 sticky top-24">
            @if(!$activeShift)
                <div class="text-center py-8">
                    <div class="w-20 h-20 bg-blue-500/10 rounded-3xl flex items-center justify-center mx-auto mb-6 border border-blue-500/20 shadow-lg shadow-blue-500/5">
                        <i class="fas fa-door-open text-blue-400 text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-black text-white mb-2 uppercase italic poppins tracking-tight">Shift Belum Dibuka</h3>
                    <p class="text-slate-500 text-sm mb-8 font-medium">Buka shift untuk mulai mencatat transaksi hari ini.</p>
                    
                    <button @click="showOpenModal = true" class="w-full bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-500 hover:to-blue-400 text-white font-black py-4 rounded-2xl transition-all shadow-xl shadow-blue-500/25 hover:shadow-blue-500/40 hover:scale-[1.02] active:scale-95 flex items-center justify-center gap-3 group">
                        <i class="fas fa-plus-circle group-hover:rotate-90 transition-transform duration-500"></i> 
                        BUKA SHIFT BARU
                    </button>
                </div>

                {{-- Modal Redesign --}}
                @include('components.modals.buka-shift')
            @else
                <h3 class="text-lg font-bold text-white mb-5 flex items-center gap-2"><i class="fas fa-door-closed text-red-400"></i> Tutup Shift Aktif</h3>
                
                <div class="bg-gradient-to-br from-emerald-900/40 to-slate-800 border border-emerald-500/30 rounded-2xl p-5 mb-6 shadow-xl shadow-emerald-900/20 relative overflow-hidden group">
                    <div class="absolute -right-8 -top-8 w-24 h-24 bg-emerald-500/20 blur-2xl rounded-full pointer-events-none"></div>
                    
                    <div class="flex items-center gap-2 text-emerald-400 font-bold mb-4">
                        <div class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></div>
                        Shift Sedang Berjalan
                    </div>
                    
                    <div class="space-y-3 relative z-10">
                        <div class="flex justify-between items-center border-b border-emerald-500/20 pb-2">
                            <span class="text-xs text-slate-400 uppercase tracking-wider">Dibuka Oleh</span>
                            <span class="text-white font-bold"><i class="fas fa-user-circle text-emerald-400/70 mr-1"></i> {{ $activeShift->opener->name }}</span>
                        </div>
                        <div class="flex justify-between items-center border-b border-emerald-500/20 pb-2">
                            <span class="text-xs text-slate-400 uppercase tracking-wider">Waktu Buka</span>
                            <span class="text-white font-bold"><i class="far fa-clock text-emerald-400/70 mr-1"></i> {{ $activeShift->opened_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-slate-400 uppercase tracking-wider">Kas Awal</span>
                            <span class="text-emerald-400 font-black">Rp {{ number_format($activeShift->opening_cash, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
                
                <form action="{{ route('shifts.close', $activeShift) }}" method="POST" x-data="shiftClose()">
                    @csrf
                    <div class="space-y-5">
                        {{-- Live Summary --}}
                        <div class="bg-slate-900/40 rounded-xl p-4 border border-slate-700/30 space-y-2 text-sm">
                            <div class="flex justify-between"><span class="text-slate-400">Penjualan Tunai:</span><span class="text-emerald-400 font-bold" x-text="fmtRp(summary.cash_sales)">...</span></div>
                            <div class="flex justify-between"><span class="text-slate-400">Penjualan Bank/QRIS:</span><span class="text-blue-400 font-bold" x-text="fmtRp(summary.bank_sales)">...</span></div>
                            <div class="flex justify-between"><span class="text-slate-400">Total Penjualan:</span><span class="text-white font-black" x-text="fmtRp(summary.total_sales)">...</span></div>
                            <div class="flex justify-between"><span class="text-slate-400">Pengeluaran:</span><span class="text-red-400 font-bold" x-text="fmtRp(summary.cash_expenses)">...</span></div>
                            <div class="flex justify-between border-t border-slate-700/50 pt-2 mt-2"><span class="text-slate-400">Expected Cash:</span><span class="text-yellow-400 font-black" x-text="fmtRp(summary.expected_cash)">...</span></div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Total Uang Fisik di Laci (Rp) <span class="text-red-400">*</span></label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 font-bold">Rp</span>
                                <input type="hidden" name="closing_cash" :value="closingCash">
                                <input type="text" x-model="closingCashFormatted" @input="updateClosing()" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl pl-12 pr-4 py-3 text-white font-bold text-lg focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-500/20 shadow-inner" required placeholder="Contoh: 2.000.000">
                            </div>
                        </div>

                        {{-- Live Discrepancy --}}
                        <div class="text-center py-3 rounded-xl border font-bold text-lg"
                             :class="diff === 0 ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : (diff > 0 ? 'bg-blue-500/10 text-blue-400 border-blue-500/20' : 'bg-red-500/10 text-red-400 border-red-500/20')">
                            Selisih: <span x-text="(diff >= 0 ? '+' : '') + fmtRp(diff)"></span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Catatan Penutupan</label>
                            <textarea name="notes" rows="3" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-500/20 shadow-inner"></textarea>
                        </div>
                        <button type="button" 
                                onclick="Swal.fire({
                                    title: 'Tutup Shift?',
                                    text: 'Pastikan uang kas di laci sudah sesuai sebelum menutup shift.',
                                    icon: 'question',
                                    showCancelButton: true,
                                    confirmButtonColor: '#ef4444',
                                    cancelButtonColor: '#64748b',
                                    confirmButtonText: 'Ya, Tutup Shift',
                                    cancelButtonText: 'Batal'
                                }).then((result) => {
                                    if (result.isConfirmed) this.closest('form').submit();
                                })"
                                class="w-full bg-gradient-to-r from-red-600 to-red-500 hover:from-red-500 hover:to-red-400 text-white font-bold py-3.5 rounded-xl transition-all shadow-lg shadow-red-500/25 hover:shadow-red-500/40 hover:scale-105 active:scale-95 flex items-center justify-center gap-2">
                            <i class="fas fa-stop"></i> TUTUP SHIFT SEKARANG
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>

    {{-- Riwayat Shift --}}
    <div class="lg:col-span-2">
        <div class="card overflow-visible">
            <div class="p-6 border-b border-white/5 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h3 class="font-black text-white tracking-tight text-lg">Riwayat Shift</h3>
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-1">Audit Log Operasional</p>
                </div>
                
                <button onclick="window.openExportModal()" class="w-10 h-10 bg-slate-800 border border-white/5 text-slate-400 rounded-xl hover:bg-slate-700 hover:text-white transition-premium flex items-center justify-center shadow-lg" title="Ekspor Laporan (PDF/Excel/CSV)">
                    <i class="fas fa-file-export"></i>
                </button>
            </div>

            {{-- Filter Bar --}}
            <div class="p-6 bg-slate-900/30 border-b border-white/5 relative z-[50]">
                <form method="GET" class="flex flex-col gap-4">
                    <input type="hidden" name="date_from" value="{{ is_object($start) ? $start->format('Y-m-d') : $start }}">
                    <input type="hidden" name="date_to" value="{{ is_object($end) ? $end->format('Y-m-d') : $end }}">
                    <div class="flex flex-wrap items-center gap-3">
                        @php
                            $startVal = request('date_from');
                            $endVal = request('date_to');
                            if (is_array($startVal)) $startVal = now()->startOfMonth()->format('Y-m-d');
                            if (is_array($endVal)) $endVal = now()->format('Y-m-d');
                            $start = \Carbon\Carbon::parse($startVal ?? now()->startOfMonth());
                            $end = \Carbon\Carbon::parse($endVal ?? now());
                        @endphp
                        <x-custom-filter period="today" :dateFrom="$start" :dateTo="$end" />

                        <select name="status" onchange="this.form.submit()" class="bg-slate-800/50 border border-white/5 rounded-2xl px-4 py-2.5 text-[11px] font-black text-slate-300 focus:outline-none focus:border-blue-500 transition-all uppercase tracking-wider">
                            <option value="">â€” Semua Status â€”</option>
                            <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Aktif (Open)</option>
                            <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Selesai (Closed)</option>
                        </select>

                        <select name="user_id" onchange="this.form.submit()" class="bg-slate-800/50 border border-white/5 rounded-2xl px-4 py-2.5 text-[11px] font-black text-slate-300 focus:outline-none focus:border-blue-500 transition-all uppercase tracking-wider">
                            <option value="">â€” Semua Kasir â€”</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-800/50 border-b border-slate-700">
                            <th class="p-4 table-head">Waktu</th>
                            <th class="p-4 table-head">Petugas</th>
                            <th class="p-4 table-head text-right">Total Transaksi</th>
                            <th class="p-4 table-head text-center">Status</th>
                            <th class="p-4 table-head text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        @forelse($shifts as $s)
                        <tr class="hover:bg-slate-800 transition-all duration-200 group border-l-2 {{ $s->status === 'open' ? 'border-emerald-500 bg-emerald-500/5' : 'border-transparent hover:border-blue-500' }} cursor-default">
                            <td class="p-4">
                                <p class="text-sm font-bold text-white">{{ $s->opened_at->format('d M Y') }}</p>
                                <div class="flex items-center gap-2 mt-1">
                                    <p class="text-xs text-slate-400"><i class="far fa-clock mr-1"></i>{{ $s->opened_at->format('H:i') }} - {{ $s->closed_at ? $s->closed_at->format('H:i') : 'Sekarang' }}</p>
                                    <span class="px-1.5 py-0.5 rounded bg-slate-900/50 text-[9px] font-black text-blue-400/80 border border-white/5 uppercase tracking-tighter">{{ $s->getDuration() }}</span>
                                </div>
                            </td>
                            <td class="p-4">
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-white flex items-center gap-2"><i class="fas fa-sign-in-alt text-emerald-400 w-3"></i> {{ $s->opener->name }}</p>
                                    @if($s->closed_by)
                                    <p class="text-xs font-medium text-slate-400 flex items-center gap-2"><i class="fas fa-sign-out-alt text-red-400 w-3"></i> {{ $s->closer->name }}</p>
                                    @endif
                                </div>
                            </td>
                            <td class="p-4 text-right">
                                <p class="font-black text-emerald-400 text-base">Rp {{ number_format($s->total_sales, 0, ',', '.') }}</p>
                                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest mt-0.5">{{ $s->total_transactions }} trx</p>
                            </td>
                            <td class="p-4 text-center">
                                @if($s->status === 'open')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold tracking-wider uppercase bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 animate-pulse"><i class="fas fa-circle text-[8px] mr-1.5"></i>Aktif</span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold tracking-wider uppercase bg-slate-800 text-slate-400 border border-slate-700"><i class="fas fa-check mr-1.5"></i>Selesai</span>
                                    @if($s->discrepancy !== null)
                                        <p class="text-[10px] font-bold mt-1 {{ $s->discrepancy == 0 ? 'text-emerald-400' : ($s->discrepancy > 0 ? 'text-blue-400' : 'text-red-400') }}">Selisih: {{ $s->discrepancy >= 0 ? '+' : '' }}Rp {{ number_format($s->discrepancy, 0, ',', '.') }}</p>
                                    @endif
                                @endif
                            </td>
                            <td class="p-4 text-right">
                                    <div class="opacity-100 flex justify-end gap-2" x-data="{ 
                                        showEdit: false, 
                                        opening: {{ $s->opening_cash }}, 
                                        closing: {{ $s->closing_cash ?? 0 }},
                                        openingFormatted: '{{ number_format($s->opening_cash, 0, '', '.') }}',
                                        closingFormatted: '{{ number_format($s->closing_cash ?? 0, 0, '', '.') }}',
                                        updateOpening() {
                                            let val = this.openingFormatted.replace(/\D/g, '');
                                            this.opening = val ? parseInt(val) : 0;
                                            this.openingFormatted = val ? parseInt(val).toLocaleString('id-ID') : '';
                                        },
                                        updateClosing() {
                                            let val = this.closingFormatted.replace(/\D/g, '');
                                            this.closing = val ? parseInt(val) : 0;
                                            this.closingFormatted = val ? parseInt(val).toLocaleString('id-ID') : '';
                                        }
                                    }">
                                        <a href="{{ route('shifts.show', $s) }}" class="w-8 h-8 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-300 flex items-center justify-center transition-colors shadow-sm" title="Detail Shift">
                                            <i class="fas fa-eye text-xs"></i>
                                        </a>
                                        @if(auth()->user()->isOwner())
                                        <button @click="showEdit = true" class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-400 hover:bg-blue-600 hover:text-white flex items-center justify-center transition-colors shadow-sm" title="Edit Shift">
                                            <i class="fas fa-edit text-xs"></i>
                                        </button>
                                        
                                        {{-- Inline Edit Modal --}}
                                        <div x-show="showEdit" x-transition.opacity x-cloak class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm z-[60] flex items-center justify-center p-4 text-left">
                                            <div @click.away="showEdit = false" class="bg-[#1e293b] rounded-3xl w-full max-w-sm shadow-2xl border border-slate-700 p-6 max-h-[90vh] overflow-y-auto scrollbar-hide ">
                                                <h3 class="text-lg font-black text-white mb-4 flex items-center gap-2"><i class="fas fa-edit text-blue-400"></i> Edit Shift</h3>
                                                <form action="{{ route('shifts.update', $s->id) }}" method="POST">
                                                    @csrf @method('PUT')
                                                    <div class="space-y-4">
                                                        <div>
                                                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1">Kas Awal (Rp)</label>
                                                            <div class="relative"><span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 font-bold">Rp</span>
                                                                <input type="hidden" name="opening_cash" :value="opening">
                                                                <input type="text" x-model="openingFormatted" @input="updateOpening()" required class="w-full bg-slate-900 border border-slate-700 rounded-xl pl-12 pr-4 py-2.5 text-white font-bold focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                                            </div>
                                                        </div>
                                                        @if($s->status === 'closed')
                                                        <div>
                                                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1">Kas Laci Akhir (Rp)</label>
                                                            <div class="relative"><span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 font-bold">Rp</span>
                                                                <input type="hidden" name="closing_cash" :value="closing">
                                                                <input type="text" x-model="closingFormatted" @input="updateClosing()" required class="w-full bg-slate-900 border border-slate-700 rounded-xl pl-12 pr-4 py-2.5 text-white font-bold focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                                            </div>
                                                        </div>
                                                        @endif
                                                        <div>
                                                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1">Catatan</label>
                                                            <textarea name="notes" rows="2" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 text-white text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">{{ $s->notes }}</textarea>
                                                        </div>
                                                        <div class="flex gap-2 pt-2">
                                                            <button type="button" @click="showEdit = false" class="flex-1 py-2.5 bg-slate-700 hover:bg-slate-600 text-white font-bold rounded-xl text-sm transition-colors active:scale-95">Batal</button>
                                                            <button type="submit" class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-sm shadow-lg shadow-blue-500/20 transition-all active:scale-95"><i class="fas fa-save mr-1"></i>Simpan Perubahan</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                    <form action="{{ route('shifts.destroy', $s->id) }}" method="POST" class="m-0" id="delete-shift-{{ $s->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" onclick="confirmDelete('{{ $s->id }}')" class="w-8 h-8 rounded-lg bg-red-500/10 text-red-400 hover:bg-red-600 hover:text-white flex items-center justify-center transition-colors shadow-sm" title="Hapus Shift">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="p-10 text-center text-slate-500">
                                <div class="w-16 h-16 bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-3">
                                    <i class="fas fa-history text-2xl text-slate-600"></i>
                                </div>
                                <p>Belum ada riwayat shift.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($shifts->hasPages())
            <div class="p-4 border-t border-slate-700/50">
                {{ $shifts->links('pagination::tailwind') }}
            </div>
            @endif
        </div>
    </div>
</div>

@if($activeShift)
@push('scripts')
<script>
function shiftClose() {
    return {
        summary: { cash_sales: 0, bank_sales: 0, total_sales: 0, cash_expenses: 0, expected_cash: 0 },
        closingCash: '',
        closingCashFormatted: '',
        diff: 0,
        init() {
            fetch('{{ route("shifts.summary", $activeShift) }}')
                .then(r => r.json())
                .then(d => { this.summary = d; this.calcDiff(); });
        },
        updateClosing() {
            let val = String(this.closingCashFormatted).replace(/\D/g, '');
            this.closingCash = val ? parseInt(val) : '';
            this.closingCashFormatted = val ? parseInt(val).toLocaleString('id-ID') : '';
            this.calcDiff();
        },
        calcDiff() {
            this.diff = (parseFloat(this.closingCash) || 0) - this.summary.expected_cash;
        },
        fmtRp(v) {
            return 'Rp ' + Math.abs(Math.round(v)).toLocaleString('id-ID');
        }
    };
}

function confirmDelete(id) {
    Swal.fire({
        title: 'Hapus Shift?',
        text: 'Data shift beserta semua transaksi dan pengeluaran di dalamnya akan dihapus permanen!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#334155',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        background: '#1e293b',
        color: '#f8fafc',
        customClass: {
            popup: 'rounded-3xl border border-slate-700',
            confirmButton: 'rounded-xl font-bold px-6 py-2.5',
            cancelButton: 'rounded-xl font-bold px-6 py-2.5'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-shift-' + id).submit();
        }
    });
}
</script>
@endpush
@endif
@endsection



