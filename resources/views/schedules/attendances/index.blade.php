@extends('layouts.app')
@section('title', 'Data Absensi Crew')

@section('content')
<div class="flex flex-col gap-6 font-sans antialiased text-slate-300">
    
    <!-- MAIN NAVIGATION TABS (Schedule Module) -->
    @include('schedules._nav_tabs', ['tab' => 'attendances'])

    <!-- TOP HEADER -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-white tracking-tight">Log Absensi Harian</h2>
            <p class="text-slate-400 text-sm mt-1">Pantau riwayat foto masuk dan keluar kru beserta lokasi GPS-nya.</p>
        </div>
        
        <div class="flex flex-wrap items-center gap-3">
            @if($canManage)
            <button type="button" @click="$dispatch('open-modal', 'auto-fill-attendance')" class="bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2.5 rounded-xl font-bold shadow-lg shadow-emerald-500/30 transition-all flex items-center gap-2 text-sm">
                <i class="fas fa-magic"></i> Isi Otomatis
            </button>
            <div class="flex rounded-xl overflow-hidden border border-slate-700">
                @foreach(['daily' => 'Harian', 'weekly' => 'Mingguan', 'monthly' => 'Bulanan'] as $period => $label)
                <a href="{{ route('schedules.attendances.report', ['period' => $period, 'date' => request('date') ?: now()->toDateString(), 'location_id' => request('location_id'), 'preview' => 1]) }}"
                    class="px-3 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition-colors {{ $loop->first ? '' : 'border-l border-slate-700' }}">
                    <i class="fas fa-file-pdf text-red-400 mr-1"></i>{{ $label }}
                </a>
                @endforeach
            </div>
            @endif
            <a href="{{ route('schedules.attendances.create') }}" class="bg-blue-600 hover:bg-blue-500 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg shadow-blue-500/30 transition-all flex items-center justify-center gap-2 text-sm">
                <i class="fas fa-camera"></i> Mulai Absen (Selfie)
            </a>
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

    <!-- FILTERS -->
    <div class="bg-slate-800/40 rounded-2xl border border-white/5 p-4 shadow-lg backdrop-blur-xl mt-2">
        <form action="{{ route('schedules.attendances.index') }}" method="GET" class="flex flex-col md:flex-row items-end gap-4">
            <div class="w-full md:w-auto">
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Tanggal</label>
                <input type="date" name="date" value="{{ request('date') }}" class="bg-slate-900 border border-slate-700 text-slate-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:border-emerald-500 w-full md:w-48">
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
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Pelanggaran</label>
                <select name="violation" class="bg-slate-900 border border-slate-700 text-slate-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:border-emerald-500 w-full md:max-w-xs">
                    <option value="">Semua Status</option>
                    @foreach(\App\Services\AttendanceRuleService::labels() as $code => $label)
                        <option value="{{ $code }}" {{ request('violation') === $code ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-full md:w-auto flex gap-2">
                <button type="submit" class="bg-slate-700 hover:bg-slate-600 text-white px-5 py-2 rounded-lg font-bold text-sm transition-colors">
                    <i class="fas fa-filter mr-1"></i> Filter
                </button>
                <a href="{{ route('schedules.attendances.index') }}" class="bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-300 px-5 py-2 rounded-lg font-bold text-sm transition-colors text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- TABLE CONTAINER -->
    <div class="bg-slate-800/40 rounded-[2.5rem] border border-white/5 shadow-2xl overflow-hidden backdrop-blur-xl mt-4">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-900/40">
                    <tr class="text-[10px] font-black text-slate-500 uppercase tracking-widest border-b border-white/5">
                        <th class="px-8 py-5">Kru</th>
                        <th class="px-8 py-5 text-center">Waktu</th>
                        <th class="px-8 py-5 text-center">Tipe</th>
                        <th class="px-8 py-5">Status Waktu &amp; Lembur</th>
                        <th class="px-8 py-5">Lokasi & GPS</th>
                        <th class="px-8 py-5 text-center">Jarak</th>
                        <th class="px-8 py-5 text-center">Foto</th>
                        @if($canManage)
                        <th class="px-8 py-5 text-center">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($attendances as $row)
                    <tr class="group hover:bg-white/5 transition-colors">
                        <!-- Kru -->
                        <td class="px-8 py-5">
                            <div class="flex items-center gap-3">
                                @if($row->user && $row->user->avatar)
                                    <img src="{{ Storage::url($row->user->avatar) }}" class="w-10 h-10 rounded-full object-cover border border-slate-700">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-slate-700 flex items-center justify-center text-white font-bold border border-slate-600">
                                        {{ substr($row->user->name ?? '?', 0, 1) }}
                                    </div>
                                @endif
                                <div>
                                    <p class="text-sm font-black text-white">{{ $row->user->name ?? 'Unknown' }}</p>
                                    <p class="text-[9px] font-bold text-slate-500 uppercase">{{ $row->user->role ?? 'Crew' }}</p>
                                </div>
                            </div>
                        </td>

                        <!-- Waktu -->
                        <td class="px-8 py-5 text-center">
                            <p class="text-xs font-black text-white">{{ $row->date?->format('d M Y') ?? $row->created_at->format('d M Y') }}</p>
                            <p class="text-xs font-bold text-slate-400 mt-1">{{ $row->created_at->format('H:i') }} WIB</p>
                            @if($row->date && $row->date->toDateString() !== $row->created_at->toDateString())
                                <p class="text-[9px] font-black text-purple-400 uppercase mt-1"><i class="fas fa-moon mr-1"></i>Lewat tengah malam</p>
                            @endif
                        </td>

                        <!-- Tipe -->
                        <td class="px-8 py-5 text-center">
                            @if($row->type === 'in')
                                <span class="bg-blue-500/20 text-blue-400 border border-blue-500/30 px-3 py-1 rounded-lg text-[9px] font-black uppercase tracking-wider">
                                    <i class="fas fa-sign-in-alt mr-1"></i> Masuk
                                </span>
                            @else
                                <span class="bg-amber-500/20 text-amber-400 border border-amber-500/30 px-3 py-1 rounded-lg text-[9px] font-black uppercase tracking-wider">
                                    <i class="fas fa-sign-out-alt mr-1"></i> Keluar
                                </span>
                            @endif
                        </td>

                        <!-- Status Waktu & Lembur -->
                        <td class="px-8 py-5">
                            <div class="flex flex-wrap gap-1">
                                @forelse($row->violations ?? [] as $code => $value)
                                    <span class="text-[9px] font-black uppercase tracking-wider px-2 py-1 rounded-lg border"
                                        title="{{ $code === \App\Services\AttendanceRuleService::MISSING_CHECK_OUT ? 'Sesi belum ditutup sampai lewat batas toleransi' : 'Nilai: ' . $value . ' (menit di luar jendela / meter di luar radius)' }}"
                                        style="background:rgba(239,68,68,.12); color:#f87171; border-color:rgba(239,68,68,.3)">
                                        <i class="fas fa-triangle-exclamation mr-1"></i>{{ \App\Services\AttendanceRuleService::labels()[$code] ?? $code }}
                                        @if($code !== \App\Services\AttendanceRuleService::MISSING_CHECK_OUT)
                                        <span class="font-mono">{{ $value }}</span>
                                        @endif
                                    </span>
                                @empty
                                    @if(is_null($row->violations))
                                        <span class="text-[9px] font-black uppercase tracking-wider px-2 py-1 rounded-lg border" style="background:rgba(100,116,139,.12); color:#94a3b8; border-color:rgba(100,116,139,.25)" title="Tercatat sebelum jendela waktu absensi diatur">
                                            <i class="fas fa-minus mr-1"></i>Belum Dinilai
                                        </span>
                                    @else
                                        <span class="text-[9px] font-black uppercase tracking-wider px-2 py-1 rounded-lg border" style="background:rgba(16,185,129,.12); color:#34d399; border-color:rgba(16,185,129,.3)">
                                            <i class="fas fa-check mr-1"></i>Tepat Waktu
                                        </span>
                                    @endif
                                @endforelse

                                @if($row->type === 'out' && $row->overtimeRequest)
                                    @php $ot = $row->overtimeRequest; @endphp
                                    <span class="text-[9px] font-black uppercase tracking-wider px-2 py-1 rounded-lg border"
                                        style="background:rgba(168,85,247,.12); color:#c084fc; border-color:rgba(168,85,247,.3)">
                                        <i class="fas fa-stopwatch mr-1"></i>Lembur {{ intdiv($ot->minutes, 60) }}j {{ $ot->minutes % 60 }}m
                                        <span style="color:#facc15">{{ \App\Models\OvertimeRequest::statusLabel($ot->status) }}</span>
                                    </span>
                                @endif

                                @if(in_array($row->id, $openIds))
                                    <span class="text-[9px] font-black uppercase tracking-wider px-2 py-1 rounded-lg border"
                                        style="background:rgba(250,204,21,.12); color:#facc15; border-color:rgba(250,204,21,.3)">
                                        <i class="fas fa-hourglass-half mr-1"></i>Belum absen keluar
                                    </span>
                                @endif
                            </div>

                            @if($row->source !== \App\Models\Attendance::SOURCE_DEVICE)
                                <p class="text-[9px] font-bold text-purple-300 mt-1.5">
                                    <i class="fas fa-user-edit mr-1"></i>{{ \App\Models\Attendance::sourceLabel($row->source) }}{{ $row->corrected_by ? ' · ' . $row->corrected_by : '' }}
                                </p>
                            @endif

                            @if($row->notes)
                                <p class="text-[10px] text-slate-400 italic mt-1">{{ $row->notes }}</p>
                            @elseif($row->shift)
                                <p class="text-[9px] text-slate-500 mt-1.5 font-bold">{{ $row->shift->name }} &middot; {{ $row->shift->time_range }}</p>
                            @endif
                        </td>

                        <!-- Lokasi -->
                        <td class="px-8 py-5">
                            <p class="text-xs font-bold text-slate-300">{{ $row->location->name ?? '-' }}</p>
                            <a href="https://maps.google.com/?q={{ $row->latitude }},{{ $row->longitude }}" target="_blank" class="text-[9px] font-black text-blue-400 hover:text-blue-300 mt-1 flex items-center gap-1">
                                <i class="fas fa-map-marker-alt"></i> Cek Maps
                            </a>
                        </td>

                        <!-- Jarak (Status) -->
                        <td class="px-8 py-5 text-center">
                            @if($row->status === 'in_radius')
                                <span class="text-emerald-400 text-xs font-black flex items-center justify-center gap-1">
                                    <i class="fas fa-check-circle"></i> Sesuai
                                </span>
                            @else
                                <span class="text-red-400 text-xs font-black flex items-center justify-center gap-1" title="Jarak: {{ round($row->distance) }}m">
                                    <i class="fas fa-exclamation-triangle"></i> Luar Radius
                                </span>
                            @endif
                            <p class="text-[9px] text-slate-500 mt-1">{{ round($row->distance) }} meter</p>
                        </td>

                        <!-- Foto -->
                        <td class="px-8 py-5 text-center">
                            @if($row->photo_path)
                                <a href="{{ Storage::url($row->photo_path) }}" target="_blank" class="inline-block hover:scale-110 transition-transform">
                                    <img src="{{ Storage::url($row->photo_path) }}" class="w-12 h-12 rounded-lg object-cover border border-slate-700 shadow-md">
                                </a>
                            @else
                                <span class="text-slate-600 text-xs">-</span>
                            @endif
                        </td>
                        
                        <!-- Aksi -->
                        @if($canManage)
                        <td class="px-8 py-5 text-center">
                            <div class="flex items-center justify-center gap-3">
                                <button type="button" @click="$dispatch('open-modal', 'edit-attendance-{{ $row->id }}')" class="text-blue-400 hover:text-blue-300 transition-colors" title="Ubah jam &amp; keterangan">
                                    <i class="fas fa-pen"></i>
                                </button>
                                @if(in_array($row->id, $openIds))
                                <button type="button" @click="$dispatch('open-modal', 'close-session-{{ $row->id }}')" class="text-amber-400 hover:text-amber-300 transition-colors" title="Tutup sesi: lupa absen keluar atau dijadikan lembur">
                                    <i class="fas fa-door-closed"></i>
                                </button>
                                @endif
                                <form action="{{ route('schedules.attendances.destroy', $row) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data absensi ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-300 transition-colors" title="Hapus Data">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                            @include('schedules.attendances._modals', ['row' => $row])
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $canManage ? '8' : '7' }}" class="px-8 py-16 text-center text-slate-500 text-sm">
                            <i class="fas fa-camera text-3xl mb-3 block opacity-50"></i>
                            Belum ada data absensi
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($attendances->hasPages())
        <div class="px-8 py-6 border-t border-white/5 bg-slate-900/20">
            {{ $attendances->links() }}
        </div>
        @endif
    </div>

    @if($canManage)
        @include('schedules.attendances._autofill_modal')
    @endif
</div>
@endsection
