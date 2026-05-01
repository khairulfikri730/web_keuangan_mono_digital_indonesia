@extends('layouts.app')

@section('title', 'Edit Produk')
@section('page-title', 'Edit Produk')
@section('page-subtitle', 'Control Panel Produk')

@section('content')
<div x-data="productEditApp()" class="flex flex-col gap-6" x-init="initApp()">

    {{-- HEADER & ACTION BAR --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-[#1e293b] p-5 rounded-2xl border border-slate-700/80 shadow-sm sticky top-20 z-40">
        <div>
            <h2 class="text-xl font-black text-white tracking-tight">Edit Produk</h2>
            <p class="text-sm text-slate-400 font-medium mt-1">Mengedit data: <span class="text-blue-400">{{ $product->name }}</span></p>
        </div>
        
        <div class="flex items-center gap-3 w-full sm:w-auto">
            <button type="button" @click="$refs.deleteForm.submit()" class="bg-red-500/10 hover:bg-red-500 hover:text-white text-red-500 px-5 py-2.5 rounded-xl text-sm font-bold transition-all shadow-sm border border-red-500/20 active:scale-95 flex items-center gap-2">
                <i class="fas fa-trash"></i> <span class="hidden sm:inline">Hapus</span>
            </button>
            <button type="button" @click="$refs.mainForm.submit()" class="shrink-0 bg-blue-600 hover:bg-blue-700 text-white px-8 py-2.5 rounded-xl text-sm font-bold transition-all shadow-lg shadow-blue-500/30 active:scale-95 flex items-center gap-2">
                <i class="fas fa-save"></i> Simpan Perubahan
            </button>
        </div>
    </div>

    {{-- Hidden Form Delete --}}
    <form x-ref="deleteForm" action="{{ route('products.destroy', $product) }}" method="POST" class="hidden" onsubmit="return confirm('PERINGATAN: Yakin ingin menghapus produk ini secara permanen?')">
        @csrf @method('DELETE')
    </form>

    {{-- MAIN CONTENT GRID --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        {{-- KIRI: INFO PRODUK (60% / col-span-8) --}}
        <div class="lg:col-span-8 space-y-6">
            <form x-ref="mainForm" action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf @method('PUT')
                <input type="hidden" name="product_kind" :value="form.kind">
                <input type="hidden" name="product_type" value="{{ $product->product_type }}">

                {{-- CARD: JENIS PRODUK --}}
                <div class="bg-slate-800 rounded-2xl p-6 border border-slate-700/80 shadow-sm relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-1 h-full bg-indigo-500"></div>
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-wider mb-5 flex items-center gap-2"><i class="fas fa-tag text-indigo-400"></i> Jenis Produk</h3>
                    <div class="grid grid-cols-3 md:grid-cols-6 gap-2">
                        <template x-for="k in kinds" :key="k.id">
                            <button type="button" @click="form.kind = k.id"
                                :class="form.kind === k.id ? 'ring-2 ring-indigo-500 bg-indigo-500/10 text-indigo-400' : 'border-slate-600 text-slate-400 hover:border-indigo-400/50'"
                                class="p-2 rounded-xl border flex flex-col items-center gap-1.5 font-semibold text-[11px] transition-all">
                                <i :class="k.icon" class="text-lg mb-0.5"></i>
                                <span x-text="k.label"></span>
                            </button>
                        </template>
                    </div>
                    {{-- Info Banner --}}
                    <div class="mt-4 px-4 py-3 rounded-xl text-xs font-medium border flex items-start gap-3" style="background:rgba(99,102,241,.1);border-color:rgba(99,102,241,.2);color:#a5b4fc;">
                        <i class="fas fa-info-circle mt-0.5 shrink-0"></i>
                        <span x-html="currentKindInfo"></span>
                    </div>
                </div>
                
                {{-- CARD: INFO DASAR --}}
                <div class="bg-slate-800 rounded-2xl p-6 border border-slate-700/80 shadow-sm relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-1 h-full bg-blue-500"></div>
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-wider mb-6 flex items-center gap-2"><i class="fas fa-info-circle text-blue-400"></i> Informasi Dasar</h3>
                    
                    <div class="space-y-5">
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-2 block">Nama Produk <span class="text-red-400">*</span></label>
                            <input type="text" name="name" x-model="form.name" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-sm font-bold text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors shadow-inner" required>
                        </div>

                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-2 block">Kategori</label>
                            <div class="relative">
                                <select name="category_id" x-model="form.categoryId" @change="updateCategory()" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-sm font-bold text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors shadow-inner appearance-none pl-12">
                                    <option value="">-- Tanpa Kategori --</option>
                                    @foreach($categories as $c)
                                    <option value="{{ $c->id }}" data-color="{{ $c->color }}" data-name="{{ $c->name }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 rounded-full border border-slate-600 shadow-sm" :style="`background-color: ${activeCategoryColor}`"></div>
                                <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-500 text-xs pointer-events-none"></i>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-2 block">SKU Internal</label>
                                <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-sm font-medium text-slate-200 focus:outline-none focus:border-blue-500 transition-colors shadow-inner font-mono" placeholder="PROD-001">
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-2 block">Barcode Scanner</label>
                                <div class="relative">
                                    <input type="text" name="barcode" value="{{ old('barcode', $product->barcode) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 pl-10 text-sm font-medium text-slate-200 focus:outline-none focus:border-blue-500 transition-colors shadow-inner font-mono" placeholder="899...">
                                    <i class="fas fa-barcode absolute left-4 top-1/2 -translate-y-1/2 text-slate-500"></i>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-2 block">Deskripsi & Catatan</label>
                            <textarea name="description" rows="3" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-4 text-sm font-medium text-slate-200 focus:outline-none focus:border-blue-500 transition-colors shadow-inner resize-none">{{ old('description', $product->description) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- CARD: HARGA & STOK --}}
                <div class="bg-slate-800 rounded-2xl p-6 border border-slate-700/80 shadow-sm relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-1 h-full bg-emerald-500"></div>
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-wider mb-6 flex items-center gap-2"><i class="fas fa-tags text-emerald-400"></i> Harga & Stok</h3>
                    
                    <div class="space-y-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-2 block">Harga Jual (Rp) <span class="text-red-400">*</span></label>
                                <input type="number" name="price" x-model.number="form.price" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-lg font-black text-emerald-400 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors shadow-inner" required min="0">
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-2 block">Harga Modal (Rp)</label>
                                <input type="number" name="cost_price" x-model.number="form.costPrice" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-sm font-bold text-slate-300 focus:outline-none focus:border-blue-500 transition-colors shadow-inner" min="0">
                            </div>
                        </div>

                        {{-- Margin Indicator --}}
                        <div class="bg-slate-900/50 rounded-xl p-3 border border-slate-700 flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-400">Estimasi Margin Keuntungan:</span>
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-black" :class="marginAmount > 0 ? 'text-emerald-400' : 'text-slate-400'" x-text="formatRp(marginAmount)"></span>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-slate-800 border border-slate-700 text-slate-300" x-text="marginPercent + '%'"></span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 pt-2" x-show="isStockBased">
                            <div>
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-2 block">Minimum Stok (Peringatan)</label>
                                <input type="number" name="min_stock" value="{{ old('min_stock', $product->min_stock) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-sm font-bold text-amber-400 focus:outline-none focus:border-amber-500 transition-colors shadow-inner" min="0">
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-2 block">Satuan Unit <span class="text-red-400">*</span></label>
                                <input type="text" name="unit" x-model="form.unit" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-sm font-bold text-white focus:outline-none focus:border-blue-500 transition-colors shadow-inner" required placeholder="Pcs / Cup / Box">
                            </div>
                        </div>
                        {{-- Keterangan Unlimited/Jasa --}}
                        <div x-show="!isStockBased" class="flex items-center gap-3 bg-indigo-500/10 border border-indigo-500/30 rounded-xl p-4">
                            <i class="fas fa-infinity text-2xl text-indigo-400"></i>
                            <div>
                                <p class="text-sm font-black text-indigo-300">Tanpa Batasan Stok</p>
                                <p class="text-xs text-indigo-400/70 mt-0.5">Produk jenis <span class="font-bold" x-text="kinds.find(k=>k.id===form.kind)?.label"></span> tidak memerlukan manajemen stok fisik.</p>
                            </div>
                        </div>

                        {{-- Current Stock Highlight --}}
                        <div class="bg-gradient-to-r from-slate-900 to-slate-800 rounded-xl p-4 border border-slate-700 flex items-center justify-between relative overflow-hidden">
                            @if($product->stock <= $product->min_stock && $product->stock > 0)
                                <div class="absolute inset-0 border-2 border-amber-500/30 rounded-xl pointer-events-none"></div>
                                <div class="absolute -right-4 -top-4 w-16 h-16 bg-amber-500/10 rounded-full blur-xl pointer-events-none"></div>
                            @elseif($product->stock <= 0)
                                <div class="absolute inset-0 border-2 border-red-500/30 rounded-xl pointer-events-none"></div>
                                <div class="absolute -right-4 -top-4 w-16 h-16 bg-red-500/10 rounded-full blur-xl pointer-events-none"></div>
                            @endif
                            
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-0.5">Kondisi Stok Saat Ini</p>
                                <div class="flex items-baseline gap-2">
                                    <h4 class="text-3xl font-black {{ $product->stock <= 0 ? 'text-red-400' : ($product->stock <= $product->min_stock ? 'text-amber-400' : 'text-white') }}">{{ $product->stock }}</h4>
                                    <span class="text-sm font-bold text-slate-500" x-text="form.unit"></span>
                                </div>
                                @if($product->stock <= 0)
                                    <p class="text-[10px] font-bold text-red-400 mt-1"><i class="fas fa-exclamation-triangle"></i> Stok habis!</p>
                                @elseif($product->stock <= $product->min_stock)
                                    <p class="text-[10px] font-bold text-amber-400 mt-1"><i class="fas fa-exclamation-circle"></i> Stok menipis (Min: {{ $product->min_stock }})</p>
                                @endif
                            </div>
                            <a href="{{ route('stock.index', ['search' => $product->name]) }}" class="shrink-0 bg-slate-800 hover:bg-slate-700 text-white px-4 py-2.5 rounded-xl text-xs font-bold transition-all border border-slate-600 flex items-center gap-2 active:scale-95 shadow-sm">
                                <i class="fas fa-sliders-h"></i> Sesuaikan Stok
                            </a>
                        </div>
                    </div>
                </div>

                {{-- CARD: UPLOAD FOTO --}}
                <div class="bg-slate-800 rounded-2xl p-6 border border-slate-700/80 shadow-sm relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-1 h-full bg-purple-500"></div>
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-wider mb-4 flex items-center gap-2"><i class="fas fa-image text-purple-400"></i> Visualisasi Produk</h3>
                    
                    <div class="relative group">
                        <input type="file" name="image" id="file_upload" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" @change="previewImage">
                        
                        <div class="w-full border-2 border-dashed border-slate-600 rounded-2xl p-8 text-center bg-slate-900 group-hover:border-purple-500 group-hover:bg-slate-800/80 transition-all duration-300">
                            
                            <template x-if="!form.imagePreviewUrl && !'{{ $product->image }}'">
                                <div class="flex flex-col items-center justify-center pointer-events-none">
                                    <div class="w-16 h-16 bg-slate-800 rounded-full flex items-center justify-center mb-4 shadow-inner">
                                        <i class="fas fa-cloud-upload-alt text-2xl text-purple-400"></i>
                                    </div>
                                    <p class="text-sm font-bold text-white mb-1">Tarik & Lepas Foto Kesini</p>
                                    <p class="text-xs font-medium text-slate-500">atau klik untuk menelusuri (JPG, PNG, WEBP)</p>
                                </div>
                            </template>

                            <template x-if="form.imagePreviewUrl || '{{ $product->image }}'">
                                <div class="flex flex-col items-center pointer-events-none">
                                    <img :src="form.imagePreviewUrl || '/storage/{{ $product->image }}'" class="h-32 object-contain rounded-xl shadow-lg mb-4 ring-4 ring-slate-800">
                                    <p class="text-xs font-bold text-purple-400 bg-purple-500/10 px-3 py-1 rounded-full"><i class="fas fa-sync-alt mr-1"></i> Klik area ini untuk mengganti foto</p>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Hidden input for Alpine binding to form submit --}}
                <input type="hidden" name="is_active" :value="form.isActive ? '1' : '0'">

            </form>
        </div>

        {{-- KANAN: SIDEBAR & PREVIEW (40% / col-span-4) --}}
        <div class="lg:col-span-4 space-y-6">
            
            {{-- CARD: STATUS --}}
            <div class="bg-slate-800 rounded-2xl p-5 border border-slate-700/80 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-black text-white block">Status Visibilitas</h3>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mt-0.5">Tampil di halaman POS</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" x-model="form.isActive" class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500 shadow-inner"></div>
                    </label>
                </div>
            </div>

            {{-- CARD: POS PREVIEW --}}
            <div class="bg-slate-800 rounded-2xl p-6 border border-slate-700/80 shadow-sm flex flex-col items-center">
                <h3 class="text-xs font-black text-slate-400 uppercase tracking-wider mb-6 self-start flex items-center gap-2"><i class="fas fa-eye text-slate-300"></i> Live Preview POS</h3>
                
                {{-- Mockup POS Card --}}
                <div class="w-full max-w-[200px] bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xl transform hover:scale-[1.02] transition-transform duration-300 flex flex-col"
                     :style="`box-shadow: 0 10px 25px -5px ${activeCategoryColor || '#94a3b8'}40;`">
                    
                    {{-- Image Placeholder --}}
                    <div class="h-32 w-full relative overflow-hidden shrink-0 bg-slate-100 flex items-center justify-center">
                        <template x-if="form.imagePreviewUrl || '{{ $product->image }}'">
                            <img :src="form.imagePreviewUrl || '/storage/{{ $product->image }}'" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!form.imagePreviewUrl && !'{{ $product->image }}'">
                            <div class="w-full h-full flex flex-col items-center justify-center select-none" 
                                 :style="`background: linear-gradient(135deg, ${activeCategoryColor || '#cbd5e1'} 0%, ${adjustBrightness(activeCategoryColor || '#cbd5e1', -20)} 100%); color: ${getContrastYIQ(activeCategoryColor || '#cbd5e1')};`">
                                <i class="fas fa-box-open text-2xl mb-1 opacity-50 drop-shadow-md"></i>
                                <span class="text-xl font-black opacity-80 tracking-tighter mix-blend-overlay drop-shadow-sm" x-text="getInitials(form.name)"></span>
                            </div>
                        </template>
                    </div>

                    {{-- Info --}}
                    <div class="p-3 flex flex-col flex-1">
                        <p class="text-[9px] uppercase tracking-wider font-bold text-slate-400 mb-0.5 truncate" x-text="activeCategoryName || '-'"></p>
                        <p class="text-xs font-black text-slate-800 leading-snug mb-2 line-clamp-2" x-text="form.name || 'Nama Produk'"></p>
                        
                        <div class="flex items-center justify-between mt-auto pt-2 border-t border-slate-50">
                            <p class="text-emerald-600 font-black text-sm tracking-tight" x-text="formatRp(form.price)"></p>
                            <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shadow-sm">
                                <i class="fas fa-plus text-xs"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <p class="text-[10px] font-medium text-slate-500 mt-4 text-center">Inilah tampilan produk Anda di mata kasir pada halaman POS.</p>
            </div>

            {{-- CARD: QUICK ACTIONS --}}
            <div class="bg-slate-800 rounded-2xl p-5 border border-slate-700/80 shadow-sm space-y-3">
                <a href="{{ route('pos.index') }}" class="w-full flex items-center justify-between p-3 rounded-xl bg-slate-900 hover:bg-slate-700 border border-slate-700 transition-colors group">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-500/10 flex items-center justify-center text-blue-400 group-hover:bg-blue-500 group-hover:text-white transition-colors"><i class="fas fa-cash-register"></i></div>
                        <span class="text-sm font-bold text-slate-300 group-hover:text-white">Lihat di POS</span>
                    </div>
                    <i class="fas fa-external-link-alt text-slate-500 text-xs"></i>
                </a>
            </div>

        </div>
    </div>
</div>

<script>
function productEditApp() {
    return {
        form: {
            name: '{{ addslashes(old('name', $product->name)) }}',
            categoryId: '{{ old('category_id', $product->category_id) }}',
            price: {{ old('price', (int)$product->price) }},
            costPrice: {{ old('cost_price', (int)$product->cost_price) }},
            unit: '{{ old('unit', $product->unit) }}',
            isActive: {{ old('is_active', $product->is_active) ? 'true' : 'false' }},
            kind: '{{ old('product_kind', $product->product_kind ?? 'regular') }}',
            imagePreviewUrl: null
        },

        kinds: [
            { id:'regular',   label:'Biasa',     icon:'fas fa-cube',         info:'<strong>Produk Biasa:</strong> Stok berkurang otomatis per 1 unit saat ada transaksi.' },
            { id:'weight',    label:'Timbangan', icon:'fas fa-weight-hanging', info:'<strong>Timbangan:</strong> Harga dan stok berdasarkan berat (kg/gram).' },
            { id:'unlimited', label:'Unlimited', icon:'fas fa-infinity',      info:'<strong>Unlimited:</strong> Produk digital atau lisensi tanpa batasan stok fisik. Dapat dijual tanpa batas.' },
            { id:'service',   label:'Jasa',      icon:'fas fa-wrench',        info:'<strong>Jasa:</strong> Layanan tanpa stok fisik (contoh: Pemasangan, Perbaikan).' },
            { id:'bundle',    label:'Bundle',    icon:'fas fa-layer-group',   info:'<strong>Bundle / Paket:</strong> HPP bergantung pada komponen produk di dalamnya.' },
            { id:'formula',   label:'Formula',   icon:'fas fa-flask',         info:'<strong>Formula:</strong> Harga dinamis dihitung saat transaksi.' }
        ],

        get isStockBased() {
            return ['regular', 'weight'].includes(this.form.kind);
        },

        get currentKindInfo() {
            const k = this.kinds.find(x => x.id === this.form.kind);
            return k ? k.info : '';
        },
        activeCategoryColor: '#cbd5e1',
        activeCategoryName: '',

        initApp() {
            this.updateCategory();
        },

        get marginAmount() {
            let p = parseInt(this.form.price) || 0;
            let c = parseInt(this.form.costPrice) || 0;
            return p - c;
        },

        get marginPercent() {
            let p = parseInt(this.form.price) || 0;
            let m = this.marginAmount;
            if(p <= 0) return 0;
            return Math.round((m / p) * 100);
        },

        formatRp(val) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val);
        },

        updateCategory() {
            if(!this.form.categoryId) {
                this.activeCategoryColor = '#cbd5e1';
                this.activeCategoryName = 'Tanpa Kategori';
                return;
            }
            let select = document.querySelector('select[name="category_id"]');
            if(select && select.options[select.selectedIndex]) {
                let opt = select.options[select.selectedIndex];
                this.activeCategoryColor = opt.dataset.color || '#cbd5e1';
                this.activeCategoryName = opt.dataset.name || '';
            }
        },

        previewImage(e) {
            const file = e.target.files[0];
            if (file) {
                this.form.imagePreviewUrl = URL.createObjectURL(file);
            }
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
        }
    }
}
</script>
@endsection
