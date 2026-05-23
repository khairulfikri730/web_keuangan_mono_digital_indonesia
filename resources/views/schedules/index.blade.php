@extends('layouts.app')
@section('title', 'Jadwal Kerja')
@section('page-title', 'Jadwal Kerja')
@section('page-subtitle', 'Kelola lokasi, shift dan jadwal tim')

@section('content')
<div x-data="scheduleApp()" x-init="init()" class="space-y-6 text-slate-200">

    {{-- Tabs --}}
    <div class="flex gap-2 border-b border-slate-700/50 pb-2 overflow-x-auto custom-scrollbar">
        <template x-for="t in tabs" :key="t.id">
            <button @click="activeTab = t.id"
                :class="activeTab === t.id ? 'text-yellow-400 border-b-2 border-yellow-400 font-bold' : 'text-slate-400 hover:text-white'"
                class="px-4 py-2 text-sm whitespace-nowrap transition-colors">
                <i :class="t.icon" class="mr-1"></i><span x-text="t.name"></span>
            </button>
        </template>
        <div class="ml-auto">
            <a href="{{ route('schedules.poster') }}" target="_blank" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 rounded-xl text-xs font-bold text-white shadow-lg transition-all flex items-center gap-2">
                <i class="fas fa-image"></i> Poster Mingguan
            </a>
        </div>
    </div>

    @include('schedules._tab_dashboard')
    @include('schedules._tab_locations')
    @include('schedules._tab_crew')
    @include('schedules._tab_shifts')
    @include('schedules._tab_schedule')
</div>

@include('schedules._modals')
@include('schedules._modals_actions')
@include('schedules._modals_weekly')
@endsection

@push('scripts')
<script>
function confirmDelete(form, txt) {
    Swal.fire({
        title: 'Apakah Anda Yakin?', text: txt || 'Data yang dihapus tidak dapat dikembalikan!',
        icon: 'warning', showCancelButton: true, confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b', confirmButtonText: 'Ya, Lanjutkan!', cancelButtonText: 'Batal'
    }).then(r => { if (r.isConfirmed) form.submit(); });
}

document.addEventListener('alpine:init', () => {
    Alpine.data('scheduleApp', () => ({
        activeTab: '{{ $tab }}',
        viewMode: '{{ $viewMode }}',
        selectedDate: '{{ $date }}',
        selectedMonth: '{{ $month }}',
        assignmentsData: {!! json_encode($assignments->map(fn($a) => [
            'id' => $a->id, 'shift_id' => $a->schedule_shift_id,
            'crew_id' => $a->schedule_crew_id, 'date' => $a->date->format('Y-m-d'),
            'crew_name' => $a->crew->name ?? 'Unknown', 'notes' => $a->notes,
            'shift_name' => $a->shift->name ?? '', 'location_name' => $a->shift->location->name ?? '',
            'status' => $a->status, 'closed_by' => $a->closed_by, 'closed_reason' => $a->closed_reason,
            'original_crew_id' => $a->original_crew_id,
            'original_crew_name' => $a->originalCrew->name ?? null,
            'changed_by' => $a->changed_by,
        ])) !!},
        dates: {!! json_encode($dates) !!},
        tabs: [
            { id: 'dashboard', name: 'Dashboard', icon: 'fas fa-chart-pie' },
            { id: 'lokasi', name: 'Lokasi', icon: 'fas fa-building' },
            { id: 'tim', name: 'Tim / Crew', icon: 'fas fa-users' },
            { id: 'shift', name: 'Shift', icon: 'fas fa-clock' },
            { id: 'jadwal', name: 'Jadwal Tim', icon: 'fas fa-calendar-alt' },
        ],
        init() {},
        getForShiftDate(shiftId, date) {
            return this.assignmentsData.filter(a => a.shift_id == shiftId && a.date === date);
        },
        getForDate(date) {
            return this.assignmentsData.filter(a => a.date === date);
        }
    }));
});
</script>
@endpush
