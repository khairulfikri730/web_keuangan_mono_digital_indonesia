@php
    $payroll = $crewFinancial['payroll'] ?? null;
    $entries = ($crewFinancial['entries'] ?? collect())->sortBy(function ($entry) {
        $order = array_search($entry->component, array_keys(\App\Models\PayrollEntry::COMPONENTS));
        return $order * 100000 + (int) $entry->date->format('Ymd');
    });

    $entryIcons = [
        'deduction' => ['fa-minus-circle', 'text-red-400'],
        'photographer_fee' => ['fa-camera', 'text-cyan-400'],
        'overtime_fee' => ['fa-clock', 'text-orange-400'],
        'bonus' => ['fa-gift', 'text-purple-400'],
    ];

    $hasNotes = $entries->isNotEmpty() || ($payroll && $payroll->notes);
@endphp

@if($hasNotes)
<div class="mt-4 bg-slate-800/80 border border-slate-700 p-4 rounded-xl relative overflow-hidden">
    <div class="absolute top-0 left-0 w-1 h-full bg-blue-500"></div>
    <h4 class="text-sm font-bold text-white mb-3 flex items-center gap-2"><i class="fas fa-clipboard-list text-blue-400"></i> Evaluasi Bulanan</h4>

    <div class="space-y-2">
        @foreach($entries as $entry)
        @php $icon = $entryIcons[$entry->component] ?? ['fa-circle-dot', 'text-slate-400']; @endphp
        <div class="flex items-start gap-2">
            <i class="fas {{ $icon[0] }} {{ $icon[1] }} mt-0.5 text-xs"></i>
            <p class="text-sm text-slate-300">
                <span class="text-slate-400 font-semibold mr-1">{{ $entry->date->translatedFormat('d M') }}:</span>
                Rp {{ number_format($entry->amount, 0, ',', '.') }}
                @if($entry->note)<span class="text-slate-400">&mdash; {{ $entry->note }}</span>@endif
            </p>
        </div>
        @endforeach

        @if($payroll && $payroll->notes)
        <div class="mt-4 bg-red-900/20 border border-red-700/50 rounded-lg p-3 flex items-start gap-3">
            <div class="mt-0.5">
                <i class="fas fa-exclamation-triangle text-red-500"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-red-400 uppercase tracking-wider mb-1">Perhatian Khusus / Evaluasi</p>
                <p class="text-sm text-red-200">{{ $payroll->notes }}</p>
            </div>
        </div>
        @endif
    </div>
</div>
@endif
