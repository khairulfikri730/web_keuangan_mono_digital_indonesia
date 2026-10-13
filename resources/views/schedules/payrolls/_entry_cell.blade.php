@php
    $list = $data['entries']->where('component', $component)->sortBy('date');
    $ringkas = $list->count() > 2;
@endphp
Rp {{ number_format($data[$key], 0, ',', '.') }}
@if($list->isNotEmpty())
    <div x-data="{ buka: false }" class="mt-1 ml-auto max-w-[170px] space-y-1 text-right">
        <div :class="buka ? '' : 'line-clamp-3'" class="space-y-0.5 transition-all duration-200 overflow-hidden">
            @foreach($list as $entry)
            <div class="text-[9px] font-normal text-slate-400 leading-tight break-words italic">
                {{ $entry->date->format('d/m') }} &middot; Rp {{ number_format($entry->amount, 0, ',', '.') }}@if($entry->note) &mdash; {{ $entry->note }}@endif
            </div>
            @endforeach
        </div>
        @if($ringkas)
        <button type="button" @click="buka = !buka" class="text-[9px] font-bold uppercase tracking-widest text-slate-500 hover:text-slate-300 inline-flex items-center gap-1 transition-colors">
            <span x-text="buka ? 'Tutup' : 'Detail'"></span>
            <i class="fas fa-chevron-down transition-transform" :class="buka && 'rotate-180'"></i>
        </button>
        @endif
    </div>
@endif
