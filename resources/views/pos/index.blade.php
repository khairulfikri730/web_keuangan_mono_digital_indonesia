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
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@section('content')
<div x-data="posApp()" x-init="initApp()" class="pos-height flex flex-col lg:flex-row bg-slate-100 -mx-6 -mt-4 overflow-hidden text-slate-800 font-sans">
    
    {{-- MAIN CONTENT (TENGAH) --}}
    <div class="flex-1 flex flex-col h-full bg-slate-100 border-r border-slate-200 p-4 lg:p-6 overflow-hidden">
        
        {{-- HEADER BAR --}}
        <div class="flex items-center gap-4 mb-4 bg-white p-3 rounded-2xl shadow-sm border border-slate-200 shrink-0">
            <h2 class="text-xl font-black text-slate-800 hidden md:block px-2">POS Kasir</h2>
            
            <div class="relative flex-1">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" x-model="searchQuery" x-ref="searchInput" class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-11 pr-4 py-2.5 text-slate-700 placeholder-slate-400 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 shadow-inner text-sm transition-all" placeholder="Cari produk atau scan barcode..." autofocus>
            </div>
            
            <div class="flex items-center gap-2 shrink-0">
                <div class="flex items-center bg-slate-100 p-1 rounded-xl">
                    <button @click="viewMode='grid'" :class="viewMode==='grid' ? 'bg-white shadow text-emerald-600' : 'text-slate-500 hover:text-slate-700'" class="p-2 rounded-lg transition-all"><i class="fas fa-th-large"></i></button>
                    <button @click="viewMode='list'" :class="viewMode==='list' ? 'bg-white shadow text-emerald-600' : 'text-slate-500 hover:text-slate-700'" class="p-2 rounded-lg transition-all"><i class="fas fa-list"></i></button>
                </div>
                
                <button @click="openGroupManager()" class="bg-emerald-50 text-emerald-600 shadow-sm border border-emerald-200 hover:bg-emerald-100 px-3 py-2 rounded-xl transition-all text-sm font-bold flex items-center gap-2" title="Groupkan Item">
                    <i class="fas fa-layer-group"></i> <span class="hidden md:inline">Groupkan Item</span>
                </button>

                <div class="hidden lg:flex items-center gap-2 px-4 py-2 bg-slate-800 text-white rounded-xl text-sm font-bold shadow-sm" id="pos-clock-display">
                    <i class="far fa-clock text-blue-400"></i> <span></span>
                </div>
                
                <a href="{{ route('shifts.index') }}" class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-sm font-bold transition-all shadow-lg shadow-emerald-500/30 flex items-center gap-2">
                    <i class="fas fa-play"></i> <span class="hidden sm:inline">Buka Shift</span>
                </a>
            </div>
        </div>

        {{-- SHIFT BANNER --}}
        <div x-show="!activeShift" x-cloak class="bg-amber-100 border border-amber-300 text-amber-800 px-4 py-3 rounded-2xl flex items-center justify-between mb-4 shadow-sm shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-amber-200 flex items-center justify-center text-amber-600 shadow-inner">
                    <i class="fas fa-exclamation-triangle text-xl"></i>
                </div>
                <div>
                    <p class="font-black text-sm text-amber-900">Shift Belum Dibuka</p>
                    <p class="text-xs font-medium">Buka shift untuk mulai mencatat transaksi penjualan.</p>
                </div>
            </div>
            <a href="{{ route('shifts.index') }}" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-bold transition-colors shadow-lg shadow-amber-500/30">
                <i class="fas fa-play mr-1"></i> Buka Shift
            </a>
        </div>

        {{-- WORKSHEET BAR --}}
        <div class="flex items-center gap-3 mb-4 bg-white p-2 rounded-2xl shadow-sm border border-slate-200 shrink-0">
            <div class="bg-slate-100 px-3 py-2 rounded-xl border border-slate-200">
                <span class="text-[10px] font-black text-slate-500 uppercase tracking-wider">Worksheet Aktif</span>
            </div>
            <div class="relative flex-1 max-w-sm">
                <select x-model="activeTabId" class="w-full appearance-none bg-emerald-50 border border-emerald-200 rounded-xl pl-4 pr-10 py-2.5 text-sm font-bold text-emerald-700 outline-none focus:border-emerald-500 cursor-pointer shadow-sm transition-colors">
                    <template x-for="sheet in worksheets" :key="sheet.id">
                        <option :value="sheet.id" x-text="'📝 ' + sheet.name"></option>
                    </template>
                </select>
                <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-emerald-500 pointer-events-none text-xs"></i>
            </div>
            <button @click="addWorksheet()" class="w-11 h-11 rounded-xl bg-slate-800 text-white flex items-center justify-center hover:bg-slate-700 transition-all shadow-md active:scale-95 shrink-0" title="Tambah Worksheet">
                <i class="fas fa-plus"></i>
            </button>
            <div class="ml-auto">
                <button class="px-4 py-2 bg-slate-50 border border-slate-200 text-slate-600 rounded-xl text-xs font-bold hover:bg-slate-100 transition-colors shadow-sm">Semua Worksheet</button>
            </div>
        </div>

        {{-- FILTER KATEGORI (CHIPS) --}}
        <div class="flex gap-2 overflow-x-auto pb-2 mb-2 scrollbar-hide shrink-0" id="category-buttons">
            <button @click="setCategory('')" data-category="semua" :class="activeCategory==='' ? 'bg-slate-800 text-white shadow-lg border-slate-800 active' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50'" class="category-btn px-6 py-2 border rounded-full text-sm font-bold whitespace-nowrap transition-all">
                Semua
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
            <div x-show="filteredProductsCount === 0" class="absolute inset-0 flex flex-col items-center justify-center h-full text-slate-400 z-10 pointer-events-none">
                <div class="w-20 h-20 bg-slate-200 rounded-full flex items-center justify-center mb-4"><i class="fas fa-box-open text-3xl"></i></div>
                <p class="text-sm font-bold">Produk tidak ditemukan</p>
            </div>
            
            {{-- CUSTOM POS GROUPS --}}
            <div class="space-y-6 w-full mb-6">
                <template x-for="group in posGroups" :key="'pos-group-'+group.id">
                    <div class="w-full" x-show="group.products.filter(p => activeCategory === '' || activeCategory === (p.category_id || p.category?.id || '')).length > 0">
                        {{-- Group Header --}}
                        <div class="sticky top-0 z-20 bg-slate-100/90 backdrop-blur-md py-3 mb-4 border-b border-slate-200 flex items-center gap-3">
                            <span class="w-3 h-3 rounded-full shadow-inner" :style="`background-color: ${group.color || '#f97316'}`"></span>
                            <h3 class="font-black text-slate-700 text-sm uppercase tracking-widest" x-text="group.name"></h3>
                            <span class="text-[10px] font-bold text-slate-500 bg-slate-200 px-2 py-0.5 rounded-md" x-text="group.products.filter(p => activeCategory === '' || activeCategory === (p.category_id || p.category?.id || '')).length + ' Item'"></span>
                        </div>
                        
                        {{-- Inner Grid --}}
                        <div :class="viewMode === 'grid' ? 'grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-4 content-start' : 'flex flex-col gap-3'">
                            <template x-for="p in group.products.filter(x => activeCategory === '' || activeCategory === (x.category_id || x.category?.id || ''))" :key="'group-item-'+p.id">
                                @include('pos._product_card')
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            {{-- SEMUA PRODUK (REMAINING / ALL) --}}
            <div class="sticky top-0 z-20 bg-slate-100/90 backdrop-blur-md py-3 mb-4 border-b border-slate-200 flex items-center gap-3" x-show="posGroups.length > 0 && products.filter(p => activeCategory === '' || activeCategory === (p.category?.id || '')).length > 0">
                <span class="w-3 h-3 rounded-full shadow-inner bg-slate-300"></span>
                <h3 class="font-black text-slate-700 text-sm uppercase tracking-widest">Semua Produk</h3>
                <span class="text-[10px] font-bold text-slate-500 bg-slate-200 px-2 py-0.5 rounded-md" x-text="products.filter(p => activeCategory === '' || activeCategory === (p.category?.id || '')).length + ' Item'"></span>
            </div>

            <div :class="viewMode === 'grid' ? 'grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-4 content-start' : 'flex flex-col gap-3'">
                <template x-for="p in products" :key="p.id">
                    @include('pos._product_card')
                </template>
            </div>
        </div>
    </div>

    {{-- RIGHT PANEL: ORDER PANEL (MODULAR) --}}
    <div class="w-full lg:w-[420px] flex flex-col bg-slate-50 shadow-[-10px_0_30px_rgba(0,0,0,0.05)] z-10 h-full p-4 lg:p-6 shrink-0 overflow-y-auto scrollbar-hide space-y-4 scroll-smooth">
        
        {{-- CARD: PESANAN --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 flex flex-col min-h-[350px] shrink-0">
            <div class="p-4 border-b border-slate-100">
                <div class="flex p-1 bg-slate-100 rounded-xl">
                    <button class="flex-1 py-2 text-sm font-bold rounded-lg bg-white shadow-sm text-slate-800 transition-all">Pesanan Saat Ini</button>
                    <button class="flex-1 py-2 text-sm font-bold rounded-lg text-slate-500 hover:text-slate-700 transition-all">Riwayat</button>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto p-4 scrollbar-hide relative">
                <div x-show="activeWorksheet.cart.length === 0" x-transition class="absolute inset-0 flex flex-col items-center justify-center text-slate-400">
                    <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mb-4"><i class="fas fa-shopping-basket text-4xl text-slate-300"></i></div>
                    <p class="text-sm font-bold text-slate-500">Keranjang masih kosong</p>
                    <p class="text-xs mt-1">Pilih produk di sebelah kiri</p>
                </div>

                <div class="space-y-3">
                    <template x-for="(item, index) in activeWorksheet.cart" :key="item.product_id + '-' + index">
                        <div x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-8" x-transition:enter-end="opacity-100 transform translate-x-0" 
                             class="flex gap-3 bg-white p-3 rounded-xl border border-slate-200 shadow-sm relative group hover:border-emerald-300 transition-colors">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-black text-slate-800 truncate" x-text="item.name"></p>
                                <p class="text-xs font-bold text-slate-500 mt-0.5" x-text="formatRp(item.price)"></p>
                            </div>
                            <div class="flex flex-col items-end justify-between gap-2">
                                <div class="flex items-center gap-1 bg-slate-100 rounded-lg p-1 border border-slate-200">
                                    <button @click="changeQty(index, -1)" class="w-6 h-6 rounded bg-white shadow-sm flex items-center justify-center text-slate-600 hover:text-emerald-600 hover:scale-105 active:scale-95 transition-all"><i class="fas fa-minus text-[10px]"></i></button>
                                    <span class="text-xs font-black text-slate-800 w-6 text-center" x-text="item.quantity"></span>
                                    <button @click="changeQty(index, 1)" class="w-6 h-6 rounded bg-white shadow-sm flex items-center justify-center text-slate-600 hover:text-emerald-600 hover:scale-105 active:scale-95 transition-all"><i class="fas fa-plus text-[10px]"></i></button>
                                </div>
                                <p class="text-sm font-black text-emerald-600" x-text="formatRp((item.price * item.quantity) - item.discount)"></p>
                            </div>
                            <button @click="removeItem(index)" class="absolute -top-2 -right-2 w-6 h-6 bg-white text-red-500 border border-slate-200 rounded-full opacity-0 group-hover:opacity-100 transition-all flex items-center justify-center text-[10px] shadow-md hover:bg-red-50 hover:border-red-200 hover:scale-110"><i class="fas fa-times"></i></button>
                        </div>
                    </template>
                </div>
            </template>
            </div>
        </div>

        {{-- CARD: QUICK ACTION --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-3 shrink-0">
            <button class="w-full py-3 bg-blue-50 text-blue-600 border border-blue-200 rounded-xl font-bold text-sm hover:bg-blue-600 hover:text-white transition-all shadow-sm active:scale-98 flex items-center justify-center gap-2">
                <i class="fas fa-plus-circle"></i> Tambah Produk Manual
            </button>
        </div>

        {{-- CARD: PELANGGAN --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 shrink-0">
            <div class="flex items-center justify-between mb-3">
                <label class="text-[10px] font-black uppercase tracking-wider text-slate-500"><i class="fas fa-user-tag mr-1"></i> Data Pelanggan</label>
                <button class="text-xs font-bold text-emerald-600 hover:text-emerald-700 bg-emerald-50 px-2 py-1 rounded-md transition-colors">+ Baru</button>
            </div>
            <div class="space-y-3">
                <div class="relative">
                    <i class="fas fa-user absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                    <input type="text" x-model="activeWorksheet.customerName" class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-3 py-2 text-sm text-slate-800 font-medium focus:outline-none focus:border-emerald-500 focus:bg-white transition-colors" placeholder="Ketik nama pelanggan...">
                </div>
                <div class="flex gap-3">
                    <div class="flex-1 relative">
                        <i class="fas fa-phone absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input type="text" x-model="activeWorksheet.customerPhone" class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-3 py-2 text-sm text-slate-800 font-medium focus:outline-none focus:border-emerald-500 focus:bg-white transition-colors" placeholder="08xxx...">
                    </div>
                    <div class="w-24 relative">
                        <i class="fas fa-hashtag absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input type="text" x-model="activeWorksheet.tableNumber" class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-8 pr-3 py-2 text-sm text-slate-800 font-medium focus:outline-none focus:border-emerald-500 focus:bg-white transition-colors text-center" placeholder="Meja">
                    </div>
                </div>
            </div>
        </div>

        {{-- CARD: CATATAN --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 shrink-0">
            <label class="text-[10px] font-black uppercase tracking-wider text-slate-500 mb-2 block"><i class="fas fa-comment-dots mr-1"></i> Catatan Pesanan</label>
            <textarea x-model="activeWorksheet.notes" rows="2" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm text-slate-800 font-medium focus:outline-none focus:border-emerald-500 focus:bg-white transition-colors resize-none" placeholder="Tambahkan instruksi khusus..."></textarea>
        </div>

        {{-- CARD: DELIVERY --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 shrink-0 flex justify-between items-center cursor-pointer hover:bg-slate-50 transition-colors" @click="activeWorksheet.deliveryMode = !activeWorksheet.deliveryMode">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full flex items-center justify-center text-lg transition-colors" :class="activeWorksheet.deliveryMode ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-100 text-slate-400'">
                    <i class="fas fa-motorcycle"></i>
                </div>
                <div>
                    <span class="text-sm font-black text-slate-800 block">Mode Delivery</span>
                    <span class="text-[10px] text-slate-500 font-bold uppercase">Pesanan diantar</span>
                </div>
            </div>
            <label class="relative inline-flex items-center cursor-pointer" @click.stop>
                <input type="checkbox" x-model="activeWorksheet.deliveryMode" class="sr-only peer">
                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500 shadow-inner"></div>
            </label>
        </div>

        {{-- CARD: PEMBAYARAN --}}
        <div class="bg-white rounded-2xl shadow-[0_-5px_20px_rgba(0,0,0,0.05)] border border-slate-200 p-5 shrink-0 mt-auto sticky bottom-0 z-20">
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-bold text-slate-500">Subtotal</span>
                <span class="text-sm font-black text-slate-800" x-text="formatRp(currentSubtotal)"></span>
            </div>
            <div class="flex justify-between items-center mb-4">
                <span class="text-sm font-bold text-slate-500">Diskon</span>
                <div class="flex gap-2 w-32">
                    <input type="number" x-model.number="activeWorksheet.globalDiscount" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-2 py-1.5 text-right font-black text-slate-800 outline-none focus:border-emerald-500 transition-colors shadow-inner text-sm">
                    <select x-model="activeWorksheet.discountType" class="bg-slate-50 border border-slate-200 rounded-lg px-1 py-1.5 text-slate-800 font-black outline-none focus:border-emerald-500 transition-colors cursor-pointer text-sm">
                        <option value="nominal">Rp</option>
                        <option value="percentage">%</option>
                    </select>
                </div>
            </div>
            
            <div class="border-t border-slate-200 border-dashed pt-4 mb-5 flex justify-between items-end">
                <div>
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-500 block mb-1">Total Tagihan</span>
                    <span class="text-3xl font-black text-emerald-600 tracking-tight" x-text="formatRp(currentTotal)"></span>
                </div>
            </div>
            
            <button @click="openPayment()" :disabled="!activeShift || activeWorksheet.cart.length === 0" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-black py-4 rounded-xl transition-all shadow-lg shadow-emerald-500/30 active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed disabled:active:scale-100 disabled:shadow-none flex items-center justify-center gap-2 text-lg">
                <i class="fas fa-wallet"></i> BAYAR (F12)
            </button>
        </div>

    </div>

    {{-- MODAL LAYOUT EDITOR (DRAG & DROP) --}}
    <div x-show="showGroupManagerModal" x-transition x-cloak class="fixed inset-y-0 right-0 left-0 lg:left-64 bg-slate-100 z-[40] flex flex-col h-screen overflow-hidden">
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
    </div>

    {{-- MODAL PEMBAYARAN --}}
    <div x-show="showPaymentModal" x-transition x-cloak class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div @click.away="showPaymentModal = false" class="bg-white rounded-3xl w-full max-w-md p-6 shadow-2xl border border-slate-200 transform transition-all">
            <div class="flex justify-between items-center mb-6 border-b border-slate-100 pb-4">
                <h3 class="text-xl font-black text-slate-800">Konfirmasi Pembayaran</h3>
                <button @click="showPaymentModal = false" class="w-8 h-8 bg-slate-100 text-slate-500 rounded-full hover:bg-slate-200 hover:text-slate-800 transition-colors"><i class="fas fa-times"></i></button>
            </div>
            
            <div class="bg-emerald-50 border border-emerald-100 p-5 rounded-2xl mb-6 text-center shadow-inner">
                <p class="text-emerald-700 text-xs font-black uppercase tracking-wider mb-2">Total Tagihan</p>
                <p class="text-5xl font-black text-emerald-600 tracking-tighter" x-text="formatRp(currentTotal)"></p>
            </div>

            <div class="mb-6">
                <label class="text-xs font-black text-slate-600 uppercase tracking-wider mb-3 block">Metode Pembayaran</label>
                <div class="grid grid-cols-4 gap-3">
                    <template x-for="(m, i) in activePaymentMethods" :key="i">
                        <label class="cursor-pointer group">
                            <input type="radio" x-model="paymentMethod" :value="m.id" class="peer sr-only">
                            <div class="p-3 border-2 border-slate-100 rounded-2xl peer-checked:border-emerald-500 peer-checked:bg-emerald-50 text-center transition-all text-slate-500 peer-checked:text-emerald-600 bg-white group-hover:border-emerald-200">
                                <i :class="m.icon" class="mb-2 text-2xl"></i><br>
                                <span class="text-[10px] font-black uppercase" x-text="m.label"></span>
                            </div>
                        </label>
                    </template>
                </div>
            </div>

            <div class="mb-8">
                <label class="text-xs font-black text-slate-600 uppercase tracking-wider mb-3 block">Jumlah Uang Diterima</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">Rp</span>
                    <input type="number" x-model.number="paidAmount" class="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl pl-12 pr-4 py-4 text-2xl font-black text-slate-800 outline-none focus:border-emerald-500 focus:bg-white transition-colors" placeholder="0">
                </div>
                <div class="flex gap-2 mt-3">
                    <button @click="paidAmount = currentTotal" class="flex-1 bg-white border border-slate-200 hover:border-emerald-500 hover:text-emerald-600 text-slate-600 text-xs font-bold py-2.5 rounded-xl transition-all shadow-sm">Uang Pas</button>
                    <button @click="paidAmount += 50000" class="flex-1 bg-white border border-slate-200 hover:border-emerald-500 hover:text-emerald-600 text-slate-600 text-xs font-bold py-2.5 rounded-xl transition-all shadow-sm">+50k</button>
                    <button @click="paidAmount += 100000" class="flex-1 bg-white border border-slate-200 hover:border-emerald-500 hover:text-emerald-600 text-slate-600 text-xs font-bold py-2.5 rounded-xl transition-all shadow-sm">+100k</button>
                </div>
            </div>

            <button @click="processCheckout()" :disabled="isProcessing" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-black py-4 rounded-xl transition-all shadow-lg shadow-emerald-500/30 disabled:opacity-70 disabled:cursor-not-allowed flex items-center justify-center gap-3 text-lg">
                <i x-show="!isProcessing" class="fas fa-check-circle"></i>
                <i x-show="isProcessing" class="fas fa-spinner fa-spin"></i>
                <span x-text="isProcessing ? 'Memproses Transaksi...' : 'Proses Pembayaran'"></span>
            </button>
        </div>
    </div>

    {{-- MODAL SUKSES (RECEIPT) --}}
    <div x-show="showReceiptModal" x-transition x-cloak class="fixed inset-0 bg-slate-900/80 backdrop-blur-md z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl w-full max-w-sm p-8 shadow-2xl border border-slate-200 text-center transform transition-all relative overflow-hidden">
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
                    <a :href="'/pos/receipt/' + receiptData?.transaction_id" target="_blank" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-black py-4 rounded-xl transition-all shadow-lg shadow-slate-900/20 flex items-center justify-center gap-2">
                        <i class="fas fa-print"></i> Cetak Struk
                    </a>
                    <button @click="closeReceiptAndReset()" class="w-full bg-white border-2 border-slate-200 hover:border-slate-300 hover:bg-slate-50 text-slate-700 font-black py-4 rounded-xl transition-all shadow-sm">
                        Transaksi Baru (Enter)
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function posApp() {
    return {
        // Init Data Backend
        categories: @json($categories),
        products: @json($products),
        activeShift: {{ $activeShift ? 'true' : 'false' }},
        taxRate: parseFloat('{{ $settings["tax_rate"] ?? 0 }}') || 0,
        rawMethods: @json(json_decode($settings['active_payment_methods'] ?? '["cash"]', true) ?: ['cash']),
        
        // Multi-Worksheet Logic
        worksheets: [],
        activeTabId: null,

        // UI States
        searchQuery: '',
        activeCategory: '',
        viewMode: 'grid',
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
        lastAddedId: null,
        
        // Transaction State
        paymentMethod: 'cash',
        paidAmount: 0,
        isProcessing: false,
        receiptData: null,

        get activePaymentMethods() {
            const icons = { cash: 'fa-money-bill-1-wave', transfer: 'fa-building-columns', qris: 'fa-qrcode', debit: 'fa-credit-card' };
            const labels = { cash: 'Tunai', transfer: 'Transfer', qris: 'QRIS', debit: 'Debit' };
            return this.rawMethods.map(m => ({ id: m, icon: icons[m] || 'fa-wallet', label: labels[m] || m }));
        },

        get filteredProductsCount() {
            if (this.activeCategory === '') return this.products.length;
            return this.products.filter(p => (p.category?.id || '') === this.activeCategory).length;
        },

        setCategory(id) {
            this.activeCategory = id;
            
            // UX Improvement: Scroll ke atas grid saat kategori berubah
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

        initApp() {
            // Setup First Worksheet
            this.addWorksheet('MONOFRAME STUDIO 1');
            
            // Search Debounce/Watcher
            this.$watch('searchQuery', () => { this.fetchProducts(); });
            // Hapus watch activeCategory agar filter berjalan di client-side saja
            // Client-side filtering di-handle oleh x-show dan computed property

            // Keyboard Shortcuts
            document.addEventListener('keydown', (e) => {
                if(e.key === 'F2') { e.preventDefault(); this.$refs.searchInput.focus(); }
                if(e.key === 'F12') { e.preventDefault(); this.openPayment(); }
                if(e.key === 'Enter' && this.showReceiptModal) { e.preventDefault(); this.closeReceiptAndReset(); }
            });

            // Clock update for POS custom header
            setInterval(() => {
                const el = document.getElementById('pos-clock-display');
                if(el) {
                    const span = el.querySelector('span');
                    if(span) span.textContent = new Date().toLocaleTimeString('id-ID', {hour:'2-digit',minute:'2-digit',second:'2-digit'});
                }
            }, 1000);
        },

        addWorksheet(customName = null) {
            let id = Date.now();
            this.worksheets.push({
                id: id,
                name: customName || ('Worksheet ' + (this.worksheets.length + 1)),
                cart: [],
                customerName: '',
                customerPhone: '',
                tableNumber: '',
                notes: '',
                deliveryMode: false,
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

        async fetchProducts() {
            try {
                // Jangan kirim category_id agar backend mengembalikan semua data. 
                // Filter kategori dilakukan di sisi client-side (realtime).
                let res = await fetch(`/pos/products?search=${this.searchQuery}`);
                this.products = await res.json();
            } catch(e) { console.error(e); }
        },

        addToCart(product) {
            if(!this.activeShift) return alert('Buka shift terlebih dahulu!');
            
            let w = this.activeWorksheet;
            let exist = w.cart.find(i => i.product_id === product.id);
            let isUnlimited = ['unlimited','service'].includes(product.product_kind);
            
            if(exist) {
                if(isUnlimited || exist.quantity < product.stock) {
                    exist.quantity++;
                } else {
                    alert('Stok tidak mencukupi!');
                }
            } else {
                if(product.stock > 0 || isUnlimited) {
                    w.cart.push({
                        product_id: product.id,
                        name: product.name,
                        price: parseFloat(product.price),
                        quantity: 1,
                        stock: product.stock,
                        discount: 0,
                        kind: product.product_kind
                    });
                } else {
                    alert('Stok habis!');
                }
            }

            // Highlight Effect
            this.lastAddedId = product.id;
            setTimeout(() => {
                if(this.lastAddedId === product.id) this.lastAddedId = null;
            }, 500);
        },

        changeQty(index, delta) {
            let item = this.activeWorksheet.cart[index];
            let newQty = item.quantity + delta;
            let isUnlimited = ['unlimited','service'].includes(item.kind);

            if(newQty > 0) {
                if(isUnlimited || newQty <= item.stock) {
                    item.quantity = newQty;
                } else {
                    alert('Stok tidak mencukupi!');
                }
            }
        },

        removeItem(index) {
            this.activeWorksheet.cart.splice(index, 1);
        },

        get currentSubtotal() {
            if(!this.activeWorksheet) return 0;
            return this.activeWorksheet.cart.reduce((sum, item) => sum + ((item.price * item.quantity) - item.discount), 0);
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
            return taxable + tax;
        },

        openPayment() {
            if(!this.activeShift || this.activeWorksheet.cart.length === 0) return;
            this.paidAmount = this.currentTotal;
            this.showPaymentModal = true;
        },

        async processCheckout() {
            let w = this.activeWorksheet;
            if(this.paidAmount < this.currentTotal && this.paymentMethod === 'cash') {
                return alert('Jumlah bayar kurang dari total!');
            }

            this.isProcessing = true;

            let finalNotes = w.notes;
            if(w.tableNumber) {
                finalNotes = `No Meja: ${w.tableNumber} | ${finalNotes}`;
            }
            if(w.deliveryMode) {
                finalNotes = `[DELIVERY] ${finalNotes}`;
            }

            try {
                const res = await fetch('/pos/checkout', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        items: w.cart,
                        payment_method: this.paymentMethod,
                        paid_amount: this.paidAmount,
                        discount: w.globalDiscount,
                        discount_type: w.discountType,
                        customer_name: w.customerName,
                        customer_phone: w.customerPhone,
                        notes: finalNotes
                    })
                });

                const data = await res.json();
                if(res.ok) {
                    this.showPaymentModal = false;
                    this.receiptData = {
                        invoice: data.invoice_number,
                        change: data.change,
                        transaction_id: data.transaction.id
                    };
                    this.showReceiptModal = true;
                    this.fetchProducts(); // refresh stock
                } else {
                    alert(data.error || 'Terjadi kesalahan sistem.');
                }
            } catch(e) {
                alert('Kesalahan koneksi internet.');
            } finally {
                this.isProcessing = false;
            }
        },

        openGroupManager() {
            // Setup Draft States for Drag & Drop Editor
            this.draftGroups = JSON.parse(JSON.stringify(this.posGroups));
            
            // Find ungrouped products
            const groupedIds = new Set();
            this.draftGroups.forEach(g => {
                if(g.products) g.products.forEach(p => groupedIds.add(p.id));
            });
            this.draftUngrouped = this.products.filter(p => !groupedIds.has(p.id));

            this.showGroupManagerModal = true;
        },

        closeGroupManager() {
            if(!confirm('Batalkan perubahan layout?')) return;
            this.showGroupManagerModal = false;
        },

        addNewDraftGroup() {
            const name = prompt('Masukkan nama grup baru:');
            if(!name) return;
            this.draftGroups.unshift({
                id: 'new-' + Date.now(),
                name: name,
                color: '#10b981',
                position: 0,
                products: []
            });
        },

        deleteDraftGroup(gIndex) {
            if(!confirm('Hapus grup ini? Produk di dalamnya akan dikembalikan ke "Tidak Dikelompokkan".')) return;
            const g = this.draftGroups[gIndex];
            if (g.products && g.products.length > 0) {
                this.draftUngrouped.unshift(...g.products);
            }
            this.draftGroups.splice(gIndex, 1);
        },

        startDrag(evt, product, fromType, fromIndex) {
            this.draggedProduct = product;
            this.draggedFrom = { type: fromType, index: fromIndex };
        },

        dropItemToGroup(gIndex) {
            this.dragOverGroup = null;
            this.dragOverIndex = null;
            if(!this.draggedProduct) return;
            
            // Remove from source
            if(this.draggedFrom.type === 'ungrouped') {
                this.draftUngrouped.splice(this.draggedFrom.index, 1);
            } else {
                this.draftGroups[this.draggedFrom.type].products.splice(this.draggedFrom.index, 1);
            }
            
            // Add to destination
            if(!this.draftGroups[gIndex].products) this.draftGroups[gIndex].products = [];
            this.draftGroups[gIndex].products.push(this.draggedProduct);
            
            this.draggedProduct = null;
        },

        dropItemToIndex(gIndex, pIndex) {
            this.dragOverGroup = null;
            this.dragOverIndex = null;
            if(!this.draggedProduct) return;
            
            // Remove from source
            if(this.draggedFrom.type === 'ungrouped') {
                this.draftUngrouped.splice(this.draggedFrom.index, 1);
            } else {
                this.draftGroups[this.draggedFrom.type].products.splice(this.draggedFrom.index, 1);
            }
            
            // Re-adjust index if dropping in same group and moving forward
            let targetIndex = pIndex;
            if(this.draggedFrom.type === gIndex && this.draggedFrom.index < pIndex) {
                targetIndex--;
            }
            
            if(!this.draftGroups[gIndex].products) this.draftGroups[gIndex].products = [];
            this.draftGroups[gIndex].products.splice(targetIndex, 0, this.draggedProduct);
            
            this.draggedProduct = null;
        },

        dropItemToUngrouped() {
            this.dragOverGroup = null;
            this.dragOverIndex = null;
            if(!this.draggedProduct) return;
            
            if(this.draggedFrom.type === 'ungrouped') {
                this.draggedProduct = null;
                return; // Nothing happens
            }
            
            this.draftGroups[this.draggedFrom.type].products.splice(this.draggedFrom.index, 1);
            this.draftUngrouped.unshift(this.draggedProduct);
            this.draggedProduct = null;
        },

        async saveLayoutEditor() {
            this.isSavingGroup = true;
            try {
                // Prepare data payload with correct positions
                const payload = this.draftGroups.map((g, i) => ({
                    id: g.id,
                    name: g.name,
                    color: g.color,
                    products: (g.products || []).map((p, pIndex) => ({
                        id: p.id,
                        position: pIndex
                    }))
                }));

                const res = await fetch('/pos/groups/sync-all', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ groups: payload })
                });

                const data = await res.json();
                if(data.success) {
                    // Update live POS grid
                    this.posGroups = data.posGroups;
                    this.showGroupManagerModal = false;
                } else {
                    alert('Gagal menyimpan layout: ' + (data.error || 'Unknown error'));
                }
            } catch(e) {
                console.error(e);
                alert('Terjadi kesalahan koneksi.');
            }
            this.isSavingGroup = false;
        },

        closeReceiptAndReset() {
            this.showReceiptModal = false;
            let w = this.activeWorksheet;
            w.cart = [];
            w.globalDiscount = 0;
            w.discountType = 'nominal';
            w.customerName = '';
            w.customerPhone = '';
            w.tableNumber = '';
            w.notes = '';
            w.deliveryMode = false;
            this.receiptData = null;
        }
    }
}
</script>
@endsection
