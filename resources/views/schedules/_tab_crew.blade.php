{{-- TAB 3: TIM / CREW --}}
<div x-show="activeTab === 'tim'" x-cloak x-transition.opacity class="space-y-6">
    <div class="flex flex-wrap gap-4 justify-between items-center">
        <div>
            <h2 class="text-xl font-black text-white">Manajemen Tim / Crew</h2>
            <p class="text-sm text-slate-400">Tambah dan kelola anggota tim untuk penjadwalan.</p>
        </div>
        <button @click="$dispatch('open-modal', 'add-crew')" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-bold rounded-xl transition-colors shadow-lg shadow-emerald-500/20 flex items-center gap-2">
            <i class="fas fa-user-plus"></i> Tambah Crew
        </button>
    </div>

    <div class="bg-slate-800/80 border border-slate-700 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700 dark:text-slate-300">
                <thead class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase bg-slate-900/50 border-b border-slate-700">
                    <tr>
                        <th class="px-6 py-4">Nama</th>
                        <th class="px-6 py-4">Telepon</th>
                        <th class="px-6 py-4">Posisi</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50">
                    @forelse($crews as $crew)
                    <tr class="hover:bg-slate-700/30 transition-colors">
                        <td class="px-6 py-4 font-bold text-slate-800 dark:text-white flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full {{ $crew->is_active ? 'bg-blue-500' : 'bg-slate-600' }} flex items-center justify-center text-xs font-black text-white">{{ substr($crew->name, 0, 1) }}</div>
                            {{ $crew->name }}
                        </td>
                        <td class="px-6 py-4">{{ $crew->phone ?? '-' }}</td>
                        <td class="px-6 py-4">{{ $crew->position ?? '-' }}</td>
                        <td class="px-6 py-4 text-center">
                            <form action="{{ route('schedules.crews.toggle', $crew) }}" method="POST" class="inline m-0">
                                @csrf
                                <button type="submit" class="px-2 py-1 text-[10px] font-bold rounded-full uppercase border transition-colors {{ $crew->is_active ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30 hover:bg-emerald-500/30' : 'bg-red-500/20 text-red-400 border-red-500/30 hover:bg-red-500/30' }}">
                                    {{ $crew->is_active ? 'Aktif' : 'Nonaktif' }}
                                </button>
                            </form>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button @click="$dispatch('open-modal', 'edit-crew-{{ $crew->id }}')" class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-400 hover:bg-blue-500/20 flex items-center justify-center transition-colors border border-blue-500/20" title="Edit">
                                    <i class="fas fa-pen text-xs"></i>
                                </button>
                                <form action="{{ route('schedules.crews.destroy', $crew) }}" method="POST" class="m-0">
                                    @csrf @method('DELETE')
                                    <button type="button" onclick="confirmDelete(this.form)" class="w-8 h-8 rounded-lg bg-red-500/10 text-red-400 hover:bg-red-500/20 flex items-center justify-center transition-colors border border-red-500/20" title="Hapus">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-6 py-10 text-center text-slate-500"><i class="fas fa-users text-3xl mb-2 block"></i>Belum ada crew. Klik "Tambah Crew".</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
