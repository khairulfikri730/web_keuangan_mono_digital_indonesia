<div @click="addToCart(p)" 
     :data-category="p.category_id || p.category?.id || ''"
     x-show="filterProduct(p)"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform scale-95"
     x-transition:enter-end="opacity-100 transform scale-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 transform scale-100"
     x-transition:leave-end="opacity-0 transform scale-95"
     class="product-card group rounded-2xl border overflow-hidden cursor-pointer hover:shadow-2xl hover:-translate-y-1 hover:scale-[1.03] active:scale-95 active:shadow-sm transition-all duration-300 relative flex"
     :style="`border-color: ${p.is_stockless ? (p.category?.color || '#6366f1') : (activeCategory !== '' && activeCategory === (p.category_id || p.category?.id || '') ? (p.category?.color || '#10b981') : (lastAddedId === p.id ? (p.category?.color || '#10b981') : '#e2e8f0'))}; box-shadow: ${lastAddedId === p.id ? '0 0 0 4px '+(p.category?.color || (p.is_stockless ? '#6366f1' : '#10b981'))+'40' : (activeCategory !== '' && activeCategory === (p.category_id || p.category?.id || '') ? '0 4px 15px -3px '+(p.category?.color || '#10b981')+'40' : (p.is_stockless ? '0 4px 15px -3px '+(p.category?.color || '#6366f1')+'20' : ''))}; background: white;`"
     :class="[viewMode === 'grid' ? 'flex-col' : 'flex-row items-center p-3 gap-4', lastAddedId === p.id ? 'ring-2 ring-offset-1 ring-emerald-400' : '']">
    
    {{-- Accent Glow --}}
    <div class="absolute inset-0 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none z-0" 
         :style="`background: radial-gradient(circle at top left, ${p.category?.color || (p.is_stockless ? '#818cf8' : '#94a3b8')}25, transparent 60%);`"></div>

    {{-- Unlimited Shimmer Overlay --}}
    <template x-if="p.is_stockless">
        <div class="absolute inset-0 rounded-2xl pointer-events-none z-0 overflow-hidden">
            <div class="absolute -inset-full top-0 h-full w-1/2 -skew-x-12 bg-gradient-to-r from-transparent via-white/20 to-transparent opacity-0 group-hover:opacity-100 group-hover:translate-x-[200%] transition-all duration-1000 ease-in-out"></div>
        </div>
    </template>

    {{-- Image / Placeholder --}}
    <div :class="viewMode === 'grid' ? 'h-40 w-full' : 'h-20 w-20 rounded-xl'" class="relative overflow-hidden shrink-0 z-10"
         :style="`background: linear-gradient(135deg, ${adjustBrightness(p.category?.color || (p.is_stockless ? '#e0e7ff' : '#f1f5f9'), 20)} 0%, ${p.category?.color || (p.is_stockless ? '#c7d2fe' : '#f1f5f9')} 100%);` font-weight: 100;">
        <template x-if="p.image">
            <img :src="'/storage/'+p.image" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500 relative z-10">
        </template>
        <template x-if="!p.image">
            <div class="w-full h-full flex flex-col items-center justify-center select-none group-hover:scale-110 transition-transform duration-500 relative z-10" 
                 :style="`background: linear-gradient(135deg, ${p.category?.color || (p.is_stockless ? '#6366f1' : '#cbd5e1')} 0%, ${adjustBrightness(p.category?.color || (p.is_stockless ? '#6366f1' : '#cbd5e1'), -20)} 100%); color: ${getContrastYIQ(p.category?.color || (p.is_stockless ? '#6366f1' : '#cbd5e1'))};`">
                <div class="flex flex-col items-center">
                    <i :class="getPlaceholderIcon(p.category?.name)" class="text-3xl mb-1 opacity-50 drop-shadow-md"></i>
                    <span class="text-2xl font-black opacity-80 tracking-tighter mix-blend-overlay drop-shadow-sm" x-text="getInitials(p.name)"></span>
                </div>
            </div>
        </template>

        {{-- Stok Badge --}}
        <template x-if="!p.is_stockless">
            <div class="absolute top-2 right-2 backdrop-blur-md bg-white/90 border border-white/50 text-[10px] font-black px-2.5 py-1 rounded-lg shadow-sm"
                 :class="p.stock <= 0 ? 'text-red-600 border-red-200' : 'text-emerald-700'">
                <i class="fas fa-cubes mr-0.5 text-[8px]"></i><span x-text="p.stock"></span>
            </div>
        </template>
        <template x-if="p.is_stockless">
            <div class="absolute top-2 right-2 backdrop-blur-md bg-indigo-600 border border-indigo-400 text-white text-[10px] font-black px-2.5 py-1 rounded-lg shadow-sm flex items-center gap-1">
                <i class="fas fa-infinity text-[9px]"></i>
            </div>
        </template>
    </div>

    {{-- Info --}}
    <div class="p-4 flex flex-col flex-1">
        <p class="text-[10px] uppercase tracking-wider font-bold mb-1 truncate transition-colors"
           :style="`color: ${p.category?.color || (p.is_stockless ? '#818cf8' : '#94a3b8')}`"
           x-text="p.category ? p.category.name : '-'"></p>
        <p class="text-sm font-black leading-snug mb-2 line-clamp-2 flex-1 transition-colors"
           :class="p.is_stockless ? 'text-indigo-800 group-hover:text-indigo-600' : 'text-slate-800 group-hover:text-emerald-600'"
           x-text="p.name"></p>
        
        <div class="flex items-center justify-between mt-auto pt-2 border-t"
             :class="p.is_stockless ? 'border-indigo-100' : 'border-slate-50'">
            <div class="flex flex-col">
                <template x-if="p.is_promo && p.discount_price > 0">
                    <span class="text-[9px] text-slate-400 line-through font-bold mb-[-4px]" x-text="formatRp(p.price)"></span>
                </template>
                <p class="font-black text-lg tracking-tight transition-colors"
                   :class="p.is_stockless ? 'text-indigo-600' : 'text-emerald-600'"
                   x-text="p.is_promo && p.discount_price > 0 ? formatRp(p.discount_price) : formatRp(p.price)"></p>
            </div>
            <div class="flex items-center gap-1.5">
                <template x-if="customPriceEnabled && customPriceShowBadge">
                    <button @click.stop="openCustomPrice(p)" 
                            class="h-9 px-2 rounded-xl flex items-center justify-center opacity-100 lg:opacity-0 group-hover:opacity-100 transition-all shadow-sm hover:scale-105 active:scale-95 border bg-orange-50 text-orange-600 border-orange-200 hover:bg-orange-500 hover:text-white"
                            title="Set Harga Khusus">
                        <i class="fas fa-tags text-[10px] mr-1"></i> <span class="text-[9px] font-black uppercase">Khusus</span>
                    </button>
                </template>
                <button class="w-9 h-9 rounded-xl flex items-center justify-center opacity-100 lg:opacity-0 group-hover:opacity-100 transition-all shadow-sm hover:scale-110 active:scale-95 border"
                        :style="`background-color: ${adjustBrightness(p.category?.color || '#10b981', 40)}; border-color: ${adjustBrightness(p.category?.color || '#10b981', 30)}; color: ${p.category?.color || '#10b981'}; ${viewMode !== 'grid' ? 'opacity: 1;' : ''}`">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- Badges Khusus (top-left corner) --}}
    <div class="absolute top-2 left-2 flex flex-col gap-1 z-20">
        {{-- UNLIMITED Badge --}}
        <template x-if="p.is_stockless">
            <span class="flex items-center gap-1 text-white text-[9px] font-black px-2 py-1 rounded-lg shadow-lg"
                  style="background: linear-gradient(90deg, #6366f1, #8b5cf6); box-shadow: 0 2px 8px #6366f140;">
                <i class="fas fa-infinity text-[8px]"></i> UNLIMITED
            </span>
        </template>
        {{-- SPESIAL Badge --}}
        <template x-if="p.meta && (p.meta.discounts || p.meta.variants)">
            <span class="bg-blue-500 text-white text-[9px] font-black px-2 py-0.5 rounded-lg shadow-sm flex items-center gap-1">
                <i class="fas fa-tag text-[8px]"></i> SPESIAL
            </span>
        </template>
        {{-- PROMO Badge --}}
        <template x-if="p.is_promo">
            <span class="bg-rose-500 text-white text-[9px] font-black px-2 py-1 rounded-lg shadow-lg flex items-center gap-1 animate-pulse"
                  style="box-shadow: 0 2px 10px #f43f5e40;">
                <i class="fas fa-fire text-[8px]"></i> PROMO
            </span>
        </template>
        {{-- BEST SELLER Badge --}}
        <template x-if="bestSellerProductIds.includes(p.id)">
            <span class="bg-amber-500 text-white text-[9px] font-black px-2 py-1 rounded-lg shadow-lg flex items-center gap-1"
                  style="box-shadow: 0 2px 10px #f59e0b40;">
                <i class="fas fa-crown text-[8px]"></i> TERLARIS
            </span>
        </template>
    </div>
</div>
