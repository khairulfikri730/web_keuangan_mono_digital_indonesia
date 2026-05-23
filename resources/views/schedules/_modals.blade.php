{{-- ══════════════════════════════════════════════════════════ --}}
{{-- MODAL: ADD LOCATION --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<div x-data="{ show: false }" x-show="show" @open-modal.window="if ($event.detail === 'add-location') show = true" @close-modal.window="show = false" class="fixed inset-0 z-[99] flex items-center justify-center" style="display:none;">
    <div x-show="show" x-transition.opacity class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm" @click="show = false"></div>
    <div x-show="show" x-transition.scale.origin.bottom class="relative bg-slate-800 rounded-3xl shadow-2xl border border-slate-700 w-full max-w-md m-4 z-10 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-700/50 flex justify-between items-center bg-slate-800/50">
            <h3 class="text-lg font-black text-white"><i class="fas fa-plus text-blue-400 mr-2"></i>Tambah Lokasi</h3>
            <button @click="show = false" class="text-slate-400 hover:text-white"><i class="fas fa-times"></i></button>
        </div>
        <form action="{{ route('schedules.locations.store') }}" method="POST">
            @csrf
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Nama Lokasi <span class="text-red-400">*</span></label>
                    <input type="text" name="name" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-blue-500" required placeholder="Contoh: Youth Center">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Deskripsi</label>
                    <input type="text" name="description" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-blue-500" placeholder="Alamat atau keterangan">
                </div>
            </div>
            <div class="px-6 py-4 border-t border-slate-700/50 flex justify-end gap-3 bg-slate-800/50">
                <button type="button" @click="show = false" class="px-4 py-2 rounded-xl text-sm font-bold text-slate-300 hover:bg-slate-700">Batal</button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-2 rounded-xl text-sm font-bold shadow-lg shadow-blue-500/30">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- EDIT LOCATION MODALS --}}
@foreach($locations as $loc)
<div x-data="{ show: false }" x-show="show" @open-modal.window="if ($event.detail === 'edit-location-{{ $loc->id }}') show = true" @close-modal.window="show = false" class="fixed inset-0 z-[99] flex items-center justify-center" style="display:none;">
    <div x-show="show" x-transition.opacity class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm" @click="show = false"></div>
    <div x-show="show" x-transition.scale.origin.bottom class="relative bg-slate-800 rounded-3xl shadow-2xl border border-slate-700 w-full max-w-md m-4 z-10 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-700/50 flex justify-between items-center bg-slate-800/50">
            <h3 class="text-lg font-black text-white"><i class="fas fa-pen text-blue-400 mr-2"></i>Edit Lokasi</h3>
            <button @click="show = false" class="text-slate-400 hover:text-white"><i class="fas fa-times"></i></button>
        </div>
        <form action="{{ route('schedules.locations.update', $loc) }}" method="POST">
            @csrf @method('PUT')
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Nama Lokasi <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ $loc->name }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-blue-500" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Deskripsi</label>
                    <input type="text" name="description" value="{{ $loc->description }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-blue-500">
                </div>
            </div>
            <div class="px-6 py-4 border-t border-slate-700/50 flex justify-end gap-3 bg-slate-800/50">
                <button type="button" @click="show = false" class="px-4 py-2 rounded-xl text-sm font-bold text-slate-300 hover:bg-slate-700">Batal</button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-2 rounded-xl text-sm font-bold shadow-lg shadow-blue-500/30">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endforeach

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- MODAL: ADD CREW --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<div x-data="{ show: false }" x-show="show" @open-modal.window="if ($event.detail === 'add-crew') show = true" @close-modal.window="show = false" class="fixed inset-0 z-[99] flex items-center justify-center" style="display:none;">
    <div x-show="show" x-transition.opacity class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm" @click="show = false"></div>
    <div x-show="show" x-transition.scale.origin.bottom class="relative bg-slate-800 rounded-3xl shadow-2xl border border-slate-700 w-full max-w-md m-4 z-10 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-700/50 flex justify-between items-center bg-slate-800/50">
            <h3 class="text-lg font-black text-white"><i class="fas fa-user-plus text-emerald-400 mr-2"></i>Tambah Crew</h3>
            <button @click="show = false" class="text-slate-400 hover:text-white"><i class="fas fa-times"></i></button>
        </div>
        <form action="{{ route('schedules.crews.store') }}" method="POST">
            @csrf
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Nama <span class="text-red-400">*</span></label>
                    <input type="text" name="name" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-blue-500" required placeholder="Contoh: Fahri">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Telepon</label>
                    <input type="text" name="phone" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-blue-500" placeholder="08xxxxxxxxxx">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Posisi / Jabatan</label>
                    <input type="text" name="position" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-blue-500" placeholder="Operator / Crew">
                </div>
            </div>
            <div class="px-6 py-4 border-t border-slate-700/50 flex justify-end gap-3 bg-slate-800/50">
                <button type="button" @click="show = false" class="px-4 py-2 rounded-xl text-sm font-bold text-slate-300 hover:bg-slate-700">Batal</button>
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white px-6 py-2 rounded-xl text-sm font-bold shadow-lg shadow-emerald-500/30">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- EDIT CREW MODALS --}}
@foreach($crews as $crew)
<div x-data="{ show: false }" x-show="show" @open-modal.window="if ($event.detail === 'edit-crew-{{ $crew->id }}') show = true" @close-modal.window="show = false" class="fixed inset-0 z-[99] flex items-center justify-center" style="display:none;">
    <div x-show="show" x-transition.opacity class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm" @click="show = false"></div>
    <div x-show="show" x-transition.scale.origin.bottom class="relative bg-slate-800 rounded-3xl shadow-2xl border border-slate-700 w-full max-w-md m-4 z-10 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-700/50 flex justify-between items-center bg-slate-800/50">
            <h3 class="text-lg font-black text-white"><i class="fas fa-pen text-blue-400 mr-2"></i>Edit Crew</h3>
            <button @click="show = false" class="text-slate-400 hover:text-white"><i class="fas fa-times"></i></button>
        </div>
        <form action="{{ route('schedules.crews.update', $crew) }}" method="POST">
            @csrf @method('PUT')
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Nama <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ $crew->name }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-blue-500" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Telepon</label>
                    <input type="text" name="phone" value="{{ $crew->phone }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Posisi</label>
                    <input type="text" name="position" value="{{ $crew->position }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-blue-500">
                </div>
            </div>
            <div class="px-6 py-4 border-t border-slate-700/50 flex justify-end gap-3 bg-slate-800/50">
                <button type="button" @click="show = false" class="px-4 py-2 rounded-xl text-sm font-bold text-slate-300 hover:bg-slate-700">Batal</button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-2 rounded-xl text-sm font-bold shadow-lg shadow-blue-500/30">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endforeach

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- MODAL: ADD SHIFT --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<div x-data="{ show: false }" x-show="show" @open-modal.window="if ($event.detail === 'add-shift') show = true" @close-modal.window="show = false" class="fixed inset-0 z-[99] flex items-center justify-center" style="display:none;">
    <div x-show="show" x-transition.opacity class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm" @click="show = false"></div>
    <div x-show="show" x-transition.scale.origin.bottom class="relative bg-slate-800 rounded-3xl shadow-2xl border border-slate-700 w-full max-w-md m-4 z-10 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-700/50 flex justify-between items-center bg-slate-800/50">
            <h3 class="text-lg font-black text-white"><i class="fas fa-plus text-blue-400 mr-2"></i>Tambah Shift</h3>
            <button @click="show = false" class="text-slate-400 hover:text-white"><i class="fas fa-times"></i></button>
        </div>
        <form action="{{ route('schedules.shifts.store') }}" method="POST">
            @csrf
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Lokasi <span class="text-red-400">*</span></label>
                    <select name="schedule_location_id" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-blue-500" required>
                        @foreach($locations as $loc)
                        <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Nama Shift <span class="text-red-400">*</span></label>
                    <input type="text" name="name" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-blue-500" required placeholder="Indoor / Outdoor / Pagi / Sore">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Jam Mulai <span class="text-red-400">*</span></label>
                        <input type="time" name="start_time" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Jam Selesai <span class="text-red-400">*</span></label>
                        <input type="time" name="end_time" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-blue-500" required>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Warna <span class="text-red-400">*</span></label>
                        <input type="color" name="color" value="#3b82f6" class="w-full h-[46px] bg-slate-900 border border-slate-700 rounded-xl px-2 py-1 focus:outline-none focus:border-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Max Crew <span class="text-red-400">*</span></label>
                        <input type="number" name="max_crew" value="1" min="1" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-blue-500" required>
                    </div>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-slate-700/50 flex justify-end gap-3 bg-slate-800/50">
                <button type="button" @click="show = false" class="px-4 py-2 rounded-xl text-sm font-bold text-slate-300 hover:bg-slate-700">Batal</button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-2 rounded-xl text-sm font-bold shadow-lg shadow-blue-500/30">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- EDIT SHIFT MODALS --}}
