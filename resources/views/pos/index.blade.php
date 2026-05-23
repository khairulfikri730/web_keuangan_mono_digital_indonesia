@extends('layouts.app')

@section('title', 'POS Kasir')
@section('page-title', 'POS Kasir')

@push('styles')
<style>
    header { display: none; }
    .pos-height { height: 100vh; }
    /* Scrollbar minimal */
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endpush

@section('content')


<div x-data="posApp()" class="pos-height flex flex-col lg:flex-row bg-slate-100 -mx-6 -mt-4 text-slate-800 font-sans">
    
    {{-- MAIN CONTENT (TENGAH) --}}
    <div class="flex-1 flex flex-col h-full bg-slate-100 border-r border-slate-200 p-4 lg:p-6">
        
        {{-- HEADER BAR --}}
        <div class="flex items-center gap-4 mb-4 bg-white p-3 rounded-2xl shadow-sm border border-slate-200 shrink-0">
            <h2 class="text-xl font-black text-slate-800 hidden md:block px-2">POS Kasir</h2>
            
            <div class="relative flex-1">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" x-model="searchQuery" @input.debounce.300ms="fetchProducts()" x-ref="searchInput" class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-11 pr-4 py-2.5 text-slate-700 placeholder-slate-400 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 shadow-inner text-sm transition-all" placeholder="Cari produk atau scan barcode..." autofocus>
            </div>
            
            <div class="flex items-center gap-2 shrink-0">
                <div class="flex items-center bg-slate-100 p-1 rounded-xl">
                    <button @click="viewMode='grid'" :class="viewMode==='grid' ? 'bg-white shadow text-emerald-600' : 'text-slate-500 hover:text-slate-700'" class="p-2 rounded-lg transition-all"><i class="fas fa-th-large"></i></button>
                    <button @click="viewMode='list'" :class="viewMode==='list' ? 'bg-white shadow text-emerald-600' : 'text-slate-500 hover:text-slate-700'" class="p-2 rounded-lg transition-all"><i class="fas fa-list"></i></button>
                </div>
                
                <button @click="openGroupManager()" class="bg-emerald-50 text-emerald-600 shadow-sm border border-emerald-200 hover:bg-emerald-100 px-3 py-2 rounded-xl transition-all text-sm font-bold flex items-center gap-2" title="Groupkan Item">
                    <i class="fas fa-layer-group"></i> <span class="hidden md:inline">Groupkan Item</span>
                </button>

                <button @click="showPrinterSettings = true" :class="printerStatus === 'connected' ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 'bg-slate-50 text-slate-400 border-slate-200'" class="px-3 py-2 rounded-xl border transition-all flex items-center gap-2 text-sm font-bold shadow-sm" title="Pengaturan Printer">
                    <i class="fas fa-print"></i>
                    <div :class="printerStatus === 'connected' ? 'bg-emerald-500' : 'bg-slate-300'" class="w-2 h-2 rounded-full animate-pulse shadow-sm"></div>
                </button>

                <div class="hidden lg:flex items-center gap-2 px-4 py-2 bg-slate-800 text-white rounded-xl text-sm font-bold shadow-sm" id="pos-clock-display">
                    <i class="far fa-clock text-blue-400"></i> <span></span>
                </div>
                
                <a href="{{ route('shifts.index', ['open' => 1]) }}" x-show="!activeShift && products.length > 0" class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-sm font-bold transition-all shadow-lg shadow-emerald-500/30 flex items-center gap-2">
                    <i class="fas fa-play"></i> <span class="hidden sm:inline">Buka Shift</span>
                </a>
                {{-- Jika ada shift aktif: tampilkan 2 tombol Cash Out & Tutup Shift --}}
                @if($activeShift)
                <button @click="openCashOut()" class="px-3 py-2 bg-orange-50 text-orange-600 border border-orange-200 hover:bg-orange-100 rounded-xl text-sm font-bold transition-all flex items-center gap-2">
                    <i class="fas fa-cash-register"></i> <span class="hidden sm:inline">Cash Out</span>
                </button>
                <button onclick="openTutupShift()" class="px-3 py-2 bg-red-50 text-red-600 border border-red-200 hover:bg-red-100 rounded-xl text-sm font-bold transition-all flex items-center gap-2">
                    <i class="fas fa-door-closed"></i> <span class="hidden sm:inline">Tutup Shift</span>
                </button>
                @endif
            </div>
        </div>

        {{-- SHIFT BANNER & BEP INFO --}}
        <div class="mb-4 shrink-0">
            {{-- Shift Status --}}
            <div :class="activeShift ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-amber-100 border-amber-300 text-amber-800'" class="border px-4 py-3 rounded-2xl flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <div :class="activeShift ? 'bg-emerald-100 text-emerald-600' : 'bg-amber-200 text-amber-600'" class="w-10 h-10 rounded-full flex items-center justify-center shadow-inner">
                        <i :class="activeShift ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle'" class="text-xl"></i>
                    </div>
                    <div>
                        <p class="font-black text-sm" x-text="activeShift ? 'Shift Telah Dibuka' : 'Shift Belum Dibuka'"></p>
                        <p class="text-[10px] font-medium opacity-80" x-text="activeShift ? 'Anda sedang dalam sesi penjualan aktif.' : 'Buka shift untuk mulai mencatat transaksi.'"></p>
                    </div>
                </div>
                <template x-if="!activeShift && products.length > 0">
                    <a href="{{ route('shifts.index', ['open' => 1]) }}" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-bold transition-colors shadow-lg shadow-amber-500/30">
                        Buka Shift
                    </a>
                </template>
                @if($activeShift)
                <div class="flex items-center gap-2">
                    <button @click="openCashOut()" class="px-3 py-1.5 bg-orange-100 text-orange-600 border border-orange-200 hover:bg-orange-200 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5">
                        <i class="fas fa-cash-register text-[10px]"></i> Cash Out
                    </button>
                    <button onclick="openTutupShift()" class="px-3 py-1.5 bg-red-100 text-red-600 border border-red-200 hover:bg-red-200 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5">
                        <i class="fas fa-door-closed text-[10px]"></i> Tutup Shift
                    </button>
                </div>
                @endif
            </div>
        </div>



        {{-- FILTER KATEGORI (CHIPS) --}}
        <div class="flex gap-2 overflow-x-auto pb-2 mb-2 scrollbar-hide shrink-0" id="category-buttons">
            <button @click="setCategory('')" data-category="semua" :class="activeCategory==='' ? 'bg-slate-800 text-white shadow-lg border-slate-800 active' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50'" class="category-btn px-6 py-2 border rounded-full text-sm font-bold whitespace-nowrap transition-all">
                Semua
            </button>
            
            {{-- SPECIAL FILTERS (PROMO & BEST SELLER) --}}
            <button @click="setCategory('PROMO')" :class="activeCategory==='PROMO' ? 'bg-rose-500 text-white shadow-lg border-rose-500 active' : 'bg-white border-rose-200 text-rose-500 hover:bg-rose-50'" class="category-btn px-6 py-2 border rounded-full text-sm font-bold whitespace-nowrap transition-all flex items-center gap-2">
                <i class="fas fa-fire"></i> Promo
            </button>
            <button @click="setCategory('BEST SELLER')" :class="activeCategory==='BEST SELLER' ? 'bg-amber-500 text-white shadow-lg border-amber-500 active' : 'bg-white border-amber-200 text-amber-500 hover:bg-amber-50'" class="category-btn px-6 py-2 border rounded-full text-sm font-bold whitespace-nowrap transition-all flex items-center gap-2">
                <i class="fas fa-star"></i> Terlaris
            </button>

            <template x-for="cat in categories" :key="cat.id">
                <button @click="setCategory(cat.id)" 
                        :data-category="cat.id"
                        :style="activeCategory===cat.id ? `background-color: ${cat.color || '#10b981'}; border-color: ${cat.color || '#10b981'}; color: ${getContrastYIQ(cat.color || '#10b981')}; box-shadow: 0 4px 15px -3px ${cat.color || '#10b981'}60;` : `border-color: ${cat.color || '#e2e8f0'}; color: ${cat.color || '#64748b'};`"
                        :class="activeCategory===cat.id ? 'shadow-lg border active' : 'bg-white border hover:bg-slate-50'" 
                        class="category-btn px-6 py-2 rounded-full text-sm font-bold whitespace-nowrap transition-all flex items-center gap-2">
                    <i :class="getPlaceholderIcon(cat.name)" class="text-xs" :style="activeCategory!==cat.id ? `color: ${cat.color || '#64748b'};` : ''"></i>
                    <span x-text="cat.name"></span>
                </button>
            </template>
        </div>

        {{-- GRID PRODUK --}}
        <div id="product-grid-container" class="flex-1 overflow-y-auto pr-2 pb-6 scrollbar-hide scroll-smooth relative">
            <div x-show="filteredProductsCount === 0" class="absolute inset-0 flex flex-col items-center justify-center h-full text-slate-400 z-10">
                <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mb-4 border-2 border-dashed border-slate-200 shadow-inner">
                    <i class="fas fa-box-open text-3xl opacity-50"></i>
                </div>
                <p class="text-sm font-black text-slate-500 uppercase tracking-widest mb-2">Produk Belum Tersedia</p>
                <p class="text-xs text-slate-400 mb-6 max-w-[200px] text-center font-medium">Isi katalog produk terlebih dahulu untuk dapat membuka shift dan mulai berjualan.</p>
                <a href="{{ route('products.index') }}" class="px-6 py-3 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-xs font-black uppercase tracking-widest transition-all shadow-lg shadow-emerald-500/30 flex items-center gap-2 pointer-events-auto">
                    <i class="fas fa-plus"></i> Isi Katalog Produk
                </a>
            </div>
            
            {{-- VIRTUAL SECTION: PROMO (Only show in 'All' or 'PROMO' mode) --}}
            <div class="space-y-6 w-full mb-6" x-show="activeCategory === '' || activeCategory === 'PROMO'">
                <div class="w-full" x-show="promoProducts.length > 0">
                    <div class="sticky top-0 z-20 bg-slate-100 py-3 mb-4 border-b border-rose-200 flex items-center gap-3">
                        <span class="w-3 h-3 rounded-full shadow-inner bg-rose-500 animate-pulse"></span>
                        <h3 class="font-black text-rose-600 text-sm uppercase tracking-widest">PROMO</h3>
                        <span class="text-[10px] font-bold text-rose-500 bg-rose-50 px-2 py-0.5 rounded-md border border-rose-100" x-text="promoProducts.length + ' Item'"></span>
                    </div>
                    <div :class="viewMode === 'grid' ? 'grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-4 content-start' : 'flex flex-col gap-3'" class="relative z-0">
                        <template x-for="p in promoProducts" :key="'promo-view-'+p.id">
                            @include('pos._product_card')
                        </template>
                    </div>
                </div>
            </div>

            {{-- VIRTUAL SECTION: BEST SELLER (Only show in 'All' or 'BEST SELLER' mode) --}}
            <div class="space-y-6 w-full mb-6" x-show="activeCategory === '' || activeCategory === 'BEST SELLER'">
                <div class="w-full" x-show="bestSellerProducts.length > 0">
                    <div class="sticky top-0 z-20 bg-slate-100 py-3 mb-4 border-b border-amber-200 flex items-center gap-3">
                        <span class="w-3 h-3 rounded-full shadow-inner bg-amber-500"></span>
                        <h3 class="font-black text-amber-600 text-sm uppercase tracking-widest">BEST SELLER</h3>
                        <span class="text-[10px] font-bold text-amber-500 bg-amber-50 px-2 py-0.5 rounded-md border border-amber-100" x-text="bestSellerProducts.length + ' Item'"></span>
                    </div>
                    <div :class="viewMode === 'grid' ? 'grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-4 content-start' : 'flex flex-col gap-3'" class="relative z-0">
                        <template x-for="p in bestSellerProducts" :key="'best-view-'+p.id">
                            @include('pos._product_card')
                        </template>
                    </div>
                </div>
            </div>
            
            {{-- CUSTOM POS GROUPS (Only show in 'All' or Category mode) --}}
            <div class="space-y-6 w-full mb-6" x-show="!['PROMO', 'BEST SELLER'].includes(activeCategory)">
                <template x-for="group in posGroups" :key="'pos-group-'+group.id">
                    <div class="w-full" x-show="group.products.filter(p => filterProduct(p)).length > 0">
                        {{-- Group Header --}}
                        <div class="sticky top-0 z-20 bg-slate-100 py-3 mb-4 border-b border-slate-200 flex items-center gap-3">
                            <span class="w-3 h-3 rounded-full shadow-inner" :style="`background-color: ${group.color || '#f97316'}`"></span>
                            <h3 class="font-black text-slate-700 text-sm uppercase tracking-widest" x-text="group.name"></h3>
                            <span class="text-[10px] font-bold text-slate-500 bg-slate-200 px-2 py-0.5 rounded-md" x-text="group.products.filter(p => filterProduct(p)).length + ' Item'"></span>
                        </div>
                        
                        {{-- Inner Grid --}}
                        <div :class="viewMode === 'grid' ? 'grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-4 content-start' : 'flex flex-col gap-3'" class="relative z-0">
                            <template x-for="p in group.products.filter(x => filterProduct(x))" :key="'group-item-'+p.id">
                                @include('pos._product_card')
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            {{-- SEMUA PRODUK (REMAINING / ALL) --}}
            <div class="w-full" x-show="!['PROMO', 'BEST SELLER'].includes(activeCategory) && products.filter(p => filterProduct(p)).length > 0">
                <div class="sticky top-0 z-20 bg-slate-100 py-3 mb-4 border-b border-slate-200 flex items-center gap-3">
                    <span class="w-3 h-3 rounded-full shadow-inner bg-slate-300"></span>
                    <h3 class="font-black text-slate-700 text-sm uppercase tracking-widest">Semua Produk</h3>
                    <span class="text-[10px] font-bold text-slate-500 bg-slate-200 px-2 py-0.5 rounded-md" x-text="products.filter(p => filterProduct(p)).length + ' Item'"></span>
                </div>

                <div :class="viewMode === 'grid' ? 'grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-4 content-start' : 'flex flex-col gap-3'" class="relative z-0">
                    <template x-for="p in products.filter(p => filterProduct(p))" :key="p.id">
                        @include('pos._product_card')
                    </template>
                </div>
            </div>
        </div>
    </div>

    {{-- RIGHT PANEL: ORDER PANEL (CLEAN & TIDY) --}}
    {{-- Mobile Cart Overlay Backdrop --}}
    <div x-show="mobileCartOpen" x-transition.opacity @click="mobileCartOpen = false" x-cloak class="fixed inset-0 bg-slate-900/60 z-[140] lg:hidden"></div>

    <div class="fixed inset-y-0 right-0 z-[150] w-[360px] max-w-[90vw] lg:relative lg:w-[420px] flex flex-col bg-white shadow-2xl lg:shadow-[-10px_0_30px_rgba(0,0,0,0.02)] h-full border-l border-slate-100 shrink-0 overflow-hidden transition-transform duration-300 lg:translate-x-0" :class="mobileCartOpen ? 'translate-x-0' : 'translate-x-full'">
        
        {{-- PANEL HEADER --}}
        <div class="p-6 border-b border-slate-100 bg-white shrink-0">
            <div class="flex justify-between items-start mb-1">
                <h3 class="text-lg font-black text-slate-800 tracking-tight">Pesanan Saat Ini</h3>
                <div class="flex gap-2">
                    <button @click="showPrinterSettings = true" class="w-8 h-8 rounded-lg bg-slate-50 text-slate-400 hover:text-slate-800 transition-colors flex items-center justify-center border border-slate-100"><i class="fas fa-print text-xs"></i></button>
                    <button class="w-8 h-8 rounded-lg bg-slate-50 text-slate-400 hover:text-slate-800 transition-colors flex items-center justify-center border border-slate-100"><i class="fas fa-cog text-xs"></i></button>
                    <button @click="mobileCartOpen = false" class="lg:hidden w-8 h-8 rounded-lg bg-red-50 text-red-500 hover:text-red-700 transition-colors flex items-center justify-center border border-red-100"><i class="fas fa-times text-xs"></i></button>
                </div>
            </div>
            <div class="flex items-center gap-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                <i class="far fa-calendar-alt"></i>
                <span id="pos-date-display">Senin, 4 Mei 2026</span>
                <span class="text-slate-300 mx-1">|</span>
                <i class="far fa-clock"></i>
                <span id="pos-time-display">10:06:02</span>
            </div>
            
            <div class="flex gap-2 mt-5">
                <button @click="cartView = 'history'" 
                        :class="cartView === 'history' ? 'bg-amber-100 text-amber-700 border-amber-200' : 'bg-slate-50 text-slate-500 border-slate-100'"
                        class="flex-1 py-2 rounded-xl text-xs font-black uppercase tracking-wider border transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-history"></i> Riwayat
                </button>
                <button @click="cartView = 'active'"
                        :class="cartView === 'active' ? 'bg-indigo-100 text-indigo-700 border-indigo-200' : 'bg-slate-50 text-slate-500 border-slate-100'"
                        class="flex-1 py-2 rounded-xl text-xs font-black uppercase tracking-wider border transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-list-ul"></i> Pesanan Terbuka
                </button>
            </div>
        </div>

        {{-- CART AREA --}}
        <div class="flex-1 overflow-y-auto p-6 scrollbar-hide space-y-4">
            
            {{-- TAB: PESANAN SAAT INI --}}
            <div x-show="cartView === 'active'" class="flex flex-col">
                <div x-show="activeWorksheet.cart.length === 0" class="flex flex-col items-center justify-start py-6 text-slate-400 opacity-60">
                    <div class="w-12 h-12 bg-slate-50 rounded-full flex items-center justify-center mb-2"><i class="fas fa-shopping-cart text-xl text-slate-200"></i></div>
                    <p class="text-xs font-bold">Keranjang masih kosong</p>
                </div>

                <div class="space-y-4">
                    <template x-for="(item, index) in activeWorksheet.cart" :key="item.product_id + '-' + index">
                        <div class="flex gap-4 group">
                            <div class="flex-1">
                                <div class="flex items-center gap-1">
                                    <h4 class="text-sm font-black text-slate-800" x-text="item.name"></h4>
                                    <template x-if="item.is_custom_price">
                                        <span class="bg-orange-100 text-orange-600 border border-orange-200 text-[8px] font-black px-1.5 py-0.5 rounded uppercase ml-1" title="Harga Khusus">Khusus</span>
                                    </template>
                                </div>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-[10px] font-bold text-slate-400" x-text="formatRp(item.is_custom_price ? item.custom_price : item.price)"></span>
                                    <span class="text-[10px] text-slate-300">Ã—</span>
                                    <div class="flex items-center gap-1 bg-slate-50 border border-slate-100 rounded-md px-1.5 py-0.5">
                                        <button @click="changeQty(index, -1)" class="text-[10px] text-slate-400 hover:text-red-500"><i class="fas fa-minus"></i></button>
                                        <span class="text-xs font-black text-slate-800 w-5 text-center" x-text="item.quantity"></span>
                                        <button @click="changeQty(index, 1)" class="text-[10px] text-slate-400 hover:text-emerald-500"><i class="fas fa-plus"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-black text-slate-800" x-text="formatRp(((item.is_custom_price ? item.custom_price : item.price) * item.quantity) - item.discount)"></p>
                                <button @click="removeItem(index)" class="text-[10px] font-bold text-red-400 hover:text-red-600 uppercase tracking-tighter mt-1">Hapus</button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- TAB: RIWAYAT --}}
            <div x-show="cartView === 'history'" class="flex flex-col items-center justify-start py-6 text-slate-400 opacity-60">
                <i class="fas fa-history text-2xl mb-2"></i>
                <p class="text-xs font-bold">Riwayat Kosong</p>
            </div>
        </div>

        {{-- ORDER INFO & ACTION AREA --}}
        <div class="p-6 bg-slate-50/50 border-t border-slate-100 shrink-0 space-y-5">
            
            {{-- DATA PELANGGAN --}}
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Nama Pelanggan</label>
                    <button class="text-[10px] font-black text-emerald-600 uppercase flex items-center gap-1"><i class="fas fa-plus-circle"></i> Baru</button>
                </div>
                <div class="relative group">
                    <i class="far fa-user absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-emerald-500 transition-colors"></i>
                    <input type="text" x-model="activeWorksheet.customerName" class="w-full bg-white border border-slate-200 rounded-xl pl-11 pr-4 py-2.5 text-sm font-bold text-slate-700 focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/5 transition-all" placeholder="Ketik nama atau no HP...">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="relative group">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 block">No. HP</label>
                        <i class="fas fa-phone-alt absolute left-4 top-[38px] text-[10px] text-slate-300"></i>
                        <input type="text" x-model="activeWorksheet.customerPhone" class="w-full bg-white border border-slate-200 rounded-xl pl-10 pr-4 py-2 text-sm font-bold text-slate-700 focus:outline-none focus:border-emerald-500 transition-all" placeholder="08xxx...">
                    </div>
                    <div class="relative group">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 block">No. Meja</label>
                        <input type="text" x-model="activeWorksheet.tableNumber" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2 text-sm font-bold text-slate-700 focus:outline-none focus:border-emerald-500 transition-all" placeholder="Contoh: 12">
                    </div>
                </div>
            </div>

            {{-- DISKON & CATATAN --}}
            <div class="space-y-4">
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Diskon</label>
                    <div class="flex items-center gap-2 bg-white border border-slate-200 rounded-xl p-1 shadow-sm">
                        <div class="flex-1 relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-400" x-text="activeWorksheet.discountType === 'nominal' ? 'Rp' : '%'"></span>
                            <input type="number" x-model.number="activeWorksheet.globalDiscount" class="w-full pl-8 pr-2 py-1.5 text-sm font-black text-slate-800 focus:outline-none bg-transparent">
                        </div>
                        <div class="flex bg-slate-100 rounded-lg p-0.5">
                            <button @click="activeWorksheet.discountType = 'nominal'" :class="activeWorksheet.discountType === 'nominal' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-400'" class="px-3 py-1 rounded-md text-[10px] font-black transition-all">Rp</button>
                            <button @click="activeWorksheet.discountType = 'percentage'" :class="activeWorksheet.discountType === 'percentage' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-400'" class="px-3 py-1 rounded-md text-[10px] font-black transition-all">%</button>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Catatan Pesanan</label>
                    <textarea x-model="activeWorksheet.notes" rows="2" class="w-full bg-white border border-slate-200 rounded-xl p-3 text-xs font-medium text-slate-700 focus:outline-none focus:border-emerald-500 transition-all resize-none" placeholder="Tambahkan catatan untuk pesanan ini..."></textarea>
                </div>
            </div>

            {{-- DELIVERY MODE --}}
            <div class="bg-slate-100/50 border border-slate-200/50 rounded-xl p-3 space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3 text-slate-600">
                        <i class="fas fa-shipping-fast text-xs"></i>
                        <span class="text-[10px] font-black uppercase tracking-wider">Mode Delivery</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" x-model="activeWorksheet.deliveryMode" @change="if(!activeWorksheet.deliveryMode) activeWorksheet.deliveryFee = 0" class="sr-only peer">
                        <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500 shadow-inner"></div>
                    </label>
                </div>
                
                {{-- Ongkos Kirim Input (Shows when Delivery Mode is ON) --}}
                <div x-show="activeWorksheet.deliveryMode" x-collapse>
                    <div class="pt-3 border-t border-slate-200/50">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Ongkos Kirim (Rp)</label>
                        <div class="relative mb-3">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-xs">Rp</span>
                            <input type="number" x-model.number="activeWorksheet.deliveryFee" min="0" class="w-full bg-white border border-slate-200 rounded-xl pl-9 pr-3 py-2 text-sm font-bold text-slate-700 focus:outline-none focus:border-emerald-500 transition-all appearance-none" placeholder="0">
                        </div>
                        
                        {{-- Presets --}}
                        <template x-if="deliveryPresets && deliveryPresets.length > 0">
                            <div class="flex flex-wrap gap-2">
                                <template x-for="preset in deliveryPresets" :key="preset.name">
                                    <button @click="activeWorksheet.deliveryFee = preset.price" 
                                            class="px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-[10px] font-bold text-slate-600 hover:border-emerald-500 hover:text-emerald-600 transition-all shadow-sm">
                                        <span x-text="preset.name"></span>
                                        <span class="opacity-50 ml-1" x-text="'(' + formatRp(preset.price) + ')'"></span>
                                    </button>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- PAYMENT SUMMARY --}}
            <div class="pt-4 border-t border-slate-200 space-y-1">
                <div class="flex justify-between items-center">
                    <span class="text-xs font-bold text-slate-400">Subtotal</span>
                    <span class="text-xs font-bold text-slate-600" x-text="formatRp(currentSubtotal)"></span>
                </div>
                <template x-if="activeWorksheet && activeWorksheet.deliveryMode && activeWorksheet.deliveryFee > 0">
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-bold text-slate-400">Ongkir</span>
                        <span class="text-xs font-bold text-slate-600" x-text="'+ ' + formatRp(activeWorksheet.deliveryFee)"></span>
                    </div>
                </template>
                <div class="flex justify-between items-end pt-1">
                    <div>
                        <h4 class="text-xl font-black text-slate-800 tracking-tight">Total</h4>
                    </div>
                    <div class="text-right">
                        <span class="text-xl font-black text-slate-800 tracking-tight" x-text="formatRp(currentTotal)"></span>
                    </div>
                </div>
            </div>

            {{-- ACTIONS --}}
            <div class="grid grid-cols-2 gap-3 pt-2">
                <button @click="resetCurrentWorksheet()" class="py-3.5 bg-red-50 text-red-600 border border-red-100 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-red-500 hover:text-white transition-all active:scale-[0.98]">Batal</button>
                <button @click="openPayment()" 
                        :disabled="!activeShift || activeWorksheet.cart.length === 0" 
                        :title="!activeShift ? 'Buka shift terlebih dahulu' : (activeWorksheet.cart.length === 0 ? 'Keranjang masih kosong' : '')"
                        class="py-3.5 bg-emerald-400 text-white rounded-xl font-black text-xs uppercase tracking-widest hover:bg-emerald-500 shadow-lg shadow-emerald-500/20 transition-all active:scale-[0.98] disabled:opacity-30 disabled:cursor-not-allowed disabled:bg-slate-400">
                    Order
                </button>
            </div>
        </div>
    </div>
    
    {{-- MOBILE FLOATING CART BUTTON --}}
    <button @click="mobileCartOpen = true" class="lg:hidden fixed bottom-6 right-6 z-[130] bg-emerald-500 text-white rounded-full px-5 py-3.5 shadow-xl shadow-emerald-500/30 flex items-center gap-3 active:scale-95 transition-all">
        <div class="relative">
            <i class="fas fa-shopping-cart text-lg"></i>
            <span x-show="activeWorksheet && activeWorksheet.cart.length > 0" class="absolute -top-3 -right-3 bg-rose-500 text-white text-[10px] font-black w-5 h-5 rounded-full flex items-center justify-center border-2 border-emerald-500" x-text="activeWorksheet ? activeWorksheet.cart.reduce((s,i) => s+i.quantity, 0) : 0"></span>
        </div>
        <span class="font-black text-sm" x-text="formatRp(currentTotal)"></span>
    </button>


    {{-- MODAL LAYOUT EDITOR (DRAG & DROP) --}}
    <template x-teleport="body">
        <div x-show="showGroupManagerModal" x-transition x-cloak class="fixed inset-0 bg-slate-100 z-[200] flex flex-col h-screen overflow-hidden">
        {{-- HEADER MODAL --}}
        <div class="bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between shrink-0 shadow-sm z-10">
            <div class="flex items-center gap-4">
                <button @click="closeGroupManager()" class="w-10 h-10 bg-slate-100 text-slate-500 rounded-full hover:bg-slate-200 hover:text-slate-800 transition-colors"><i class="fas fa-arrow-left"></i></button>
                <div>
                    <h3 class="text-xl font-black text-slate-800">Edit Layout POS</h3>
                    <p class="text-xs font-bold text-slate-500">Seret produk untuk mengatur urutan & kelompok</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button @click="addNewDraftGroup()" class="px-5 py-2.5 rounded-xl font-bold text-blue-600 bg-blue-50 border border-blue-200 hover:bg-blue-600 hover:text-white transition-all shadow-sm flex items-center gap-2">
                    <i class="fas fa-plus"></i> Tambah Grup
                </button>
                <button @click="saveLayoutEditor()" :disabled="isSavingGroup" class="px-8 py-2.5 rounded-xl font-bold text-white bg-emerald-500 hover:bg-emerald-600 shadow-lg shadow-emerald-500/30 transition-all disabled:opacity-50 flex items-center gap-2">
                    <i x-show="!isSavingGroup" class="fas fa-check"></i>
                    <i x-show="isSavingGroup" class="fas fa-spinner fa-spin"></i>
                    Selesai
                </button>
            </div>
        </div>

        {{-- BODY EDITOR --}}
        <div class="flex-1 overflow-y-auto p-6 bg-slate-100/80 flex flex-col gap-6">
            {{-- GROUPS CONTAINER --}}
            <template x-for="(g, gIndex) in draftGroups" :key="'draft-g-'+gIndex">
                <div class="bg-white rounded-3xl border-2 shadow-sm p-5 relative transition-all" 
                     :style="`border-color: ${dragOverGroup === gIndex ? g.color : g.color+'40'}; background-color: ${g.color}05;`">
                    {{-- Group Header --}}
                    <div class="flex items-center justify-between mb-4 border-b border-white/50 pb-3">
                        <div class="flex items-center gap-3 w-1/3">
                            <input type="color" x-model="g.color" class="w-8 h-8 rounded-xl cursor-pointer border-2 border-white shadow-sm p-0">
                            <input type="text" x-model="g.name" class="font-black text-slate-700 bg-transparent border-b border-dashed border-slate-300 focus:border-slate-500 focus:outline-none px-1 w-full text-lg" placeholder="Nama Grup">
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-bold text-slate-500 bg-white/60 px-3 py-1 rounded-lg shadow-sm" x-text="g.products.length + ' produk'"></span>
                            <button @click="deleteDraftGroup(gIndex)" class="w-8 h-8 rounded-lg bg-red-50 text-red-400 hover:bg-red-500 hover:text-white shadow-sm transition-colors flex items-center justify-center"><i class="fas fa-trash-alt text-xs"></i></button>
                        </div>
                    </div>
                    
                    {{-- Group Droppable Area --}}
                    <div class="min-h-[160px] flex gap-4 overflow-x-auto pb-4 scrollbar-hide items-stretch"
                         @dragover.prevent="dragOverGroup = gIndex"
                         @dragleave.prevent="dragOverGroup = null"
                         @drop.prevent="dropItemToGroup(gIndex)"
                         :class="dragOverGroup === gIndex ? 'ring-2 ring-emerald-400 ring-inset rounded-2xl bg-white/40' : ''">
                        
                        <template x-if="g.products.length === 0">
                            <div class="w-full flex items-center justify-center text-slate-400 font-bold text-sm border-2 border-dashed border-slate-300 rounded-2xl bg-white/50">
                                <div class="flex flex-col items-center gap-2">
                                    <i class="fas fa-hand-holding-box text-3xl opacity-50"></i>
                                    <span>Seret produk ke sini</span>
                                </div>
                            </div>
                        </template>
                        
                        <template x-for="(p, pIndex) in g.products" :key="'draft-p-'+p.id">
                            <div draggable="true" 
                                 @dragstart="startDrag($event, p, gIndex, pIndex)"
                                 @dragover.stop.prevent="dragOverIndex = pIndex"
                                 @drop.stop.prevent="dropItemToIndex(gIndex, pIndex)"
                                 class="w-40 bg-white border-2 rounded-2xl overflow-hidden shrink-0 shadow-sm cursor-grab active:cursor-grabbing hover:-translate-y-1 hover:shadow-lg transition-all relative group flex flex-col"
                                 :style="`border-color: ${dragOverIndex === pIndex && dragOverGroup === gIndex ? g.color : '#e2e8f0'}; box-shadow: ${dragOverIndex === pIndex && dragOverGroup === gIndex ? '0 0 0 4px '+g.color+'40' : ''};`">
                                
                                <div class="h-28 w-full bg-slate-100 relative overflow-hidden shrink-0">
                                    <template x-if="p.image">
                                        <img :src="'/storage/'+p.image" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!p.image">
                                        <div class="w-full h-full flex flex-col items-center justify-center select-none" 
                                             :style="`background: linear-gradient(135deg, ${p.category?.color || '#cbd5e1'} 0%, ${adjustBrightness(p.category?.color || '#cbd5e1', -20)} 100%); color: ${getContrastYIQ(p.category?.color || '#cbd5e1')};`">
                                            <i :class="getPlaceholderIcon(p.category?.name)" class="text-3xl mb-1 opacity-50 drop-shadow-md"></i>
                                            <span class="text-lg font-black opacity-80 tracking-tighter mix-blend-overlay drop-shadow-sm" x-text="getInitials(p.name)"></span>
                                        </div>
                                    </template>
                                </div>
                                
                                <div class="p-3 flex flex-col flex-1">
                                    <p class="text-[9px] uppercase tracking-wider font-bold text-slate-400 mb-0.5 truncate" x-text="p.category ? p.category.name : '-'"></p>
                                    <p class="text-xs font-black text-slate-800 leading-snug mb-1 line-clamp-2 flex-1 group-hover:text-emerald-600 transition-colors" x-text="p.name"></p>
                                    <div class="mt-auto pt-2 border-t border-slate-50">
                                        <p class="text-emerald-600 font-black text-sm tracking-tight" x-text="formatRp(p.price)"></p>
                                    </div>
                                </div>
                                <button @click="g.products.splice(pIndex, 1); draftUngrouped.unshift(p)" class="absolute top-2 right-2 w-7 h-7 bg-white/90 backdrop-blur-sm text-red-500 rounded-lg shadow-sm opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center hover:bg-red-500 hover:text-white border border-red-100"><i class="fas fa-times text-xs"></i></button>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            {{-- UNGROUPED PRODUCTS --}}
            <div class="bg-slate-50 rounded-3xl border-2 border-dashed border-slate-300 p-5 mt-4 shadow-inner">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="font-black text-slate-500 flex items-center gap-2 text-lg"><i class="fas fa-box-open text-slate-400"></i> Tidak Dikelompokkan</h4>
                    <span class="text-xs font-bold text-slate-500 bg-white px-3 py-1 rounded-lg border border-slate-200 shadow-sm" x-text="draftUngrouped.length + ' produk'"></span>
                </div>
                
                <div class="min-h-[160px] flex flex-wrap gap-4"
                     @dragover.prevent="dragOverGroup = 'ungrouped'"
                     @dragleave.prevent="dragOverGroup = null"
                     @drop.prevent="dropItemToUngrouped()"
                     :class="dragOverGroup === 'ungrouped' ? 'bg-slate-100/80 rounded-2xl ring-2 ring-emerald-400 ring-inset' : ''">
                    
                    <template x-if="draftUngrouped.length === 0">
                        <div class="w-full flex flex-col items-center justify-center text-emerald-500 font-bold text-sm bg-emerald-50/50 rounded-2xl border border-emerald-100 py-8">
                            <i class="fas fa-check-circle text-4xl mb-2 text-emerald-400 opacity-50"></i>
                            Semua produk telah dikelompokkan
                        </div>
                    </template>
                    
                    <template x-for="(p, pIndex) in draftUngrouped" :key="'ungrouped-'+p.id">
                        <div draggable="true" 
                             @dragstart="startDrag($event, p, 'ungrouped', pIndex)"
                             class="w-40 bg-white border border-slate-200 rounded-2xl overflow-hidden shrink-0 shadow-sm cursor-grab active:cursor-grabbing hover:-translate-y-1 hover:shadow-lg transition-all relative group flex flex-col opacity-80 hover:opacity-100 grayscale-[0.5] hover:grayscale-0">
                            
                            <div class="h-28 w-full bg-slate-100 relative overflow-hidden shrink-0">
                                <template x-if="p.image">
                                    <img :src="'/storage/'+p.image" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!p.image">
                                    <div class="w-full h-full flex flex-col items-center justify-center select-none" 
                                         :style="`background: linear-gradient(135deg, ${p.category?.color || '#cbd5e1'} 0%, ${adjustBrightness(p.category?.color || '#cbd5e1', -20)} 100%); color: ${getContrastYIQ(p.category?.color || '#cbd5e1')};`">
                                        <i :class="getPlaceholderIcon(p.category?.name)" class="text-3xl mb-1 opacity-50 drop-shadow-md"></i>
                                        <span class="text-lg font-black opacity-80 tracking-tighter mix-blend-overlay drop-shadow-sm" x-text="getInitials(p.name)"></span>
                                    </div>
                                </template>
                            </div>
                            
                            <div class="p-3 flex flex-col flex-1">
                                <p class="text-[9px] uppercase tracking-wider font-bold text-slate-400 mb-0.5 truncate" x-text="p.category ? p.category.name : '-'"></p>
                                <p class="text-xs font-black text-slate-800 leading-snug mb-1 line-clamp-2 flex-1" x-text="p.name"></p>
                                <div class="mt-auto pt-2 border-t border-slate-50">
                                    <p class="text-slate-500 font-black text-sm tracking-tight" x-text="formatRp(p.price)"></p>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </template>

    {{-- MODAL PEMBAYARAN --}}
    <template x-teleport="body">
        <div x-show="showPaymentModal" x-transition x-cloak class="fixed inset-0 bg-slate-900/80 backdrop-blur-md z-[200] flex items-center justify-center p-4">
        <div @click.away="showPaymentModal = false" class="bg-white rounded-3xl w-full max-w-md shadow-2xl border border-slate-200 transform transition-all max-h-[90vh] overflow-y-auto scrollbar-hide ">
            
            {{-- Header --}}
            <div class="p-6 border-b border-slate-100 flex justify-between items-center sticky top-0 bg-white rounded-t-3xl z-10">
                <h3 class="text-xl font-black text-slate-800">Detail Pembayaran</h3>
                <button @click="showPaymentModal = false" class="w-8 h-8 bg-slate-100 text-slate-500 rounded-full hover:bg-slate-200 hover:text-slate-800 transition-colors"><i class="fas fa-times"></i></button>
            </div>

            <div class="p-6 space-y-6">
                {{-- Total Tagihan --}}
                <div class="flex items-center justify-between">
                    <span class="text-sm font-bold text-slate-500">Total Tagihan:</span>
                    <span class="text-3xl font-black text-emerald-600 tracking-tight" x-text="formatRp(currentTotal)"></span>
                </div>

                {{-- Toggle: Bayar Sekarang / Bayar Nanti --}}
                <div>
                    <label class="text-xs font-black text-slate-600 uppercase tracking-wider mb-3 block">Waktu Pembayaran</label>
                    <div class="flex p-1 bg-slate-100 rounded-xl">
                        <button @click="paymentTiming = 'now'" 
                            :class="paymentTiming === 'now' ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/30' : 'text-slate-500 hover:text-slate-700'" 
                            class="flex-1 py-2.5 text-sm font-bold rounded-lg transition-all">Bayar Sekarang</button>
                        <button @click="paymentTiming = 'later'" 
                            :class="paymentTiming === 'later' ? 'bg-orange-500 text-white shadow-lg shadow-orange-500/30' : 'text-slate-500 hover:text-slate-700'" 
                            class="flex-1 py-2.5 text-sm font-bold rounded-lg transition-all">Bayar Nanti</button>
                    </div>
                </div>

                {{-- ========== BAYAR SEKARANG ========== --}}
                <template x-if="paymentTiming === 'now'">
                    <div class="space-y-6">
                        {{-- Metode Pembayaran --}}
                        <div>
                            <label class="text-xs font-black text-slate-600 uppercase tracking-wider mb-3 block">Metode Pembayaran</label>
                            <div class="flex gap-2">
                                <template x-for="(m, i) in payNowMethods" :key="i">
                                    <button @click="paymentMethod = m.id" 
                                        :class="paymentMethod === m.id ? 'bg-emerald-500 text-white border-emerald-500 shadow-lg shadow-emerald-500/20' : 'bg-white text-slate-600 border-slate-200 hover:border-emerald-300'"
                                        class="flex-1 py-2.5 px-3 border-2 rounded-xl text-sm font-bold transition-all flex items-center justify-center gap-2">
                                        <span x-text="m.label"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        {{-- PANEL: TUNAI --}}
                        <div x-show="paymentMethod === 'cash'" x-transition class="space-y-4">
                            <div>
                                <label class="text-xs font-black text-slate-600 uppercase tracking-wider mb-2 block flex items-center gap-2">
                                    Jumlah Uang Diterima
                                    <button @click="paidAmount = currentTotal; paidAmountDisplay = formatInput(paidAmount)" class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200 hover:bg-emerald-100 transition-colors"><i class="fas fa-check mr-1"></i>Uang Pas ({{ 'Rp ' }})<span x-text="new Intl.NumberFormat('id-ID').format(currentTotal)"></span></button>
                                </label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-sm">Rp</span>
                                    <input type="text" 
                                        :value="paidAmountDisplay"
                                        @input="paidAmountDisplay = formatInput($event.target.value); paidAmount = parseInput($event.target.value)"
                                        class="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl pl-12 pr-4 py-4 text-xl font-black text-slate-800 outline-none focus:border-emerald-500 focus:bg-white transition-colors" placeholder="0">
                                </div>
                            </div>
                            <div class="flex justify-between items-center bg-slate-50 p-4 rounded-2xl border border-slate-100">
                                <span class="text-sm font-bold text-slate-500">Kembalian:</span>
                                <span class="text-2xl font-black tracking-tight" 
                                    :class="(paidAmount - currentTotal) >= 0 ? 'text-emerald-600' : 'text-red-500'"
                                    x-text="formatRp(Math.max(0, paidAmount - currentTotal))"></span>
                            </div>
                        </div>

                        {{-- PANEL: QRIS --}}
                        <div x-show="paymentMethod === 'qris'" x-transition class="space-y-4">
                            @if(!empty($settings['qris_image']))
                            <div class="bg-white border-2 border-slate-100 rounded-2xl p-6 flex flex-col items-center">
                                <img src="{{ asset('storage/' . $settings['qris_image']) }}" class="w-56 h-auto rounded-xl shadow-md mb-4" alt="QRIS QR Code">
                                <p class="text-sm text-slate-500 font-medium text-center">Pindai kode QR untuk membayar.</p>
                            </div>
                            @else
                            <div class="bg-slate-50 border-2 border-dashed border-slate-200 rounded-2xl p-8 flex flex-col items-center text-center">
                                <div class="w-16 h-16 bg-slate-200 rounded-full flex items-center justify-center mb-3"><i class="fas fa-qrcode text-2xl text-slate-400"></i></div>
                                <p class="text-sm font-bold text-slate-500">QR Code belum diatur</p>
                                <p class="text-xs text-slate-400 mt-1">Upload di menu Pengaturan Toko â†’ Gambar QRIS</p>
                            </div>
                            @endif
                        </div>

                        {{-- PANEL: TRANSFER --}}
                        <div x-show="paymentMethod === 'transfer'" x-transition class="space-y-4">
                            @if(!empty($settings['bank_name']))
                            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5 space-y-3">
                                <div class="flex items-center gap-2 mb-1">
                                    <i class="fas fa-building-columns text-blue-500"></i>
                                    <span class="text-sm font-black text-slate-700">Info Rekening Transfer</span>
                                </div>
                                <div class="grid grid-cols-[auto_1fr] gap-x-4 gap-y-2 text-sm">
                                    <span class="text-slate-500 font-medium">Bank</span>
                                    <span class="font-black text-slate-800">{{ $settings['bank_name'] ?? '-' }}</span>
                                    <span class="text-slate-500 font-medium">No. Rekening</span>
                                    <span class="font-black text-slate-800 tracking-wider">{{ $settings['bank_account'] ?? '-' }}</span>
                                    <span class="text-slate-500 font-medium">Atas Nama</span>
                                    <span class="font-black text-slate-800">{{ strtoupper($settings['bank_holder'] ?? '-') }}</span>
                                    <span class="text-slate-500 font-medium">Jumlah Transfer</span>
                                    <span class="font-black text-emerald-600" x-text="formatRp(currentTotal)"></span>
                                </div>
                            </div>
                            @else
                            <div class="bg-slate-50 border-2 border-dashed border-slate-200 rounded-2xl p-8 flex flex-col items-center text-center">
                                <div class="w-16 h-16 bg-slate-200 rounded-full flex items-center justify-center mb-3"><i class="fas fa-building-columns text-2xl text-slate-400"></i></div>
                                <p class="text-sm font-bold text-slate-500">Info rekening belum diatur</p>
                                <p class="text-xs text-slate-400 mt-1">Isi di menu Pengaturan Toko â†’ Info Rekening Transfer</p>
                            </div>
                            @endif
                        </div>

                        {{-- PANEL: DEBIT --}}
                        <div x-show="paymentMethod === 'debit'" x-transition>
                            <div class="bg-blue-50 border border-blue-100 rounded-2xl p-5 flex items-center gap-4">
                                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center shrink-0"><i class="fas fa-credit-card text-blue-500 text-xl"></i></div>
                                <div>
                                    <p class="text-sm font-bold text-blue-800">Pembayaran via Kartu Debit</p>
                                    <p class="text-xs text-blue-600/70 mt-0.5">Proses pembayaran melalui mesin EDC.</p>
                                </div>
                            </div>
                        </div>

                        {{-- Button Konfirmasi --}}
                        <button @click="processCheckout()" :disabled="isProcessing || (paymentMethod === 'cash' && paidAmount < currentTotal)" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-black py-4 rounded-xl transition-all shadow-lg shadow-emerald-500/30 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-3 text-lg active:scale-[0.98]">
                            <i x-show="!isProcessing" class="fas fa-check-circle"></i>
                            <i x-show="isProcessing" class="fas fa-spinner fa-spin"></i>
                            <span x-text="isProcessing ? 'Memproses...' : 'Selesaikan Pembayaran'"></span>
                        </button>
                    </div>
                </template>

                {{-- ========== BAYAR NANTI (PIUTANG) ========== --}}
                <template x-if="paymentTiming === 'later'">
                    <div class="space-y-6">
                        <div class="bg-orange-50 border border-orange-200 rounded-2xl p-5">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-10 h-10 bg-orange-100 rounded-xl flex items-center justify-center shrink-0"><i class="fas fa-hand-holding-dollar text-orange-500 text-lg"></i></div>
                                <div>
                                    <p class="text-sm font-black text-orange-800">Pembayaran Uang Muka (DP)</p>
                                    <p class="text-xs text-orange-600/70">Masukkan nominal DP atau biarkan kosong untuk mencatat sebagai hutang penuh.</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="text-xs font-black text-slate-600 uppercase tracking-wider mb-2 block">Nominal Uang Diterima (DP)</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-sm">Rp</span>
                                <input type="text" 
                                    :value="dpAmountDisplay"
                                    @input="dpAmountDisplay = formatInput($event.target.value); dpAmount = parseInput($event.target.value)"
                                    class="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl pl-12 pr-4 py-4 text-xl font-black text-slate-800 outline-none focus:border-orange-500 focus:bg-white transition-colors" placeholder="0">
                            </div>
                        </div>

                        {{-- Metode Pembayaran DP (Hanya muncul jika ada DP) --}}
                        <div x-show="dpAmount > 0" x-transition>
                            <label class="text-xs font-black text-slate-600 uppercase tracking-wider mb-3 block">Metode Pembayaran DP</label>
                            <div class="flex gap-2">
                                <template x-for="(m, i) in payNowMethods" :key="i">
                                    <button @click="paymentMethod = m.id" 
                                        :class="paymentMethod === m.id ? 'bg-orange-500 text-white border-orange-500 shadow-lg shadow-orange-500/20' : 'bg-white text-slate-600 border-slate-200 hover:border-orange-300'"
                                        class="flex-1 py-2.5 px-3 border-2 rounded-xl text-sm font-bold transition-all flex items-center justify-center gap-2">
                                        <span x-text="m.label"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <div class="flex justify-between items-center bg-orange-50 p-4 rounded-2xl border border-orange-100">
                            <span class="text-sm font-bold text-orange-700">Sisa Piutang:</span>
                            <span class="text-2xl font-black text-orange-600 tracking-tight" x-text="formatRp(Math.max(0, currentTotal - (dpAmount || 0)))"></span>
                        </div>

                        <button @click="processCheckout()" :disabled="isProcessing" class="w-full bg-orange-500 hover:bg-orange-600 text-white font-black py-4 rounded-xl transition-all shadow-lg shadow-orange-500/30 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-3 text-lg active:scale-[0.98]">
                            <i x-show="!isProcessing" class="fas fa-file-invoice-dollar"></i>
                            <i x-show="isProcessing" class="fas fa-spinner fa-spin"></i>
                            <span x-text="isProcessing ? 'Memproses...' : 'Catat Pesanan'"></span>
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </template>

    {{-- MODAL SUKSES (RECEIPT) --}}
    <template x-teleport="body">
        <div x-show="showReceiptModal" x-transition x-cloak class="fixed inset-0 bg-slate-900/80 backdrop-blur-md z-[200] flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl w-full max-w-sm p-8 shadow-2xl border border-slate-200 text-center transform transition-all relative overflow-hidden max-h-[90vh] overflow-y-auto scrollbar-hide ">
            <div class="absolute -top-10 -right-10 w-40 h-40 bg-emerald-50 rounded-full opacity-50"></div>
            <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-blue-50 rounded-full opacity-50"></div>
            
            <div class="relative z-10">
                <div class="w-24 h-24 bg-emerald-100 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-6 text-5xl shadow-inner ring-4 ring-emerald-50">
                    <i class="fas fa-check"></i>
                </div>
                <h3 class="text-2xl font-black text-slate-800 mb-2">Transaksi Sukses!</h3>
                <p class="text-slate-500 text-sm font-bold mb-8 flex items-center justify-center gap-2">
                    <i class="fas fa-receipt"></i> <span x-text="receiptData?.invoice"></span>
                </p>
                
                <div class="bg-slate-50 p-5 rounded-2xl mb-8 border border-slate-200 shadow-inner">
                    <p class="text-slate-400 text-xs font-black uppercase tracking-wider mb-2">Uang Kembali</p>
                    <p class="text-4xl font-black text-emerald-600 tracking-tighter" x-text="formatRp(receiptData?.change || 0)"></p>
                </div>
                
                <div class="flex flex-col gap-3">
                    <button @click="doPrint(receiptData?.transaction_id)" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-black py-4 rounded-xl transition-all shadow-lg shadow-slate-900/20 flex items-center justify-center gap-2">
                        <i class="fas fa-print"></i> Cetak Struk
                    </button>
                    <button @click="closeReceiptAndReset()" class="w-full bg-white border-2 border-slate-200 hover:border-slate-300 hover:bg-slate-50 text-slate-700 font-black py-4 rounded-xl transition-all shadow-sm">
                        Transaksi Baru (Enter)
                    </button>
                </div>
            </div>
        </div>
    </template>

    {{-- MODAL TAMBAH GRUP --}}
    <template x-teleport="body">
        <div x-show="showAddGroupModal" x-transition x-cloak class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm z-[210] flex items-center justify-center p-4">
        <div @click.away="closeAddGroupModal()" @keydown.escape.window="closeAddGroupModal()" class="bg-slate-800 rounded-2xl w-full max-w-md shadow-2xl border border-slate-700 transform transition-all overflow-hidden max-h-[90vh] overflow-y-auto scrollbar-hide ">
            <div class="px-6 py-4 border-b border-slate-700/50 flex justify-between items-center bg-slate-800/80">
                <h3 class="text-lg font-black text-white flex items-center gap-2"><i class="fas fa-layer-group text-blue-400"></i> Tambah Group Produk</h3>
                <button @click="closeAddGroupModal()" class="text-slate-400 hover:text-white transition-colors"><i class="fas fa-times"></i></button>
            </div>
            <div class="p-6">
                <label class="text-xs font-bold text-slate-300 uppercase tracking-wider mb-2 block">Nama Group</label>
                <input type="text" x-model="newGroupName" @keydown.enter="submitNewGroup()" x-ref="newGroupNameInput" class="w-full bg-slate-900/50 border border-slate-600 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-sm" placeholder="Contoh: Promo Ramadhan, Minuman Dingin...">
                <p x-show="newGroupError" class="text-red-400 text-xs mt-2 font-medium" x-text="newGroupError"></p>
            </div>
            <div class="px-6 py-4 border-t border-slate-700/50 bg-slate-800/50 flex justify-end gap-3">
                <button @click="closeAddGroupModal()" class="px-5 py-2.5 rounded-xl font-bold text-slate-300 bg-slate-700 hover:bg-slate-600 transition-all text-sm">Batal</button>
                <button @click="submitNewGroup()" class="px-5 py-2.5 rounded-xl font-bold text-white bg-blue-600 hover:bg-blue-500 transition-all shadow-lg shadow-blue-500/20 text-sm">Simpan Group</button>
            </div>
        </div>
    </template>


    {{-- MODAL PENGELUARAN / EXPENSE --}}
    <template x-teleport="body">
        <div x-show="showExpenseModal" x-transition x-cloak class="fixed inset-0 bg-slate-900/80 backdrop-blur-md z-[200] flex items-center justify-center p-4">
        <div @click.away="showExpenseModal = false" class="bg-white rounded-3xl w-full max-w-md p-6 shadow-2xl border border-slate-200 max-h-[90vh] overflow-y-auto scrollbar-hide ">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-lg font-black text-slate-800"><i class="fas fa-arrow-down text-orange-500 mr-2"></i>Catat Pengeluaran</h2>
                <button @click="showExpenseModal = false" class="w-8 h-8 bg-slate-100 text-slate-500 rounded-full hover:bg-slate-200"><i class="fas fa-times"></i></button>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Deskripsi <span class="text-red-500">*</span></label>
                    <input type="text" x-model="expenseDesc" class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-500" placeholder="Beli air mineral, kertas, dll...">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Nominal (Rp) <span class="text-red-500">*</span></label>
                    <input type="number" x-model="expenseAmount" class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold focus:outline-none focus:border-blue-500" placeholder="10000" min="1">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Kategori</label>
                    <select x-model="expenseCategory" class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-500">
                        <option>Pengeluaran Kasir</option>
                        <option>Beli Keperluan</option>
                        <option>Operasional</option>
                        <option>Lain-lain</option>
                    </select>
                </div>
                <template x-if="expenseError">
                    <p class="text-red-500 text-xs font-bold" x-text="expenseError"></p>
                </template>
                <button @click="submitExpense()" class="w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 rounded-xl transition-all shadow-lg active:scale-95">
                    <i class="fas fa-check mr-2"></i>Simpan Pengeluaran
                </button>
            </div>
        </div>
    </template>

