<div @click="addToCart(p)" 
     :data-category="p.category_id || p.category?.id || ''"
     x-show="activeCategory === '' || activeCategory === (p.category_id || p.category?.id || '')"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform scale-95"
     x-transition:enter-end="opacity-100 transform scale-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 transform scale-100"
     x-transition:leave-end="opacity-0 transform scale-95"
     class="product-card group bg-white rounded-2xl border overflow-hidden cursor-pointer hover:shadow-2xl hover:-translate-y-1 hover:scale-[1.03] active:scale-95 active:shadow-sm transition-all duration-300 relative flex"
     :style="`border-color: ${activeCategory === (p.category_id || p.category?.id || '') ? (p.category?.color || '#10b981') : (lastAddedId === p.id ? (p.category?.color || '#10b981') : '#e2e8f0')}; box-shadow: ${lastAddedId === p.id ? '0 0 0 4px '+(p.category?.color || '#10b981')+'40' : (activeCategory === (p.category_id || p.category?.id || '') ? '0 4px 15px -3px '+(p.category?.color || '#10b981')+'40' : '')};`"
     :class="[viewMode === 'grid' ? 'flex-col' : 'flex-row items-center p-3 gap-4', lastAddedId === p.id ? 'bg-slate-50' : '']">
    
    {{-- Accent Glow --}}
    <div class="absolute inset-0 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none z-0" :style="`background: radial-gradient(circle at top left, ${p.category?.color || '#94a3b8'}25, transparent 60%);`"></div>

    {{-- Image / Placeholder --}}
    <div :class="viewMode === 'grid' ? 'h-40 w-full' : 'h-20 w-20 rounded-xl'" class="bg-slate-100 relative overflow-hidden shrink-0 z-10">
        <template x-if="p.image">
            <img :src="'/storage/'+p.image" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500 relative z-10">
        </template>
        <template x-if="!p.image">
            <div class="w-full h-full flex flex-col items-center justify-center select-none group-hover:scale-110 transition-transform duration-500 relative z-10" 
                 :style="`background: linear-gradient(135deg, ${p.category?.color || '#cbd5e1'} 0%, ${adjustBrightness(p.category?.color || '#cbd5e1', -20)} 100%); color: ${getContrastYIQ(p.category?.color || '#cbd5e1')};`">
                <i :class="getPlaceholderIcon(p.category?.name)" class="text-3xl mb-1 opacity-50 drop-shadow-md"></i>
                <span class="text-2xl font-black opacity-80 tracking-tighter mix-blend-overlay drop-shadow-sm" x-text="getInitials(p.name)"></span>
            </div>
        </template>

        {{-- Stok Badge --}}
        <div class="absolute top-2 right-2 backdrop-blur-md bg-white/90 border border-white/50 text-[10px] font-black px-2.5 py-1 rounded-lg shadow-sm"
             :class="p.stock <= 0 && !['unlimited','service'].includes(p.product_kind) ? 'text-red-600' : 'text-emerald-700'">
            Stok: <span x-text="['unlimited','service'].includes(p.product_kind) ? '∞' : p.stock"></span>
        </div>
    </div>

    {{-- Info --}}
    <div class="p-4 flex flex-col flex-1">
        <p class="text-[10px] uppercase tracking-wider font-bold text-slate-400 mb-1 truncate" x-text="p.category ? p.category.name : '-'"></p>
        <p class="text-sm font-black text-slate-800 leading-snug mb-2 line-clamp-2 flex-1 group-hover:text-emerald-600 transition-colors" x-text="p.name"></p>
        
        <div class="flex items-center justify-between mt-auto pt-2 border-t border-slate-50">
            <p class="text-emerald-600 font-black text-lg tracking-tight" x-text="formatRp(p.price)"></p>
            <button class="w-9 h-9 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center opacity-100 lg:opacity-0 group-hover:opacity-100 transition-all hover:bg-emerald-500 hover:text-white shadow-sm hover:scale-110 active:scale-95" :class="viewMode !== 'grid' && 'lg:opacity-100'">
                <i class="fas fa-plus"></i>
            </button>
        </div>

        {{-- Badges Khusus --}}
        <div class="absolute top-2 left-2 flex flex-col gap-1">
            <span x-show="['unlimited','service'].includes(p.product_kind)" class="bg-blue-500 text-white text-[9px] font-black px-2 py-0.5 rounded shadow-sm">UNLIMITED</span>
            <span x-show="p.meta && (p.meta.includes('discounts') || p.meta.includes('variants'))" class="bg-orange-500 text-white text-[9px] font-black px-2 py-0.5 rounded shadow-sm">SPESIAL</span>
        </div>
    </div>
</div>