@foreach($locations as $loc)
@foreach($loc->shifts as $shift)
<div x-data="{ show: false }" x-show="show" @open-modal.window="if ($event.detail === 'edit-shift-{{ $shift->id }}') show = true" @close-modal.window="show = false" class="fixed inset-0 z-[99] flex items-center justify-center" style="display:none;">
    <div x-show="show" x-transition.opacity class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm" @click="show = false"></div>
    <div x-show="show" x-transition.scale.origin.bottom class="relative bg-slate-800 rounded-3xl shadow-2xl border border-slate-700 w-full max-w-md m-4 z-10 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-700/50 flex justify-between items-center bg-slate-800/50">
            <h3 class="text-lg font-black text-white"><i class="fas fa-pen text-blue-400 mr-2"></i>Edit Shift — {{ $loc->name }}</h3>
            <button @click="show = false" class="text-slate-400 hover:text-white"><i class="fas fa-times"></i></button>
        </div>
        <form action="{{ route('schedules.shifts.update', $shift) }}" method="POST">
            @csrf @method('PUT')
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Nama Shift <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ $shift->name }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-blue-500" required>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Jam Mulai <span class="text-red-400">*</span></label>
                        <input type="time" name="start_time" value="{{ substr($shift->start_time,0,5) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Jam Selesai <span class="text-red-400">*</span></label>
                        <input type="time" name="end_time" value="{{ substr($shift->end_time,0,5) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-blue-500" required>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Warna <span class="text-red-400">*</span></label>
                        <input type="color" name="color" value="{{ $shift->color }}" class="w-full h-[46px] bg-slate-900 border border-slate-700 rounded-xl px-2 py-1 focus:outline-none focus:border-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Max Crew <span class="text-red-400">*</span></label>
                        <input type="number" name="max_crew" value="{{ $shift->max_crew }}" min="1" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-blue-500" required>
                    </div>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-slate-700/50 flex justify-end gap-3 bg-slate-800/50">
                <button type="button" @click="show = false" class="px-4 py-2 rounded-xl text-sm font-bold text-slate-300 hover:bg-slate-700">Batal</button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-2 rounded-xl text-sm font-bold shadow-lg shadow-blue-500/30">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endforeach
