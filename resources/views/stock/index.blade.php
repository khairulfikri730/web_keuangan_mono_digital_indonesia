@extends('layouts.app')

@section('title', 'Manajemen Stok')
@section('page-title', 'Gudang & Stok')
@section('page-subtitle', 'Kelola stok dan mutasi produk')

@section('content')
@php
    $totalProducts = count($products);
    $stockableProducts = collect($products)->filter(fn($p) => !$p->is_stockless);
    $lowStock = $stockableProducts->where('stock', '<=', 5)->where('stock', '>', 0)->count();
    $outOfStock = $stockableProducts->where('stock', '<=', 0)->count();
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
                    <input type="text" name="search" value="{{ is_array(request('search')) ? '' : request('search') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl pl-11 pr-4 py-2.5 text-slate-200 placeholder-slate-500 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-sm font-medium shadow-inner" placeholder="Cari catatan mutasi...">
                    <input type="hidden" name="type" value="{{ is_array(request('type')) ? '' : request('type') }}">
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

        {{-- Card 2 (Stok Menipis) --}}
        <a href="{{ route('products.index', ['stock_status' => 'low']) }}" class="block bg-slate-800 hover:bg-slate-700/80 hover:border-amber-500/50 rounded-2xl p-4 border border-slate-700/50 shadow-sm relative overflow-hidden transition-all group cursor-pointer">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-amber-500/10 flex items-center justify-center border border-amber-500/20 shrink-0 group-hover:bg-amber-500/20 transition-colors">
                    <i class="fas fa-exclamation-triangle text-amber-400 text-xl"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-0.5 group-hover:text-amber-300 transition-colors">Stok Menipis <i class="fas fa-chevron-right text-[10px] ml-1 opacity-0 group-hover:opacity-100 transition-opacity"></i></p>
                    <h3 class="text-2xl font-black text-white">{{ $lowStock }}</h3>
                </div>
            </div>
            @if($lowStock > 0)
                <div class="absolute top-0 right-0 w-2 h-full bg-amber-500"></div>
            @endif
        </a>

        {{-- Card 3 (Stok Habis) --}}
        <a href="{{ route('products.index', ['stock_status' => 'empty']) }}" class="block bg-slate-800 hover:bg-slate-700/80 hover:border-red-500/50 rounded-2xl p-4 border border-slate-700/50 shadow-sm relative overflow-hidden transition-all group cursor-pointer">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-red-500/10 flex items-center justify-center border border-red-500/20 shrink-0 group-hover:bg-red-500/20 transition-colors">
                    <i class="fas fa-times-circle text-red-400 text-xl"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-0.5 group-hover:text-red-300 transition-colors">Stok Habis <i class="fas fa-chevron-right text-[10px] ml-1 opacity-0 group-hover:opacity-100 transition-opacity"></i></p>
                    <h3 class="text-2xl font-black text-white">{{ $outOfStock }}</h3>
                </div>
            </div>
            @if($outOfStock > 0)
                <div class="absolute top-0 right-0 w-2 h-full bg-red-500 shadow-[0_0_10px_#ef4444]"></div>
            @endif
        </a>

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
    <div class="space-y-3">
        {{-- Mutation Type Pills --}}
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('stock.index', request()->except(['type','page'])) }}" class="px-5 py-2 rounded-full text-sm font-bold whitespace-nowrap transition-all border {{ !request('type') ? 'bg-blue-600 text-white border-blue-500 shadow-lg shadow-blue-500/30' : 'bg-slate-800 border-slate-700 text-slate-400 hover:bg-slate-700 hover:text-white' }}">
                <i class="fas fa-th-large mr-1"></i> Semua Data
            </a>
            <a href="{{ route('stock.index', array_merge(request()->except(['page']), ['type' => 'in'])) }}" class="px-5 py-2 rounded-full text-sm font-bold whitespace-nowrap transition-all border {{ request('type') == 'in' ? 'bg-emerald-600 text-white border-emerald-500 shadow-lg shadow-emerald-500/30' : 'bg-slate-800 border-slate-700 text-slate-400 hover:bg-slate-700 hover:text-white' }}">
                <i class="fas fa-arrow-down mr-1"></i> Masuk
            </a>
            <a href="{{ route('stock.index', array_merge(request()->except(['page']), ['type' => 'out'])) }}" class="px-5 py-2 rounded-full text-sm font-bold whitespace-nowrap transition-all border {{ request('type') == 'out' ? 'bg-red-600 text-white border-red-500 shadow-lg shadow-red-500/30' : 'bg-slate-800 border-slate-700 text-slate-400 hover:bg-slate-700 hover:text-white' }}">
                <i class="fas fa-arrow-up mr-1"></i> Keluar
            </a>
            <a href="{{ route('stock.index', array_merge(request()->except(['page']), ['type' => 'adjustment'])) }}" class="px-5 py-2 rounded-full text-sm font-bold whitespace-nowrap transition-all border {{ request('type') == 'adjustment' ? 'bg-amber-600 text-white border-amber-500 shadow-lg shadow-amber-500/30' : 'bg-slate-800 border-slate-700 text-slate-400 hover:bg-slate-700 hover:text-white' }}">
                <i class="fas fa-sliders-h mr-1"></i> Penyesuaian
            </a>
        </div>

        {{-- Category Pills --}}
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-slate-500 text-sm font-bold mr-1"><i class="fas fa-tags mr-1"></i> Kategori:</span>
            <a href="{{ route('stock.index', request()->except(['category_id','page'])) }}" 
               class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ !request('category_id') ? 'bg-slate-600 text-white' : 'bg-slate-800/40 border border-slate-700/50 text-slate-400 hover:bg-slate-700 hover:text-white' }}">Semua</a>
            @foreach($categories as $cat)
            <a href="{{ route('stock.index', array_merge(request()->except(['page']), ['category_id' => $cat->id])) }}" 
               class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ request('category_id') == $cat->id ? 'bg-slate-600 text-white' : 'bg-slate-800/40 border border-slate-700/50 text-slate-400 hover:bg-slate-700 hover:text-white' }}">
                @if($cat->color)<span class="inline-block w-2 h-2 rounded-full mr-1" style="background:{{ $cat->color }}"></span>@endif{{ $cat->name }}
            </a>
            @endforeach
        </div>

        {{-- Stock Type Filter Pills --}}
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-slate-500 text-sm font-bold mr-1"><i class="fas fa-cubes mr-1"></i> Tipe Stok:</span>
            @php $curStockType = request('stock_type'); @endphp
            <a href="{{ route('stock.index', array_merge(request()->except(['stock_type','page']), ['stock_type' => 'habis_pakai'])) }}" 
               class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all inline-flex items-center gap-1.5 {{ $curStockType === 'habis_pakai' || !$curStockType ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-slate-800/40 border border-slate-700/50 text-slate-400 hover:bg-slate-700 hover:text-white' }}">
                <i class="fas fa-box text-[10px]"></i> Habis Pakai (Stok Terbatas)
            </a>
            <a href="{{ route('stock.index', array_merge(request()->except(['stock_type','page']), ['stock_type' => 'unlimited'])) }}" 
               class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all inline-flex items-center gap-1.5 {{ $curStockType === 'unlimited' ? 'bg-blue-500/20 text-blue-400 border border-blue-500/30' : 'bg-slate-800/40 border border-slate-700/50 text-slate-400 hover:bg-slate-700 hover:text-white' }}">
                <i class="fas fa-infinity text-[10px]"></i> Unlimited (Tidak Terbatas)
            </a>
        </div>
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
                            <span class="text-slate-600">â€¢</span>
                        @endif
                        @if($m->product && $m->product->is_stockless)
                            <span class="text-xs font-medium text-blue-400"><i class="fas fa-infinity text-[10px] mr-1"></i> Unlimited</span>
                        @else
                            <span class="text-xs font-medium text-slate-400">Stok: <span class="text-slate-300">{{ $m->stock_before }}</span> <i class="fas fa-long-arrow-alt-right text-[10px] mx-0.5 text-slate-500"></i> <span class="text-white font-bold">{{ $m->stock_after }}</span></span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Bagian Tengah: Perubahan Besar --}}
            <div class="flex-shrink-0 flex justify-start md:justify-center w-full md:w-32">
                @if($m->product && $m->product->is_stockless)
                    {{-- Unlimited product: show quantity sold/used, no stock impact --}}
                    <div class="px-4 py-2 rounded-xl bg-blue-500/10 border border-blue-500/20 text-blue-400 flex items-center gap-2 shadow-inner">
                        <div class="w-6 h-6 rounded-md bg-blue-500/20 flex items-center justify-center"><i class="fas fa-infinity text-xs"></i></div>
                        <span class="font-black text-xl tracking-tight">{{ $m->quantity }}</span>
                    </div>
                @elseif($m->type === 'in')
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
                    <span>â€¢</span>
                    <span><i class="far fa-user mr-1"></i> {{ $m->user->name }}</span>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center gap-1.5 bg-slate-900/50 p-1 rounded-xl border border-slate-700/50 shrink-0">
                {{-- Restock --}}
                @if($m->product && !$m->product->isStockless())
                <a href="{{ route('stock.index', ['action' => 'restock', 'product_id' => $m->product_id]) }}" class="w-9 h-9 rounded-lg bg-slate-800 hover:bg-emerald-500/20 text-emerald-400 hover:text-emerald-300 inline-flex items-center justify-center transition-all text-sm" title="Restock / Sesuaikan Stok"><i class="fas fa-plus"></i></a>
                @endif
                {{-- Lihat Produk --}}
                @if($m->product)
                <a href="{{ route('products.edit', $m->product) }}" class="w-9 h-9 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 inline-flex items-center justify-center transition-all text-sm" title="Lihat Produk"><i class="fas fa-eye"></i></a>
                {{-- Filter by Product --}}
                <a href="{{ route('stock.index', ['product_id' => $m->product_id]) }}" class="w-9 h-9 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 inline-flex items-center justify-center transition-all text-sm" title="Riwayat Produk Ini"><i class="fas fa-filter"></i></a>
                @endif
                {{-- Hapus --}}
                @if(auth()->user()->isOwner())
                <form action="{{ route('stock.destroy', ['mutation' => $m->id]) }}" method="POST" onsubmit="return confirm('Hapus mutasi ini? Stok akan dikembalikan.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-9 h-9 rounded-lg bg-slate-800 hover:bg-red-500/20 text-slate-400 hover:text-red-400 inline-flex items-center justify-center transition-all text-sm" title="Hapus"><i class="fas fa-trash"></i></button>
                </form>
                @endif
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
        <div @click.away="closeModal()" x-show="isModalOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0" class="bg-[#1e293b] rounded-3xl w-full max-w-md shadow-2xl border border-slate-700 transform overflow-hidden flex flex-col max-h-[90vh] overflow-y-auto scrollbar-hide ">
            
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
                                @if(!$p->is_stockless)
                                <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>{{ $p->name }} (Stok: {{ $p->stock }})</option>
                                @endif
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
        isModalOpen: {{ request('action') == 'restock' ? 'true' : 'false' }},
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


