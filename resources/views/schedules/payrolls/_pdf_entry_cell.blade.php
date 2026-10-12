{{ number_format($data[$key], 0, ',', '.') }}
@foreach($data['entries']->where('component', $component)->take(4) as $entry)
    <br><span style="font-size: 7px; color: #666; font-style: italic;">{{ $entry->date->format('d/m') }}: {{ number_format($entry->amount, 0, ',', '.') }}{{ $entry->note ? ' - ' . $entry->note : '' }}</span>
@endforeach
@if($data['entries']->where('component', $component)->count() > 4)
    <br><span style="font-size: 7px; color: #999;">+{{ $data['entries']->where('component', $component)->count() - 4 }} entri lain</span>
@endif
