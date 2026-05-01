@extends('layouts.app')

@section('title', 'Kategori Produk')
@section('page-title', 'Kategori Produk')
@section('page-subtitle', 'Manajemen Kategori')

@section('content')
<div x-data="categoryApp()" class="flex flex-col gap-6">

    {{-- HEADER & ACTION BAR --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-[#1e293b] p-5 rounded-2xl border border-slate-700/80 shadow-sm">
        <div>
            <h2 class="text-xl font-black text-white tracking-tight">Daftar Kategori</h2>
            <p class="text-sm text-slate-400 font-medium mt-1">Kelola dan kelompokkan produk POS Anda</p>
        </div>
        
        <div class="flex items-center gap-3 w-full sm:w-auto">
            <div class="relative w-full sm:w-72">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" x-model="search" class="w-full bg-slate-900 border border-slate-700 rounded-xl pl-11 pr-4 py-2.5 text-slate-200 placeholder-slate-500 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all text-sm font-medium shadow-inner" placeholder="Cari kategori...">
            </div>
            <button @click="openCreateModal()" class="shrink-0 bg-emerald-500 hover:bg-emerald-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold transition-all shadow-lg shadow-emerald-500/20 active:scale-95 flex items-center gap-2">
                <i class="fas fa-plus"></i> <span class="hidden sm:inline">Tambah Kategori</span>
            </button>
        </div>
    </div>

    {{-- EMPTY STATE --}}
    @if(count($categories) === 0)
    <div class="flex flex-col items-center justify-center py-20 px-4 text-center bg-slate-800/30 rounded-3xl border border-slate-700/50 border-dashed">
        <div class="w-24 h-24 bg-slate-800 rounded-full flex items-center justify-center mb-6 shadow-inner border border-slate-700/80">
            <i class="fas fa-layer-group text-4xl text-slate-500"></i>
        </div>
        <h3 class="text-xl font-black text-white mb-2">Belum Ada Kategori</h3>
        <p class="text-slate-400 max-w-md mx-auto mb-8 font-medium">Kategori mempermudah proses kasir. Tambahkan kategori pertama Anda sekarang!</p>
        <button @click="openCreateModal()" class="bg-emerald-500 hover:bg-emerald-600 text-white px-6 py-3 rounded-xl text-sm font-bold transition-all shadow-lg shadow-emerald-500/20 active:scale-95 flex items-center gap-2">
            <i class="fas fa-plus-circle text-lg"></i> Buat Kategori Baru
        </button>
    </div>
    @else
    
    {{-- GRID CARD --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        @foreach($categories as $c)
        <div class="relative group bg-slate-800 rounded-2xl p-5 border border-slate-700/80 hover:border-slate-500 hover:shadow-2xl hover:-translate-y-1 hover:scale-[1.02] transition-all duration-300 flex flex-col min-h-[160px]" 
             x-show="search === '' || '{{ strtolower($c->name) }}'.includes(search.toLowerCase())"
             style="box-shadow: 0 4px 30px -5px {{ $c->color }}15;">
            
            {{-- Accent glow --}}
            <div class="absolute inset-0 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none" style="background: radial-gradient(circle at top right, {{ $c->color }}15, transparent 60%);"></div>

            <div class="flex justify-between items-start mb-3 relative z-10">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center shadow-inner" style="background-color: {{ $c->color }}20; border: 1px solid {{ $c->color }}40;">
                        <i class="fas fa-tag text-lg" style="color: {{ $c->color }}"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-white text-base leading-tight group-hover:text-emerald-400 transition-colors">{{ $c->name }}</h3>
                        <span class="text-[9px] uppercase font-bold text-slate-500 tracking-wider">Kategori</span>
                    </div>
                </div>

                {{-- Hover Actions --}}
                <div class="flex gap-1.5 opacity-100 lg:opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-x-2 group-hover:translate-x-0">
                    <button @click="openEditModal({{ $c->id }}, '{{ addslashes($c->name) }}', '{{ $c->color }}', '{{ addslashes($c->description) }}', {{ $c->is_active ? 'true' : 'false' }})" class="w-8 h-8 rounded-lg bg-slate-700 hover:bg-blue-500 text-slate-300 hover:text-white flex items-center justify-center transition-colors shadow-sm" title="Edit">
                        <i class="fas fa-pen text-xs"></i>
                    </button>
                    <button @click="confirmDelete({{ $c->id }}, '{{ addslashes($c->name) }}')" class="w-8 h-8 rounded-lg bg-slate-700 hover:bg-red-500 text-slate-300 hover:text-white flex items-center justify-center transition-colors shadow-sm" title="Hapus">
                        <i class="fas fa-trash text-xs"></i>
                    </button>
                </div>
            </div>

            <p class="text-sm text-slate-400 mb-5 line-clamp-2 min-h-[40px] relative z-10 font-medium">
                {{ $c->description ?: 'Tidak ada deskripsi.' }}
            </p>

            <div class="flex items-center justify-between mt-auto pt-4 border-t border-slate-700/60 relative z-10">
                <div class="px-2.5 py-1.5 rounded-lg bg-slate-900 border border-slate-700 flex items-center gap-2 shadow-inner">
                    <i class="fas fa-box text-slate-500 text-[10px]"></i>
                    <span class="text-xs font-black text-slate-300">{{ $c->products_count ?? 0 }} Produk</span>
                </div>
                
                <div>
                    @if($c->is_active)
                        <span class="px-3 py-1.5 rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-[10px] font-black uppercase flex items-center gap-2 tracking-wider shadow-sm"><div class="w-1.5 h-1.5 rounded-full bg-emerald-400 shadow-[0_0_5px_#34d399]"></div> Aktif</span>
                    @else
                        <span class="px-3 py-1.5 rounded-lg bg-slate-800 text-slate-500 border border-slate-700 text-[10px] font-black uppercase flex items-center gap-2 tracking-wider"><div class="w-1.5 h-1.5 rounded-full bg-slate-500"></div> Nonaktif</span>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if($categories instanceof \Illuminate\Pagination\LengthAwarePaginator && $categories->hasPages())
    <div class="mt-6">
        {{ $categories->links('pagination::tailwind') }}
    </div>
    @endif

    @endif

    {{-- MODAL TAMBAH / EDIT --}}
    <div x-show="showModal" x-transition.opacity x-cloak class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div @click.away="closeModal()" x-show="showModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0" class="bg-[#1e293b] rounded-3xl w-full max-w-md shadow-2xl border border-slate-700 transform overflow-hidden flex flex-col">
            
            <div class="p-6 border-b border-slate-700/80 flex justify-between items-center bg-slate-800/50">
                <h3 class="text-xl font-black text-white" x-text="isEdit ? 'Edit Kategori' : 'Kategori Baru'"></h3>
                <button @click="closeModal()" class="w-8 h-8 bg-slate-700 hover:bg-slate-600 rounded-full text-slate-400 hover:text-white transition-colors flex items-center justify-center"><i class="fas fa-times"></i></button>
            </div>

            <form :action="formAction" method="POST" class="p-6">
                @csrf
                <input type="hidden" name="_method" :value="isEdit ? 'PUT' : 'POST'">
                
                <div class="space-y-6">
                    {{-- Preview Live Badge --}}
                    <div class="flex justify-center mb-4">
                        <div class="px-5 py-2.5 rounded-xl flex items-center gap-3 border-2 shadow-lg transition-all duration-300 bg-slate-900" :style="`border-color: ${form.color}50; box-shadow: 0 4px 20px -5px ${form.color}40;`">
                            <div class="w-6 h-6 rounded-md flex items-center justify-center" :style="`background-color: ${form.color}30;`">
                                <i class="fas fa-tag text-xs" :style="`color: ${form.color}`"></i>
                            </div>
                            <span class="font-black text-sm text-white tracking-wide" x-text="form.name || 'Preview Nama Kategori'"></span>
                        </div>
                    </div>

                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-2 block flex items-center gap-1.5"><i class="fas fa-font"></i> Nama Kategori <span class="text-red-400">*</span></label>
                        <input type="text" name="name" x-model="form.name" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-sm font-medium text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors shadow-inner" placeholder="Contoh: Minuman Dingin" required>
                    </div>

                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-2 block flex items-center gap-1.5"><i class="fas fa-palette"></i> Warna Kategori</label>
                        <div class="flex items-center gap-3 bg-slate-900 p-2 rounded-xl border border-slate-700 shadow-inner">
                            <input type="color" name="color" x-model="form.color" class="w-12 h-10 rounded-lg cursor-pointer bg-transparent border-0 outline-none p-0">
                            <input type="text" x-model="form.color" class="flex-1 bg-transparent border-0 text-sm font-bold text-white uppercase focus:outline-none focus:ring-0" placeholder="#HEXCODE">
                        </div>
                    </div>

                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-2 block flex items-center gap-1.5"><i class="fas fa-align-left"></i> Deskripsi Singkat</label>
                        <textarea name="description" x-model="form.description" rows="2" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-4 text-sm font-medium text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors resize-none shadow-inner" placeholder="Berikan sedikit catatan opsional..."></textarea>
                    </div>

                    <div x-show="isEdit" class="flex items-center justify-between p-4 bg-slate-800/50 rounded-xl border border-slate-700">
                        <div>
                            <span class="text-sm font-black text-white block">Status Kategori</span>
                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Tampil di halaman POS</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" x-model="form.isActive" class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500 shadow-inner"></div>
                        </label>
                    </div>

                    <div class="pt-6 flex gap-3 mt-2">
                        <button type="button" @click="closeModal()" class="flex-1 py-3.5 bg-slate-700 hover:bg-slate-600 text-white font-bold rounded-xl transition-colors text-sm shadow-sm active:scale-95">Batal</button>
                        <button type="submit" class="flex-1 py-3.5 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl transition-colors shadow-lg shadow-blue-500/30 text-sm flex items-center justify-center gap-2 active:scale-95">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Form Delete Hidden --}}
    <form id="deleteForm" method="POST" class="hidden">
        @csrf @method('DELETE')
    </form>
</div>

<script>
function categoryApp() {
    return {
        search: '',
        showModal: false,
        isEdit: false,
        formAction: '{{ route('categories.store') }}',
        form: {
            name: '',
            color: '#3b82f6',
            description: '',
            isActive: true
        },
        
        openCreateModal() {
            this.isEdit = false;
            this.formAction = '{{ route('categories.store') }}';
            this.form = { name: '', color: '#3b82f6', description: '', isActive: true };
            this.showModal = true;
        },

        openEditModal(id, name, color, desc, isActive) {
            this.isEdit = true;
            this.formAction = `/categories/${id}`;
            this.form = {
                name: name,
                color: color || '#3b82f6',
                description: desc || '',
                isActive: isActive
            };
            this.showModal = true;
        },

        closeModal() {
            this.showModal = false;
        },

        confirmDelete(id, name) {
            if(confirm(`Yakin ingin menghapus kategori "${name}"?`)) {
                let form = document.getElementById('deleteForm');
                form.action = `/categories/${id}`;
                form.submit();
            }
        }
    }
}
</script>
@endsection