</div>

@if($activeShift)

{{-- ====================== MODAL TUTUP SHIFT ====================== --}}
<div id="modal-tutup-shift" class="hidden fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-[200] flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl w-full max-w-md shadow-2xl border border-slate-200 overflow-hidden max-h-[90vh] overflow-y-auto scrollbar-hide ">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-red-500 to-rose-600 p-6 text-white">
            <div class="flex items-center justify-between mb-1">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-door-closed text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-black">Tutup Shift</h3>
                        <p class="text-xs text-red-100">Ringkasan sesi kasir</p>
                    </div>
                </div>
                <button onclick="document.getElementById('modal-tutup-shift').classList.add('hidden')" class="w-8 h-8 bg-white/20 hover:bg-white/30 rounded-full flex items-center justify-center transition-colors">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
        </div>

        <div class="p-6 space-y-4 max-h-[60vh] overflow-y-auto">
            {{-- Info Shift --}}
            <div class="bg-slate-50 rounded-2xl p-4 space-y-2 text-sm border border-slate-100">
                <div class="flex justify-between">
                    <span class="text-slate-500 font-medium">Kasir</span>
                    <span class="font-black text-slate-800">{{ $activeShift->opener->name ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 font-medium">Shift Dibuka</span>
                    <span class="font-black text-slate-800">{{ $activeShift->opened_at->format('d M, H:i') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 font-medium">Modal Awal</span>
                    <span class="font-black text-slate-800">Rp {{ number_format($activeShift->opening_cash, 0, ',', '.') }}</span>
                </div>
                @php
                    $closeCashSales = \App\Models\Transaction::withoutGlobalScope('worksheet')->where('shift_id', $activeShift->id)->completed()->where('payment_method', 'cash')->sum('total');
                    $closeQrisSales = \App\Models\Transaction::withoutGlobalScope('worksheet')->where('shift_id', $activeShift->id)->completed()->where('payment_method', 'qris')->sum('total');
                    $closeTransferSales = \App\Models\Transaction::withoutGlobalScope('worksheet')->where('shift_id', $activeShift->id)->completed()->where('payment_method', 'transfer')->sum('total');
                    $closeDebitSales = \App\Models\Transaction::withoutGlobalScope('worksheet')->where('shift_id', $activeShift->id)->completed()->where('payment_method', 'debit')->sum('total');
                    
                    $closeCashExp   = \App\Models\Cashflow::withoutGlobalScope('worksheet')->where('shift_id', $activeShift->id)->where('type','expense')->where('source','pos_cash')->sum('amount');
                    $closeBankExp   = \App\Models\Cashflow::withoutGlobalScope('worksheet')->where('shift_id', $activeShift->id)->where('type','expense')->where('source','pos_bank')->sum('amount');
                    $closeTotalTrx  = \App\Models\Transaction::withoutGlobalScope('worksheet')->where('shift_id', $activeShift->id)->completed()->count();
                    $closeTotalSales= \App\Models\Transaction::withoutGlobalScope('worksheet')->where('shift_id', $activeShift->id)->completed()->sum('total');
                    
                    $closeTransfers = \App\Models\Cashflow::withoutGlobalScope('worksheet')
                        ->where('shift_id', $activeShift->id)
                        ->where('source', 'pos_cash')
                        ->where('category', '!=', 'Penjualan')
                        ->where('transaction_category', '!=', 'expense')
                        ->sum(\Illuminate\Support\Facades\DB::raw('CASE WHEN type = "income" THEN amount ELSE -amount END'));

                    $closeExpected  = $activeShift->opening_cash + $closeCashSales - $closeCashExp + $closeTransfers;
                @endphp
                <div class="flex justify-between">
                    <span class="text-slate-500 font-medium">Total Transaksi</span>
                    <span class="font-black text-slate-800">{{ $closeTotalTrx }} transaksi</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 font-medium">Total Penjualan</span>
                    <span class="font-black text-emerald-600">Rp {{ number_format($closeTotalSales, 0, ',', '.') }}</span>
                </div>
                
                {{-- Breakdown --}}
                <div class="space-y-1 ml-4 border-l-2 border-slate-100 pl-3 py-1">
                    <div class="flex justify-between text-[11px]">
                        <span class="text-slate-400 font-medium">Tunai</span>
                        <span class="font-bold text-slate-600">Rp {{ number_format($closeCashSales, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-[11px]">
                        <span class="text-slate-400 font-medium">QRIS</span>
                        <span class="font-bold text-slate-600">Rp {{ number_format($closeQrisSales, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-[11px]">
                        <span class="text-slate-400 font-medium">Transfer</span>
                        <span class="font-bold text-slate-600">Rp {{ number_format($closeTransferSales, 0, ',', '.') }}</span>
                    </div>
                    <div x-show="{{ $closeDebitSales }} > 0" class="flex justify-between text-[11px]">
                        <span class="text-slate-400 font-medium">Debit</span>
                        <span class="font-bold text-slate-600">Rp {{ number_format($closeDebitSales, 0, ',', '.') }}</span>
                    </div>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 font-medium">Pengeluaran Tunai</span>
                    <span class="font-black text-red-500">Rp {{ number_format($closeCashExp, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 font-medium">Pengeluaran Bank</span>
                    <span class="font-black text-red-500">Rp {{ number_format($closeBankExp, 0, ',', '.') }}</span>
                </div>
            </div>

            {{-- Expected Cash --}}
            <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4 flex justify-between items-center" id="expected-cash-container" data-expected="{{ $closeExpected }}">
                <div>
                    <p class="text-xs font-black text-blue-600 uppercase tracking-wider">Expected Cash</p>
                    <p class="text-xs text-blue-500 mt-0.5">Modal + Tunai Masuk - Pengeluaran</p>
                </div>
                <p class="text-xl font-black text-blue-700">Rp {{ number_format($closeExpected, 0, ',', '.') }}</p>
            </div>

            {{-- Form Tutup Shift --}}
            <form action="{{ route('shifts.close', $activeShift) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="text-xs font-black text-slate-600 uppercase tracking-wider mb-2 block">
                        Uang di Laci (Rp) <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="closing_cash_display" 
                           class="w-full bg-slate-50 border-2 border-slate-200 rounded-2xl px-4 py-3 text-xl font-black text-slate-800 outline-none focus:border-red-500 focus:bg-white transition-colors"
                           placeholder="Hitung uang di laci..."
                           oninput="formatTutupShift(this)">
                    <input type="hidden" name="closing_cash" id="closing_cash_raw" required>
                    <p class="text-xs text-slate-400 mt-1.5 font-medium min-h-[1.25rem] flex items-center" id="shift-diff-note">
                        <i class="fas fa-info-circle text-blue-400 mr-1"></i>
                        Selisih akan dihitung otomatis setelah menutup shift.
                    </p>
                </div>
                <div>
                    <label class="text-xs font-black text-slate-600 uppercase tracking-wider mb-2 block">Catatan (opsional)</label>
                    <textarea name="notes" rows="2"
                              class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-700 outline-none focus:border-red-400 resize-none transition-colors"
                              placeholder="Catatan tutup shift..."></textarea>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-tutup-shift').classList.add('hidden')"
                            class="flex-1 py-3 border-2 border-slate-200 text-slate-600 font-black rounded-xl hover:bg-slate-50 transition-all text-sm">
                        Batal
                    </button>
                    <button type="submit"
                            class="flex-1 py-3 bg-red-500 hover:bg-red-600 text-white font-black rounded-xl transition-all shadow-lg shadow-red-500/30 active:scale-[0.98] flex items-center justify-center gap-2 text-sm">
                        <i class="fas fa-door-closed"></i> Tutup Shift
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

    {{-- MODAL CUSTOM HARGA (HARGA KHUSUS) --}}
    <template x-teleport="body">
        <div x-show="showCustomPriceModal" x-transition x-cloak class="fixed inset-0 bg-slate-900/80 backdrop-blur-md z-[200] flex items-center justify-center p-4">
            <div @click.away="showCustomPriceModal = false" class="bg-white rounded-[2.5rem] w-full max-w-sm shadow-2xl border border-slate-200 overflow-hidden max-h-[90vh] overflow-y-auto scrollbar-hide ">
                {{-- Header --}}
                <div class="bg-gradient-to-r from-orange-500 to-amber-600 p-8 text-white relative">
                    <div class="absolute top-0 right-0 p-8 opacity-10"><i class="fas fa-tags text-7xl"></i></div>
                    <div class="relative z-10 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center backdrop-blur-md">
                                <i class="fas fa-tags text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-black">Harga Khusus</h3>
                                <p class="text-[10px] font-bold text-orange-100 uppercase tracking-widest mt-0.5 opacity-80" x-text="customPriceProduct ? customPriceProduct.name : ''"></p>
                            </div>
                        </div>
                        <button @click="showCustomPriceModal = false" class="w-10 h-10 bg-white/10 hover:bg-white/20 rounded-xl flex items-center justify-center transition-all">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <div class="p-8 space-y-6">
                    {{-- Input Harga Jual --}}
                    <div>
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 block">Harga Jual Baru (Rp)</label>
                        <div class="relative">
                            <span class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 font-black text-lg">Rp</span>
                            <input type="text" 
                                   :value="customPriceInput"
                                   @input="customPriceInput = formatInput($event.target.value); customPriceInputRaw = parseInput($event.target.value)"
                                   class="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl pl-14 pr-5 py-4 text-2xl font-black text-orange-600 outline-none focus:border-orange-500 focus:bg-white transition-all shadow-inner"
                                   placeholder="0">
                        </div>
                    </div>

                    {{-- Input HPP (Optional) --}}
                    <div x-show="customPriceAllowHPP">
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 flex items-center justify-between">
                            <span>Modal / HPP (Rp)</span>
                            <span class="text-[8px] bg-slate-200 text-slate-500 px-2 py-0.5 rounded-full">Opsional</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-sm">Rp</span>
                            <input type="text" 
                                   :value="customHppInput"
                                   @input="customHppInput = formatInput($event.target.value); customHppInputRaw = parseInput($event.target.value)"
                                   class="w-full bg-slate-50 border-2 border-slate-100 rounded-xl pl-10 pr-4 py-3 text-lg font-bold text-slate-700 outline-none focus:border-orange-500 focus:bg-white transition-all"
                                   placeholder="0">
                        </div>
                    </div>

                    {{-- Estimasi Profit Preview --}}
                    <div class="bg-orange-50 border border-orange-100 rounded-xl p-3 flex justify-between items-center">
                        <span class="text-[10px] font-black text-orange-600 uppercase">Estimasi Profit / Item</span>
                        <span class="text-sm font-black text-orange-700" x-text="formatRp(customPriceInputRaw - (customPriceAllowHPP ? customHppInputRaw : (customPriceProduct ? customPriceProduct.cost_price : 0)))"></span>
                    </div>

                    {{-- Input Alasan --}}
                    <div>
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 flex items-center justify-between">
                            <span>Alasan Perubahan</span>
                            <span class="text-[8px] px-2 py-0.5 rounded-full" :class="customPriceRequireReason ? 'bg-red-100 text-red-600' : 'bg-slate-200 text-slate-500'" x-text="customPriceRequireReason ? 'Wajib' : 'Opsional'"></span>
                        </label>
                        <textarea x-model="customPriceReason" rows="2"
                                  class="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-5 py-3 text-sm font-bold text-slate-700 outline-none focus:border-orange-500 transition-all resize-none"
                                  placeholder="Tulis alasan ubah harga..."></textarea>
                    </div>

                    {{-- Error Message --}}
                    <template x-if="customPriceError">
                        <div class="bg-red-50 border border-red-100 text-red-500 p-3 rounded-xl text-[10px] font-bold flex items-center gap-2">
                            <i class="fas fa-exclamation-circle"></i>
                            <span x-text="customPriceError"></span>
                        </div>
                    </template>

                    {{-- Button --}}
                    <button @click="applyCustomPrice()" 
                            class="w-full bg-orange-500 hover:bg-orange-600 text-white font-black py-4 rounded-2xl transition-all shadow-xl shadow-orange-500/20 flex items-center justify-center gap-3 text-sm uppercase tracking-widest active:scale-95">
                        <i class="fas fa-check-circle"></i> Terapkan Harga
                    </button>
                </div>
            </div>
        </div>
    </template>

    {{-- MODAL CASH OUT (MODERNIZED) --}}
    <template x-teleport="body">
        <div x-show="showCashOutModal" x-transition x-cloak class="fixed inset-0 bg-slate-900/80 backdrop-blur-md z-[200] flex items-center justify-center p-4">
            <div @click.away="showCashOutModal = false" class="bg-white rounded-[2.5rem] w-full max-w-sm shadow-2xl border border-slate-200 overflow-hidden max-h-[90vh] overflow-y-auto scrollbar-hide ">
                {{-- Header --}}
                <div class="bg-gradient-to-r from-orange-500 to-amber-600 p-8 text-white relative">
                    <div class="absolute top-0 right-0 p-8 opacity-10"><i class="fas fa-cash-register text-7xl"></i></div>
                    <div class="relative z-10 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center backdrop-blur-md">
                                <i class="fas fa-cash-register text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-black">Cash Out</h3>
                                <p class="text-[10px] font-bold text-orange-100 uppercase tracking-widest mt-0.5 opacity-80" x-text="cashOutSource === 'bank' ? 'Ambil dari Saldo Bank' : 'Ambil dari laci kasir'"></p>
                            </div>
                        </div>
                        <button @click="showCashOutModal = false" class="w-10 h-10 bg-white/10 hover:bg-white/20 rounded-xl flex items-center justify-center transition-all">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <div class="p-8 space-y-6">
                    {{-- Nominal --}}
                    <div>
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 block">Jumlah Penarikan (Rp)</label>
                        <div class="relative">
                            <span class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 font-black text-lg">Rp</span>
                            <input type="text" 
                                   :value="cashOutAmountDisplay"
                                   @input="cashOutAmountDisplay = formatInput($event.target.value); cashOutAmount = parseInput($event.target.value)"
                                   class="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl pl-14 pr-5 py-4 text-2xl font-black text-slate-800 outline-none focus:border-orange-500 focus:bg-white transition-all shadow-inner"
                                   placeholder="0">
                        </div>
                    </div>

                    {{-- Source Selection --}}
                    <div x-show="cashOutAccessSetting === 'both'" class="mt-4">
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 block">Sumber Dana</label>
                        <select x-model="cashOutSource" 
                                class="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-5 py-3 text-sm font-bold text-slate-700 outline-none focus:border-orange-500 transition-all">
                            <option value="cash">Tunai (Laci Kasir)</option>
                            <option value="bank">Saldo Bank</option>
                        </select>
                    </div>

                    {{-- Main Category --}}
                    <div>
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 block">Jenis Pengeluaran</label>
                        <div class="grid grid-cols-2 gap-2">
                            <template x-for="cat in ['operasional', 'consumable', 'bahan_baku', 'variabel']">
                                <button @click="cashOutMainCategory = cat; cashOutSubCategory = ''"
                                        :class="cashOutMainCategory === cat ? 'bg-orange-500 text-white border-orange-500 shadow-lg shadow-orange-500/20' : 'bg-slate-50 text-slate-500 border-slate-100 hover:border-orange-200'"
                                        class="py-2.5 px-3 border-2 rounded-xl text-[10px] font-black transition-all flex items-center justify-center uppercase tracking-tighter">
                                    <span x-text="cat.replace('_', ' ').toUpperCase()"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- Sub Category (Item Selection) --}}
                    <div x-show="cashOutMainCategory" x-transition>
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 block">Pilih Rincian Biaya</label>
                        <select x-model="cashOutSubCategory" 
                                class="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-5 py-3 text-sm font-bold text-slate-700 outline-none focus:border-orange-500 transition-all">
                            <option value="">-- Pilih --</option>
                            <template x-for="item in availableSubCategories" :key="item.id">
                                <option :value="item.name" x-text="item.name"></option>
                            </template>
                        </select>
                    </div>

                    {{-- Notes (Optional) --}}
                    <div x-show="cashOutSubCategory" x-transition>
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 block">Catatan Tambahan (Opsional)</label>
                        <input type="text" x-model="cashOutDesc"
                               class="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-5 py-3 text-sm font-bold text-slate-700 outline-none focus:border-orange-500 transition-all"
                               placeholder="Misal: Untuk 2 Galon, dll...">
                    </div>

                    <template x-if="cashOutError">
                        <div class="bg-red-50 border border-red-100 text-red-500 p-3 rounded-xl text-[10px] font-bold flex items-center gap-2">
                            <i class="fas fa-exclamation-circle"></i>
                            <span x-text="cashOutError"></span>
                        </div>
                    </template>

                    <button @click="submitCashOut()" 
                            :disabled="isProcessing || !cashOutAmount || !cashOutSubCategory"
                            class="w-full bg-slate-800 hover:bg-slate-900 text-white font-black py-4 rounded-2xl transition-all shadow-xl shadow-slate-900/20 flex items-center justify-center gap-3 text-sm uppercase tracking-widest disabled:opacity-50 active:scale-95">
                        <i x-show="!isProcessing" class="fas fa-check-circle"></i>
                        <i x-show="isProcessing" class="fas fa-spinner fa-spin"></i>
                        <span x-text="isProcessing ? 'Memproses...' : 'Konfirmasi Cash Out'"></span>
                    </button>
                </div>
            </div>
        </div>
    </template>

    {{-- MODAL PENGATURAN PRINTER (PREMIUM UI) --}}
    <template x-teleport="body">
        <div x-show="showPrinterSettings" x-transition x-cloak class="fixed inset-0 bg-slate-900/80 backdrop-blur-md z-[201] flex items-center justify-center p-4">
            <div @click.away="showPrinterSettings = false" class="bg-white rounded-[2.5rem] w-full max-w-lg shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[90vh] max-h-[90vh] overflow-y-auto scrollbar-hide ">
                
                {{-- Header --}}
                <div class="p-8 border-b border-slate-100 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center shadow-inner">
                            <i class="fas fa-print text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-slate-800">Pengaturan Printer</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Hubungkan & sesuaikan tampilan struk</p>
                        </div>
                    </div>
                    <button @click="showPrinterSettings = false" class="w-10 h-10 bg-slate-50 hover:bg-slate-100 text-slate-400 rounded-xl flex items-center justify-center transition-all">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                {{-- Tab Switcher --}}
                <div class="px-8 pt-4 pb-0 shrink-0">
                    <div class="flex bg-slate-50 p-1.5 rounded-2xl border border-slate-100">
                        <button @click="printerTab = 'connection'" 
                                :class="printerTab === 'connection' ? 'bg-white text-indigo-600 shadow-sm border border-slate-100' : 'text-slate-400 hover:text-slate-600'"
                                class="flex-1 py-3 rounded-xl text-xs font-black uppercase tracking-widest transition-all flex items-center justify-center gap-2">
                            <i class="fas fa-plug-circle-bolt text-[10px]"></i> Koneksi
                        </button>
                        <button @click="printerTab = 'preview'" 
                                :class="printerTab === 'preview' ? 'bg-white text-indigo-600 shadow-sm border border-slate-100' : 'text-slate-400 hover:text-slate-600'"
                                class="flex-1 py-3 rounded-xl text-xs font-black uppercase tracking-widest transition-all flex items-center justify-center gap-2">
                            <i class="fas fa-receipt text-[10px]"></i> Preview Struk
                        </button>
                    </div>
                </div>

                {{-- Content Area --}}
                <div class="p-8 overflow-y-auto custom-scrollbar flex-1">
                    
                    {{-- TAB 1: KONEKSI --}}
                    <div x-show="printerTab === 'connection'" x-transition class="space-y-6">
                        
                        {{-- Status Card --}}
                        <div :class="printerStatus === 'connected' ? 'bg-emerald-50 border-emerald-100' : 'bg-slate-50 border-slate-100'" 
                             class="p-5 rounded-3xl border-2 flex items-center justify-between transition-all">
                            <div class="flex items-center gap-3">
                                <div :class="printerStatus === 'connected' ? 'bg-emerald-500' : 'bg-slate-300'" class="w-3 h-3 rounded-full animate-pulse shadow-sm"></div>
                                <div>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Status Printer</p>
                                    <p :class="printerStatus === 'connected' ? 'text-emerald-700' : 'text-slate-600'" class="text-sm font-black" x-text="printerStatus === 'connected' ? 'Printer Connected: ' + printerName : 'Tidak Terhubung'"></p>
                                </div>
                            </div>
                            <button x-show="printerStatus === 'connected'" @click="disconnectPrinter()" class="text-[10px] font-black text-red-500 uppercase hover:underline">Putuskan</button>
                        </div>

                        {{-- Paper Size --}}
                        <div>
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3 block">Lebar Kertas</label>
                            <div class="flex bg-slate-50 p-1 rounded-2xl border border-slate-100">
                                <button @click="paperSize = '58mm'; savePrinterSettings()" :class="paperSize === '58mm' ? 'bg-slate-800 text-white shadow-lg' : 'text-slate-400'" class="flex-1 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">58mm (32 kolom)</button>
                                <button @click="paperSize = '80mm'; savePrinterSettings()" :class="paperSize === '80mm' ? 'bg-slate-800 text-white shadow-lg' : 'text-slate-400'" class="flex-1 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">80mm (42 kolom)</button>
                            </div>
                        </div>

                        {{-- Font Small Toggle --}}
                        <div class="flex items-center justify-between p-5 bg-slate-50 border border-slate-100 rounded-2xl">
                            <div>
                                <p class="text-[11px] font-black text-slate-800 uppercase tracking-widest leading-none mb-1">Font Kecil (Font B)</p>
                                <p class="text-[10px] text-slate-400 font-medium">Aktifkan jika teks hilang/terpotong saat print</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" x-model="fontSmall" @change="savePrinterSettings()" class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                            </label>
                        </div>

                        {{-- Connection Methods --}}
                        <div x-show="!isScanning && discoveredDevices.length === 0">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3 block">Pilih Koneksi</label>
                            <div class="space-y-3">
                                <button @click="scanDevices('bluetooth')" class="w-full p-4 bg-white border-2 border-slate-100 rounded-3xl flex items-center gap-4 hover:border-indigo-500 hover:bg-indigo-50/30 transition-all group">
                                    <div class="w-12 h-12 bg-indigo-50 text-indigo-500 rounded-2xl flex items-center justify-center shadow-inner group-hover:bg-indigo-500 group-hover:text-white transition-all">
                                        <i class="fab fa-bluetooth-b text-xl"></i>
                                    </div>
                                    <div class="text-left flex-1">
                                        <p class="text-sm font-black text-slate-800">Bluetooth Printer</p>
                                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-tight">Cari perangkat bluetooth terdekat</p>
                                    </div>
                                    <i class="fas fa-chevron-right text-slate-300 group-hover:text-indigo-500 transition-all"></i>
                                </button>

                                <button @click="connectionMethod = 'server_escpos'; printerStatus = 'connected'; printerName = 'Server Side ESC/POS'; savePrinterSettings()" class="w-full p-4 bg-white border-2 border-slate-100 rounded-3xl flex items-center gap-4 hover:border-indigo-500 hover:bg-indigo-50/30 transition-all group">
                                    <div class="w-12 h-12 bg-indigo-50 text-indigo-500 rounded-2xl flex items-center justify-center shadow-inner group-hover:bg-indigo-500 group-hover:text-white transition-all">
                                        <i class="fas fa-server text-xl"></i>
                                    </div>
                                    <div class="text-left flex-1">
                                        <p class="text-sm font-black text-slate-800">Server Side ESC/POS</p>
                                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-tight">Gunakan Driver Server (Drawer RJ11)</p>
                                    </div>
                                    <i class="fas fa-chevron-right text-slate-300 group-hover:text-indigo-500 transition-all"></i>
                                </button>
                                <button @click="scanDevices('usb_serial')" class="w-full p-4 bg-white border-2 border-slate-100 rounded-3xl flex items-center gap-4 hover:border-indigo-500 hover:bg-indigo-50/30 transition-all group">
                                    <div class="w-10 h-10 bg-purple-50 text-purple-500 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform"><i class="fas fa-microchip text-lg"></i></div>
                                    <div class="text-left flex-1">
                                        <p class="text-sm font-black text-slate-800">USB (Serial)</p>
                                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-tight">Printer via kabel USB â€” Pilih COM Port</p>
                                    </div>
                                    <i class="fas fa-chevron-right text-slate-300 group-hover:text-indigo-500"></i>
                                </button>
                                <button @click="scanDevices('usb_direct')" class="w-full p-4 bg-white border-2 border-slate-100 rounded-3xl flex items-center gap-4 hover:border-indigo-500 hover:bg-indigo-50/30 transition-all group">
                                    <div class="w-10 h-10 bg-emerald-50 text-emerald-500 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform"><i class="fas fa-usb text-lg"></i></div>
                                    <div class="text-left flex-1">
                                        <p class="text-sm font-black text-slate-800">USB (WebUSB)</p>
                                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-tight">Deteksi printer USB langsung (Rekomendasi)</p>
                                    </div>
                                    <i class="fas fa-chevron-right text-slate-300 group-hover:text-indigo-500"></i>
                                </button>
                                <button @click="useBrowserDefault()" class="w-full p-4 bg-white border-2 border-slate-100 rounded-3xl flex items-center gap-4 hover:border-indigo-500 hover:bg-indigo-50/30 transition-all group">
                                    <div class="w-10 h-10 bg-slate-50 text-slate-500 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform"><i class="fas fa-window-maximize text-lg"></i></div>
                                    <div class="text-left flex-1">
                                        <p class="text-sm font-black text-slate-800">Browser Default</p>
                                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-tight">Gunakan dialog print bawaan Windows/Browser</p>
                                    </div>
                                    <i class="fas fa-chevron-right text-slate-300 group-hover:text-indigo-500"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Scanning / Device List --}}
                        <div x-show="isScanning || discoveredDevices.length > 0" x-transition>
                            <div class="flex items-center justify-between mb-4">
                                <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest" x-text="isScanning ? 'Mencari Perangkat...' : 'Perangkat Ditemukan'"></label>
                                <button @click="discoveredDevices = []; isScanning = false" class="text-[10px] font-black text-indigo-600 uppercase">Kembali</button>
                            </div>

                            <div x-show="isScanning" class="py-12 flex flex-col items-center justify-center space-y-4">
                                <div class="w-12 h-12 border-4 border-indigo-100 border-t-indigo-600 rounded-full animate-spin"></div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Harap Tunggu...</p>
                            </div>

                            <div x-show="!isScanning && discoveredDevices.length > 0" class="space-y-2">
                                <template x-for="device in discoveredDevices" :key="device.id">
                                    <button @click="connectDevice(device)" class="w-full p-4 bg-slate-50 border-2 border-slate-100 rounded-2xl text-left flex items-center justify-between hover:border-indigo-500 group transition-all">
                                        <div>
                                            <p class="text-sm font-black text-slate-800" x-text="device.name"></p>
                                            <p class="text-[10px] text-slate-400 font-bold" x-text="device.id"></p>
                                        </div>
                                        <span class="text-[10px] font-black text-indigo-600 uppercase opacity-0 group-hover:opacity-100 transition-opacity">Hubungkan</span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        {{-- Help Card --}}
                        <div class="bg-indigo-50/50 rounded-[2rem] p-6 border border-indigo-100">
                            <div class="flex items-center gap-3 mb-4 text-indigo-600">
                                <i class="fas fa-info-circle text-lg"></i>
                                <h4 class="text-xs font-black uppercase tracking-widest">Cara Kerja</h4>
                            </div>
                            <ol class="space-y-3 text-[11px] text-slate-600 font-medium leading-relaxed">
                                <li class="flex gap-2"><span class="w-4 h-4 rounded-full bg-indigo-600 text-white flex items-center justify-center shrink-0 text-[8px] font-black mt-0.5">1</span> Nyalakan printer thermal & aktifkan Bluetooth/USB.</li>
                                <li class="flex gap-2"><span class="w-4 h-4 rounded-full bg-indigo-600 text-white flex items-center justify-center shrink-0 text-[8px] font-black mt-0.5">2</span> Klik tombol koneksi di atas & pilih printer dari daftar.</li>
                                <li class="flex gap-2"><span class="w-4 h-4 rounded-full bg-indigo-600 text-white flex items-center justify-center shrink-0 text-[8px] font-black mt-0.5">3</span> Klik "Test Print" untuk mencoba mencetak.</li>
                            </ol>
                        </div>

                        <button @click="testPrint()" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-4 rounded-2xl font-black uppercase tracking-widest text-xs transition-all shadow-xl shadow-indigo-600/20 flex items-center justify-center gap-3">
                            <i class="fas fa-file-invoice"></i> Test Print
                        </button>

                    </div>

                    {{-- TAB 2: PREVIEW STRUK --}}
                    <div x-show="printerTab === 'preview'" x-transition class="flex gap-8 items-start">
                        
                        {{-- Controls --}}
                        <div class="w-44 shrink-0 space-y-6">
                            <div>
                                <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3 block">Lebar Kertas</label>
                                <div class="flex flex-col gap-2">
                                    <button @click="paperSize = '58mm'; savePrinterSettings()" :class="paperSize === '58mm' ? 'bg-slate-800 text-white shadow-lg' : 'bg-slate-50 text-slate-400'" class="w-full py-3 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">58mm</button>
                                    <button @click="paperSize = '80mm'; savePrinterSettings()" :class="paperSize === '80mm' ? 'bg-slate-800 text-white shadow-lg' : 'bg-slate-50 text-slate-400'" class="w-full py-3 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">80mm</button>
                                </div>
                            </div>

                            <div>
                                <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3 block">Ukuran Font</label>
                                <div class="flex flex-col gap-2">
                                    <button @click="fontSize = 'small'; savePrinterSettings()" :class="fontSize === 'small' ? 'bg-indigo-600 text-white shadow-lg' : 'bg-slate-50 text-slate-400'" class="w-full py-3 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">Kecil</button>
                                    <button @click="fontSize = 'medium'; savePrinterSettings()" :class="fontSize === 'medium' ? 'bg-indigo-600 text-white shadow-lg' : 'bg-slate-50 text-slate-400'" class="w-full py-3 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">Sedang</button>
                                    <button @click="fontSize = 'large'; savePrinterSettings()" :class="fontSize === 'large' ? 'bg-indigo-600 text-white shadow-lg' : 'bg-slate-50 text-slate-400'" class="w-full py-3 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">Besar</button>
                                </div>
                            </div>

                            <div class="p-4 bg-amber-50 border border-amber-100 rounded-2xl">
                                <p class="text-[9px] font-bold text-amber-700 leading-relaxed uppercase tracking-widest">Catatan: <span class="normal-case tracking-normal text-amber-600">Ukuran font hanya mempengaruhi tampilan di layar. Printer akan menggunakan font bawaan hardware-nya sendiri.</span></p>
                            </div>
                        </div>

                        {{-- Receipt Live Preview --}}
                        <div class="flex-1 bg-slate-900/5 rounded-3xl p-6 flex flex-col items-center">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Preview Struk</label>
                            
                            {{-- Paper Simulation --}}
                            <div :style="paperSize === '58mm' ? 'width: 240px;' : 'width: 320px;'" 
                                 class="bg-white shadow-[0_20px_50px_rgba(0,0,0,0.1)] p-6 min-h-[400px] transition-all flex flex-col border border-slate-100 font-mono">
                                
                                <div :class="fontSize === 'small' ? 'text-[9px]' : (fontSize === 'large' ? 'text-[13px]' : 'text-[11px]')" 
                                     class="text-slate-900 space-y-1">
                                    
                                    <div class="text-center space-y-0.5 mb-4">
                                        <p class="font-black text-base tracking-tight" x-text="business.name"></p>
                                        <p class="opacity-70" x-text="business.address"></p>
                                        <p class="opacity-70" x-text="business.phone"></p>
                                    </div>

                                    <div class="border-b border-dashed border-slate-300 py-1 flex justify-between">
                                        <div class="space-y-0.5">
                                            <p>No.Order: POS-177004386</p>
                                            <p>Tanggal : 08/05/2026</p>
                                        </div>
                                        <div class="text-right space-y-0.5">
                                            <p>Kasir: Owner</p>
                                            <p>Meja : -</p>
                                        </div>
                                    </div>

                                    <div class="py-2 space-y-2">
                                        <div class="flex justify-between items-start">
                                            <div class="flex-1">
                                                <p class="font-bold">Kopi Susu Gula Jawa</p>
                                                <p class="opacity-60">1 x 18.000</p>
                                            </div>
                                            <p class="font-bold">18.000</p>
                                        </div>
                                        <div class="flex justify-between items-start">
                                            <div class="flex-1">
                                                <p class="font-bold">Sambel Ayam</p>
                                                <p class="opacity-60">1 x 15.000</p>
                                            </div>
                                            <p class="font-bold">15.000</p>
                                        </div>
                                        <div class="flex justify-between items-start">
                                            <div class="flex-1">
                                                <p class="font-bold">Nasi Goreng Kambing</p>
                                                <p class="opacity-60">1 x 38.000</p>
                                            </div>
                                            <p class="font-bold">38.000</p>
                                        </div>
                                    </div>

                                    <div class="border-t border-dashed border-slate-300 pt-2 space-y-1">
                                        <div class="flex justify-between">
                                            <p>Subtotal</p>
                                            <p>71.000</p>
                                        </div>
                                        <div class="flex justify-between">
                                            <p>Pajak (0%)</p>
                                            <p>0</p>
                                        </div>
                                        <div class="flex justify-between font-black text-base py-1">
                                            <p>TOTAL</p>
                                            <p>71.000</p>
                                        </div>
                                    </div>

                                    <div class="border-t border-dashed border-slate-300 pt-2 space-y-1">
                                        <div class="flex justify-between">
                                            <p>Metode</p>
                                            <p>Tunai</p>
                                        </div>
                                        <div class="flex justify-between">
                                            <p>Bayar</p>
                                            <p>100.000</p>
                                        </div>
                                        <div class="flex justify-between font-bold">
                                            <p>Kembali</p>
                                            <p>29.000</p>
                                        </div>
                                    </div>

                                    <div class="text-center pt-8 opacity-60">
                                        <p>Terima kasih atas kunjungan Anda!</p>
                                        <p>www.monoframestudio.id</p>
                                        <p>Powered by Monoframe POS</p>
                                    </div>

                                </div>
                            </div>
                            
                            <p class="text-[10px] text-slate-400 font-bold mt-4" x-text="paperSize + ' â€” font ' + fontSize"></p>
                        </div>
                    </div>
                </div>

                {{-- Auto Print Toggle (Bottom Bar) --}}
                <div class="px-8 py-4 bg-slate-50 border-t border-slate-100 shrink-0">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-indigo-600 shadow-sm border border-slate-200">
                                <i class="fas fa-bolt"></i>
                            </div>
                            <div>
                                <p class="text-[11px] font-black text-slate-800 uppercase tracking-widest leading-none mb-1">Auto Print</p>
                                <p class="text-[10px] text-slate-400 font-medium">Cetak struk otomatis setelah checkout</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" x-model="autoPrint" @change="savePrinterSettings()" class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                        </label>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="p-8 border-t border-slate-100 shrink-0">
                    <button @click="savePrinterSettings(); showPrinterSettings = false" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-4 rounded-2xl font-black uppercase tracking-widest text-xs transition-all shadow-lg shadow-indigo-500/20 flex items-center justify-center gap-2">
                        <i class="fas fa-check-circle"></i> Simpan & Tutup
                    </button>
                </div>

            </div>
        </div>
    </template>

    <iframe id="print-iframe" style="display:none;"></iframe>

<script>
function openTutupShift() {
    document.getElementById('modal-tutup-shift').classList.remove('hidden');
    setTimeout(() => document.getElementById('closing_cash_display')?.focus(), 100);
}

function formatTutupShift(input) {
    let raw = input.value.replace(/\D/g, '');
    let formatted = raw ? parseInt(raw, 10).toLocaleString('id-ID') : '';
    input.value = formatted;
    document.getElementById('closing_cash_raw').value = raw || '';

    // Calculate Diff Real-time
    const container = document.getElementById('expected-cash-container');
    const diffEl = document.getElementById('shift-diff-note');
    if (!container || !diffEl) return;

    const expected = parseInt(container.dataset.expected || 0);
    const actual = parseInt(raw || 0);
    const diff = actual - expected;

    if (raw) {
        let diffFmt = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(Math.abs(diff));
        if (diff === 0) {
            diffEl.innerHTML = '<i class="fas fa-check-circle text-emerald-500 mr-1"></i> <span class="text-emerald-600 font-bold">Uang pas (Sesuai)</span>';
        } else if (diff > 0) {
            diffEl.innerHTML = '<i class="fas fa-plus-circle text-blue-500 mr-1"></i> <span class="text-blue-600 font-bold">Selisih Lebih: ' + diffFmt + '</span>';
        } else {
            diffEl.innerHTML = '<i class="fas fa-minus-circle text-red-500 mr-1"></i> <span class="text-red-600 font-bold">Selisih Kurang: ' + diffFmt + '</span>';
        }
    } else {
        diffEl.innerHTML = '<i class="fas fa-info-circle text-blue-400 mr-1"></i> Selisih akan dihitung otomatis setelah menutup shift.';
    }
}

// ---- Cash Out helpers ----
function formatCashOutAmount(input) {
    // Ambil hanya digit
    let raw = input.value.replace(/\D/g, '');
    // Format dengan titik ribuan (ID)
    let formatted = raw ? parseInt(raw, 10).toLocaleString('id-ID') : '';
    input.value = formatted;
    // Simpan nilai numerik ke hidden field
    document.getElementById('cashout_amount_raw').value = raw || '';
}

function prepCashOut(form) {
    const raw = document.getElementById('cashout_amount_raw').value;
    const errEl = document.getElementById('cashout_amount_error');
    if (!raw || parseInt(raw, 10) < 1) {
        errEl.classList.remove('hidden');
        document.getElementById('cashout_amount_display').focus();
        return false;
    }
    errEl.classList.add('hidden');
    return true;
}

function closeCashOut() {
    document.getElementById('modal-cashout').classList.add('hidden');
    // Reset form
    document.getElementById('cashout_amount_display').value = '';
    document.getElementById('cashout_amount_raw').value = '';
    document.getElementById('cashout_desc').value = '';
    document.getElementById('cashout_category').value = '';
    document.getElementById('cashout_amount_error').classList.add('hidden');
}
</script>

@endif

@push('scripts')
<script>
    function registerPosApp() {
        if (window.posAppInitialized) return;
        console.log("Registering posApp data...");
        
        Alpine.data('posApp', () => ({
            // Init Data Backend
            categories: @json($categories),
            products: @json($products),
            promoProductIds: @json($promoProductIds),
            bestSellerProductIds: @json($bestSellerProductIds),
            activeShift: {{ $activeShift ? 'true' : 'false' }},
            taxRate: parseFloat('{{ $settings["tax_rate"] ?? 0 }}') || 0,
            rawMethods: @json(json_decode($settings['active_payment_methods'] ?? '["cash"]', true) ?: ['cash']),
            deliveryPresets: @json(json_decode($settings['delivery_presets'] ?? '[]', true) ?: []),
            
            // Custom Price Settings
            customPriceEnabled: {{ ($settings['custom_price_enabled'] ?? '0') == '1' ? 'true' : 'false' }},
            customPriceAllowHPP: {{ ($settings['custom_price_allow_hpp'] ?? '0') == '1' ? 'true' : 'false' }},
            customPriceShowBadge: {{ ($settings['custom_price_show_badge'] ?? '1') == '1' ? 'true' : 'false' }},
            customPriceRequireReason: {{ ($settings['custom_price_require_reason'] ?? '0') == '1' ? 'true' : 'false' }},
            customPriceAccess: '{{ $settings['custom_price_access'] ?? "all" }}',
            
            // BEP Analysis Data
            totalCapital: {{ $totalCapital ?? 0 }},
            monthlyRevenue: {{ $monthlyRevenue ?? 0 }},

            get bepMonths() {
                if (this.monthlyRevenue <= 0) return 'âˆž';
                let months = this.totalCapital / this.monthlyRevenue;
                return isFinite(months) ? Math.ceil(months) : 'âˆž';
            },

            // Multi-Worksheet Logic
            worksheets: [],
            activeTabId: null,

            // UI States
            searchQuery: '',
            activeCategory: '',
            viewMode: 'grid',
            cartView: 'active',
            showGroupManagerModal: false,
            posGroups: @json($posGroups),
            // Drag & Drop Layout Editor States
            draftGroups: [],
            draftUngrouped: [],
            draggedProduct: null,
            draggedFrom: null,
            dragOverGroup: null,
            dragOverIndex: null,
            isSavingGroup: false,
            showPaymentModal: false,
            showReceiptModal: false,
            showAddGroupModal: false,
            showExpenseModal: false,
            expenseDesc: '',
            expenseAmount: 0,
            expenseCategory: 'Pengeluaran Kasir',
            expenseError: '',
            newGroupName: '',
            newGroupError: '',
            lastAddedId: null,
            // Transaction & Payment States
            isProcessing: false,
            paymentTiming: 'now', // 'now' | 'later'
            paymentMethod: 'cash', // 'cash' | 'transfer' | 'qris' | 'debit'
            paidAmount: 0,
            paidAmountDisplay: '',
            dpAmount: 0,
            dpAmountDisplay: '',
            receiptData: null,
            
            // Printer Settings State
            showPrinterSettings: false,
            printerTab: 'connection', // 'connection' | 'preview'
            printerStatus: 'disconnected', // 'connected' | 'disconnected'
            paperSize: '58mm', // '58mm' | '80mm'
            fontSmall: false,
            fontSize: 'medium', // 'small' | 'medium' | 'large'
            autoPrint: false,
            connectionMethod: null, // 'bluetooth' | 'usb_serial' | 'usb_direct'
            printerName: '',
            printerHandle: null,
            printerFeedLines: {{ $settings['printer_feed_lines'] ?? 0 }},
            isScanning: false,
            discoveredDevices: [],
            
            // Cash Out States
            showCashOutModal: false,
            cashOutMainCategory: '',
            cashOutSubCategory: '',
            cashOutAmount: 0,
            cashOutAmountDisplay: '',
            cashOutDesc: '',
            cashOutSource: 'cash',
            cashOutAccessSetting: '{{ $settings["cashout_source_access"] ?? "cash_only" }}',
            cashOutRoleAccess: '{{ $settings["cashout_role_access"] ?? "all" }}',
            cashOutError: '',
            expenseCategories: @json($expenseCategories),

            // Custom Price Modal States
            showCustomPriceModal: false,
            customPriceProduct: null,
            customPriceInput: '',
            customPriceInputRaw: 0,
            customHppInput: '',
            customHppInputRaw: 0,
            customPriceReason: '',
            customPriceError: '',

            openCustomPrice(product) {
                if(!this.customPriceEnabled) return;
                // Check access
                let userRole = '{{ auth()->user()->role ?? "cashier" }}';
                if(this.customPriceAccess === 'owner' && userRole !== 'owner') {
                    Toast.fire({ icon: 'error', title: 'Akses ditolak. Hanya owner yang bisa ubah harga.' });
                    return;
                }
                if(this.customPriceAccess === 'admin_owner' && !['admin', 'owner'].includes(userRole)) {
                    Toast.fire({ icon: 'error', title: 'Akses ditolak. Hanya admin/owner yang bisa ubah harga.' });
                    return;
                }
                
                this.customPriceProduct = product;
                this.customPriceInputRaw = product.is_promo && product.discount_price > 0 ? parseFloat(product.discount_price) : parseFloat(product.price);
                this.customPriceInput = this.formatInput(this.customPriceInputRaw);
                this.customHppInputRaw = parseFloat(product.cost_price || 0);
                this.customHppInput = this.formatInput(this.customHppInputRaw);
                this.customPriceReason = '';
                this.customPriceError = '';
                this.showCustomPriceModal = true;
            },

            applyCustomPrice() {
                if(this.customPriceInputRaw < 0) {
                    this.customPriceError = 'Harga tidak boleh negatif';
                    return;
                }
                if(this.customPriceAllowHPP && this.customHppInputRaw > this.customPriceInputRaw) {
                    this.customPriceError = 'Modal (HPP) tidak boleh lebih besar dari harga jual';
                    return;
                }
                if(this.customPriceRequireReason && this.customPriceReason.trim() === '') {
                    this.customPriceError = 'Alasan wajib diisi';
                    return;
                }

                // Add to cart with custom price
                let w = this.activeWorksheet;
                let product = this.customPriceProduct;
                let isUnlimited = !!(product.is_stockless);
                
                // For custom price, we always add as a separate line item if reason/price differs
                // Or we can just add a new item object
                if(isUnlimited || product.stock > 0) {
                    w.cart.push({
                        product_id: product.id,
                        name: product.name,
                        price: product.price, // original price
                        quantity: 1,
                        stock: product.stock,
                        discount: 0,
                        is_stockless: isUnlimited,
                        is_custom_price: true,
                        custom_price: this.customPriceInputRaw,
                        custom_hpp: this.customPriceAllowHPP ? this.customHppInputRaw : null,
                        custom_price_reason: this.customPriceReason.trim(),
                    });
                    
                    // Reduce stock temporarily in view? We already handle that globally by checking total cart qty
                    Toast.fire({ icon: 'success', title: 'Produk ditambahkan dengan harga khusus' });
                    this.showCustomPriceModal = false;
                } else {
                    Toast.fire({ icon: 'error', title: 'Stok produk ini sudah habis!' });
                }
            },

            openCashOut() {
                // Check role access
                let userRole = '{{ auth()->user()->role ?? "cashier" }}';
                if(this.cashOutRoleAccess === 'owner' && userRole !== 'owner') {
                    Toast.fire({ icon: 'error', title: 'Akses ditolak. Hanya owner yang bisa melakukan Cash Out.' });
                    return;
                }
                if(this.cashOutRoleAccess === 'admin_owner' && !['admin', 'owner'].includes(userRole)) {
                    Toast.fire({ icon: 'error', title: 'Akses ditolak. Hanya admin/owner yang bisa melakukan Cash Out.' });
                    return;
                }

                this.showCashOutModal = true;
                this.cashOutMainCategory = '';
                this.cashOutSubCategory = '';
                this.cashOutAmount = 0;
                this.cashOutAmountDisplay = '';
                this.cashOutDesc = '';
                this.cashOutError = '';
                
                if (this.cashOutAccessSetting === 'bank_only') {
                    this.cashOutSource = 'bank';
                } else {
                    this.cashOutSource = 'cash';
                }
            },

            get availableSubCategories() {
                if (!this.cashOutMainCategory) return [];
                let items = this.expenseCategories[this.cashOutMainCategory] || [];
                return [...items].sort((a, b) => a.name.localeCompare(b.name));
            },

            async submitCashOut() {
                if (!this.cashOutAmount || this.cashOutAmount < 1) {
                    this.cashOutError = 'Nominal wajib diisi!';
                    return;
                }
                if (!this.cashOutMainCategory) {
                    this.cashOutError = 'Pilih kategori utama!';
                    return;
                }
                if (!this.cashOutSubCategory) {
                    this.cashOutError = 'Pilih rincian biaya!';
                    return;
                }

                this.isProcessing = true;
                this.cashOutError = '';
                
                try {
                    let res = await fetch('{{ route("shifts.cashout", $activeShift->id ?? 0) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            amount: this.cashOutAmount,
                            description: this.cashOutSubCategory + (this.cashOutDesc ? ' (' + this.cashOutDesc + ')' : ''),
                            category: this.cashOutMainCategory,
                            source: this.cashOutSource
                        })
                    });
                    
                    const contentType = res.headers.get("content-type");
                    if (contentType && contentType.indexOf("application/json") !== -1) {
                        let data = await res.json();
                        if (data.success) {
                            this.showCashOutModal = false;
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: 'Cash Out berhasil dicatat ke sistem.',
                                background: '#1e293b',
                                color: '#f8fafc',
                                confirmButtonColor: '#f97316',
                                customClass: {
                                    popup: 'rounded-[2.5rem] border border-emerald-500/20 shadow-2xl shadow-emerald-500/10',
                                    title: 'text-2xl font-black tracking-tight',
                                    htmlContainer: 'text-slate-400 font-medium'
                                }
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            this.cashOutError = data.error || data.message || 'Terjadi kesalahan pada server.';
                        }
                    } else {
                        // Jika bukan JSON (kemungkinan 500 HTML Error)
                        console.error("Server Error:", await res.text());
                        this.cashOutError = 'Server error (500). Silakan hubungi admin atau cek log.';
                    }
                } catch (e) {
                    console.error("Network Error:", e);
                    this.cashOutError = 'Koneksi gagal atau terjadi kesalahan jaringan.';
                } finally {
                    this.isProcessing = false;
                }
            },

            // Helper format input rupiah
            formatInput(val) {
                if (!val) return '';
                let num = val.toString().replace(/[^0-9]/g, '');
                if (!num) return '';
                return new Intl.NumberFormat('id-ID').format(parseInt(num));
            },

            parseInput(val) {
                if (!val) return 0;
                return parseInt(val.toString().replace(/[^0-9]/g, '')) || 0;
            },

            get payNowMethods() {
                const labels = { cash: 'Tunai', transfer: 'Transfer', qris: 'QRIS', debit: 'Debit' };
                return this.rawMethods.filter(m => m !== 'piutang').map(m => ({ id: m, label: labels[m] || m }));
            },

            get promoProducts() {
                let group = this.posGroups.find(g => g.name.toLowerCase() === 'promo');
                if (group && group.products && group.products.length > 0) return group.products;
                return this.products.filter(p => this.promoProductIds.includes(p.id));
            },

            get bestSellerProducts() {
                let group = this.posGroups.find(g => g.name.toLowerCase() === 'best seller' || g.name.toLowerCase() === 'terlaris');
                if (group && group.products && group.products.length > 0) return group.products;
                return this.products.filter(p => this.bestSellerProductIds.includes(p.id));
            },

            get filteredProductsCount() {
                if (this.activeCategory === '') return this.products.length;
                return this.products.filter(p => this.filterProduct(p)).length;
            },

            filterProduct(p) {
                if (this.activeCategory === '') return true;
                
                if (this.activeCategory === 'PROMO') {
                    // 1. Cek apakah ada group layout bernama "promo"
                    let promoGroup = this.posGroups.find(g => g.name.toLowerCase() === 'promo');
                    if (promoGroup && promoGroup.products) {
                        return promoGroup.products.some(gp => gp.id === p.id);
                    }
                    // 2. Fallback ke data otomatis is_promo
                    return this.promoProductIds.includes(p.id);
                }
                
                if (this.activeCategory === 'BEST SELLER') {
                    // 1. Cek apakah ada group layout bernama "best seller" atau "terlaris"
                    let bestGroup = this.posGroups.find(g => g.name.toLowerCase() === 'best seller' || g.name.toLowerCase() === 'terlaris');
                    if (bestGroup && bestGroup.products) {
                        return bestGroup.products.some(gp => gp.id === p.id);
                    }
                    // 2. Fallback ke data otomatis penjualan
                    return this.bestSellerProductIds.includes(p.id);
                }
                
                return (p.category_id || p.category?.id || '') == this.activeCategory;
            },

            setCategory(id) {
                this.activeCategory = id;
                const gridContainer = document.getElementById('product-grid-container');
                if (gridContainer) gridContainer.scrollTo({ top: 0, behavior: 'smooth' });
            },

            getInitials(name) {
                if(!name) return 'PR';
                let words = name.trim().split(' ');
                if(words.length >= 2) return (words[0][0] + words[1][0]).toUpperCase();
                return name.substring(0,2).toUpperCase();
            },

            adjustBrightness(hex, percent) {
                if(!hex) return '#cbd5e1';
                let num = parseInt(hex.replace('#',''), 16);
                if(isNaN(num)) return '#cbd5e1';
                let amt = Math.round(2.55 * percent),
                    R = (num >> 16) + amt,
                    B = (num >> 8 & 0x00FF) + amt,
                    G = (num & 0x0000FF) + amt;
                return "#" + (0x1000000 + (R<255?R<1?0:R:255)*0x10000 + (B<255?B<1?0:B:255)*0x100 + (G<255?G<1?0:G:255)).toString(16).slice(1);
            },

            getContrastYIQ(hexcolor) {
                if(!hexcolor) return '#334155';
                let hex = hexcolor.replace('#', '');
                if(hex.length === 3) hex = hex.split('').map(x => x+x).join('');
                let r = parseInt(hex.substr(0,2),16);
                let g = parseInt(hex.substr(2,2),16);
                let b = parseInt(hex.substr(4,2),16);
                let yiq = ((r*299)+(g*587)+(b*114))/1000;
                return (yiq >= 128) ? '#0f172a' : '#ffffff';
            },

            getPlaceholderIcon(catName) {
                if(!catName) return 'fas fa-box-open';
                let cat = catName.toLowerCase();
                if(cat.includes('minum')) return 'fas fa-glass-water';
                if(cat.includes('makan')) return 'fas fa-utensils';
                if(cat.includes('snack') || cat.includes('camilan')) return 'fas fa-cookie-bite';
                if(cat.includes('elektronik') || cat.includes('gadget')) return 'fas fa-laptop';
                if(cat.includes('jasa') || cat.includes('service')) return 'fas fa-screwdriver-wrench';
                return 'fas fa-box-open';
            },

            init() {
                console.log("Initializing POS App...");
                this.loadPrinterSettings();
                try {
                    @if($activeWorksheetId === 'all')
                        @if(isset($userWorksheets) && $userWorksheets->count() > 0)
                            @foreach($userWorksheets as $ws)
                                this.addWorksheet(@json($ws->name), @json($ws->id));
                            @endforeach
                        @else
                            this.addWorksheet('Draft POS');
                        @endif
                    @elseif($activeWorksheetId && $activeWorksheet)
                        this.addWorksheet(@json($activeWorksheet->name), @json($activeWorksheetId));
                    @else
                        this.addWorksheet('Draft POS');
                    @endif
                    
                    document.addEventListener('keydown', (e) => {
                        if(e.key === 'F2') { e.preventDefault(); if(this.$refs.searchInput) this.$refs.searchInput.focus(); }
                        if(e.key === 'F12') { e.preventDefault(); this.openPayment(); }
                        if(e.key === 'Enter' && this.showReceiptModal) { e.preventDefault(); this.closeReceiptAndReset(); }
                    });

                    setInterval(() => {
                        const el = document.getElementById('pos-clock-display');
                        if(el) {
                            const span = el.querySelector('span');
                            if(span) span.textContent = new Date().toLocaleTimeString('id-ID', {hour:'2-digit',minute:'2-digit',second:'2-digit'});
                        }
                    }, 1000);
                } catch (e) {
                    console.error("POS App Init Error:", e);
                }
            },

            addWorksheet(customName = null, customId = null) {
                let id = customId || Date.now();
                this.worksheets.push({
                    id: id,
                    name: customName || ('Worksheet ' + (this.worksheets.length + 1)),
                    cart: [],
                    customerName: '',
                    customerPhone: '',
                    tableNumber: '',
                    notes: '',
                    deliveryMode: false,
                    deliveryFee: 0,
                    globalDiscount: 0,
                    discountType: 'nominal'
                });
                this.activeTabId = id;
            },

            get activeWorksheet() {
                return this.worksheets.find(w => w.id === this.activeTabId) || this.worksheets[0];
            },

            formatRp(angka) {
                return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
            },
            
            fmt(num) {
                return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(num);
            },

            async fetchProducts() {
                try {
                    let res = await fetch(`/pos/products?search=${this.searchQuery}`);
                    this.products = await res.json();
                } catch(e) { console.error(e); }
            },

            addToCart(product) {
                if(!this.activeShift) {
                    Swal.fire({
                        title: 'Shift Belum Dibuka',
                        text: 'Buka shift terlebih dahulu untuk mulai mencatat transaksi.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Buka Shift Sekarang',
                        cancelButtonText: 'Nanti saja',
                        background: '#1e293b',
                        color: '#f8fafc',
                        confirmButtonColor: '#10b981',
                        cancelButtonColor: '#334155',
                        customClass: {
                            popup: 'rounded-3xl border border-slate-700',
                            confirmButton: 'rounded-xl font-bold px-6 py-2.5',
                            cancelButton: 'rounded-xl font-bold px-6 py-2.5'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = "{{ route('shifts.index', ['open' => 1]) }}";
                        }
                    });
                    return;
                }
                let w = this.activeWorksheet;
                let exist = w.cart.find(i => i.product_id === product.id && !i.is_custom_price);
                let isUnlimited = !!(product.is_stockless);
                
                // Calculate total quantity of this product in cart (including custom priced items)
                let totalInCart = w.cart.filter(i => i.product_id === product.id).reduce((sum, item) => sum + item.quantity, 0);

                if(exist) {
                    if(isUnlimited || totalInCart < product.stock) {
                        exist.quantity++;
                    } else {
                        Toast.fire({ icon: 'warning', title: 'Stok tidak mencukupi! Sisa stok: ' + product.stock });
                    }
                } else {
                    if(isUnlimited || totalInCart < product.stock) {
                        let finalPrice = (product.is_promo && product.discount_price > 0) 
                            ? parseFloat(product.discount_price) 
                            : parseFloat(product.price);
                            
                        w.cart.push({
                            product_id: product.id,
                            name: product.name,
                            price: finalPrice,
                            quantity: 1,
                            stock: product.stock,
                            discount: 0,
                            is_stockless: isUnlimited
                        });
                    } else {
                        Toast.fire({ icon: 'error', title: 'Stok produk ini sudah habis!' });
                    }
                }
                this.lastAddedId = product.id;
                setTimeout(() => { if(this.lastAddedId === product.id) this.lastAddedId = null; }, 600);
            },

            changeQty(index, delta) {
                let item = this.activeWorksheet.cart[index];
                let newQty = item.quantity + delta;
                let isUnlimited = !!(item.is_stockless);
                
                let totalInCartOther = this.activeWorksheet.cart.filter((i, idx) => i.product_id === item.product_id && idx !== index).reduce((sum, i) => sum + i.quantity, 0);

                if(newQty > 0) {
                    if(isUnlimited || (newQty + totalInCartOther) <= item.stock) {
                        item.quantity = newQty;
                    } else {
                        Toast.fire({ icon: 'warning', title: 'Stok tidak mencukupi!' });
                    }
                }
            },

            removeItem(index) {
                this.activeWorksheet.cart.splice(index, 1);
            },

            get currentSubtotal() {
                if(!this.activeWorksheet) return 0;
                return this.activeWorksheet.cart.reduce((sum, item) => {
                    let price = item.is_custom_price ? item.custom_price : item.price;
                    return sum + ((price * item.quantity) - item.discount);
                }, 0);
            },

            get currentDiscountValue() {
                if(!this.activeWorksheet) return 0;
                let w = this.activeWorksheet;
                if(w.discountType === 'percentage') return this.currentSubtotal * (parseFloat(w.globalDiscount) / 100 || 0);
                return parseFloat(w.globalDiscount) || 0;
            },

            get currentTotal() {
                let taxable = this.currentSubtotal - this.currentDiscountValue;
                let tax = taxable * (this.taxRate / 100);
                let delivery = this.activeWorksheet && this.activeWorksheet.deliveryMode ? (parseFloat(this.activeWorksheet.deliveryFee) || 0) : 0;
                return taxable + tax + delivery;
            },

            openPayment() {
                if(!this.activeShift) {
                    Swal.fire({
                        title: 'Shift Belum Dibuka',
                        text: 'Buka shift terlebih dahulu untuk mulai mencatat transaksi.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Buka Shift Sekarang',
                        cancelButtonText: 'Nanti saja',
                        background: '#1e293b',
                        color: '#f8fafc',
                        confirmButtonColor: '#10b981',
                        cancelButtonColor: '#334155',
                        customClass: {
                            popup: 'rounded-3xl border border-slate-700',
                            confirmButton: 'rounded-xl font-bold px-6 py-2.5',
                            cancelButton: 'rounded-xl font-bold px-6 py-2.5'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = "{{ route('shifts.index', ['open' => 1]) }}";
                        }
                    });
                    return;
                }
                if (this.activeWorksheet.cart.length === 0) {
                    Toast.fire({ icon: 'info', title: 'Keranjang masih kosong!' });
                    return;
                }
                this.paymentTiming = 'now';
                this.paymentMethod = 'cash';
                this.paidAmount = this.currentTotal;
                this.paidAmountDisplay = this.formatInput(this.paidAmount);
                this.dpAmount = 0;
                this.dpAmountDisplay = '';
                this.showPaymentModal = true;
            },

            async processCheckout() {
                let w = this.activeWorksheet;
                let isPiutang = this.paymentTiming === 'later';
                let method = isPiutang ? 'piutang' : this.paymentMethod;
                if(!isPiutang && method === 'cash' && this.paidAmount < this.currentTotal) {
                    return Toast.fire({ icon: 'warning', title: 'Jumlah bayar kurang!' });
                }
                this.isProcessing = true;
                let finalNotes = w.notes;
                if(w.tableNumber) finalNotes = `No Meja: ${w.tableNumber} | ${finalNotes}`;
                if(w.deliveryMode) finalNotes = `[DELIVERY] ${finalNotes}`;
                let finalPaidAmount = isPiutang ? (this.dpAmount || 0) : this.paidAmount;
                try {
                    const res = await fetch('/pos/checkout', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body: JSON.stringify({
                            items: w.cart, 
                            payment_method: method, 
                            dp_method: isPiutang ? this.paymentMethod : null,
                            paid_amount: finalPaidAmount,
                            discount: w.globalDiscount, 
                            discount_type: w.discountType,
                            delivery_fee: w.deliveryMode ? (parseFloat(w.deliveryFee) || 0) : 0,
                            customer_name: w.customerName, 
                            customer_phone: w.customerPhone, 
                            notes: finalNotes
                        })
                    });
                    const data = await res.json();
                    if(res.ok) {
                        this.showPaymentModal = false;
                        this.receiptData = { invoice: data.invoice_number, change: data.change, transaction_id: data.transaction.id };
                        this.showReceiptModal = true;
                        this.fetchProducts();

                        // Notifikasi Printer/Drawer
                        if (data.printer_status) {
                            if (data.printer_status.success) {
                                Toast.fire({ icon: 'success', title: data.printer_status.message });
                            } else {
                                Toast.fire({ icon: 'warning', title: data.printer_status.message });
                            }
                        }
                        
                        // Auto Print
                        if (this.autoPrint) {
                            this.doPrint(data.transaction.id);
                        }
                    } else { Toast.fire({ icon: 'error', title: data.error || 'Gagal checkout' }); }
                } catch(e) { Toast.fire({ icon: 'error', title: 'Kesalahan koneksi' }); }
                finally { this.isProcessing = false; }
            },

            openGroupManager() {
                this.draftGroups = JSON.parse(JSON.stringify(this.posGroups));
                const groupedIds = new Set();
                this.draftGroups.forEach(g => { if(g.products) g.products.forEach(p => groupedIds.add(p.id)); });
                this.draftUngrouped = this.products.filter(p => !groupedIds.has(p.id));
                this.showGroupManagerModal = true;
            },

            closeGroupManager() {
                Swal.fire({
                    title: 'Batalkan Perubahan?',
                    text: 'Perubahan tata letak yang belum disimpan akan hilang.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Batal',
                    cancelButtonText: 'Kembali'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.showGroupManagerModal = false;
                    }
                });
            },

            addNewDraftGroup() {
                this.newGroupName = '';
                this.newGroupError = '';
                this.showAddGroupModal = true;
            },

            submitNewGroup() {
                const name = this.newGroupName.trim();
                if (!name) { this.newGroupError = 'Nama wajib diisi'; return; }
                this.draftGroups.unshift({ id: 'new-' + Date.now(), name: name, color: '#10b981', position: 0, products: [] });
                this.showAddGroupModal = false;
            },

            deleteDraftGroup(gIndex) {
                Swal.fire({
                    title: 'Hapus Grup?',
                    text: 'Grup ini akan dihapus dan produk di dalamnya akan dikembalikan ke kategori umum.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const g = this.draftGroups[gIndex];
                        if (g.products) this.draftUngrouped.unshift(...g.products);
                        this.draftGroups.splice(gIndex, 1);
                    }
                });
            },

            startDrag(evt, product, fromType, fromIndex) {
                this.draggedProduct = product;
                this.draggedFrom = { type: fromType, index: fromIndex };
            },

            dropItemToGroup(gIndex) {
                if(!this.draggedProduct) return;
                if(this.draggedFrom.type === 'ungrouped') this.draftUngrouped.splice(this.draggedFrom.index, 1);
                else this.draftGroups[this.draggedFrom.type].products.splice(this.draggedFrom.index, 1);
                if(!this.draftGroups[gIndex].products) this.draftGroups[gIndex].products = [];
                this.draftGroups[gIndex].products.push(this.draggedProduct);
                this.draggedProduct = null;
            },

            dropItemToIndex(gIndex, pIndex) {
                if(!this.draggedProduct) return;
                if(this.draggedFrom.type === 'ungrouped') this.draftUngrouped.splice(this.draggedFrom.index, 1);
                else this.draftGroups[this.draggedFrom.type].products.splice(this.draggedFrom.index, 1);
                let targetIndex = pIndex;
                if(this.draggedFrom.type === gIndex && this.draggedFrom.index < pIndex) targetIndex--;
                if(!this.draftGroups[gIndex].products) this.draftGroups[gIndex].products = [];
                this.draftGroups[gIndex].products.splice(targetIndex, 0, this.draggedProduct);
                this.draggedProduct = null;
            },

            dropItemToUngrouped() {
                if(!this.draggedProduct) return;
                if(this.draggedFrom.type === 'ungrouped') { this.draggedProduct = null; return; }
                this.draftGroups[this.draggedFrom.type].products.splice(this.draggedFrom.index, 1);
                this.draftUngrouped.unshift(this.draggedProduct);
                this.draggedProduct = null;
            },

            async saveLayoutEditor() {
                this.isSavingGroup = true;
                try {
                    const payload = this.draftGroups.map(g => ({
                        id: g.id, name: g.name, color: g.color,
                        products: (g.products || []).map((p, pIndex) => ({ id: p.id, position: pIndex }))
                    }));
                    const res = await fetch('/pos/groups/sync-all', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body: JSON.stringify({ groups: payload })
                    });
                    const data = await res.json();
                    if(data.success) { this.posGroups = data.posGroups; this.showGroupManagerModal = false; }
                    else { Toast.fire({ icon: 'error', title: 'Gagal simpan' }); }
                } catch(e) { Toast.fire({ icon: 'error', title: 'Kesalahan koneksi' }); }
                this.isSavingGroup = false;
            },

            closeReceiptAndReset() {
                this.showReceiptModal = false;
                let w = this.activeWorksheet;
                w.cart = []; w.globalDiscount = 0; w.customerName = '';
                this.receiptData = null;
            },

            resetCurrentWorksheet() {
                if(this.activeWorksheet.cart.length === 0) return;
                
                Swal.fire({
                    title: 'Batalkan Pesanan?',
                    text: 'Seluruh pesanan di worksheet ini akan dihapus.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Batalkan!',
                    cancelButtonText: 'Kembali'
                }).then((result) => {
                    if (result.isConfirmed) {
                        let w = this.activeWorksheet;
                        w.cart = [];
                        w.customerName = '';
                        w.customerPhone = '';
                        w.tableNumber = '';
                        w.notes = '';
                        w.deliveryMode = false;
                        w.globalDiscount = 0;
                        w.discountType = 'nominal';

                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Pesanan dibatalkan',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true
                        });
                    }
                });
            },

            async submitExpense() {
                if (!this.expenseDesc.trim() || !this.expenseAmount) return;
                try {
                    const res = await fetch('/pos/expense', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body: JSON.stringify({ description: this.expenseDesc, amount: this.expenseAmount, category: this.expenseCategory })
                    });
                    if (res.ok) { this.showExpenseModal = false; this.expenseDesc = ''; this.expenseAmount = 0; }
                } catch (e) { alert('Gagal simpan pengeluaran'); }
            },

            async loadPrinterSettings() {
                const saved = localStorage.getItem('pos_printer_settings');
                if (saved) {
                    const settings = JSON.parse(saved);
                    this.paperSize = settings.paperSize || '58mm';
                    this.fontSmall = settings.fontSmall || false;
                    this.fontSize = settings.fontSize || 'medium';
                    this.connectionMethod = settings.connectionMethod || null;
                    this.printerName = settings.printerName || '';
                } else {
                    // Fallback to database defaults
                    this.paperSize = '{{ $settings["printer_paper_size"] ?? "58mm" }}';
                    this.autoPrint = {{ ($settings["printer_auto_print"] ?? "0") === "1" ? "true" : "false" }};
                    this.fontSmall = {{ ($settings["printer_font_small"] ?? "0") === "1" ? "true" : "false" }};
                }
                
                if (this.connectionMethod === 'usb_direct' && this.printerName && navigator.usb) {
                        try {
                            const devices = await navigator.usb.getDevices();
                            const matching = devices.find(d => d.productName === this.printerName);
                            if (matching) {
                                this.printerHandle = matching;
                                this.printerStatus = 'connected';
                                console.log("Auto-reconnected USB printer:", this.printerName);
                            } else {
                                console.warn("USB printer authorized but not found in current session.");
                                // We keep printerStatus as 'connected' because it's "Saved", 
                                // but printing will prompt for reconnect if handle is null.
                                this.printerStatus = 'connected'; 
                            }
                        } catch (e) { console.error("Auto-reconnect failed:", e); }
                    } else if (this.printerName) {
                        this.printerStatus = 'connected';
                    }
            },

            savePrinterSettings() {
                const settings = {
                    paperSize: this.paperSize,
                    fontSmall: this.fontSmall,
                    fontSize: this.fontSize,
                    autoPrint: this.autoPrint,
                    connectionMethod: this.connectionMethod,
                    printerName: this.printerName
                };
                localStorage.setItem('pos_printer_settings', JSON.stringify(settings));
            },

            async scanDevices(method) {
                this.connectionMethod = method;
                this.isScanning = true;
                this.discoveredDevices = [];
                
                try {
                    if (method === 'usb_direct') {
                        if (!navigator.usb) {
                            throw new Error('Browser ini tidak mendukung WebUSB. Gunakan Chrome atau Edge.');
                        }
                        const device = await navigator.usb.requestDevice({ filters: [] });
                        this.discoveredDevices = [{
                            name: device.productName || 'USB Thermal Printer',
                            id: (device.vendorId.toString(16) + ':' + device.productId.toString(16)).toUpperCase(),
                            raw: device
                        }];
                        // Auto connect if one device picked
                        this.connectDevice(this.discoveredDevices[0]);
                    } else if (method === 'bluetooth') {
                        if (!navigator.bluetooth) {
                            throw new Error('Browser ini tidak mendukung Web Bluetooth.');
                        }
                        const device = await navigator.bluetooth.requestDevice({
                            acceptAllDevices: true,
                            optionalServices: ['000018f0-0000-1000-8000-00805f9b34fb'] // Common thermal printer service
                        });
                        this.discoveredDevices = [{
                            name: device.name || 'Bluetooth Printer',
                            id: device.id,
                            raw: device
                        }];
                        this.connectDevice(this.discoveredDevices[0]);
                    } else if (method === 'usb_serial') {
                        // Simulation for serial as it's more complex
                        setTimeout(() => {
                            this.discoveredDevices = [
                                { name: 'COM3 (USB Serial Port)', id: 'COM3' },
                                { name: 'COM5 (Silicon Labs CP210x)', id: 'COM5' }
                            ];
                            this.isScanning = false;
                        }, 1000);
                        return;
                    }
                } catch (e) {
                    console.error("Discovery Error:", e);
                    if (e.name !== 'NotFoundError') { // Ignore user cancel
                        Toast.fire({ icon: 'error', title: e.message || 'Gagal mencari perangkat' });
                    }
                } finally {
                    this.isScanning = false;
                }
            },

            useBrowserDefault() {
                this.printerName = 'System Default (Browser)';
                this.printerStatus = 'connected';
                this.connectionMethod = 'browser_default';
                this.savePrinterSettings();
                Toast.fire({ icon: 'success', title: 'Menggunakan Printer Bawaan Browser' });
            },

            connectDevice(device) {
                this.printerName = device.name;
                this.printerStatus = 'connected';
                this.printerHandle = device.raw || null;
                
                // Auto detect paper size from common models
                if (device.name.toLowerCase().includes('58') || device.name.toLowerCase().includes('eco')) {
                    this.paperSize = '58mm';
                } else if (device.name.toLowerCase().includes('80')) {
                    this.paperSize = '80mm';
                }
                
                this.savePrinterSettings();
                Toast.fire({ icon: 'success', title: 'Printer Terhubung: ' + device.name });
            },

            async printRaw(commands) {
                if (!this.printerHandle) {
                    console.warn("No printer handle found");
                    return false;
                }
                
                const device = this.printerHandle;
                try {
                    if (!device.opened) await device.open();
                    await device.selectConfiguration(1);
                    
                    let interfaceNumber = -1;
                    let endpointOut = -1;
                    
                    // Priority 1: Look for Printer Class (7)
                    for (const iface of device.configuration.interfaces) {
                        for (const alt of iface.alternates) {
                            if (alt.interfaceClass === 7) { 
                                interfaceNumber = iface.interfaceNumber;
                                for (const endpoint of alt.endpoints) {
                                    if (endpoint.direction === 'out') {
                                        endpointOut = endpoint.endpointNumber;
                                        break;
                                    }
                                }
                            }
                        }
                        if (interfaceNumber !== -1 && endpointOut !== -1) break;
                    }

                    // Priority 2: Fallback to any OUT endpoint if Printer Class not found
                    if (interfaceNumber === -1 || endpointOut === -1) {
                        for (const iface of device.configuration.interfaces) {
                            for (const alt of iface.alternates) {
                                for (const endpoint of alt.endpoints) {
                                    if (endpoint.direction === 'out') {
                                        interfaceNumber = iface.interfaceNumber;
                                        endpointOut = endpoint.endpointNumber;
                                        break;
                                    }
                                }
                                if (interfaceNumber !== -1) break;
                            }
                            if (interfaceNumber !== -1) break;
                        }
                    }

                    if (interfaceNumber === -1 || endpointOut === -1) {
                        throw new Error("Tidak menemukan endpoint printer yang cocok.");
                    }
                    
                    await device.claimInterface(interfaceNumber);
                    await device.transferOut(endpointOut, commands);
                    // No need to release immediately if we want to keep it open, 
                    // but for compatibility we'll release and close for now
                    await device.releaseInterface(interfaceNumber);
                    await device.close();
                    return true;
                } catch (e) {
                    console.error("Direct Print Error Details:", e);
                    // Do not toast here to avoid clutter, the caller will handle the fallback
                    return false;
                }
            },

            async testPrint() {
                if (this.printerStatus !== 'connected') {
                    return Toast.fire({ icon: 'warning', title: 'Printer belum terhubung!' });
                }

                // If using WebUSB, try direct print first
                if (this.connectionMethod === 'usb_direct' && this.printerHandle) {
                    const encoder = new TextEncoder();
                    const init = new Uint8Array([0x1B, 0x40]); // Init
                    const center = new Uint8Array([0x1B, 0x61, 0x01]); // Center
                    const left = new Uint8Array([0x1B, 0x61, 0x00]); // Left
                    const boldOn = new Uint8Array([0x1B, 0x45, 0x01]); // Bold On
                    const boldOff = new Uint8Array([0x1B, 0x45, 0x00]); // Bold Off
                    
                    let commands = [];
                    commands.push(init, center, boldOn);
                    commands.push(encoder.encode('MONOFRAME STUDIO\n'));
                    commands.push(boldOff);
                    commands.push(encoder.encode('Jl. Srigunting No.6, Padang\n'));
                    commands.push(encoder.encode('Telp: 082323426600\n'));
                    commands.push(encoder.encode('--------------------------------\n'));
                    commands.push(left);
                    commands.push(encoder.encode('No. Order:         TEST-123456\n'));
                    commands.push(encoder.encode('Kasir:                Kasir Test\n'));
                    commands.push(encoder.encode('--------------------------------\n'));
                    commands.push(encoder.encode('Item Testing Printer\n'));
                    commands.push(encoder.encode('1 x 10.000             10.000\n'));
                    commands.push(encoder.encode('--------------------------------\n'));
                    commands.push(encoder.encode('TOTAL:                 10.000\n'));
                    commands.push(encoder.encode('--------------------------------\n'));
                    commands.push(center);
                    commands.push(encoder.encode('\nTerima kasih atas telah\nmengabadikan moment bersama\nmonoframe studio\n'));
                    commands.push(encoder.encode('\nPowered by monodev.id\n\n\n\n\n'));
                    
                    const cut = new Uint8Array([0x1D, 0x56, 0x41, 0x03]);
                    commands.push(cut);

                    // Combine all
                    let totalLen = commands.reduce((acc, c) => acc + c.length, 0);
                    let combined = new Uint8Array(totalLen);
                    let offset = 0;
                    for (const c of commands) {
                        combined.set(c, offset);
                        offset += c.length;
                    }

                    Toast.fire({ icon: 'info', title: 'Mencoba cetak langsung...' });
                    const success = await this.printRaw(combined);
                    if (success) {
                        return Toast.fire({ icon: 'success', title: 'Berhasil cetak langsung!' });
                    }
                }
                
                // Fallback
                let url = `/pos/receipt-test?paper=${this.paperSize}&font=${this.fontSize}&small_font=${this.fontSmall}`;
                const iframe = document.getElementById('print-iframe');
                iframe.src = url;
            },

            disconnectPrinter() {
                this.printerName = '';
                this.printerStatus = 'disconnected';
                this.printerHandle = null;
                this.savePrinterSettings();
            },

            async doPrint(transactionId) {
                if (!transactionId) return;

                // Priority: Server Side ESC/POS (for RJ11 Drawer support)
                if (this.connectionMethod === 'server_escpos') {
                    try {
                        const res = await fetch(`/pos/print-receipt/${transactionId}`, {
                            method: 'POST',
                            headers: { 
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json' 
                            }
                        });
                        const data = await res.json();
                        if (data.success) {
                            Toast.fire({ icon: 'success', title: data.message });
                        } else {
                            Toast.fire({ icon: 'warning', title: data.message });
                        }
                        return;
                    } catch (e) {
                        console.error("Server Print Error:", e);
                        Toast.fire({ icon: 'error', title: 'Gagal menghubungi printer server.' });
                    }
                }

                if (this.connectionMethod === 'usb_direct' && this.printerHandle) {
                    try {
                        const res = await fetch(`/transactions/${transactionId}`, {
                            headers: { 'Accept': 'application/json' }
                        });
                        const tx = await res.json();
                        
                        const encoder = new TextEncoder();
                        const init = new Uint8Array([0x1B, 0x40]);
                        const center = new Uint8Array([0x1B, 0x61, 0x01]);
                        const left = new Uint8Array([0x1B, 0x61, 0x00]);
                        const boldOn = new Uint8Array([0x1B, 0x45, 0x01]);
                        const boldOff = new Uint8Array([0x1B, 0x45, 0x00]);
                        
                        let commands = [];
                        commands.push(init, center, boldOn);
                        commands.push(encoder.encode(tx.store_name + '\n'));
                        commands.push(boldOff);
                        if (tx.store_address) commands.push(encoder.encode(tx.store_address + '\n'));
                        if (tx.store_phone) commands.push(encoder.encode('Telp: ' + tx.store_phone + '\n'));
                        commands.push(encoder.encode('--------------------------------\n'));
                        
                        commands.push(left);
                        commands.push(encoder.encode('No : ' + tx.invoice_number + '\n'));
                        commands.push(encoder.encode('Tgl: ' + tx.created_at + '\n'));
                        if (tx.customer_name) commands.push(encoder.encode('Plg: ' + tx.customer_name + '\n'));
                        if (tx.customer_phone) commands.push(encoder.encode('Hp : ' + tx.customer_phone + '\n'));
                        commands.push(encoder.encode('--------------------------------\n'));
                        
                        tx.items.forEach(item => {
                            commands.push(boldOn, encoder.encode(item.product_name + '\n'), boldOff);
                            let qtyPrice = item.quantity + ' x ' + this.fmt(item.price);
                            let sub = this.fmt(item.subtotal);
                            commands.push(encoder.encode(qtyPrice.padEnd(32 - sub.length) + sub + '\n'));
                        });
                        
                        commands.push(encoder.encode('--------------------------------\n'));
                        let subVal = this.fmt(tx.subtotal);
                        commands.push(encoder.encode('Subtotal:'.padEnd(32 - subVal.length) + subVal + '\n'));
                        
                        if (tx.discount > 0) {
                            let discVal = '-' + this.fmt(tx.discount);
                            commands.push(encoder.encode('Diskon:'.padEnd(32 - discVal.length) + discVal + '\n'));
                        }

                        if (tx.delivery_fee > 0) {
                            let devVal = '+' + this.fmt(tx.delivery_fee);
                            commands.push(encoder.encode('Ongkir:'.padEnd(32 - devVal.length) + devVal + '\n'));
                        }

                        let totalVal = this.fmt(tx.total);
                        commands.push(boldOn, encoder.encode('TOTAL:'.padEnd(32 - totalVal.length) + totalVal + '\n'), boldOff);
                        commands.push(encoder.encode('--------------------------------\n'));
                        
                        let paidVal = this.fmt(tx.paid_amount);
                        commands.push(encoder.encode((tx.payment_method + ':').padEnd(32 - paidVal.length) + paidVal + '\n'));
                        let changeVal = this.fmt(tx.change_amount);
                        commands.push(encoder.encode('KEMBALI:'.padEnd(32 - changeVal.length) + changeVal + '\n'));

                        if (tx.notes) {
                            commands.push(encoder.encode('--------------------------------\n'));
                            commands.push(encoder.encode('Catatan:\n' + tx.notes + '\n'));
                        }
                        commands.push(encoder.encode('--------------------------------\n'));
                        
                        commands.push(center);
                        commands.push(encoder.encode('\n' + tx.store_footer + '\n'));
                        
                        // Dynamic feed lines
                        let feedLines = '\nPowered by monodev.id\n';
                        for(let i=0; i < this.printerFeedLines; i++) feedLines += '\n';
                        if(this.printerFeedLines === 0) feedLines += '\n\n\n\n'; // Default safety
                        commands.push(encoder.encode(feedLines));
                        
                        const cut = new Uint8Array([0x1D, 0x56, 0x41, 0x03]);
                        commands.push(cut);

                        let totalLen = commands.reduce((acc, c) => acc + c.length, 0);
                        let combined = new Uint8Array(totalLen);
                        let offset = 0;
                        for (const c of commands) {
                            combined.set(c, offset);
                            offset += c.length;
                        }
                        
                        const success = await this.printRaw(combined);
                        if (success) return;
                    } catch (e) {
                        console.error("Direct Print failed:", e);
                    }
                }

                const iframe = document.getElementById('print-iframe');
                const url = `/pos/receipt/${transactionId}?paper=${this.paperSize}&font=${this.fontSize}&small_font=${this.fontSmall}`;
                iframe.src = url;
            }
        }));

        document.addEventListener('alpine:init', () => {
            // This is already handled by registerPosApp, but let's make sure loadPrinterSettings is called
        });
        
        window.posAppInitialized = true;
    }

    if (window.Alpine) {
        registerPosApp();
        // Trigger load settings
        setTimeout(() => {
            const el = document.querySelector('[x-data]');
            if (el && el.__x) el.__x.$data.loadPrinterSettings();
        }, 100);
    } else {
        document.addEventListener('alpine:init', registerPosApp);
    }
</script>
@endpush
@endsection


