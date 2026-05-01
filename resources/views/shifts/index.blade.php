@extends('layouts.app')

@section('title', 'Manajemen Shift')
@section('page-title', 'Manajemen Shift Kasir')
@section('page-subtitle', 'Buka dan tutup sesi shift harian')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Form Buka/Tutup Shift --}}
    <div class="lg:col-span-1">
        <div class="card p-6 sticky top-24">
            @if(!$activeShift)
                <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2"><i class="fas fa-door-open text-blue-400"></i> Buka Shift Baru</h3>
                <form action="{{ route('shifts.open') }}" method="POST">
                    @csrf
                    <div class="space-y-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Uang Kas Awal (Rp) <span class="text-red-400">*</span></label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 font-bold">Rp</span>
                                <input type="number" name="opening_cash" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl pl-12 pr-4 py-3 text-white font-bold text-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 shadow-inner" required min="0" value="0">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Catatan Pembukaan</label>
                            <textarea name="notes" rows="3" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 shadow-inner"></textarea>
                        </div>
                        <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-500 hover:to-blue-400 text-white font-bold py-3.5 rounded-xl transition-all shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 hover:scale-105 active:scale-95 flex items-center justify-center gap-2"><i class="fas fa-play"></i> BUKA SHIFT</button>
                    </div>
                </form>
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
                
                <form action="{{ route('shifts.close', $activeShift) }}" method="POST">
                    @csrf
                    <div class="space-y-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Total Uang Fisik di Laci (Rp) <span class="text-red-400">*</span></label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 font-bold">Rp</span>
                                <input type="number" name="closing_cash" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl pl-12 pr-4 py-3 text-white font-bold text-lg focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-500/20 shadow-inner" required min="0" placeholder="Hitung uang fisik">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Catatan Penutupan</label>
                            <textarea name="notes" rows="3" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-500/20 shadow-inner"></textarea>
                        </div>
                        <button type="submit" class="w-full bg-gradient-to-r from-red-600 to-red-500 hover:from-red-500 hover:to-red-400 text-white font-bold py-3.5 rounded-xl transition-all shadow-lg shadow-red-500/25 hover:shadow-red-500/40 hover:scale-105 active:scale-95 flex items-center justify-center gap-2" onclick="return confirm('Yakin ingin menutup shift? Kasir tidak akan bisa digunakan sampai shift baru dibuka.')"><i class="fas fa-stop"></i> TUTUP SHIFT SEKARANG</button>
                    </div>
                </form>
            @endif
        </div>
    </div>

    {{-- Riwayat Shift --}}
    <div class="lg:col-span-2">
        <div class="card">
            <div class="p-5 border-b border-slate-700/50">
                <h3 class="font-bold text-white">Riwayat Shift</h3>
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
                                <p class="text-xs text-slate-400 mt-0.5"><i class="far fa-clock mr-1"></i>{{ $s->opened_at->format('H:i') }} - {{ $s->closed_at ? $s->closed_at->format('H:i') : 'Sekarang' }}</p>
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
                                @endif
                            </td>
                            <td class="p-4 text-right">
                                <div class="opacity-0 group-hover:opacity-100 transition-opacity flex justify-end">
                                    <a href="{{ route('shifts.show', $s) }}" class="w-8 h-8 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-300 flex items-center justify-center transition-colors shadow-sm" title="Detail Shift">
                                        <i class="fas fa-eye text-xs"></i>
                                    </a>
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
@endsection
