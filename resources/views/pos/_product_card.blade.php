<div @click="addToCart(p)" 
     :data-category="p.category_id || p.category?.id || ''"
     x-show="filterProduct(p)"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform scale-95"
     x-transition:enter-end="opacity-100 transform scale-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 transform scale-100"
     x-transition:leave-end="opacity-0 transform scale-95"
     class="product-card group rounded-xl bg-white overflow-hidden cursor-pointer hover:shadow-lg hover:-translate-y-1 active:scale-95 transition-all duration-300 border border-slate-200 flex flex-row items-center p-3 gap-4 shadow-sm relative"
     :class="[lastAddedId === p.id ? 'ring-2 ring-emerald-400 shadow-emerald-500/20 bg-emerald-50/30' : '']">

    {{-- Image / Placeholder (Left side, square) --}}
    <div class="h-20 w-20 sm:h-24 sm:w-24 rounded-lg relative overflow-hidden shrink-0 bg-slate-50 border border-slate-100 flex items-center justify-center">
        <template x-if="p.image">
            <img :src="'/storage/'+p.image" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 relative z-10">
        </template>
        <template x-if="!p.image">
            <div class="w-full h-full flex flex-col items-center justify-center select-none group-hover:scale-105 transition-transform duration-500 relative z-10 text-slate-300 bg-slate-100/50">
                <i class="fas fa-image text-2xl mb-1"></i>
                <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400" x-text="getInitials(p.name)"></span>
            </div>
        </template>
        
        {{-- Badges Khusus (Overlay on image) --}}
        <div class="absolute top-1 left-1 flex flex-col gap-1 z-20">
            <template x-if="p.is_stockless">
                <span class="bg-indigo-500/90 backdrop-blur-sm text-white text-[8px] font-black px-1.5 py-0.5 rounded shadow-sm">
                    UNLMTD
                </span>
            </template>
            <template x-if="!p.is_stockless">
                <span class="backdrop-blur-md bg-white/90 border border-slate-100 text-[9px] font-black px-1.5 py-0.5 rounded shadow-sm text-emerald-700 flex items-center gap-1">
                    <span x-text="p.stock"></span>
                </span>
            </template>
        </div>
    </div>

    {{-- Info (Right side) --}}
    <div class="flex flex-col flex-1 h-full py-1">
        <p class="text-[9px] sm:text-[10px] uppercase tracking-widest font-black text-slate-400 mb-0.5 truncate"
           x-text="p.category ? p.category.name : 'UMUM'"></p>
        <p class="text-sm font-bold leading-tight mb-2 line-clamp-2 text-slate-800 group-hover:text-emerald-600 transition-colors"
           x-text="p.name"></p>
        
        <div class="flex items-end justify-between mt-auto">
            <div class="flex flex-col">
                <template x-if="p.is_promo && p.discount_price > 0">
                    <span class="text-[9px] text-slate-400 line-through font-bold -mb-1" x-text="formatRp(p.price)"></span>
                </template>
                <p class="font-black text-emerald-600 text-sm tracking-tight"
                   x-text="p.is_promo && p.discount_price > 0 ? formatRp(p.discount_price) : formatRp(p.price)"></p>
            </div>
            
            <div class="flex items-center gap-2">
                <template x-if="customPriceEnabled && customPriceShowBadge">
                    <button @click.stop="openCustomPrice(p)" 
                            class="h-7 px-2 rounded-lg flex items-center justify-center transition-all bg-orange-50 text-orange-600 border border-orange-200 hover:bg-orange-500 hover:text-white shrink-0"
                            title="Set Harga Khusus">
                        <span class="text-[9px] font-black uppercase tracking-tighter text-nowrap">Khusus</span>
                    </button>
                </template>
                <template x-if="!customPriceEnabled || !customPriceShowBadge">
                    <button class="w-8 h-8 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center shadow-sm group-hover:bg-emerald-500 group-hover:text-white group-hover:border-emerald-500 active:scale-95 transition-all shrink-0">
                        <i class="fas fa-plus text-xs"></i>
                    </button>
                </template>
            </div>
        </div>
    </div>
</div>
