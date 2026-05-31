@extends('layouts.app')

@section('content')
<div class="p-6 sm:p-10 min-h-screen bg-[#0f172a]" x-data="{ 
    showAddModal: false, 
    showEditModal: false,
    editData: { id: '', name: '', parent_category: '' },
    showAddMasterModal: false,
    showEditMasterModal: false,
    editMasterData: { id: '', name: '', color: 'blue' }
}">
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-10">
        <div>
            <h2 class="text-3xl font-black text-white tracking-tight">Master Jenis Biaya</h2>
            <p class="text-slate-400 text-sm mt-1">Kelola sub-kategori pengeluaran untuk studio Anda.</p>
        </div>
        <div class="flex gap-2">
            <button @click="showAddMasterModal = true" class="px-6 py-3 bg-slate-800 hover:bg-slate-700 text-white rounded-2xl font-bold flex items-center gap-2 border border-white/10 transition-all">
                <i class="fas fa-layer-group text-xs text-blue-400"></i>
                Master Jenis Biaya
            </button>
            <button @click="showAddModal = true" class="px-6 py-3 bg-blue-600 hover:bg-blue-500 text-white rounded-2xl font-bold flex items-center gap-2 shadow-lg shadow-blue-900/20 transition-all">
                <i class="fas fa-plus text-xs"></i>
                Tambah Jenis Biaya
            </button>
        </div>
    </div>

    <!-- CARDS BY CATEGORY -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach($masterCategories as $master)
        @php 
            $catKey = str_replace(' ', '_', strtolower($master->name));
            $color = $master->color;
        @endphp
        <div class="bg-slate-800/40 rounded-3xl border border-white/5 overflow-hidden backdrop-blur-xl">
            <div class="px-6 py-4 bg-{{ $color }}-500/10 border-b border-{{ $color }}-500/20 flex justify-between items-center group">
                <div>
                    <h4 class="text-[10px] font-black text-{{ $color }}-400 uppercase tracking-widest">{{ $master->name }}</h4>
                    <span class="text-[10px] font-black text-slate-500">{{ $categories->where('parent_category', $catKey)->count() }} Items</span>
                </div>
                <div class="flex items-center gap-1 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-all">
                    <button @click="editMasterData = { id: '{{ $master->id }}', name: '{{ $master->name }}', color: '{{ $master->color }}' }; showEditMasterModal = true" 
                            class="w-6 h-6 rounded bg-slate-800/50 text-slate-400 hover:text-blue-400 flex items-center justify-center transition-all">
                        <i class="fas fa-edit text-[9px]"></i>
                    </button>
                    <form action="{{ route('master_expense_categories.destroy', $master->id) }}" method="POST" onsubmit="return confirm('Hapus master jenis biaya ini beserta semua isinya?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="w-6 h-6 rounded bg-slate-800/50 text-slate-400 hover:text-red-400 flex items-center justify-center transition-all">
                            <i class="fas fa-trash text-[9px]"></i>
                        </button>
                    </form>
                </div>
            </div>
            <div class="p-4 space-y-2 max-h-[400px] overflow-y-auto hide-scrollbar">
                @forelse($categories->where('parent_category', $catKey) as $item)
                <div class="group flex items-center justify-between p-3 rounded-xl bg-slate-900/40 border border-white/5 hover:border-{{ $color }}-500/30 transition-all">
                    <span class="text-sm font-bold text-slate-300 group-hover:text-white">{{ $item->name }}</span>
                    <div class="flex items-center gap-1 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-all">
                        <button @click="editData = { id: '{{ $item->id }}', name: '{{ $item->name }}', parent_category: '{{ $item->parent_category }}' }; showEditModal = true" 
                                class="w-8 h-8 rounded-lg bg-slate-800 text-slate-400 hover:text-blue-400 flex items-center justify-center transition-all">
                            <i class="fas fa-edit text-[10px]"></i>
                        </button>
                        <form action="{{ route('expense_categories.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus jenis biaya ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-8 h-8 rounded-lg bg-slate-800 text-slate-400 hover:text-red-400 flex items-center justify-center transition-all">
                                <i class="fas fa-trash text-[10px]"></i>
                            </button>
                        </form>
                    </div>
                </div>
                @empty
                <p class="text-[10px] text-slate-600 italic text-center py-4">Belum ada data</p>
                @endforelse
            </div>
        </div>
        @endforeach
    </div>

    <!-- ADD MODAL -->
    <div x-show="showAddModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm" x-cloak>
        <div class="bg-slate-900 border border-white/10 rounded-3xl w-full max-w-md overflow-hidden shadow-2xl max-h-[90vh] overflow-y-auto scrollbar-hide " @click.away="showAddModal = false">
            <div class="p-6 border-b border-white/5">
                <h3 class="text-lg font-black text-white">Tambah Jenis Biaya</h3>
            </div>
            <form action="{{ route('expense_categories.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase mb-2">Kategori Utama</label>
                    <select name="parent_category" class="w-full bg-slate-800 border border-white/5 rounded-xl px-4 py-3 text-white outline-none focus:border-blue-500 transition-all">
                        @foreach($masterCategories as $m)
                        <option value="{{ str_replace(' ', '_', strtolower($m->name)) }}">{{ $m->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase mb-2">Nama Jenis Biaya</label>
                    <input type="text" name="name" required placeholder="Contoh: Listrik, Adobe, WiFi" class="w-full bg-slate-800 border border-white/5 rounded-xl px-4 py-3 text-white outline-none focus:border-blue-500 transition-all">
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="button" @click="showAddModal = false" class="flex-1 py-3 bg-slate-800 text-slate-400 rounded-xl font-bold">Batal</button>
                    <button type="submit" class="flex-1 py-3 bg-blue-600 text-white rounded-xl font-bold shadow-lg shadow-blue-900/20">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT MODAL -->
    <div x-show="showEditModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm" x-cloak>
        <div class="bg-slate-900 border border-white/10 rounded-3xl w-full max-w-md overflow-hidden shadow-2xl max-h-[90vh] overflow-y-auto scrollbar-hide " @click.away="showEditModal = false">
            <div class="p-6 border-b border-white/5">
                <h3 class="text-lg font-black text-white">Edit Jenis Biaya</h3>
            </div>
            <form :action="'{{ url('expense_categories') }}/' + editData.id" method="POST" class="p-6 space-y-4">
                @csrf @method('PUT')
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase mb-2">Kategori Utama</label>
                    <select name="parent_category" x-model="editData.parent_category" class="w-full bg-slate-800 border border-white/5 rounded-xl px-4 py-3 text-white outline-none focus:border-blue-500 transition-all">
                        @foreach($masterCategories as $m)
                        <option value="{{ str_replace(' ', '_', strtolower($m->name)) }}">{{ $m->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase mb-2">Nama Jenis Biaya</label>
                    <input type="text" name="name" x-model="editData.name" required class="w-full bg-slate-800 border border-white/5 rounded-xl px-4 py-3 text-white outline-none focus:border-blue-500 transition-all">
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="button" @click="showEditModal = false" class="flex-1 py-3 bg-slate-800 text-slate-400 rounded-xl font-bold">Batal</button>
                    <button type="submit" class="flex-1 py-3 bg-blue-600 text-white rounded-xl font-bold shadow-lg shadow-blue-900/20">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ADD MASTER MODAL -->
    <div x-show="showAddMasterModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm" x-cloak>
        <div class="bg-slate-900 border border-white/10 rounded-3xl w-full max-w-md overflow-hidden shadow-2xl max-h-[90vh] overflow-y-auto scrollbar-hide " @click.away="showAddMasterModal = false">
            <div class="p-6 border-b border-white/5">
                <h3 class="text-lg font-black text-white">Tambah Master Jenis Biaya</h3>
            </div>
            <form action="{{ route('master_expense_categories.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase mb-2">Nama Master Kategori</label>
                    <input type="text" name="name" required placeholder="Contoh: Operasional" class="w-full bg-slate-800 border border-white/5 rounded-xl px-4 py-3 text-white outline-none focus:border-blue-500 transition-all">
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase mb-2">Warna (Tailwind)</label>
                    <select name="color" class="w-full bg-slate-800 border border-white/5 rounded-xl px-4 py-3 text-white outline-none focus:border-blue-500 transition-all">
                        <option value="blue">Blue</option>
                        <option value="emerald">Emerald</option>
                        <option value="purple">Purple</option>
                        <option value="amber">Amber</option>
                        <option value="rose">Rose</option>
                        <option value="cyan">Cyan</option>
                        <option value="fuchsia">Fuchsia</option>
                        <option value="indigo">Indigo</option>
                        <option value="orange">Orange</option>
                    </select>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="button" @click="showAddMasterModal = false" class="flex-1 py-3 bg-slate-800 text-slate-400 rounded-xl font-bold">Batal</button>
                    <button type="submit" class="flex-1 py-3 bg-blue-600 text-white rounded-xl font-bold shadow-lg shadow-blue-900/20">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT MASTER MODAL -->
    <div x-show="showEditMasterModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm" x-cloak>
        <div class="bg-slate-900 border border-white/10 rounded-3xl w-full max-w-md overflow-hidden shadow-2xl max-h-[90vh] overflow-y-auto scrollbar-hide " @click.away="showEditMasterModal = false">
            <div class="p-6 border-b border-white/5">
                <h3 class="text-lg font-black text-white">Edit Master Jenis Biaya</h3>
            </div>
            <form :action="'{{ url('master_expense_categories') }}/' + editMasterData.id" method="POST" class="p-6 space-y-4">
                @csrf @method('PUT')
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase mb-2">Nama Master Kategori</label>
                    <input type="text" name="name" x-model="editMasterData.name" required class="w-full bg-slate-800 border border-white/5 rounded-xl px-4 py-3 text-white outline-none focus:border-blue-500 transition-all">
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase mb-2">Warna (Tailwind)</label>
                    <select name="color" x-model="editMasterData.color" class="w-full bg-slate-800 border border-white/5 rounded-xl px-4 py-3 text-white outline-none focus:border-blue-500 transition-all">
                        <option value="blue">Blue</option>
                        <option value="emerald">Emerald</option>
                        <option value="purple">Purple</option>
                        <option value="amber">Amber</option>
                        <option value="rose">Rose</option>
                        <option value="cyan">Cyan</option>
                        <option value="fuchsia">Fuchsia</option>
                        <option value="indigo">Indigo</option>
                        <option value="orange">Orange</option>
                    </select>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="button" @click="showEditMasterModal = false" class="flex-1 py-3 bg-slate-800 text-slate-400 rounded-xl font-bold">Batal</button>
                    <button type="submit" class="flex-1 py-3 bg-blue-600 text-white rounded-xl font-bold shadow-lg shadow-blue-900/20">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .hide-scrollbar::-webkit-scrollbar { display: none; }
    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endsection


