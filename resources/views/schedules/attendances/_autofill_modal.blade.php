<div x-data="{ show: false }" @open-modal.window="if ($event.detail === 'auto-fill-attendance') show = true" @close-modal.window="show = false">
    <template x-teleport="body">
        <div x-show="show" x-cloak class="fixed inset-0 z-[99] flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm" @click="show = false"></div>
            <div class="relative bg-slate-800 rounded-3xl shadow-2xl border border-slate-700 w-full max-w-lg z-10 max-h-[92vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-slate-700/50 flex justify-between items-center">
            <h3 class="text-lg font-black text-white"><i class="fas fa-magic text-emerald-400 mr-2"></i>Isi Absensi Otomatis</h3>
            <button @click="show = false" class="text-slate-400 hover:text-white"><i class="fas fa-times"></i></button>
        </div>

        <form action="{{ route('schedules.attendances.auto-fill') }}" method="POST" class="p-6 space-y-4">
            @csrf

            <div class="bg-blue-500/10 border border-blue-500/20 text-blue-300 text-[11px] rounded-xl p-3 leading-relaxed">
                Absen masuk dan keluar dibuat dari jendela waktu shift pada jadwal crew. Statusnya tercatat tepat waktu dan bisa diubah satu per satu setelahnya. Sesi yang sudah lengkap tidak disentuh, sesi yang jam operasionalnya belum lewat dilewati, dan lembur tetap diisi manual di Rekap Lembur. Bila crew baru absen masuk saja, sistem hanya melengkapi absen keluarnya.
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ now()->startOfMonth()->toDateString() }}" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-emerald-500" required>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ now()->toDateString() }}" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-emerald-500" required>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Lokasi</label>
                    <select name="location_id" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-emerald-500">
                        <option value="">Semua Lokasi</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Crew</label>
                    <select name="user_id" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-emerald-500">
                        <option value="">Semua Crew</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex gap-2 justify-end pt-2">
                <button type="button" @click="show = false" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg text-xs font-bold">Batal</button>
                <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-xs font-black uppercase tracking-wider">
                    <i class="fas fa-bolt mr-1"></i> Isi Sekarang
                </button>
            </div>
        </form>
            </div>
        </div>
    </template>
</div>
