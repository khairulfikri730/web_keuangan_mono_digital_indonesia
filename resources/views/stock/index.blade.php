@extends('layouts.app')

@section('title', 'Manajemen Stok')
@section('page-title', 'Gudang & Stok')
@section('page-subtitle', 'Kelola stok dan mutasi produk')

@section('content')
@php
    $totalProducts = count($products);
    $lowStock = collect($products)->where('stock', '<=', 5)->where('stock', '>', 0)->count();
    $outOfStock = collect($products)->where('stock', '<=', 0)->count();
    $totalMutations = $mutations->total();
@endphp

<div x-data="stockApp()" class="flex flex-col gap-6">

    {{-- HEADER & ACTION BAR --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-[#1e293b] p-5 rounded-2xl border border-slate-700/80 shadow-sm">
        <div>
            <h2 class="text-xl font-black text-white tracking-tight">Riwayat Mutasi</h2>
            <p class="text-sm text-slate-400 font-medium mt-1">Pantau arus masuk keluar barang secara realtime</p>
        </div>
        
        <div class="flex items-center gap-3 w-full sm:w-auto">
            <div class="relative w-full sm:w-72">
                <form action="{{ route('stock.index') }}" method="GET" id="searchForm">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl pl-11 pr-4 py-2.5 text-slate-200 placeholder-slate-500 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-sm font-medium shadow-inner" placeholder="Cari catatan mutasi...">
                    <input type="hidden" name="type" value="{{ request('type') }}">
                </form>
            </div>
            <button @click="openModal()" class="shrink-0 bg-emerald-500 hover:bg-emerald-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold transition-all shadow-lg shadow-emerald-500/20 active:scale-95 flex items-center gap-2">
                <i class="fas fa-plus"></i> <span class="hidden sm:inline">Penyesuaian Stok</span>
            </button>
        </div>
    </div>

    {{-- SUMMARY CARDS --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Card 1 --}}
        <div class="bg-slate-800 rounded-2xl p-4 border border-slate-700/50 flex items-center gap-4 shadow-sm relative overflow-hidden">
            <div class="w-12 h-12 rounded-xl bg-blue-500/10 flex items-center justify-center border border-blue-500/20 shrink-0">
                <i class="fas fa-box text-blue-400 text-xl"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-0.5">Total Produk</p>
                <h3 class="text-2xl font-black text-white">{{ $totalProducts }}</h3>
            </div>
        </div>

        {{-- Card 2 --}}
        <div class="bg-slate-800 rounded-2xl p-4 border border-slate-700/50 flex items-center gap-4 shadow-sm relative overflow-hidden">
            <div class="w-12 h-12 rounded-xl bg-amber-500/10 flex items-center justify-center border border-amber-500/20 shrink-0">
                <i class="fas fa-exclamation-triangle text-amber-400 text-xl"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-0.5">Stok Menipis</p>
                <h3 class="text-2xl font-black text-white">{{ $lowStock }}</h3>
            </div>
            @if($lowStock > 0)
                <div class="absolute top-0 right-0 w-2 h-full bg-amber-500"></div>
            @endif
        </div>

        {{-- Card 3 --}}
        <div class="bg-slate-800 rounded-2xl p-4 border border-slate-700/50 flex items-center gap-4 shadow-sm relative overflow-hidden">
            <div class="w-12 h-12 rounded-xl bg-red-500/10 flex items-center justify-center border border-red-500/20 shrink-0">
                <i class="fas fa-times-circle text-red-400 text-xl"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-0.5">Stok Habis</p>
                <h3 class="text-2xl font-black text-white">{{ $outOfStock }}</h3>
            </div>
            @if($outOfStock > 0)
                <div class="absolute top-0 right-0 w-2 h-full bg-red-500 shadow-[0_0_10px_#ef4444]"></div>
            @endif
        </div>

        {{-- Card 4 --}}
        <div class="bg-slate-800 rounded-2xl p-4 border border-slate-700/50 flex items-center gap-4 shadow-sm relative overflow-hidden">
            <div class="w-12 h-12 rounded-xl bg-emerald-500/10 flex items-center justify-center border border-emerald-500/20 shrink-0">
                <i class="fas fa-exchange-alt text-emerald-400 text-xl"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-0.5">Total Mutasi</p>
                <h3 class="text-2xl font-black text-white">{{ $totalMutations }}</h3>
            </div>
        </div>
    </div>

    {{-- FILTER CHIPS --}}
    <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-hide shrink-0">
        <a href="{{ route('stock.index') }}" class="px-5 py-2 rounded-full text-sm font-bold whitespace-nowrap transition-all border {{ !request('type') ? 'bg-blue-600 text-white border-blue-500 shadow-lg shadow-blue-500/30' : 'bg-slate-800 border-slate-700 text-slate-400 hover:bg-slate-700 hover:text-white' }}">
            Semua Data
        </a>
        <a href="{{ route('stock.index', ['type' => 'in']) }}" class="px-5 py-2 rounded-full text-sm font-bold whitespace-nowrap transition-all border {{ request('type') == 'in' ? 'bg-emerald-600 text-white border-emerald-500 shadow-lg shadow-emerald-500/30' : 'bg-slate-800 border-slate-700 text-slate-400 hover:bg-slate-700 hover:text-white' }}">
            <i class="fas fa-arrow-down mr-1"></i> Masuk
        </a>
        <a href="{{ route('stock.index', ['type' => 'out']) }}" class="px-5 py-2 rounded-full text-sm font-bold whitespace-nowrap transition-all border {{ request('type') == 'out' ? 'bg-red-600 text-white border-red-500 shadow-lg shadow-red-500/30' : 'bg-slate-800 border-slate-700 text-slate-400 hover:bg-slate-700 hover:text-white' }}">
            <i class="fas fa-arrow-up mr-1"></i> Keluar
        </a>
        <a href="{{ route('stock.index', ['type' => 'adjustment']) }}" class="px-5 py-2 rounded-full text-sm font-bold whitespace-nowrap transition-all border {{ request('type') == 'adjustment' ? 'bg-amber-600 text-white border-amber-500 shadow-lg shadow-amber-500/30' : 'bg-slate-800 border-slate-700 text-slate-400 hover:bg-slate-700 hover:text-white' }}">
            <i class="fas fa-sliders-h mr-1"></i> Penyesuaian
        </a>
    </div>

    {{-- LIST ACTIVITY CARDS --}}
    <div class="flex flex-col gap-3">
        @forelse($mutations as $m)
        <div class="group bg-slate-800 rounded-2xl p-4 border border-slate-700/80 hover:border-slate-500 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 flex flex-col md:flex-row md:items-center justify-between gap-4 relative overflow-hidden">
            
            {{-- Bagian Kiri: Info Produk --}}
            <div class="flex items-center gap-4 flex-1">
                <div class="w-12 h-12 rounded-xl bg-slate-900 border border-slate-700 flex items-center justify-center shrink-0 shadow-inner">
                    @if($m->product && $m->product->category && $m->product->category->color)
                        <i class="fas fa-box text-xl" style="color: {{ $m->product->category->color }}"></i>
                    @else
                        <i class="fas fa-box text-slate-500 text-xl"></i>
                    @endif
                </div>
                <div>
                    <h3 class="font-black text-white text-base leading-tight group-hover:text-blue-400 transition-colors">{{ $m->product ? $m->product->name : 'Produk Dihapus' }}</h3>
                    <div class="flex items-center gap-2 mt-1">
                        @if($m->product && $m->product->category)
                            <span class="text-[10px] uppercase font-bold text-slate-500 tracking-wider">{{ $m->product->category->name }}</span>
                            <span class="text-slate-600">•</span>
                        @endif
                        <span class="text-xs font-medium text-slate-400">Stok: <span class="text-slate-300">{{ $m->stock_before }}</span> <i class="fas fa-long-arrow-alt-right text-[10px] mx-0.5 text-slate-500"></i> <span class="text-white font-bold">{{ $m->stock_after }}</span></span>
                    </div>
                </div>
            </div>

            {{-- Bagian Tengah: Perubahan Besar --}}
            <div class="flex-shrink-0 flex justify-start md:justify-center w-full md:w-32">
                @if($m->type === 'in')
                    <div class="px-4 py-2 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center gap-2 shadow-inner">
                        <div class="w-6 h-6 rounded-md bg-emerald-500/20 flex items-center justify-center"><i class="fas fa-plus text-xs"></i></div>
                        <span class="font-black text-xl tracking-tight">{{ $m->quantity }}</span>
                    </div>
                @elseif($m->type === 'out')
                    <div class="px-4 py-2 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 flex items-center gap-2 shadow-inner">
                        <div class="w-6 h-6 rounded-md bg-red-500/20 flex items-center justify-center"><i class="fas fa-minus text-xs"></i></div>
                        <span class="font-black text-xl tracking-tight">{{ $m->quantity }}</span>
                    </div>
                @else
                    <div class="px-4 py-2 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center gap-2 shadow-inner">
                        <div class="w-6 h-6 rounded-md bg-amber-500/20 flex items-center justify-center"><i class="fas fa-equals text-xs"></i></div>
                        <span class="font-black text-lg tracking-tight">{{ $m->quantity }}</span>
                    </div>
                @endif
            </div>

            {{-- Bagian Kanan: Waktu & Catatan --}}
            <div class="flex-1 flex flex-col md:items-end text-left md:text-right border-t md:border-t-0 md:border-l border-slate-700/50 pt-3 md:pt-0 md:pl-4 mt-1 md:mt-0">
                <p class="text-xs font-bold text-slate-300 mb-1 line-clamp-1"><i class="fas fa-comment-alt text-[10px] text-slate-500 mr-1"></i> {{ $m->notes }}</p>
                <div class="flex items-center md:justify-end gap-2 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                    <span><i class="far fa-clock mr-1"></i> {{ $m->created_at->diffForHumans() }}</span>
                    <span>•</span>
                    <span><i class="far fa-user mr-1"></i> {{ $m->user->name }}</span>
                </div>
            </div>

            {{-- Quick Hover Action Icon --}}
            <div class="absolute right-4 top-1/2 -translate-y-1/2 opacity-0 group-hover:opacity-100 transition-opacity hidden lg:block bg-slate-800 shadow-[-10px_0_10px_#1e293b]">
                <button class="w-10 h-10 rounded-xl bg-slate-700 hover:bg-blue-600 text-slate-300 hover:text-white flex items-center justify-center transition-colors shadow-lg">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
        @empty
        {{-- EMPTY STATE --}}
        <div class="flex flex-col items-center justify-center py-20 px-4 text-center bg-slate-800/30 rounded-3xl border border-slate-700/50 border-dashed mt-4">
            <div class="w-24 h-24 bg-slate-800 rounded-full flex items-center justify-center mb-6 shadow-inner border border-slate-700/80 relative">
                <i class="fas fa-clipboard-list text-4xl text-slate-500"></i>
                <div class="absolute -bottom-1 -right-1 w-8 h-8 bg-slate-700 rounded-full flex items-center justify-center border-2 border-slate-800">
                    <i class="fas fa-search text-xs text-slate-400"></i>
                </div>
            </div>
            <h3 class="text-xl font-black text-white mb-2">Tidak Ada Data Mutasi</h3>
            <p class="text-slate-400 max-w-md mx-auto mb-8 font-medium">Belum ada riwayat stok yang tercatat dengan filter saat ini. Klik tombol di bawah untuk menambah stok baru.</p>
            <button @click="openModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl text-sm font-bold transition-all shadow-lg shadow-blue-500/20 active:scale-95 flex items-center gap-2">
                <i class="fas fa-plus"></i> Penyesuaian Stok
            </button>
        </div>
        @endforelse
    </div>

    @if($mutations instanceof \Illuminate\Pagination\LengthAwarePaginator && $mutations->hasPages())
    <div class="mt-4 bg-slate-800/50 p-4 rounded-2xl border border-slate-700/50">
        {{ $mutations->appends(request()->query())->links('pagination::tailwind') }}
    </div>
    @endif

    {{-- MODAL PENYESUAIAN STOK --}}
    <div x-show="isModalOpen" x-transition.opacity x-cloak class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div @click.away="closeModal()" x-show="isModalOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0" class="bg-[#1e293b] rounded-3xl w-full max-w-md shadow-2xl border border-slate-700 transform overflow-hidden flex flex-col">
            
            <div class="p-6 border-b border-slate-700/80 flex justify-between items-center bg-slate-800/50 relative overflow-hidden">
                <div class="absolute inset-0 opacity-20 pointer-events-none" style="background: radial-gradient(circle at top right, #3b82f6, transparent 70%);"></div>
                <h3 class="text-xl font-black text-white relative z-10">Penyesuaian Stok Baru</h3>
                <button @click="closeModal()" class="w-8 h-8 bg-slate-700 hover:bg-slate-600 rounded-full text-slate-400 hover:text-white transition-colors flex items-center justify-center relative z-10"><i class="fas fa-times"></i></button>
            </div>

            <form action="{{ route('stock.adjust') }}" method="POST" class="p-6">
                @csrf
                <div class="space-y-6">
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-2 block flex items-center gap-1.5"><i class="fas fa-box"></i> Pilih Produk <span class="text-red-400">*</span></label>
                        <select name="product_id" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-sm font-medium text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors shadow-inner appearance-none" required>
                            <option value="">-- Pilih Produk --</option>
                            @foreach($products as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} (Stok: {{ $p->stock }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-2 block flex items-center gap-1.5"><i class="fas fa-exchange-alt"></i> Jenis <span class="text-red-400">*</span></label>
                            <select name="type" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-sm font-bold text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors shadow-inner appearance-none" required>
                                <option value="in" class="text-emerald-400">Masuk (+)</option>
                                <option value="out" class="text-red-400">Keluar (-)</option>
                                <option value="adjustment" class="text-amber-400">Ubah (Set)</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-2 block flex items-center gap-1.5"><i class="fas fa-hashtag"></i> Jumlah <span class="text-red-400">*</span></label>
                            <input type="number" name="quantity" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-sm font-black text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors shadow-inner" required min="1" placeholder="0">
                        </div>
                    </div>

                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-2 block flex items-center gap-1.5"><i class="fas fa-pen"></i> Catatan Mutasi <span class="text-red-400">*</span></label>
                        <textarea name="notes" rows="2" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-4 text-sm font-medium text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors resize-none shadow-inner" placeholder="Contoh: Barang retur, stok awal..." required></textarea>
                    </div>

                    <div class="pt-6 flex gap-3 mt-2 border-t border-slate-700/50">
                        <button type="button" @click="closeModal()" class="flex-1 py-3.5 bg-slate-700 hover:bg-slate-600 text-white font-bold rounded-xl transition-colors text-sm shadow-sm active:scale-95">Batal</button>
                        <button type="submit" class="flex-1 py-3.5 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl transition-colors shadow-lg shadow-blue-500/30 text-sm flex items-center justify-center gap-2 active:scale-95">
                            <i class="fas fa-check-circle"></i> Proses Stok
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function stockApp() {
    return {
        isModalOpen: false,
        openModal() {
            this.isModalOpen = true;
        },
        closeModal() {
            this.isModalOpen = false;
        }
    }
}
</script>
@endsection
