@extends('layouts.app')
@section('title', 'Rekap Gaji & Payroll')
@section('page-title', 'Penggajian (Payroll)')
@section('page-subtitle', 'Manajemen gaji bulanan, bonus, lembur, dan potongan crew.')

@section('content')
@php
    $defaultEntryDate = \Carbon\Carbon::now()->format('Y-m') === $month
        ? \Carbon\Carbon::now()->toDateString()
        : $month . '-01';
@endphp

<div class="space-y-6 text-slate-200" x-data="{
    selectedUsers: [],
    selectAll: true,
    allUserIds: {{ json_encode(collect($salaryData)->pluck('user.id')->map(fn($id) => (string)$id)->values()) }},
    toggleAll() {
        if (this.selectAll) {
            this.selectedUsers = [...this.allUserIds];
        } else {
            this.selectedUsers = [];
        }
    },
    init() {
        this.selectedUsers = [...this.allUserIds];
        this.$watch('selectedUsers', val => {
            this.selectAll = val.length === this.allUserIds.length && this.allUserIds.length > 0;
        });
    }
}">

    @include('schedules._nav_tabs', ['tab' => 'payrolls'])

    @if(session('success'))
    <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 p-4 rounded-xl flex items-center gap-3">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="bg-red-500/10 border border-red-500/20 text-red-400 p-4 rounded-xl flex items-center gap-3">
        <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
    </div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-4">
        <a href="{{ route('schedules.index') }}" class="px-4 py-2 bg-slate-800 text-slate-300 hover:text-white rounded-xl text-sm font-bold border border-slate-700 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i> Kembali ke Jadwal
        </a>

        <div class="flex items-center gap-2">
            <form method="GET" class="flex gap-2 mr-2">
                <input type="month" name="month" value="{{ $month }}" class="bg-slate-900 border border-slate-700 rounded-lg px-3 py-1.5 text-white text-sm focus:outline-none focus:border-emerald-500">
                <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-bold rounded-lg transition-colors">Tampilkan</button>
            </form>
            <a :href="`{{ route('schedules.payrolls.export-pdf', ['month' => $month]) }}&users=${selectedUsers.join(',')}`" class="px-3 py-1.5 bg-red-600/20 hover:bg-red-600/40 text-red-400 text-sm font-bold rounded-lg border border-red-500/30 transition-colors" target="_blank" title="Export PDF">
                <i class="fas fa-file-pdf"></i>
            </a>
            <a :href="`{{ route('schedules.payrolls.export-excel', ['month' => $month]) }}&users=${selectedUsers.join(',')}`" class="px-3 py-1.5 bg-green-600/20 hover:bg-green-600/40 text-green-400 text-sm font-bold rounded-lg border border-green-500/30 transition-colors" title="Export Excel">
                <i class="fas fa-file-excel"></i>
            </a>
        </div>
    </div>

    <div class="bg-emerald-500/10 border border-emerald-500/30 p-6 rounded-2xl flex items-center justify-between">
        <div>
            <p class="text-sm text-emerald-400 font-bold mb-1">Total Pengeluaran Gaji Bulan Ini</p>
            <h2 class="text-3xl font-black text-white">Rp {{ number_format($totalSistem, 0, ',', '.') }}</h2>
            <p class="text-xs text-slate-400 mt-1">Periode: {{ \Carbon\Carbon::parse($month.'-01')->translatedFormat('F Y') }}</p>
        </div>
        <div class="w-16 h-16 rounded-full bg-emerald-500/20 flex items-center justify-center text-emerald-400 text-3xl">
            <i class="fas fa-wallet"></i>
        </div>
    </div>

    <div class="bg-slate-800/80 border border-slate-700 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-slate-300">
                <thead class="text-[10px] font-bold text-slate-400 uppercase bg-slate-900/50 border-b border-slate-700">
                    <tr>
                        <th class="px-4 py-3 text-left w-10">
                            <input type="checkbox" x-model="selectAll" @change="toggleAll" class="w-4 h-4 text-emerald-500 bg-slate-900 border-slate-700 rounded focus:ring-emerald-500 focus:ring-2">
                        </th>
                        <th class="px-4 py-3 text-left">Crew</th>
                        <th class="px-4 py-3 text-right">Tunjangan/Gaji Pokok</th>
                        <th class="px-4 py-3 text-right">Komisi Shift</th>
                        <th class="px-4 py-3 text-right">Motret / Project</th>
                        <th class="px-4 py-3 text-right">Lembur</th>
                        <th class="px-4 py-3 text-right">Bonus</th>
                        <th class="px-4 py-3 text-right text-red-400">Potongan/Kasbon</th>
                        <th class="px-4 py-3 text-right text-emerald-400 font-black">TOTAL BERSIH</th>
                        <th class="px-4 py-3 text-right text-blue-400 font-bold border-l border-slate-700">Cash PB (Di Tangan)</th>
                        <th class="px-4 py-3 text-left">Catatan</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50">
                    @forelse($salaryData as $data)
                    <tr class="hover:bg-slate-700/20 transition-colors">
                        <td class="px-4 py-3">
                            <input type="checkbox" x-model="selectedUsers" value="{{ $data['user']->id }}" class="w-4 h-4 text-emerald-500 bg-slate-900 border-slate-700 rounded focus:ring-emerald-500 focus:ring-2">
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-xs font-black text-white flex-shrink-0">{{ substr($data['user']->name, 0, 1) }}</div>
                                <div>
                                    <span class="font-bold text-slate-800 dark:text-white">{{ $data['user']->name }}</span>
                                    @if($data['user']->allowance_type !== 'none')
                                    <span class="text-[9px] px-1.5 py-0.5 bg-blue-500/20 text-blue-400 rounded-full ml-1">{{ $data['user']->allowance_type == 'daily' ? 'Harian' : 'Bulanan' }}</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-blue-400">Rp {{ number_format($data['allowance'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-bold text-slate-300">
                            Rp {{ number_format($data['komisi_shift'], 0, ',', '.') }}
                            <div class="text-[9px] font-normal text-slate-500">{{ $data['total_shifts'] }} shift</div>
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-yellow-400 align-top">
                            @include('schedules.payrolls._entry_cell', ['data' => $data, 'key' => 'motret', 'component' => 'photographer_fee'])
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-orange-400 align-top">
                            @include('schedules.payrolls._entry_cell', ['data' => $data, 'key' => 'lembur', 'component' => 'overtime_fee'])
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-emerald-400 align-top">
                            @include('schedules.payrolls._entry_cell', ['data' => $data, 'key' => 'bonus', 'component' => 'bonus'])
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-red-400 align-top">
                            - @include('schedules.payrolls._entry_cell', ['data' => $data, 'key' => 'kasbon', 'component' => 'deduction'])
                        </td>
                        <td class="px-4 py-3 text-right font-black text-emerald-400 text-base bg-emerald-500/5 align-top">
                            Rp {{ number_format($data['total_bersih'], 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right align-top border-l border-slate-700">
                            <div class="font-bold text-blue-400">Rp {{ number_format($data['cash_balance'], 0, ',', '.') }}</div>
                            @if($data['cash_income'] > 0 || $data['cash_expense'] > 0)
                            <div class="text-[9px] text-slate-500 mt-1">In: Rp {{ number_format($data['cash_income'], 0, ',', '.') }}</div>
                            <div class="text-[9px] text-slate-500">Out: Rp {{ number_format($data['cash_expense'], 0, ',', '.') }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-400 align-top min-w-[150px] max-w-[250px]">
                            @if($data['payroll'] && $data['payroll']->notes)
                                <div x-data="{ expanded: false }">
                                    <div :class="expanded ? '' : 'line-clamp-2'" class="transition-all duration-300 overflow-hidden text-ellipsis whitespace-pre-wrap leading-relaxed">
                                        {{ $data['payroll']->notes }}
                                    </div>
                                    <button @click="expanded = !expanded" type="button" class="text-emerald-400 hover:text-emerald-300 text-[10px] mt-2 flex items-center gap-1 font-bold uppercase tracking-widest bg-emerald-500/10 px-2 py-1 rounded transition-colors">
                                        <span x-text="expanded ? 'Tutup' : 'Lihat Catatan'"></span>
                                        <i class="fas" :class="expanded ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                                    </button>
                                </div>
                            @else
                                <span class="text-slate-600">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center align-top">
                            @if($data['payroll'])
                                <form action="{{ route('schedules.payrolls.toggle-status', $data['payroll']->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $data['payroll']->is_finalized ? 'bg-emerald-500' : 'bg-slate-600' }}" title="{{ $data['payroll']->is_finalized ? 'Gaji Pas' : 'Sedang Proses' }}">
                                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $data['payroll']->is_finalized ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                    </button>
                                    <div class="text-[9px] mt-1 font-bold {{ $data['payroll']->is_finalized ? 'text-emerald-400' : 'text-slate-400' }}">
                                        {{ $data['payroll']->is_finalized ? 'Selesai' : 'Proses' }}
                                    </div>
                                </form>
                            @else
                                <span class="text-[9px] text-slate-500 italic">Belum ada</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center align-top">
                            <button @click="$dispatch('open-modal', 'edit-payroll-{{ $data['user']->id }}')" class="w-8 h-8 rounded-full bg-slate-700 hover:bg-emerald-500 hover:text-white transition-colors text-slate-400 flex items-center justify-center inline-flex" title="Edit komponen gaji">
                                <i class="fas fa-edit"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="12" class="px-4 py-8 text-center text-slate-500">Belum ada data crew.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL ENTRI KOMPONEN GAJI PER CREW --}}
    @foreach($salaryData as $data)
        @include('schedules.payrolls._modal_entries', ['data' => $data])
    @endforeach
</div>
@endsection
