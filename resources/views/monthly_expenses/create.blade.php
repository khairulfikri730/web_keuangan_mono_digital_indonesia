@extends('layouts.app')
@section('title', 'Catat Biaya Baru')

@section('content')
<div x-data="expenseForm()" class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8 font-sans antialiased text-slate-300">

    <div class="mb-10">
        <a href="{{ route('monthly_expenses.index') }}" class="text-blue-500 hover:text-blue-400 font-bold text-sm mb-4 inline-flex items-center gap-2">
            <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
        </a>
        <h2 class="text-4xl font-black text-white tracking-tight">{{ isset($isEdit) ? 'Edit' : 'Catat' }} Pengeluaran</h2>
        <p class="text-slate-400 mt-2 text-sm">Masukkan data pengeluaran bisnis Anda dengan cepat.</p>
    </div>

    <form action="{{ isset($isEdit) ? route('monthly_expenses.update', $expense->id) : route('monthly_expenses.store') }}" method="POST" class="space-y-8 pb-20">
        @csrf
        @if(isset($isEdit)) @method('PUT') @endif

        <!-- 1. PILIH KATEGORI UTAMA -->
        <div class="bg-slate-800/40 rounded-3xl border border-white/5 p-8 shadow-xl backdrop-blur-xl">
            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-6 text-center">1. Pilih Kategori Pengeluaran</label>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach(['operasional' => 'fa-bolt', 'consumable' => 'fa-box-open', 'bahan_baku' => 'fa-cube', 'variabel' => 'fa-car'] as $cat => $icon)
                <button type="button" @click="setCategory('{{ $cat }}')" 
                        :class="category === '{{ $cat }}' ? 'bg-blue-600 border-blue-400 shadow-blue-900/40 scale-105' : 'bg-slate-900/60 border-white/5 hover:bg-slate-800'"
                        class="flex flex-col items-center justify-center p-6 rounded-2xl border-2 transition-all group relative overflow-hidden">
                    <i class="fas {{ $icon }} text-2xl mb-3" :class="category === '{{ $cat }}' ? 'text-white' : 'text-slate-500 group-hover:text-blue-400'"></i>
                    <span class="text-[10px] font-black uppercase tracking-widest" :class="category === '{{ $cat }}' ? 'text-white' : 'text-slate-400'">{{ str_replace('_', ' ', $cat) }}</span>
                </button>
                @endforeach
            </div>
            <input type="hidden" name="expense_type" x-model="category">
        </div>

        <!-- 2. DETAIL PENGELUARAN -->
        <div class="bg-slate-800/40 rounded-3xl border border-white/5 p-8 shadow-xl backdrop-blur-xl">
            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-8">2. Detail Pengeluaran</label>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Jenis Biaya -->
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase mb-3">Jenis Biaya <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <select name="sub_category" class="w-full bg-slate-900/50 border border-slate-700 rounded-2xl px-5 py-4 text-white font-bold focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-all appearance-none">
                            <template x-for="opt in filteredCategories" :key="opt.id">
                                <option :value="opt.name" x-text="opt.name" :selected="opt.name == '{{ $expense->sub_category ?? '' }}'"></option>
                            </template>
                        </select>
                        <div class="absolute right-5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-500">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>

                <!-- Nama Item -->
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase mb-3">Nama Barang / Jasa <span class="text-red-500">*</span></label>
                    <input type="text" name="expense_name" value="{{ $expense->expense_name ?? '' }}" required placeholder="Contoh: Tinta Epson L805" class="w-full bg-slate-900/50 border border-slate-700 rounded-2xl px-5 py-4 text-white font-bold focus:border-blue-500 outline-none transition-all">
                </div>

                <!-- Qty & Satuan -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase mb-3">Jumlah (Qty)</label>
                        <input type="number" name="quantity" value="{{ $expense->quantity ?? 1 }}" required min="1" class="w-full bg-slate-900/50 border border-slate-700 rounded-2xl px-5 py-4 text-white font-bold focus:border-blue-500 outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase mb-3">Satuan</label>
                        <input type="text" name="unit" value="{{ $expense->unit ?? 'Pcs' }}" placeholder="Roll / Pack" class="w-full bg-slate-900/50 border border-slate-700 rounded-2xl px-5 py-4 text-white font-bold focus:border-blue-500 outline-none transition-all">
                    </div>
                </div>

                <!-- Nominal -->
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase mb-3">Nominal (Rp) <span class="text-red-500">*</span></label>
                    <input type="text" x-model="amountFormatted" @input="formatInput" required placeholder="0" class="w-full bg-slate-900/50 border border-slate-700 rounded-2xl px-5 py-4 text-white text-xl font-black focus:border-blue-500 outline-none transition-all">
                    <input type="hidden" name="usage_amount" x-model="amountReal">
                </div>

                <!-- Tanggal -->
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase mb-3">Tanggal <span class="text-red-500">*</span></label>
                    <input type="date" name="expense_date" value="{{ isset($expense) ? $expense->expense_date->format('Y-m-d') : date('Y-m-d') }}" required class="w-full bg-slate-900/50 border border-slate-700 rounded-2xl px-5 py-4 text-white font-bold focus:border-blue-500 outline-none transition-all">
                </div>

                <!-- Sumber Dana -->
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase mb-3">Sumber Dana <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <select name="payment_method" class="w-full bg-slate-900/50 border border-slate-700 rounded-2xl px-5 py-4 text-white font-bold focus:border-blue-500 outline-none transition-all appearance-none">
                            <option value="Tunai" {{ (isset($expense) && $expense->payment_method == 'Tunai') ? 'selected' : '' }}>Saldo Kasir (Tunai)</option>
                            <option value="Bank / Transfer" {{ (isset($expense) && $expense->payment_method == 'Bank / Transfer') ? 'selected' : '' }}>Saldo Bank (Transfer)</option>
                        </select>
                        <div class="absolute right-5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-500">
                            <i class="fas fa-wallet text-xs"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Catatan -->
            <div class="mt-8">
                <label class="block text-[10px] font-black text-slate-500 uppercase mb-3">Catatan Tambahan</label>
                <textarea name="description" rows="3" placeholder="Opsional..." class="w-full bg-slate-900/50 border border-slate-700 rounded-2xl px-5 py-4 text-white font-bold focus:border-blue-500 outline-none transition-all">{{ $expense->description ?? '' }}</textarea>
            </div>

            <div class="mt-12 flex justify-end">
                <button type="submit" class="w-full md:w-auto px-12 py-5 bg-blue-600 hover:bg-blue-500 text-white rounded-2xl font-black text-sm uppercase tracking-widest shadow-2xl shadow-blue-900/40 flex items-center justify-center gap-4 transition-all">
                    <i class="fas fa-save"></i>
                    {{ isset($isEdit) ? 'Simpan Perubahan' : 'Simpan Pengeluaran' }}
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('expenseForm', () => ({
            category: '{{ $expense->expense_type ?? 'operasional' }}',
            amountFormatted: '{{ isset($expense) ? number_format($expense->usage_amount, 0, ',', '.') : '' }}',
            amountReal: '{{ $expense->usage_amount ?? 0 }}',
            allCategories: @json($categories),
            
            get filteredCategories() {
                return this.allCategories.filter(c => c.parent_category === this.category);
            },
            
            setCategory(cat) {
                this.category = cat;
            },
            
            formatInput(e) {
                let val = e.target.value.replace(/\D/g, '');
                this.amountReal = val;
                this.amountFormatted = val.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }
        }));
    });
</script>
@endsection
