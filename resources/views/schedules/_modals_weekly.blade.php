{{-- WEEKLY BULK ASSIGN MODAL --}}
<div x-data="weeklyBulk()" x-show="show" @open-modal.window="if ($event.detail === 'weekly-bulk-assign') { show = true; calcDates(); }" @close-modal.window="show = false" class="fixed inset-0 z-[99] flex items-center justify-center" style="display:none;">
    <div x-show="show" x-transition.opacity class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm" @click="show = false"></div>
    <div x-show="show" x-transition.scale.origin.bottom class="relative bg-slate-800 rounded-3xl shadow-2xl border border-slate-700 w-full max-w-lg m-4 z-10 overflow-hidden max-h-[90vh] overflow-y-auto scrollbar-hide ">
        <div class="px-6 py-4 border-b border-slate-700/50 flex justify-between items-center bg-slate-800/50">
            <h3 class="text-lg font-black text-white"><i class="fas fa-calendar-week text-purple-400 mr-2"></i>Assign Mingguan</h3>
            <button @click="show = false" class="text-slate-400 hover:text-white"><i class="fas fa-times"></i></button>
        </div>
        <form action="{{ route('schedules.assignments.weekly-bulk') }}" method="POST">
            @csrf
            <div class="p-6 space-y-4">
                <p class="text-xs text-slate-400 bg-slate-900/50 p-3 rounded-xl border border-slate-700"><i class="fas fa-info-circle text-purple-400 mr-1"></i> Pilih shift dan crew, lalu centang hari mana saja yang diambil dalam 1 minggu.</p>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Shift <span class="text-red-400">*</span></label>
                    <select name="schedule_shift_id" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-purple-500" required>
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
                    <select name="schedule_crew_id" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-purple-500" required>
                        <option value="">-- Pilih Crew --</option>
                        @foreach($activeCrews as $crew)
                        <option value="{{ $crew->id }}">{{ $crew->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Mulai Minggu (Senin)</label>
                    <input type="date" name="week_start" x-model="weekStart" @change="calcDates()" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-purple-500" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Pilih Hari <span class="text-red-400">*</span></label>
                    <div class="grid grid-cols-7 gap-2">
                        <template x-for="(day, idx) in dayLabels" :key="idx">
                            <label class="flex flex-col items-center cursor-pointer">
                                <input type="checkbox" name="days[]" :value="idx" class="hidden peer">
                                <div class="w-full py-2 px-1 rounded-xl border text-center text-xs font-bold transition-all peer-checked:bg-purple-600 peer-checked:text-white peer-checked:border-purple-500 bg-slate-900 text-slate-400 border-slate-700 hover:border-purple-500">
                                    <div x-text="day"></div>
                                    <div class="text-[9px] mt-0.5 opacity-70" x-text="dayDates[idx] || ''"></div>
                                </div>
                            </label>
                        </template>
                    </div>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-slate-700/50 flex justify-end gap-3 bg-slate-800/50">
                <button type="button" @click="show = false" class="px-4 py-2 rounded-xl text-sm font-bold text-slate-300 hover:bg-slate-700">Batal</button>
                <button type="submit" class="bg-purple-600 hover:bg-purple-500 text-white px-6 py-2 rounded-xl text-sm font-bold shadow-lg shadow-purple-500/30">Assign</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function weeklyBulk() {
    return {
        show: false,
        weekStart: '{{ now()->startOfWeek(\Carbon\Carbon::MONDAY)->format("Y-m-d") }}',
        dayLabels: ['Sen','Sel','Rab','Kam','Jum','Sab','Min'],
        dayDates: [],
        calcDates() {
            if (!this.weekStart) return;
            let d = new Date(this.weekStart);
            // Adjust to Monday
            let day = d.getDay();
            let diff = (day === 0 ? -6 : 1) - day;
            d.setDate(d.getDate() + diff);
            this.dayDates = [];
            for (let i = 0; i < 7; i++) {
                let dd = new Date(d);
                dd.setDate(d.getDate() + i);
                this.dayDates.push(dd.getDate() + '/' + (dd.getMonth()+1));
            }
        }
    }
}
</script>
@endpush


