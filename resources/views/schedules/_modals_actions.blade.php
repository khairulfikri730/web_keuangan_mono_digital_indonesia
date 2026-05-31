{{-- CLOSE ASSIGNMENT MODALS --}}
@foreach($assignments as $asgn)
@if($asgn->isOpen())
<div x-data="{ show: false }" x-show="show" @open-modal.window="if ($event.detail === 'close-assignment-{{ $asgn->id }}') show = true" @close-modal.window="show = false" class="fixed inset-0 z-[99] flex items-center justify-center" style="display:none;">
    <div x-show="show" x-transition.opacity class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm" @click="show = false"></div>
    <div x-show="show" x-transition.scale.origin.bottom class="relative bg-slate-800 rounded-3xl shadow-2xl border border-slate-700 w-full max-w-md m-4 z-10 overflow-hidden max-h-[90vh] overflow-y-auto scrollbar-hide ">
        <div class="px-6 py-4 border-b border-slate-700/50 flex justify-between items-center bg-slate-800/50">
            <h3 class="text-lg font-black text-white"><i class="fas fa-ban text-red-400 mr-2"></i>Close Shift</h3>
            <button @click="show = false" class="text-slate-400 hover:text-white"><i class="fas fa-times"></i></button>
        </div>
        <form action="{{ route('schedules.assignments.close', $asgn) }}" method="POST">
            @csrf
            <div class="p-6 space-y-4">
                <div class="bg-slate-900/50 p-3 rounded-xl border border-slate-700 text-xs text-slate-300">
                    <b>{{ $asgn->crew->name ?? '?' }}</b> - {{ $asgn->shift->name ?? '' }} ({{ $asgn->shift->location->name ?? '' }})
                    <br>Tanggal: {{ $asgn->date->translatedFormat('l, d M Y') }}
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Alasan Close (Opsional)</label>
                    <textarea name="closed_reason" rows="2" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-red-500" placeholder="Contoh: Hujan deras"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Jam Close Aktual (Opsional)</label>
                    <input type="time" name="closed_at_time" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-red-500">
                    <p class="text-[10px] text-slate-500 mt-1">Isi jika ingin mencatat jam pulang lembur (misal: 00:00 untuk shift outdoor)</p>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-slate-700/50 flex justify-end gap-3 bg-slate-800/50">
                <button type="button" @click="show = false" class="px-4 py-2 rounded-xl text-sm font-bold text-slate-300 hover:bg-slate-700">Batal</button>
                <button type="submit" class="bg-red-600 hover:bg-red-500 text-white px-6 py-2 rounded-xl text-sm font-bold shadow-lg shadow-red-500/30">Close Shift</button>
            </div>
        </form>
    </div>
</div>
{{-- CHANGE CREW MODAL --}}
<div x-data="{ show: false }" x-show="show" @open-modal.window="if ($event.detail === 'change-assignment-{{ $asgn->id }}') show = true" @close-modal.window="show = false" class="fixed inset-0 z-[99] flex items-center justify-center" style="display:none;">
    <div x-show="show" x-transition.opacity class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm" @click="show = false"></div>
    <div x-show="show" x-transition.scale.origin.bottom class="relative bg-slate-800 rounded-3xl shadow-2xl border border-slate-700 w-full max-w-md m-4 z-10 overflow-hidden max-h-[90vh] overflow-y-auto scrollbar-hide ">
        <div class="px-6 py-4 border-b border-slate-700/50 flex justify-between items-center bg-slate-800/50">
            <h3 class="text-lg font-black text-white"><i class="fas fa-exchange-alt text-orange-400 mr-2"></i>Ganti Crew</h3>
            <button @click="show = false" class="text-slate-400 hover:text-white"><i class="fas fa-times"></i></button>
        </div>
        <form action="{{ route('schedules.assignments.change', $asgn) }}" method="POST">
            @csrf
            <div class="p-6 space-y-4">
                <div class="bg-slate-900/50 p-3 rounded-xl border border-slate-700 text-xs text-slate-300">
                    Crew saat ini: <b class="text-orange-400">{{ $asgn->crew->name ?? '?' }}</b><br>
                    Shift: {{ $asgn->shift->name ?? '' }} Â· {{ $asgn->shift->location->name ?? '' }}<br>
                    Tanggal: {{ $asgn->date->translatedFormat('l, d M Y') }}
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Crew Pengganti <span class="text-red-400">*</span></label>
                    <select name="new_crew_id" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-orange-500" required>
                        <option value="">-- Pilih Pengganti --</option>
                        @foreach($activeCrews as $c)
                        @if($c->id !== $asgn->schedule_crew_id)
                        <option value="{{ $c->id }}">{{ $c->name }}{{ $c->position ? " ($c->position)" : '' }}</option>
                        @endif
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Catatan</label>
                    <input type="text" name="change_notes" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-orange-500" placeholder="Opsional">
                </div>
            </div>
            <div class="px-6 py-4 border-t border-slate-700/50 flex justify-end gap-3 bg-slate-800/50">
                <button type="button" @click="show = false" class="px-4 py-2 rounded-xl text-sm font-bold text-slate-300 hover:bg-slate-700">Batal</button>
                <button type="submit" class="bg-orange-600 hover:bg-orange-500 text-white px-6 py-2 rounded-xl text-sm font-bold shadow-lg shadow-orange-500/30">Ganti Crew</button>
            </div>
        </form>
    </div>
