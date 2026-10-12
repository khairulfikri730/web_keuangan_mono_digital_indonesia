{{-- Field jendela waktu absensi, dipakai modal tambah & edit shift. --}}
@php
    $clock = fn ($time) => $time ? substr($time, 0, 5) : '';
    $windowValue = fn ($key) => $clock($shift->{$key} ?? null);
@endphp

<div class="pt-4 border-t border-slate-700/50 space-y-3">
    <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest">
        <i class="fas fa-stopwatch text-amber-400 mr-1"></i> Jendela Waktu Absensi
    </p>

    <div>
        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Absen Masuk</label>
        <div class="grid grid-cols-2 gap-3">
            <input type="time" name="check_in_start" value="{{ $windowValue('check_in_start') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white text-sm focus:outline-none focus:border-amber-500">
            <input type="time" name="check_in_end" value="{{ $windowValue('check_in_end') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white text-sm focus:outline-none focus:border-amber-500">
        </div>
    </div>

    <div>
        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Absen Keluar</label>
        <div class="grid grid-cols-2 gap-3">
            <input type="time" name="check_out_start" value="{{ $windowValue('check_out_start') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white text-sm focus:outline-none focus:border-amber-500">
            <input type="time" name="check_out_end" value="{{ $windowValue('check_out_end') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white text-sm focus:outline-none focus:border-amber-500">
        </div>
    </div>

    <p class="text-[10px] text-slate-500 leading-relaxed">
        Isi rentang jam yang diperbolehkan (contoh jam 10:00 &rarr; 09:20 sampai 09:40). Kosongkan bila tidak dibatasi.
        Absen di luar jendela ditandai sebagai pelanggaran, dan absen keluar setelah jam akhir jendela keluar dihitung sebagai lembur.
        Untuk shift yang melewati tengah malam (selesai lebih pagi dari mulai), jendela keluar otomatis jatuh di hari berikutnya.
    </p>
</div>
