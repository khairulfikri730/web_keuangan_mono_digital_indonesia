<div @click="addToCart(p)" 
     :data-category="p.category_id || p.category?.id || ''"
     x-show="filterProduct(p)"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform scale-95"
     x-transition:enter-end="opacity-100 transform scale-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 transform scale-100"
     x-transition:leave-end="opacity-0 transform scale-95"
     class="product-card group rounded-xl bg-white overflow-hidden cursor-pointer hover:shadow-lg hover:-translate-y-1 hover:scale-[1.02] active:scale-95 transition-all duration-300 border border-slate-100 flex shadow-[0_4px_12px_rgba(0,0,0,0.04)] relative"
     :class="[viewMode === 'grid' ? 'flex-col' : 'flex-row items-center p-3 gap-4', lastAddedId === p.id ? 'ring-2 ring-emerald-400 shadow-emerald-500/20' : '']">

    {{-- Image / Placeholder --}}
    <div :class="viewMode === 'grid' ? 'h-36 sm:h-40 w-full' : 'h-16 w-16 rounded-xl'" class="relative overflow-hidden shrink-0 bg-gradient-to-br from-blue-50 to-emerald-50 border-b border-slate-50">
        <template x-if="p.image">
            <img :src="'/storage/'+p.image" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 relative z-10">
        </template>
        <template x-if="!p.image">
            <div class="w-full h-full flex flex-col items-center justify-center select-none group-hover:scale-105 transition-transform duration-500 relative z-10 text-emerald-600/30">
                <i class="fas fa-image text-3xl mb-1"></i>
                <span class="text-sm font-bold uppercase tracking-widest" x-text="getInitials(p.name)"></span>
            </div>
        </template>
    </div>

    {{-- Info --}}
    <div class="p-3 sm:p-4 flex flex-col flex-1">
        <p class="text-[9px] sm:text-[10px] uppercase tracking-widest font-black text-slate-400 mb-1 truncate"
           x-text="p.category ? p.category.name : 'UMUM'"></p>
        <p class="text-xs sm:text-sm font-bold leading-tight mb-2 line-clamp-2 text-slate-700 group-hover:text-emerald-600 transition-colors"
           x-text="p.name"></p>
        
        <div class="flex items-end justify-between mt-auto">
            <div class="flex flex-col">
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Harga</p>
                <template x-if="p.is_promo && p.discount_price > 0">
                    <span class="text-[9px] text-slate-400 line-through font-bold -mb-1" x-text="formatRp(p.price)"></span>
                </template>
                <p class="font-black text-emerald-600 text-sm sm:text-base tracking-tight"
                   x-text="p.is_promo && p.discount_price > 0 ? formatRp(p.discount_price) : formatRp(p.price)"></p>
            </div>
            
            <div class="flex items-center gap-1.5">
                <template x-if="customPriceEnabled && customPriceShowBadge">
                    <button @click.stop="openCustomPrice(p)" 
                            class="h-6 px-2 rounded-lg flex items-center justify-center transition-all bg-orange-50 text-orange-600 hover:bg-orange-500 hover:text-white"
                            title="Set Harga Khusus">
                        <span class="text-[9px] font-black uppercase tracking-tighter text-nowrap">+ Harga Khusus</span>
                    </button>
                </template>
                <template x-if="!customPriceEnabled || !customPriceShowBadge">
                    <button class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center shadow-md shadow-emerald-500/30 group-hover:bg-emerald-600 group-hover:scale-110 active:scale-95 transition-all shrink-0">
                        <i class="fas fa-plus text-[10px] sm:text-xs"></i>
                    </button>
                </template>
            </div>
        </div>
    </div>

    {{-- Badges Khusus (top-left corner) --}}
    <div class="absolute top-2 left-2 flex flex-col gap-1 z-20">
        <template x-if="p.is_stockless">
            <span class="bg-indigo-500 text-white text-[9px] font-black px-2 py-1 rounded-full shadow-sm">
                Unlimited
            </span>
        </template>
        <template x-if="!p.is_stockless">
            <span class="backdrop-blur-md bg-white/90 border border-slate-100 text-[10px] font-black px-2 py-1 rounded-full shadow-sm text-emerald-700 flex items-center gap-1">
                <i class="fas fa-cubes text-[8px]"></i><span x-text="p.stock"></span>
            </span>
        </template>
    </div>
</div>