</div>
@endif
@endforeach

{{-- POSTER MODAL --}}
<div x-data="{ show: false, type: 'weekly' }" x-show="show" @open-modal.window="if ($event.detail === 'poster-modal') show = true" @close-modal.window="show = false" class="fixed inset-0 z-[99] flex items-center justify-center" style="display:none;">
    <div x-show="show" x-transition.opacity class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm" @click="show = false"></div>
    <div x-show="show" x-transition.scale.origin.bottom class="relative bg-slate-800 rounded-3xl shadow-2xl border border-slate-700 w-full max-w-md m-4 z-10 overflow-hidden max-h-[90vh] overflow-y-auto scrollbar-hide ">
        <div class="px-6 py-4 border-b border-slate-700/50 flex justify-between items-center bg-slate-800/50">
            <h3 class="text-lg font-black text-white"><i class="fas fa-image text-blue-400 mr-2"></i>Custom Poster Jadwal</h3>
            <button @click="show = false" class="text-slate-400 hover:text-white"><i class="fas fa-times"></i></button>
        </div>
        <form action="{{ route('schedules.poster') }}" method="GET" target="_blank">
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Tipe Laporan</label>
                    <select name="type" x-model="type" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-blue-500">
                        <option value="daily">Harian</option>
                        <option value="weekly">Mingguan</option>
                        <option value="monthly">Bulanan</option>
                    </select>
                </div>
                
                <div x-show="type === 'monthly'">
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Pilih Bulan</label>
                    <input type="month" name="month" value="{{ date('Y-m') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-blue-500">
                </div>

                <div x-show="type !== 'monthly'">
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Pilih Tanggal <span x-show="type === 'weekly'">(Dalam Minggu Tersebut)</span></label>
                    <input type="date" name="date" value="{{ date('Y-m-d') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Lokasi</label>
                    <select name="location_id" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-blue-500">
                        <option value="">Semua Lokasi</option>
                        @foreach($locations as $loc)
                        <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-slate-700/50 flex justify-end gap-3 bg-slate-800/50">
                <button type="button" @click="show = false" class="px-4 py-2 rounded-xl text-sm font-bold text-slate-300 hover:bg-slate-700">Batal</button>
                <button type="submit" @click="show = false" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white px-6 py-2 rounded-xl text-sm font-bold shadow-lg shadow-blue-500/30 flex items-center gap-2">
                    <i class="fas fa-print"></i> Generate Poster
                </button>
            </div>
        </form>
    </div>
</div>

{{-- QUICK ADD ASSIGNMENT MODAL --}}
<div x-data="{ show: false, date: '', shiftId: '' }" x-show="show" @open-quick-add.window="date = $event.detail.date; shiftId = $event.detail.shiftId; show = true;" @close-modal.window="show = false" class="fixed inset-0 z-[99] flex items-center justify-center" style="display:none;">
    <div x-show="show" x-transition.opacity class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm" @click="show = false"></div>
    <div x-show="show" x-transition.scale.origin.bottom class="relative bg-slate-800 rounded-3xl shadow-2xl border border-slate-700 w-full max-w-md m-4 z-10 overflow-hidden max-h-[90vh] overflow-y-auto scrollbar-hide ">
        <div class="px-6 py-4 border-b border-slate-700/50 flex justify-between items-center bg-slate-800/50">
            <h3 class="text-lg font-black text-white"><i class="fas fa-plus-circle text-emerald-400 mr-2"></i>Tugaskan Crew</h3>
            <button @click="show = false" class="text-slate-400 hover:text-white"><i class="fas fa-times"></i></button>
        </div>
        <form action="{{ route('schedules.assignments.store') }}" method="POST">
            @csrf
            <input type="hidden" name="date" :value="date">
            <input type="hidden" name="schedule_shift_id" :value="shiftId">
            <div class="p-6 space-y-4">
                <div class="bg-slate-900/50 p-3 rounded-xl border border-slate-700 text-xs text-slate-300">
                    Silakan pilih crew untuk mengisi slot kosong ini.
                    <br>Tanggal: <span x-text="date" class="font-bold text-emerald-400"></span>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Pilih Crew <span class="text-red-400">*</span></label>
                    <select name="schedule_crew_id" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-emerald-500" required>
                        <option value="">-- Pilih Crew --</option>
                        @foreach($activeCrews as $crew)
                        <option value="{{ $crew->id }}">{{ $crew->name }}{{ $crew->position ? " ($crew->position)" : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Catatan (Opsional)</label>
                    <input type="text" name="notes" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-emerald-500" placeholder="Opsional">
                </div>
            </div>
            <div class="px-6 py-4 border-t border-slate-700/50 flex justify-end gap-3 bg-slate-800/50">
                <button type="button" @click="show = false" class="px-4 py-2 rounded-xl text-sm font-bold text-slate-300 hover:bg-slate-700">Batal</button>
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white px-6 py-2 rounded-xl text-sm font-bold shadow-lg shadow-emerald-500/30">Tugaskan</button>
            </div>
        </form>
    </div>
</div>


