@extends('layouts.app')
@section('title', 'Modal Usaha')
@section('page-title', 'Manajemen Modal Usaha')

@section('content')
<div x-data="capitalApp()" class="flex flex-col gap-6">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-black text-white tracking-tight">Modal Awal / Injeksi Dana</h2>
            <p class="text-slate-400 text-sm mt-1">Kelola modal awal dan rincian aset / bahan habis pakai</p>
        </div>
        <div class="flex gap-2">
            <button @click="showImportModal = true" class="bg-blue-600 hover:bg-blue-500 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg shadow-blue-500/30 transition-all flex items-center gap-2">
                <i class="fas fa-file-excel"></i> Import Excel
            </button>
            <button @click="openAddModal()" class="bg-emerald-600 hover:bg-emerald-500 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg shadow-emerald-500/30 transition-all flex items-center gap-2">
                <i class="fas fa-plus"></i> Tambah Modal
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6">
        @forelse($capitals as $capital)
        <div class="bg-slate-800 rounded-2xl border border-slate-700 shadow-xl overflow-hidden">
            <div class="p-6 border-b border-slate-700/50 flex justify-between items-center bg-slate-800/50">
                <div>
                    <h3 class="text-lg font-black text-white"><i class="fas fa-wallet text-emerald-400 mr-2"></i> Rp {{ number_format($capital->total_amount, 0, ',', '.') }}</h3>
                    <p class="text-xs text-slate-400 mt-1">Tanggal: {{ $capital->date->format('d M Y') }} Â· {{ $capital->is_detailed ? 'Detail Terlampir' : 'Hanya Total' }}</p>
                </div>
                <div class="flex gap-2">
                    <button type="button" @click="openEditModal({{ $capital->id }})" class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-400 hover:bg-blue-500 hover:text-white flex items-center justify-center transition-all border border-blue-500/20" title="Edit Data">
                        <i class="fas fa-edit"></i>
                    </button>
                    <form action="{{ route('capitals.destroy', $capital) }}" method="POST" onsubmit="return confirm('Hapus data modal ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="w-10 h-10 rounded-xl bg-red-500/10 text-red-400 hover:bg-red-500 hover:text-white flex items-center justify-center transition-all border border-red-500/20" title="Hapus Data">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
            
            @if($capital->is_detailed && $capital->items->count() > 0)
            <div class="p-0 overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-300">
                    <thead class="bg-slate-900/50 text-slate-400 font-bold uppercase text-[10px] tracking-wider border-b border-slate-700">
                        <tr>
                            <th class="px-6 py-4">Item</th>
                            <th class="px-6 py-4">Tipe</th>
                            <th class="px-6 py-4">Harga Satuan</th>
                            <th class="px-6 py-4">Qty</th>
                            <th class="px-6 py-4 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        @foreach($capital->items as $item)
                        <tr class="hover:bg-slate-800/80 transition-colors">
                            <td class="px-6 py-4 font-bold text-white">{{ $item->name }}</td>
                            <td class="px-6 py-4">
                                @if($item->type === 'asset')
                                <span class="bg-blue-500/10 text-blue-400 border border-blue-500/20 px-2 py-1 rounded text-xs font-bold uppercase tracking-wider">Aset</span>
                                @elseif($item->type === 'maintenance')
                                <span class="bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 px-2 py-1 rounded text-xs font-bold uppercase tracking-wider">Servis</span>
                                @else
                                <span class="bg-amber-500/10 text-amber-400 border border-amber-500/20 px-2 py-1 rounded text-xs font-bold uppercase tracking-wider">Habis Pakai</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                            <td class="px-6 py-4">{{ $item->quantity }}</td>
                            <td class="px-6 py-4 text-right font-bold text-emerald-400">Rp {{ number_format($item->total_price, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="p-6 text-center text-slate-500 text-sm">
                Tidak ada rincian (Input Modal Total Saja)
            </div>
            @endif
        </div>
        @empty
        <div class="bg-slate-800/50 border border-slate-700 border-dashed rounded-2xl p-12 text-center">
            <div class="w-16 h-16 bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4 border border-slate-700 text-slate-500">
                <i class="fas fa-wallet text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-white mb-2">Belum Ada Data Modal Usaha</h3>
            <p class="text-slate-400 text-sm max-w-sm mx-auto mb-6">Mulai dengan mencatat injeksi modal awal untuk menghitung ROI dan Laba Bersih akurat.</p>
            <button @click="openAddModal()" class="bg-slate-700 hover:bg-slate-600 text-white px-5 py-2.5 rounded-xl font-bold transition-all inline-flex items-center gap-2">
                <i class="fas fa-plus"></i> Tambah Sekarang
            </button>
        </div>
        @endforelse
    </div>

    <!-- Modal Form (Add & Edit) -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden p-4">
        <div x-show="showModal" x-transition.opacity class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm" @click="showModal = false"></div>
        <div x-show="showModal" x-transition.scale.origin.bottom class="relative bg-slate-800 rounded-3xl shadow-2xl border border-slate-700 w-full max-w-4xl z-10 overflow-hidden max-h-[90vh] overflow-y-auto scrollbar-hide ">
            <div class="px-6 py-4 border-b border-slate-700/50 flex justify-between items-center bg-slate-800/50">
                <h3 class="text-lg font-black text-white flex items-center gap-2">
                    <i :class="isEdit ? 'fas fa-edit text-blue-400' : 'fas fa-plus-circle text-emerald-400'"></i>
                    <span x-text="isEdit ? 'Edit Modal Usaha' : 'Catat Modal Baru'"></span>
                </h3>
                <button @click="showModal = false" class="text-slate-400 hover:text-white transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form :action="formAction" method="POST">
                @csrf
                <template x-if="isEdit">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto custom-scrollbar">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Tanggal</label>
                            <input type="date" name="date" x-model="formData.date" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-blue-500 transition-all" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Jenis Input</label>
                            <select x-model="formData.is_detailed" name="is_detailed" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-blue-500 transition-all" required>
                                <option value="0">Hanya Total Modal</option>
                                <option value="1">Rincian Item (Aset & Habis Pakai)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Jika Hanya Total -->
                    <div x-show="formData.is_detailed == 0" x-transition>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Total Modal (Rp)</label>
                        <input type="text" x-model="formData.totalAmountFormatted" @input="formData.totalAmountFormatted = formatCurrency($event.target.value)" placeholder="0" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white text-lg font-black focus:outline-none focus:border-blue-500 transition-all" :required="formData.is_detailed == '0'">
                        <input type="hidden" name="total_amount" :value="formData.totalAmountFormatted.replace(/\./g, '')">
                    </div>

                    <!-- Jika Rincian -->
                    <div x-show="formData.is_detailed == '1'" x-transition class="space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-700 pb-2">
                            <h4 class="font-bold text-white">Daftar Item Modal</h4>
                            <button type="button" @click="addItem()" class="text-xs bg-blue-500/20 text-blue-400 border border-blue-500/50 hover:bg-blue-500 hover:text-white px-3 py-1.5 rounded-lg transition-colors font-bold">
                                + Tambah Baris
                            </button>
                        </div>
                        
                        <div class="space-y-3">
                            <template x-for="(item, index) in formData.items" :key="index">
                                <div class="flex flex-wrap md:flex-nowrap gap-3 items-end p-4 bg-slate-900/30 rounded-xl border border-slate-700">
                                    <div class="w-full md:w-1/3">
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Nama Item</label>
                                        <input type="text" :name="`items[${index}][name]`" x-model="item.name" class="w-full bg-slate-800 border border-slate-600 rounded-lg px-3 py-2 text-white text-sm focus:border-blue-500" :required="formData.is_detailed == '1'">
                                    </div>
                                    <div class="w-full md:w-1/6">
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Tipe</label>
                                        <select :name="`items[${index}][type]`" x-model="item.type" class="w-full bg-slate-800 border border-slate-600 rounded-lg px-3 py-2 text-white text-sm focus:border-blue-500" :required="formData.is_detailed == '1'">
                                            <option value="asset">Aset</option>
                                            <option value="consumable">Habis Pakai</option>
                                            <option value="maintenance">Biaya Beban / Servis</option>
                                        </select>
                                    </div>
                                    <div class="w-full md:w-1/4">
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Harga Satuan</label>
                                        <input type="text" x-model="item.priceFormatted" @input="item.priceFormatted = formatCurrency($event.target.value); item.price = item.priceFormatted.replace(/\./g, '')" placeholder="0" class="w-full bg-slate-800 border border-slate-600 rounded-lg px-3 py-2 text-white text-sm focus:border-blue-500" :required="formData.is_detailed == '1'">
                                        <input type="hidden" :name="`items[${index}][price]`" :value="item.price">
                                    </div>
                                    <div class="w-full md:w-1/6">
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Qty</label>
                                        <input type="number" :name="`items[${index}][quantity]`" x-model="item.quantity" min="1" class="w-full bg-slate-800 border border-slate-600 rounded-lg px-3 py-2 text-white text-sm focus:border-blue-500" :required="formData.is_detailed == '1'">
                                    </div>
                                    <div class="w-full md:w-auto shrink-0">
                                        <button type="button" @click="removeItem(index)" class="w-9 h-9 rounded-lg bg-red-500/10 text-red-400 hover:bg-red-500 hover:text-white flex items-center justify-center transition-all border border-red-500/20">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                </div>
                <div class="px-6 py-4 border-t border-slate-700/50 flex justify-end gap-3 bg-slate-800/50">
                    <button type="button" @click="showModal = false" class="px-4 py-2 rounded-xl text-sm font-bold text-slate-300 hover:bg-slate-700 transition-colors">Batal</button>
                    <button type="submit" :class="isEdit ? 'bg-blue-600 hover:bg-blue-500 shadow-blue-500/30' : 'bg-emerald-600 hover:bg-emerald-500 shadow-emerald-500/30'" class="text-white px-6 py-2 rounded-xl text-sm font-bold shadow-lg transition-all" x-text="isEdit ? 'Perbarui Modal' : 'Simpan Modal'"></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Import Excel -->
    <div x-show="showImportModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden p-4">
        <div x-show="showImportModal" x-transition.opacity class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm" @click="showImportModal = false"></div>
        <div x-show="showImportModal" x-transition.scale.origin.bottom class="relative bg-slate-800 rounded-3xl shadow-2xl border border-slate-700 w-full max-w-md z-10 overflow-hidden max-h-[90vh] overflow-y-auto scrollbar-hide ">
            <div class="px-6 py-4 border-b border-slate-700/50 flex justify-between items-center bg-slate-800/50">
                <h3 class="text-lg font-black text-white flex items-center gap-2">
                    <i class="fas fa-file-import text-blue-400"></i> Import Modal via Excel
                </h3>
                <button @click="showImportModal = false" class="text-slate-400 hover:text-white transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="{{ route('capitals.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="p-6 space-y-6">
                    <div class="bg-blue-500/10 border border-blue-500/20 rounded-xl p-4 text-sm text-blue-400">
                        <i class="fas fa-info-circle mr-2"></i> Pastikan format Excel sesuai dengan template yang disediakan.
                        <a href="{{ route('capitals.template') }}" class="block mt-2 font-bold underline hover:text-blue-300">
                            <i class="fas fa-download"></i> Download Template Excel
                        </a>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Tanggal Import</label>
                        <input type="date" name="date" value="{{ date('Y-m-d') }}" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-blue-500 transition-all" required>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Upload File Excel (.xlsx)</label>
                        <input type="file" name="file" accept=".xlsx,.xls,.csv" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-blue-500 transition-all file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-500/10 file:text-blue-400 hover:file:bg-blue-500/20" required>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-700/50 flex justify-end gap-3 bg-slate-800/50">
                    <button type="button" @click="showImportModal = false" class="px-4 py-2 rounded-xl text-sm font-bold text-slate-300 hover:bg-slate-700 transition-colors">Batal</button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-2 rounded-xl text-sm font-bold shadow-lg shadow-blue-500/30 transition-all">Upload & Proses</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('capitalApp', () => ({
            showModal: false,
            showImportModal: false,
            isEdit: false,
            formAction: '',
            formData: {
                date: '{{ date('Y-m-d') }}',
                is_detailed: '0',
                totalAmountFormatted: '',
                items: []
            },
            init() {
                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.get('action') === 'edit_latest') {
                    @if($capitals->isNotEmpty())
                        this.openEditModal({{ $capitals->first()->id }});
                    @else
                        this.openAddModal();
                    @endif
                }
            },
            formatCurrency(value) {
                if(!value) return '';
                let val = value.toString().replace(/[^0-9]/g, '');
                if(val) {
                    val = parseInt(val, 10).toString();
                    return val.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                }
                return '';
            },
            addItem() {
                this.formData.items.push({ name: '', type: 'asset', price: '', priceFormatted: '', quantity: 1 });
            },
            removeItem(index) {
                if(this.formData.items.length > 1) this.formData.items.splice(index, 1);
            },
            openAddModal() {
                this.isEdit = false;
                this.formAction = '{{ route('capitals.store') }}';
                this.formData = {
                    date: '{{ date('Y-m-d') }}',
                    is_detailed: '0',
                    totalAmountFormatted: '',
                    items: [
                        { name: '', type: 'asset', price: '', priceFormatted: '', quantity: 1 }
                    ]
                };
                this.showModal = true;
            },
            async openEditModal(id) {
                try {
                    const response = await fetch(`/capitals/${id}/edit`);
                    const data = await response.json();
                    
                    this.isEdit = true;
                    this.formAction = `/capitals/${id}`;
                    this.formData.date = data.date.split('T')[0];
                    this.formData.is_detailed = data.is_detailed ? '1' : '0';
                    this.formData.totalAmountFormatted = this.formatCurrency(Math.floor(data.total_amount));
                    
                    this.formData.items = data.items.map(item => ({
                        name: item.name,
                        type: item.type,
                        price: Math.floor(item.price).toString(),
                        priceFormatted: this.formatCurrency(Math.floor(item.price)),
                        quantity: item.quantity
                    }));

                    if(this.formData.items.length === 0) {
                        this.formData.items = [{ name: '', type: 'asset', price: '', priceFormatted: '', quantity: 1 }];
                    }

                    this.showModal = true;
                } catch (e) {
                    alert('Gagal mengambil data modal.');
                }
            }
        }));
    });
</script>
@endpush



