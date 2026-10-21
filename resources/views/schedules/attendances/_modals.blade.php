@php
    $isOpen = in_array($row->id, $openIds);
    $isOrphan = in_array($row->id, $orphanOutIds ?? []);
    $window = \App\Services\AttendanceRuleService::sessionWindow(
        \App\Services\AttendanceRuleService::assignmentsFor($row->user, $row->location, $row->date->toDateString()),
        $row->date->toDateString()
    );
    $statusOptions = array_merge(
        ['auto' => 'Nilai ulang dari jam', 'clean' => 'Tepat Waktu', 'lembur' => 'Lembur', 'unjudged' => 'Belum Dinilai'],
        \App\Services\AttendanceRuleService::labels()
    );
    $current = $row->type === 'out' && $row->overtimeRequest
        ? 'lembur'
        : ($row->violations === null ? 'unjudged' : (key($row->violations) ?: 'clean'));
@endphp

{{-- Ubah satu baris absensi --}}
<div x-data="{ show: false }" @open-modal.window="if ($event.detail === 'edit-attendance-{{ $row->id }}') show = true" @close-modal.window="show = false">
    <template x-teleport="body">
        <div x-show="show" x-cloak class="fixed inset-0 z-[160] flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm" @click="show = false"></div>
            <div class="relative bg-slate-800 rounded-3xl shadow-2xl border border-slate-700 w-full max-w-lg z-10 max-h-[calc(100vh-2.5rem)] overflow-y-auto">
                <div class="px-6 py-4 border-b border-slate-700/50 flex justify-between items-center sticky top-0 bg-slate-800">
                    <h3 class="text-lg font-black text-white"><i class="fas fa-pen text-blue-400 mr-2"></i>Ubah Absensi</h3>
                    <button @click="show = false" class="text-slate-400 hover:text-white"><i class="fas fa-times"></i></button>
                </div>

                <form action="{{ route('schedules.attendances.update', $row) }}" method="POST" class="p-6 space-y-4">
                    @csrf @method('PUT')

                    <div class="text-[10px] text-slate-400 bg-slate-900/50 border border-slate-700 rounded-xl p-3">
                        {{ $row->user->name ?? '-' }} &middot; {{ $row->location->name ?? '-' }} &middot;
                        {{ $row->type === 'in' ? 'Absen Masuk' : 'Absen Keluar' }}
                        @if($row->shift) &middot; {{ $row->shift->name }} @endif
                        <div class="mt-1 text-slate-500">
                            Jendela: masuk <b class="text-slate-300">{{ \App\Services\AttendanceRuleService::windowLabel($window, 'in', $row->date->toDateString()) ?? 'belum diatur' }}</b>,
                            keluar <b class="text-slate-300">{{ \App\Services\AttendanceRuleService::windowLabel($window, 'out', $row->date->toDateString()) ?? 'belum diatur' }}</b>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Tanggal Operasional</label>
                            <input type="date" name="date" value="{{ $row->date->toDateString() }}" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Jam Absen</label>
                            <input type="time" name="time" value="{{ $row->created_at->format('H:i') }}" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-blue-500" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Keterangan / Status</label>
                        <select name="status" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-blue-500">
                            @foreach($statusOptions as $code => $label)
                                @if($code === 'lembur' && $row->type !== 'out')
                                    @continue
                                @endif
                                <option value="{{ $code }}" {{ $current === $code ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="text-[9px] text-slate-500 mt-1">
                            Pilih "Lembur" agar durasinya dihitung otomatis dari jam keluar dan langsung masuk ke Rekap Lembur.
                        </p>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Catatan</label>
                        <textarea name="notes" rows="2" placeholder="Contoh: izin datang terlambat karena kendaraan rusak" class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-3 py-2 text-slate-300 text-xs italic focus:outline-none focus:border-blue-500">{{ $row->notes }}</textarea>
                    </div>

                    <div class="flex gap-2 justify-end pt-2">
                        <button type="button" @click="show = false" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg text-xs font-bold">Batal</button>
                        <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-xs font-black uppercase tracking-wider">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>

{{-- Tutup sesi yang belum absen keluar --}}
@if($isOpen)
<div x-data="{ show: false, action: 'missing' }" @open-modal.window="if ($event.detail === 'close-session-{{ $row->id }}') show = true" @close-modal.window="show = false">
    <template x-teleport="body">
        <div x-show="show" x-cloak class="fixed inset-0 z-[160] flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm" @click="show = false"></div>
            <div class="relative bg-slate-800 rounded-3xl shadow-2xl border border-slate-700 w-full max-w-lg z-10 max-h-[calc(100vh-2.5rem)] overflow-y-auto">
                <div class="px-6 py-4 border-b border-slate-700/50 flex justify-between items-center sticky top-0 bg-slate-800">
                    <h3 class="text-lg font-black text-white"><i class="fas fa-door-closed text-amber-400 mr-2"></i>Tutup Sesi: {{ $row->user->name ?? '-' }}</h3>
                    <button @click="show = false" class="text-slate-400 hover:text-white"><i class="fas fa-times"></i></button>
                </div>

                <form action="{{ route('schedules.attendances.close-session', $row) }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    <p class="text-xs text-slate-400">Absen masuk {{ $row->created_at->format('H:i') }} pada {{ $row->date->translatedFormat('d M Y') }} belum pernah ditutup.</p>

                    @if($row->notes)
                        <div class="bg-blue-500/10 border border-blue-500/20 rounded-xl p-3 text-[11px] text-blue-200">
                            <i class="fas fa-comment-dots mr-1"></i>Alasan crew: <span class="italic">{{ $row->notes }}</span>
                        </div>
                    @endif

                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center gap-2 bg-slate-900/60 border border-slate-700 rounded-xl px-3 py-3 cursor-pointer">
                            <input type="radio" name="action" value="missing" x-model="action" class="accent-red-500">
                            <span class="text-xs font-bold text-slate-200">Memang lupa absen keluar</span>
                        </label>
                        <label class="flex items-center gap-2 bg-slate-900/60 border border-slate-700 rounded-xl px-3 py-3 cursor-pointer">
                            <input type="radio" name="action" value="overtime" x-model="action" class="accent-purple-500">
                            <span class="text-xs font-bold text-slate-200">Sesungguhnya lembur</span>
                        </label>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Tanggal Keluar</label>
                            <input type="date" name="check_out_date" value="{{ $row->date->toDateString() }}" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-amber-500" required>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Jam Keluar</label>
                            <input type="time" name="check_out_time" value="{{ now()->format('H:i') }}" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-amber-500" required>
                        </div>
                    </div>

                    <div x-show="action === 'overtime'" x-cloak class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Durasi Lembur (menit)</label>
                            <input type="number" name="minutes" min="1" max="1440" placeholder="otomatis" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-purple-500">
                            <p class="text-[9px] text-slate-500 mt-1">
                                @if($window['out'][1])
                                    Otomatis: selisih jam keluar dengan batas {{ $window['out'][1]->format('H:i') }}.
                                @else
                                    Kosongkan untuk hitung dari batas jendela keluar.
                                @endif
                            </p>
                        </div>
                        <div x-data="{ tampil: '', raw: '' }">
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Nominal (Rp)</label>
                            <input type="text" inputmode="numeric" x-model="tampil"
                                @input="raw = tampil.replace(/\D/g, ''); tampil = raw ? new Intl.NumberFormat('id-ID').format(raw) : ''"
                                @blur="tampil = raw ? new Intl.NumberFormat('id-ID').format(raw) : ''"
                                placeholder="15.000" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm text-right focus:outline-none focus:border-purple-500">
                            <input type="hidden" name="amount" :value="raw">
                            <p class="text-[9px] text-slate-500 mt-1">Kosongkan untuk pakai tarif Rp {{ number_format(\App\Services\AttendanceRuleService::overtimeRate(), 0, ',', '.') }}/jam.</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Catatan</label>
                        <textarea name="note" rows="2" placeholder="Contoh: membantu bongkar pasang dekorasi sampai selesai" class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-3 py-2 text-slate-300 text-xs italic focus:outline-none focus:border-amber-500"></textarea>
                    </div>

                    <div class="flex gap-2 justify-end pt-2">
                        <button type="button" @click="show = false" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg text-xs font-bold">Batal</button>
                        <button type="submit" class="px-5 py-2 bg-amber-600 hover:bg-amber-500 text-white rounded-lg text-xs font-black uppercase tracking-wider">Tutup Sesi</button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>
@endif

{{-- Isi absen masuk yang tidak pernah diabsen --}}
@if($isOrphan)
@php
    $jamMasuk = $window['in'][1] ? $window['in'][1]->format('H:i') : $row->created_at->copy()->subHours(8)->format('H:i');
@endphp
<div x-data="{ show: false }" @open-modal.window="if ($event.detail === 'close-check-in-{{ $row->id }}') show = true" @close-modal.window="show = false">
    <template x-teleport="body">
        <div x-show="show" x-cloak class="fixed inset-0 z-[160] flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm" @click="show = false"></div>
            <div class="relative bg-slate-800 rounded-3xl shadow-2xl border border-slate-700 w-full max-w-lg z-10 max-h-[calc(100vh-2.5rem)] overflow-y-auto">
                <div class="px-6 py-4 border-b border-slate-700/50 flex justify-between items-center sticky top-0 bg-slate-800">
                    <h3 class="text-lg font-black text-white"><i class="fas fa-sign-in-alt text-blue-400 mr-2"></i>Isi Absen Masuk: {{ $row->user->name ?? '-' }}</h3>
                    <button @click="show = false" class="text-slate-400 hover:text-white"><i class="fas fa-times"></i></button>
                </div>

                <form action="{{ route('schedules.attendances.close-check-in', $row) }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    <p class="text-xs text-slate-400">
                        Absen keluar {{ $row->created_at->format('H:i') }} pada {{ $row->date->translatedFormat('d M Y') }} tercatat tanpa absen masuk.
                        Isi jam masuk sebenarnya; kalau lewat jendela <b class="text-slate-200">{{ \App\Services\AttendanceRuleService::windowLabel($window, 'in', $row->date->toDateString()) ?? 'belum diatur' }}</b>,
                        statusnya otomatis menjadi terlambat absen masuk lengkap dengan durasinya.
                    </p>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Tanggal Masuk</label>
                            <input type="date" name="check_in_date" value="{{ $row->date->toDateString() }}" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Jam Masuk</label>
                            <input type="time" name="check_in_time" value="{{ $jamMasuk }}" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-blue-500" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Catatan</label>
                        <textarea name="note" rows="2" placeholder="Contoh: langsung pegang customer begitu sampai, baru sempat motret pas pulang" class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-3 py-2 text-slate-300 text-xs italic focus:outline-none focus:border-blue-500"></textarea>
                    </div>

                    <div class="flex gap-2 justify-end pt-2">
                        <button type="button" @click="show = false" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg text-xs font-bold">Batal</button>
                        <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-xs font-black uppercase tracking-wider">Simpan Absen Masuk</button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>
@endif