@endforeach

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- MODAL: BULK ASSIGN --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<div x-data="{ show: false }" x-show="show" @open-modal.window="if ($event.detail === 'bulk-assign') show = true" @close-modal.window="show = false" class="fixed inset-0 z-[99] flex items-center justify-center" style="display:none;">
    <div x-show="show" x-transition.opacity class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm" @click="show = false"></div>
    <div x-show="show" x-transition.scale.origin.bottom class="relative bg-slate-800 rounded-3xl shadow-2xl border border-slate-700 w-full max-w-lg m-4 z-10 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-700/50 flex justify-between items-center bg-slate-800/50">
            <h3 class="text-lg font-black text-white"><i class="fas fa-layer-group text-purple-400 mr-2"></i>Bulk Assign Jadwal</h3>
            <button @click="show = false" class="text-slate-400 hover:text-white"><i class="fas fa-times"></i></button>
        </div>
        <form action="{{ route('schedules.assignments.bulk') }}" method="POST">
            @csrf
            <div class="p-6 space-y-4">
                <p class="text-xs text-slate-400 bg-slate-900/50 p-3 rounded-xl border border-slate-700"><i class="fas fa-info-circle text-blue-400 mr-1"></i> Assign crew ke shift untuk rentang tanggal sekaligus. Duplikat & shift penuh akan otomatis dilewati.</p>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Shift <span class="text-red-400">*</span></label>
                    <select name="schedule_shift_id" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-blue-500" required>
                        <option value="">-- Pilih Shift --</option>
                        @foreach($locations as $loc)
                        <optgroup label="{{ $loc->name }}">
                            @foreach($loc->shifts as $shift)
                            <option value="{{ $shift->id }}">{{ $shift->name }} ({{ substr($shift->start_time,0,5) }}-{{ substr($shift->end_time,0,5) }})</option>
                            @endforeach
                        </optgroup>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Crew <span class="text-red-400">*</span></label>
                    <select name="schedule_crew_id" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-blue-500" required>
                        <option value="">-- Pilih Crew --</option>
                        @foreach($activeCrews as $crew)
                        <option value="{{ $crew->id }}">{{ $crew->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Dari Tanggal <span class="text-red-400">*</span></label>
                        <input type="date" name="date_from" value="{{ $date }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Sampai Tanggal <span class="text-red-400">*</span></label>
                        <input type="date" name="date_to" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-blue-500" required>
                    </div>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-slate-700/50 flex justify-end gap-3 bg-slate-800/50">
                <button type="button" @click="show = false" class="px-4 py-2 rounded-xl text-sm font-bold text-slate-300 hover:bg-slate-700">Batal</button>
                <button type="submit" class="bg-purple-600 hover:bg-purple-500 text-white px-6 py-2 rounded-xl text-sm font-bold shadow-lg shadow-purple-500/30">Assign Semua</button>
            </div>
        </form>
    </div>
</div>
