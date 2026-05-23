{{-- TAB 4: SHIFT --}}
<div x-show="activeTab === 'shift'" x-cloak x-transition.opacity class="space-y-6">
    <div class="flex flex-wrap gap-4 justify-between items-center">
        <div>
            <h2 class="text-xl font-black text-white">Master Shift</h2>
            <p class="text-sm text-slate-400">Buat tipe shift dan jam kerja untuk masing-masing lokasi.</p>
        </div>
        <button @click="$dispatch('open-modal', 'add-shift')" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-bold rounded-xl transition-colors shadow-lg shadow-blue-500/20 flex items-center gap-2">
            <i class="fas fa-plus"></i> Tambah Shift
        </button>
    </div>

    @foreach($locations as $loc)
    @if($loc->shifts->count() > 0)
    <div class="bg-slate-800/80 border border-slate-700 rounded-2xl overflow-hidden">
        <div class="bg-slate-900/50 px-6 py-3 border-b border-slate-700 flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-yellow-400 text-slate-900 flex items-center justify-center font-black text-sm">{{ substr($loc->name, 0, 1) }}</div>
            <h3 class="font-bold text-white">{{ $loc->name }}</h3>
            <span class="text-xs text-slate-500">{{ $loc->shifts->count() }} shift</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="text-xs font-bold text-slate-400 uppercase bg-slate-900/30 border-b border-slate-700/50">
                    <tr>
                        <th class="px-6 py-3">Nama Shift</th>
                        <th class="px-6 py-3 text-center">Jam Kerja</th>
                        <th class="px-6 py-3 text-center">Max Crew</th>
                        <th class="px-6 py-3 text-center">Warna</th>
                        <th class="px-6 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50">
                    @foreach($loc->shifts as $shift)
                    <tr class="hover:bg-slate-700/30 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 rounded-full shadow-sm" style="background:{{ $shift->color }}"></div>
                                <span class="font-bold text-white">{{ $shift->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="bg-slate-900 px-3 py-1 rounded text-xs font-mono text-blue-400 border border-slate-700">{{ substr($shift->start_time,0,5) }} - {{ substr($shift->end_time,0,5) }}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="font-bold {{ $shift->max_crew > 1 ? 'text-orange-400' : 'text-slate-400' }}">{{ $shift->max_crew }} Orang</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="w-6 h-6 rounded-lg mx-auto border border-slate-600" style="background:{{ $shift->color }}"></div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button @click="$dispatch('open-modal', 'edit-shift-{{ $shift->id }}')" class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-400 hover:bg-blue-500/20 flex items-center justify-center transition-colors border border-blue-500/20" title="Edit">
                                    <i class="fas fa-pen text-xs"></i>
                                </button>
                                <form action="{{ route('schedules.shifts.destroy', $shift) }}" method="POST" class="m-0">
                                    @csrf @method('DELETE')
                                    <button type="button" onclick="confirmDelete(this.form)" class="w-8 h-8 rounded-lg bg-red-500/10 text-red-400 hover:bg-red-500/20 flex items-center justify-center transition-colors border border-red-500/20" title="Hapus">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
    @endforeach

    @if($locations->sum(fn($l) => $l->shifts->count()) === 0)
    <div class="text-center p-10 border border-dashed border-slate-700 rounded-2xl">
        <i class="fas fa-clock text-4xl text-slate-600 mb-3"></i>
        <p class="text-slate-400 font-medium">Belum ada shift. Tambahkan lokasi terlebih dahulu, lalu buat shift.</p>
    </div>
    @endif
</div>
