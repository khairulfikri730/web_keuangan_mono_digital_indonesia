@extends('layouts.app')
@section('title', 'Rekap Lembur')

@section('content')
<div class="flex flex-col gap-6 font-sans antialiased text-slate-300">

    @include('schedules._nav_tabs', ['tab' => 'overtimes'])

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-white tracking-tight">Rekap Lembur Crew</h2>
            <p class="text-slate-400 text-sm mt-1">
                Lembur dihitung otomatis dari selisih absen keluar dengan batas jendela. Nominal saat ini Rp {{ number_format($ratePerHour, 0, ',', '.') }} / jam.
            </p>
        </div>
    </div>

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

    @php $canReview = in_array(auth()->user()->role, ['superadmin', 'owner']) || auth()->user()->hasPermission('schedules.manage'); @endphp

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        @foreach([
            ['label' => 'Menunggu Acc', 'rows' => $summary['pending'], 'class' => 'text-amber-400', 'icon' => 'fa-hourglass-half'],
            ['label' => 'Sudah Di-acc', 'rows' => $summary['approved'], 'class' => 'text-emerald-400', 'icon' => 'fa-circle-check'],
            ['label' => 'Ditolak', 'rows' => $summary['rejected'], 'class' => 'text-slate-400', 'icon' => 'fa-circle-xmark'],
        ] as $card)
        <div class="bg-slate-800/40 border border-white/5 rounded-2xl p-5 backdrop-blur-xl">
            <p class="text-[10px] font-black uppercase tracking-widest {{ $card['class'] }}">
                <i class="fas {{ $card['icon'] }} mr-1"></i>{{ $card['label'] }}
            </p>
            <p class="text-2xl font-black text-white mt-2">{{ $card['rows']->count() }} pengajuan</p>
            <p class="text-xs text-slate-400 mt-1">Rp {{ number_format($card['rows']->sum('amount'), 0, ',', '.') }}</p>
        </div>
        @endforeach
    </div>

    @if($canReview)
    <div x-data="{ open: false }" class="bg-slate-800/40 border border-white/5 rounded-2xl backdrop-blur-xl overflow-hidden">
        <button type="button" @click="open = !open" class="w-full px-5 py-4 flex items-center justify-between hover:bg-white/5 transition-colors">
            <span class="text-xs font-black uppercase tracking-widest text-purple-300">
                <i class="fas fa-plus-circle mr-2"></i>Isi Lembur Manual
            </span>
            <i class="fas fa-chevron-down text-slate-400 text-xs transition-transform" :class="open && 'rotate-180'"></i>
        </button>

        <div x-show="open" x-cloak class="px-5 pb-5 border-t border-white/5">
            <form action="{{ route('schedules.overtimes.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-12 gap-3 pt-4">
                @csrf
                <div class="md:col-span-3">
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Crew</label>
                    <select name="user_id" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-purple-500" required>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-3">
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Lokasi</label>
                    <select name="schedule_location_id" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-purple-500" required>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Tanggal</label>
                    <input type="date" name="date" value="{{ now()->toDateString() }}" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-purple-500" required>
                </div>
                <div class="md:col-span-1">
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Menit</label>
                    <input type="number" name="minutes" min="1" max="1440" value="60" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2 py-2 text-white text-sm focus:outline-none focus:border-purple-500" required>
                </div>
                <div x-data="{ tampil: '', raw: '' }" class="md:col-span-2">
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Nominal (Rp)</label>
                    <input type="text" inputmode="numeric" x-model="tampil"
                        @input="raw = tampil.replace(/\D/g, ''); tampil = raw ? new Intl.NumberFormat('id-ID').format(raw) : ''"
                        @blur="tampil = raw ? new Intl.NumberFormat('id-ID').format(raw) : ''"
                        placeholder="{{ number_format($ratePerHour, 0, ',', '.') }}" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm text-right focus:outline-none focus:border-purple-500">
                    <input type="hidden" name="amount" :value="raw">
                </div>
                <div class="md:col-span-11">
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Catatan Lembur</label>
                    <input type="text" name="note" placeholder="Contoh: lembur finishing pesanan klien" class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-3 py-2 text-slate-300 text-sm italic focus:outline-none focus:border-purple-500" required>
                </div>
                <div class="md:col-span-1 flex items-end">
                    <button type="submit" class="w-full bg-purple-600 hover:bg-purple-500 text-white rounded-lg px-3 py-2 text-[10px] font-black uppercase tracking-wider transition-colors">Simpan</button>
                </div>
            </form>
            <p class="text-[10px] text-slate-500 mt-3">
                Nominal dikosongkan akan dihitung otomatis dari tarif Rp {{ number_format($ratePerHour, 0, ',', '.') }} / jam secara proporsional. Pengisian baru menimpa entri lembur pada tanggal dan lokasi yang sama.
            </p>
        </div>
    </div>
    @endif

    <div class="bg-slate-800/40 rounded-2xl border border-white/5 p-4 shadow-lg backdrop-blur-xl">
        <form action="{{ route('schedules.overtimes.index') }}" method="GET" class="flex flex-col md:flex-row items-end gap-4">
            <div class="w-full md:w-auto">
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Bulan</label>
                <input type="month" name="month" value="{{ $month }}" class="bg-slate-900 border border-slate-700 text-slate-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:border-emerald-500">
            </div>
            <div class="w-full md:w-auto flex-1">
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Lokasi</label>
                <select name="location_id" class="bg-slate-900 border border-slate-700 text-slate-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:border-emerald-500 w-full md:max-w-xs">
                    <option value="">Semua Lokasi</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ request('location_id') == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full md:w-auto">
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Status</label>
                <select name="status" class="bg-slate-900 border border-slate-700 text-slate-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:border-emerald-500">
                    <option value="">Semua</option>
                    @foreach(['pending', 'approved', 'rejected'] as $statusOption)
                        <option value="{{ $statusOption }}" {{ request('status') === $statusOption ? 'selected' : '' }}>{{ \App\Models\OvertimeRequest::statusLabel($statusOption) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="bg-slate-700 hover:bg-slate-600 text-white px-5 py-2 rounded-lg font-bold text-sm transition-colors"><i class="fas fa-filter mr-1"></i> Filter</button>
                <a href="{{ route('schedules.overtimes.index', ['month' => $month]) }}" class="bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-300 px-5 py-2 rounded-lg font-bold text-sm transition-colors">Reset</a>
            </div>
        </form>
    </div>

    <div class="bg-slate-800/40 rounded-[2.5rem] border border-white/5 shadow-2xl overflow-hidden backdrop-blur-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-900/40">
                    <tr class="text-[10px] font-black text-slate-500 uppercase tracking-widest border-b border-white/5">
                        <th class="px-6 py-5">Crew</th>
                        <th class="px-6 py-5">Tanggal</th>
                        <th class="px-6 py-5">Lokasi &amp; GPS</th>
                        <th class="px-6 py-5 text-center">Jarak</th>
                        <th class="px-6 py-5 text-center">Foto</th>
                        <th class="px-6 py-5 text-center">Batas &amp; Absen Keluar</th>
                        <th class="px-6 py-5 text-center">Durasi</th>
                        <th class="px-6 py-5 text-right">Nominal</th>
                        <th class="px-6 py-5">Catatan Crew</th>
                        <th class="px-6 py-5 text-center">Status</th>
                        @if($canReview)
                        <th class="px-6 py-5 text-center">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($requests as $row)
                    <tr class="group hover:bg-white/5 transition-colors">
                        <td class="px-6 py-5">
                            <p class="text-sm font-black text-white">{{ $row->user->name ?? 'Unknown' }}</p>
                            <p class="text-[9px] font-bold text-slate-500 uppercase">{{ $row->user->role ?? 'Crew' }}</p>
                        </td>
                        <td class="px-6 py-5">
                            <p class="text-xs font-bold text-slate-200">{{ $row->date->translatedFormat('D, d M Y') }}</p>
                        </td>
                        @php $absen = $row->checkoutAttendance; @endphp
                        <td class="px-6 py-5">
                            <p class="text-xs font-bold text-slate-300">{{ $row->location->name ?? '-' }}</p>
                            @if($absen && $absen->latitude && $absen->longitude)
                                <a href="https://maps.google.com/?q={{ $absen->latitude }},{{ $absen->longitude }}" target="_blank" class="text-[9px] font-black text-blue-400 hover:text-blue-300 mt-1 flex items-center gap-1">
                                    <i class="fas fa-map-marker-alt"></i> Cek Maps
                                </a>
                            @else
                                <p class="text-[9px] text-slate-600 mt-1">Tanpa data GPS</p>
                            @endif
                        </td>
                        <td class="px-6 py-5 text-center">
                            @if($absen && $absen->status === 'in_radius')
                                <span class="text-emerald-400 text-xs font-black flex items-center justify-center gap-1">
                                    <i class="fas fa-check-circle"></i> Sesuai
                                </span>
                            @elseif($absen)
                                <span class="text-red-400 text-xs font-black flex items-center justify-center gap-1">
                                    <i class="fas fa-exclamation-triangle"></i> Luar Radius
                                </span>
                            @else
                                <span class="text-slate-600 text-xs">-</span>
                            @endif
                            <p class="text-[9px] text-slate-500 mt-1">{{ $absen && $absen->distance !== null ? round($absen->distance) . ' meter' : '' }}</p>
                        </td>
                        <td class="px-6 py-5 text-center">
                            @if($absen && $absen->photo_path)
                                <a href="{{ Storage::url($absen->photo_path) }}" target="_blank" class="inline-block hover:scale-110 transition-transform">
                                    <img src="{{ Storage::url($absen->photo_path) }}" class="w-12 h-12 rounded-lg object-cover border border-slate-700 shadow-md">
                                </a>
                            @else
                                <span class="text-slate-600 text-xs">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-5 text-center">
                            <p class="text-[10px] font-mono text-slate-500">{{ $row->window_end_at->format('d/m H:i') }}</p>
                            <p class="text-xs font-mono font-black text-amber-400 mt-0.5">{{ $row->check_out_at->format('d/m H:i') }}</p>
                        </td>
                        <td class="px-6 py-5 text-center">
                            <span class="text-xs font-black text-white">{{ $row->durationLabel() }}</span>
                            <p class="text-[9px] text-slate-500 mt-1">Rp {{ number_format($row->rate_per_hour, 0, ',', '.') }}/jam</p>
                        </td>
                        <td class="px-6 py-5 text-right">
                            @if($canReview && $row->status === \App\Models\OvertimeRequest::STATUS_PENDING)
                                <div x-data="{ tampil: '{{ number_format($row->amount, 0, ',', '.') }}', raw: '{{ $row->amount }}' }" class="flex items-center justify-end gap-1">
                                    <span class="text-[10px] text-slate-500 font-bold">Rp</span>
                                    <input type="text" inputmode="numeric" form="acc-{{ $row->id }}" x-model="tampil"
                                        @input="raw = tampil.replace(/\D/g, ''); tampil = raw ? new Intl.NumberFormat('id-ID').format(raw) : ''"
                                        @blur="tampil = raw ? new Intl.NumberFormat('id-ID').format(raw) : ''"
                                        class="w-24 bg-slate-900 border border-slate-700 rounded-lg px-2 py-1 text-right text-xs font-bold text-white focus:outline-none focus:border-emerald-500">
                                    <input type="hidden" form="acc-{{ $row->id }}" name="amount" :value="raw">
                                </div>
                            @else
                                <span class="text-xs font-black text-emerald-400">Rp {{ number_format($row->amount, 0, ',', '.') }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-5 text-xs text-slate-400 max-w-[220px]">
                            {{ $row->note ?: '' }}<span class="{{ $row->note ? 'hidden' : '' }} text-slate-600 italic">Tidak ada catatan</span>
                        </td>
                        <td class="px-6 py-5 text-center">
                            @php
                                $style = [
                                    'pending' => 'background:rgba(245,158,11,.12); color:#fbbf24; border-color:rgba(245,158,11,.3)',
                                    'approved' => 'background:rgba(16,185,129,.12); color:#34d399; border-color:rgba(16,185,129,.3)',
                                    'rejected' => 'background:rgba(100,116,139,.15); color:#94a3b8; border-color:rgba(100,116,139,.3)',
                                ][$row->status] ?? '';
                            @endphp
                            <span class="text-[9px] font-black uppercase tracking-wider px-2 py-1 rounded-lg border" style="{{ $style }}">
                                {{ \App\Models\OvertimeRequest::statusLabel($row->status) }}
                            </span>
                            @if($row->reviewed_by)
                                <p class="text-[9px] text-slate-500 mt-1">oleh {{ $row->reviewed_by }}</p>
                            @endif
                        </td>
                        @if($canReview)
                        <td class="px-6 py-5 text-center">
                            @if($row->status === \App\Models\OvertimeRequest::STATUS_PENDING)
                                <div class="flex items-center justify-center gap-2">
                                    <form id="acc-{{ $row->id }}" action="{{ route('schedules.overtimes.approve', $row) }}" method="POST" class="m-0">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-[10px] font-black uppercase tracking-wider transition-colors">
                                            <i class="fas fa-check mr-1"></i> Acc
                                        </button>
                                    </form>
                                    <form action="{{ route('schedules.overtimes.reject', $row) }}" method="POST" class="m-0" onsubmit="return confirm('Tolak pengajuan lembur ini?')">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-slate-700 hover:bg-red-600 hover:text-white text-slate-300 text-[10px] font-black uppercase tracking-wider transition-colors">
                                            <i class="fas fa-xmark"></i>
                                        </button>
                                    </form>
                                </div>
                            @else
                                <span class="text-[10px] text-slate-600">Selesai diproses</span>
                            @endif
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $canReview ? '11' : '10' }}" class="px-8 py-16 text-center text-slate-500 text-sm">
                            <i class="fas fa-stopwatch text-3xl mb-3 block opacity-50"></i>
                            Tidak ada pengajuan lembur pada periode ini
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
