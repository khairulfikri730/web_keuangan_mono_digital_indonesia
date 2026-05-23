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
                    <b>{{ $asgn->crew->name ?? '?' }}</b> â€” {{ $asgn->shift->name ?? '' }} ({{ $asgn->shift->location->name ?? '' }})
                    <br>Tanggal: {{ $asgn->date->translatedFormat('l, d M Y') }}
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Alasan Close</label>
                    <textarea name="closed_reason" rows="2" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-red-500" placeholder="Contoh: Hujan deras"></textarea>
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


