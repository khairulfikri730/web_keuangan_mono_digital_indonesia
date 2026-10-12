@php
    $isOwner = in_array(auth()->user()->role, ['superadmin', 'owner']);
    $tab = $tab ?? null;
    $class = fn ($key) => 'px-6 py-3 font-bold tracking-wide transition-colors whitespace-nowrap border-b-2 '
        . ($tab === $key
            ? 'border-emerald-500 text-emerald-500'
            : 'border-transparent text-slate-500 hover:text-slate-300');
@endphp

<div class="flex border-b border-slate-700/50 mb-2 overflow-x-auto hide-scrollbar">
    @if($isOwner)
        <a href="{{ route('schedules.index') }}" class="{{ $class('schedules') }}">Dashboard & Shift</a>
        <a href="{{ route('schedules.poster') }}" class="{{ $class('poster') }}">Poster Jadwal</a>
    @else
        <a href="{{ route('schedules.index') }}" class="{{ $class('schedules') }}">Jadwal Tim</a>
    @endif

    <a href="{{ route('schedules.attendances.index') }}" class="{{ $class('attendances') }}">Absensi</a>
    <a href="{{ route('schedules.overtimes.index') }}" class="{{ $class('overtimes') }}">Rekap Lembur</a>

    @if($isOwner)
        <a href="{{ route('schedules.payrolls.index') }}" class="{{ $class('payrolls') }}">Penggajian</a>
    @endif
</div>
