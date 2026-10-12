@php
    $list = $data['entries']->where('component', $component);
@endphp
Rp {{ number_format($data[$key], 0, ',', '.') }}
@if($list->isNotEmpty())
    <div class="mt-1 ml-auto max-w-[150px] space-y-0.5 text-right">
        @foreach($list->take(4) as $entry)
        <div class="text-[9px] font-normal text-slate-400 leading-tight break-words italic">
            {{ $entry->date->format('d/m') }} &middot; Rp {{ number_format($entry->amount, 0, ',', '.') }}@if($entry->note) &mdash; {{ $entry->note }}@endif
        </div>
        @endforeach
        @if($list->count() > 4)
        <div class="text-[9px] font-bold text-slate-500">+{{ $list->count() - 4 }} entri lainnya</div>
        @endif
    </div>
@endif
