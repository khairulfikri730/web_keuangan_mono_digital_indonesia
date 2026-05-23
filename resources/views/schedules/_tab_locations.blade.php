{{-- TAB 2: LOKASI --}}
<div x-show="activeTab === 'lokasi'" x-cloak x-transition.opacity class="space-y-6">
    <div class="flex flex-wrap gap-4 justify-between items-center">
        <div>
            <h2 class="text-xl font-black text-white">Manajemen Lokasi</h2>
            <p class="text-sm text-slate-400">Kelola lokasi kerja secara independen. Tidak terhubung dengan cashflow.</p>
        </div>
        <button @click="$dispatch('open-modal', 'add-location')" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-bold rounded-xl transition-colors shadow-lg shadow-blue-500/20 flex items-center gap-2">
            <i class="fas fa-plus"></i> Tambah Lokasi
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @forelse($locations as $loc)
        <div class="bg-slate-800/80 border border-slate-700 rounded-2xl p-6 relative overflow-hidden group">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-blue-500/10 rounded-full blur-xl group-hover:bg-blue-500/20 transition-all"></div>
            <div class="flex items-center gap-4 mb-4">
                <div class="w-14 h-14 rounded-full bg-slate-900 border border-slate-700 flex items-center justify-center font-black text-xl text-yellow-400">{{ substr($loc->name, 0, 1) }}</div>
                <div>
                    <h3 class="font-bold text-white text-lg">{{ $loc->name }}</h3>
                    <span class="px-2 py-0.5 {{ $loc->is_active ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30' : 'bg-red-500/20 text-red-400 border-red-500/30' }} border text-[10px] font-bold rounded uppercase">{{ $loc->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                </div>
            </div>
            <div class="space-y-2 mt-4">
                <p class="text-xs text-slate-400"><i class="fas fa-map-marker-alt w-5 text-center text-blue-400"></i> {{ $loc->description ?? 'Tidak ada deskripsi' }}</p>
                <p class="text-xs text-slate-400"><i class="fas fa-clock w-5 text-center text-blue-400"></i> {{ $loc->shifts->count() }} Tipe Shift</p>
            </div>
            <div class="mt-5 flex gap-2">
                <button @click="$dispatch('open-modal', 'edit-location-{{ $loc->id }}')" class="flex-1 py-2 bg-slate-900 hover:bg-slate-700 text-sm font-bold text-white rounded-xl transition-colors border border-slate-700">
                    <i class="fas fa-pen text-xs mr-1"></i> Edit
                </button>
                <form action="{{ route('schedules.locations.destroy', $loc) }}" method="POST" class="m-0">
                    @csrf @method('DELETE')
                    <button type="button" onclick="confirmDelete(this.form)" class="py-2 px-4 bg-red-500/10 hover:bg-red-500/20 text-red-400 text-sm font-bold rounded-xl transition-colors border border-red-500/20">
                        <i class="fas fa-trash text-xs"></i>
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center p-10 border border-dashed border-slate-700 rounded-2xl">
            <i class="fas fa-building text-4xl text-slate-600 mb-3"></i>
            <p class="text-slate-400 font-medium">Belum ada lokasi. Klik "Tambah Lokasi" untuk memulai.</p>
        </div>
        @endforelse
    </div>
</div>
