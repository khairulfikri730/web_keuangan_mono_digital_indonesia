@extends('layouts.app')
@section('title', 'Jadwal Kerja')
@section('page-title', 'Jadwal Kerja')
@section('page-subtitle', 'Kelola lokasi, shift dan jadwal tim')

@section('content')
<div x-data="scheduleApp()" x-init="init()" class="space-y-6 text-slate-200">

    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-700/50 pb-2">
        <div class="flex gap-2 overflow-x-auto custom-scrollbar">
            @if(auth()->user()->role !== 'crew')
                <template x-for="t in tabs" :key="t.id">
                    <button @click="activeTab = t.id"
                        :class="activeTab === t.id ? 'text-yellow-400 border-b-2 border-yellow-400 font-bold' : 'text-slate-400 hover:text-white'"
                        class="px-4 py-2 text-sm whitespace-nowrap transition-colors">
                        <i :class="t.icon" class="mr-1"></i><span x-text="t.name"></span>
                    </button>
                </template>
            @else
                <button class="text-yellow-400 border-b-2 border-yellow-400 font-bold px-4 py-2 text-sm whitespace-nowrap">
                    <i class="fas fa-calendar-alt mr-1"></i> Jadwal Tim
                </button>
            @endif
        </div>
        <div class="flex gap-2">
            <a href="{{ route('schedules.attendances.index') }}" class="px-4 py-2 bg-blue-600/20 text-blue-400 border border-blue-500/50 rounded-xl text-sm font-bold hover:bg-blue-600 hover:text-white transition-colors">
                <i class="fas fa-camera mr-2"></i> Rekap Absensi
            </a>
            @if(auth()->user()->role !== 'crew')
            <a href="{{ route('schedules.payrolls.index') }}" class="px-4 py-2 bg-emerald-600/20 text-emerald-400 border border-emerald-500/50 rounded-xl text-sm font-bold hover:bg-emerald-600 hover:text-white transition-colors">
                <i class="fas fa-money-check-alt mr-2"></i> Penggajian (Payroll)
            </a>
            @endif
        </div>
    </div>

    @if(auth()->user()->role !== 'crew')

    @include('schedules._tab_dashboard')
    @include('schedules._tab_locations')
    @include('schedules._tab_shifts')
    @endif

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
        activeTab: '{{ auth()->user()->role === 'crew' ? 'jadwal' : $tab }}',
        viewMode: '{{ $viewMode }}',
        selectedDate: '{{ $date }}',
        selectedMonth: '{{ $month }}',
        assignmentsData: {!! json_encode($assignments->map(fn($a) => [
            'id' => $a->id, 'shift_id' => $a->schedule_shift_id,
            'user_id' => $a->user_id, 'date' => $a->date->format('Y-m-d'),
            'user_name' => $a->user->name ?? 'Unknown', 'notes' => $a->notes,
            'shift_name' => $a->shift->name ?? '', 'location_name' => $a->shift->location->name ?? '',
            'status' => $a->status, 'closed_by' => $a->closed_by, 'closed_reason' => $a->closed_reason,
            'original_user_id' => $a->original_user_id,
            'original_user_name' => $a->originalUser->name ?? null,
            'changed_by' => $a->changed_by,
        ])) !!},
        dates: {!! json_encode($dates) !!},
        tabs: [
            @if(auth()->user()->role !== 'crew')
            { id: 'dashboard', name: 'Dashboard', icon: 'fas fa-chart-pie' },
            { id: 'lokasi', name: 'Lokasi', icon: 'fas fa-building' },
            { id: 'shift', name: 'Shift', icon: 'fas fa-clock' },
            @endif
            { id: 'jadwal', name: 'Jadwal Tim', icon: 'fas fa-calendar-alt' },
        ],
        init() {},
        getForShiftDate(shiftId, date) {
            return this.assignmentsData.filter(a => a.shift_id == shiftId && a.date === date);
        },
        getForDate(date) {
            return this.assignmentsData.filter(a => a.date === date);
        },
        openQuickAssign(date, shiftId) {
            window.dispatchEvent(new CustomEvent('open-quick-add', { detail: { date: date, shiftId: shiftId } }));
        }
    }));
});
</script>
@endpush
