<!-- FLOW: BAHAN BAKU -->

<!-- STEP 2: Info Bahan -->
<div x-show="step === 2" x-cloak x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <div class="lg:col-span-8">
            <span class="text-purple-500 font-bold text-sm mb-2 block">Langkah 2 dari <span x-text="getTotalSteps()"></span></span>
            <h2 class="text-2xl font-bold text-white mb-2">Info Bahan Baku</h2>
            <p class="text-slate-400 text-sm mb-8">Detail informasi bahan baku yang akan digunakan untuk produksi.</p>

            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-bold text-white mb-2">Nama Bahan <span class="text-red-500">*</span></label>
                    <input type="text" x-model="formData.name" placeholder="Kertas Foto Glossy 4R" class="w-full bg-[#1e2336]/80 border border-slate-700/70 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-purple-500 transition-colors shadow-inner">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-white mb-2">Kategori</label>
                        <select x-model="formData.sub_category" class="w-full bg-[#1e2336]/80 border border-slate-700/70 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-purple-500 transition-colors shadow-inner appearance-none">
                            <option value="">Pilih kategori</option>
                            <option value="Kertas">Kertas</option>
                            <option value="Tinta">Tinta</option>
                            <option value="Bahan Kimia">Bahan Kimia</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-white mb-2">Satuan Dasar (HPP) <span class="text-red-500">*</span></label>
                        <select x-model="formData.satuan_dasar" class="w-full bg-[#1e2336]/80 border border-slate-700/70 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-purple-500 transition-colors shadow-inner appearance-none">
                            <option value="Lembar">Lembar</option>
                            <option value="Ml">Mililiter (ml)</option>
                            <option value="Gram">Gram</option>
                            <option value="Pcs">Pcs</option>
                            <option value="Cm">Centimeter (cm)</option>
                        </select>
                        <p class="text-[12px] text-slate-500 mt-2">Satuan terkecil yang dipakai di produk.</p>
                    </div>
                </div>

                <div class="bg-slate-800/40 border border-slate-700/60 rounded-2xl p-6 mb-8 flex items-center justify-between cursor-pointer transition-all hover:border-slate-600"
                     @click="formData.status_aktif = !formData.status_aktif">
                    <div class="flex items-center gap-5">
                        <div class="w-12 h-6 rounded-full relative transition-colors duration-300 ease-in-out"
                             :class="formData.status_aktif ? 'bg-purple-600' : 'bg-slate-700'">
                            <div class="absolute top-1 w-4 h-4 rounded-full bg-white transition-transform duration-300 ease-in-out shadow-sm"
                                 :class="formData.status_aktif ? 'left-7' : 'left-1'"></div>
                        </div>
                        <div>
                            <h4 class="text-base font-bold text-white mb-1">Status Aktif Produksi</h4>
                            <p class="text-[13px] text-slate-400">Bahan baku ini aktif digunakan dalam resep produk.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex justify-between items-center pt-6 border-t border-slate-700/50">
                <button @click="step--" class="px-6 py-3 rounded-lg text-sm font-bold text-slate-300 hover:text-white hover:bg-slate-800 border border-slate-700 transition-all flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i> Kembali
                </button>
                <button @click="nextStep()" :disabled="!canContinue" 
                        :class="canContinue ? 'bg-purple-600 hover:bg-purple-500 shadow-lg shadow-purple-500/20 text-white' : 'bg-slate-700 text-slate-500 cursor-not-allowed'"
                        class="px-8 py-3 rounded-lg text-sm font-bold transition-all flex items-center gap-2 group">
                    Selanjutnya <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                </button>
            </div>
        </div>
        <div class="lg:col-span-4">
            <div class="bg-[#1e2336]/80 rounded-2xl border border-slate-700/50 p-6 shadow-xl sticky top-8">
                <h4 class="text-sm font-bold text-white mb-6">Ringkasan Pilihan</h4>
                <div class="flex gap-4">
                    <div class="w-12 h-12 rounded-full bg-[#4c1d95] text-purple-400 flex items-center justify-center shrink-0">
                        <i class="fas fa-cube text-lg"></i>
                    </div>
                    <div>
                        <h5 class="text-sm font-bold text-white mb-1">Bahan Baku</h5>
                        <p class="text-[13px] text-slate-400 leading-relaxed">Barang untuk produksi yang menjadi bagian dari HPP.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- STEP 3: Pembelian & Konversi -->
<div x-show="step === 3" x-cloak x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <div class="lg:col-span-8">
            <div class="bg-[#1e2336]/80 rounded-[2rem] border border-slate-700/50 p-8 shadow-2xl backdrop-blur-sm">
                <span class="text-purple-500 font-bold text-sm mb-2 block">Langkah 3 dari <span x-text="getTotalSteps()"></span></span>
                <h2 class="text-2xl font-bold text-white mb-2">Pembelian & Konversi</h2>
                <p class="text-slate-400 text-sm mb-8">Atur bagaimana bahan baku dibeli dan dikonversi ke satuan dasar.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-[13px] font-bold text-white mb-2">Harga Beli Total <span class="text-red-500">*</span></label>
                        <div class="relative w-full">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <span class="text-slate-400 font-bold">Rp</span>
                            </div>
                            <input type="text" x-model="amountFormatted" @input="amountFormatted = formatCurrency($event.target.value); formData.amount = parseInt($event.target.value.replace(/[^0-9]/g, '')) || 0;" placeholder="0" class="w-full bg-[#0f172a]/50 border border-slate-700/70 rounded-xl pl-12 pr-4 py-3 text-white focus:outline-none focus:border-purple-500 transition-colors shadow-inner font-bold">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[13px] font-bold text-white mb-2">Jumlah Pembelian <span class="text-red-500">*</span></label>
                        <div class="flex">
                            <input type="number" x-model.number="formData.jumlah_beli" min="1" class="w-full bg-[#0f172a]/50 border border-slate-700/70 rounded-l-xl px-4 py-3 text-white focus:outline-none focus:border-purple-500 transition-colors shadow-inner font-bold text-center">
                            <div class="bg-slate-800 border-y border-r border-slate-700/70 rounded-r-xl w-32 flex items-center justify-center">
                                <select x-model="formData.satuan_pembelian" class="w-full bg-transparent border-none text-slate-300 text-sm focus:outline-none focus:ring-0">
                                    <option value="Rim">Rim</option>
                                    <option value="Karton">Karton</option>
                                    <option value="Roll">Roll</option>
                                    <option value="Botol">Botol</option>
                                    <option value="Kg">Kg</option>
                                    <option value="Liter">Liter</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-purple-900/10 border border-purple-900/30 rounded-xl p-6 mb-8">
                    <h4 class="text-[13px] font-bold text-white mb-4">Konversi Satuan</h4>
                    <div class="flex items-center gap-4">
                        <div class="flex-1">
                            <div class="bg-[#0f172a]/80 rounded-lg p-3 text-center border border-slate-700/50">
                                <span class="text-white font-bold block">1</span>
                                <span class="text-[11px] text-slate-400" x-text="formData.satuan_pembelian"></span>
                            </div>
                        </div>
                        <i class="fas fa-equals text-slate-500"></i>
                        <div class="flex-[2] flex items-center">
                            <input type="number" x-model.number="formData.konversi_satuan_dasar" min="1" class="w-full bg-[#0f172a]/50 border border-slate-700/70 rounded-l-xl px-4 py-3 text-white focus:outline-none focus:border-purple-500 transition-colors shadow-inner font-bold text-center">
                            <div class="bg-slate-800 border-y border-r border-slate-700/70 rounded-r-xl px-4 py-3 flex items-center justify-center">
                                <span class="text-slate-400 text-sm font-bold" x-text="formData.satuan_dasar"></span>
                            </div>
                        </div>
                    </div>
                    <p class="text-[12px] text-purple-300 mt-4 text-center">1 <span x-text="formData.satuan_pembelian"></span> sama dengan <span x-text="formData.konversi_satuan_dasar"></span> <span x-text="formData.satuan_dasar"></span></p>
                </div>

                <div class="mb-8">
                    <label class="block text-[13px] font-bold text-slate-400 mb-2">Harga per <span x-text="formData.satuan_dasar"></span> (HPP)</label>
                    <div class="relative w-full md:w-1/2">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <span class="text-purple-500 font-bold">Rp</span>
                        </div>
                        <input type="text" readonly :value="formatCurrency(Math.round(formData.amount / Math.max(1, (formData.jumlah_beli * formData.konversi_satuan_dasar))))" class="w-full bg-[#0f172a]/30 border border-slate-700/30 rounded-xl pl-12 pr-4 py-3 text-purple-400 shadow-inner font-bold cursor-not-allowed">
                    </div>
                </div>

                <div class="mt-8 flex justify-between items-center pt-6 border-t border-slate-700/50">
                    <button @click="step--" class="px-6 py-3 rounded-lg text-sm font-bold text-slate-300 hover:text-white hover:bg-slate-800 border border-slate-700 transition-all flex items-center gap-2">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </button>
                    <button @click="nextStep()" :disabled="!canContinue" 
                            :class="canContinue ? 'bg-purple-600 hover:bg-purple-500 shadow-lg shadow-purple-500/20 text-white' : 'bg-slate-700 text-slate-500 cursor-not-allowed'"
                            class="px-8 py-3 rounded-lg text-sm font-bold transition-all flex items-center gap-2 group">
                        Selanjutnya <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Right Summary Step 3 -->
        <div class="lg:col-span-4">
            <div class="bg-[#1e2336]/80 rounded-2xl border border-slate-700/50 p-6 shadow-xl sticky top-8">
                <h4 class="text-sm font-bold text-white mb-6">Ringkasan Pembelian</h4>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-[12px] text-slate-400 font-bold">Total Pembelian</span>
                        <span class="text-sm text-white font-medium"><span x-text="formData.jumlah_beli"></span> <span x-text="formData.satuan_pembelian"></span></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-[12px] text-slate-400 font-bold">Total Nilai</span>
                        <span class="text-sm text-white font-bold">Rp <span x-text="formatCurrency(formData.amount)"></span></span>
                    </div>
                    <div class="pt-4 border-t border-slate-700/50">
                        <p class="text-[11px] text-slate-500 uppercase tracking-wider font-bold mb-2">Total Stok yang Didapat</p>
                        <p class="text-lg text-purple-400 font-bold"><span x-text="formData.jumlah_beli * formData.konversi_satuan_dasar"></span> <span x-text="formData.satuan_dasar"></span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- STEP 4: Stok -->
<div x-show="step === 4" x-cloak x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <div class="lg:col-span-8">
            <div class="bg-[#1e2336]/80 rounded-[2rem] border border-slate-700/50 p-8 shadow-2xl backdrop-blur-sm">
                <span class="text-purple-500 font-bold text-sm mb-2 block">Langkah 4 dari <span x-text="getTotalSteps()"></span></span>
                <h2 class="text-2xl font-bold text-white mb-2">Stok Bahan Baku</h2>
                <p class="text-slate-400 text-sm mb-8">Atur saldo stok awal dan peringatan stok minimum.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div>
                        <label class="block text-[13px] font-bold text-white mb-2">Stok Awal <span class="text-red-500">*</span></label>
                        <div class="flex">
                            <input type="number" x-model.number="formData.stok_awal" class="w-full bg-[#0f172a]/50 border border-slate-700/70 rounded-l-xl px-4 py-3 text-white focus:outline-none focus:border-purple-500 transition-colors shadow-inner font-bold text-center">
                            <div class="bg-slate-800 border-y border-r border-slate-700/70 rounded-r-xl px-4 flex items-center justify-center">
                                <span class="text-slate-400 text-sm font-bold" x-text="formData.satuan_dasar"></span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[13px] font-bold text-white mb-2">Minimum Stok (Alert)</label>
                        <div class="flex">
                            <input type="number" x-model.number="formData.minimum_stok" class="w-full bg-[#0f172a]/50 border border-slate-700/70 rounded-l-xl px-4 py-3 text-white focus:outline-none focus:border-purple-500 transition-colors shadow-inner font-bold text-center">
                            <div class="bg-slate-800 border-y border-r border-slate-700/70 rounded-r-xl px-4 flex items-center justify-center">
                                <span class="text-slate-400 text-sm font-bold" x-text="formData.satuan_dasar"></span>
                            </div>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-2">Sistem akan memberi tahu jika stok kurang dari ini.</p>
                    </div>
                </div>

                <div class="mt-8 flex justify-between items-center pt-6 border-t border-slate-700/50">
                    <button @click="step--" class="px-6 py-3 rounded-lg text-sm font-bold text-slate-300 hover:text-white hover:bg-slate-800 border border-slate-700 transition-all flex items-center gap-2">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </button>
                    <button @click="nextStep()" :disabled="!canContinue" 
                            :class="canContinue ? 'bg-purple-600 hover:bg-purple-500 shadow-lg shadow-purple-500/20 text-white' : 'bg-slate-700 text-slate-500 cursor-not-allowed'"
                            class="px-8 py-3 rounded-lg text-sm font-bold transition-all flex items-center gap-2 group">
                        Selanjutnya <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <div class="lg:col-span-4">
            <div class="bg-[#1e2336]/80 rounded-2xl border border-slate-700/50 p-6 shadow-xl sticky top-8">
                <h4 class="text-sm font-bold text-white mb-6">Ringkasan Stok</h4>
                <div class="p-4 rounded-xl border border-slate-700/60 bg-slate-800/30">
                    <p class="text-[11px] text-slate-500 uppercase tracking-wider font-bold mb-2">Status Bahan</p>
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-2 h-2 rounded-full" :class="formData.stok_awal > formData.minimum_stok ? 'bg-emerald-500' : 'bg-red-500'"></div>
                        <span class="text-sm font-bold text-white" x-text="formData.stok_awal > formData.minimum_stok ? 'Aman' : 'Perlu Restock'"></span>
                    </div>
                    <p class="text-[11px] text-slate-500 uppercase tracking-wider font-bold mb-1">Berulang saat produk terjual</p>
                    <p class="text-xs text-slate-400">Stok akan berkurang otomatis sesuai dengan resep yang dihubungkan di langkah berikutnya.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- STEP 5: Hubungkan ke Produk -->
<div x-show="step === 5" x-cloak x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <div class="lg:col-span-8">
            <div class="bg-[#1e2336]/80 rounded-[2rem] border border-slate-700/50 p-8 shadow-2xl backdrop-blur-sm">
                <span class="text-purple-500 font-bold text-sm mb-2 block">Langkah 5 dari <span x-text="getTotalSteps()"></span></span>
                <h2 class="text-2xl font-bold text-white mb-2">Hubungkan ke Produk</h2>
                <p class="text-slate-400 text-sm mb-8">Pilih produk jualan yang menggunakan bahan baku ini (Opsional).</p>

                <div class="bg-[#0f172a]/50 rounded-2xl border border-slate-700/50 p-6 mb-6">
                    <h4 class="text-[13px] font-bold text-white mb-4">Produk yang menggunakan bahan ini</h4>
                    
                    <!-- Table Header -->
                    <div class="grid grid-cols-12 gap-4 pb-3 border-b border-slate-700/50 mb-4">
                        <div class="col-span-7 text-[12px] font-bold text-slate-400">Produk</div>
                        <div class="col-span-5 text-[12px] font-bold text-slate-400 text-center">Pemakaian per 1 Produk</div>
                    </div>

                    <!-- Row 1 -->
                    <div class="grid grid-cols-12 gap-4 items-center mb-4">
                        <div class="col-span-7">
                            <select class="w-full bg-slate-800 border border-slate-700/70 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-purple-500">
                                <option value="1">Cetak Foto 4R</option>
                                <option value="2">Cetak Foto 3R</option>
                                <option value="3">Pas Foto</option>
                            </select>
                        </div>
                        <div class="col-span-5 flex items-center gap-2">
                            <input type="number" value="1" class="w-full bg-slate-800 border border-slate-700/70 rounded-lg px-3 py-2 text-sm text-center text-white focus:outline-none focus:border-purple-500 font-bold">
                            <span class="text-xs text-slate-400" x-text="formData.satuan_dasar"></span>
                        </div>
                    </div>

                    <!-- Row 2 -->
                    <div class="grid grid-cols-12 gap-4 items-center">
                        <div class="col-span-7">
                            <select class="w-full bg-slate-800 border border-slate-700/70 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-purple-500">
                                <option value="">Pilih Produk...</option>
                                <option value="2">Cetak Foto 3R</option>
                            </select>
                        </div>
                        <div class="col-span-5 flex items-center gap-2">
                            <input type="number" placeholder="0" class="w-full bg-slate-800 border border-slate-700/70 rounded-lg px-3 py-2 text-sm text-center text-white focus:outline-none focus:border-purple-500 font-bold">
                            <span class="text-xs text-slate-400" x-text="formData.satuan_dasar"></span>
                        </div>
                    </div>

                    <button class="mt-6 px-4 py-2 bg-blue-600/20 text-blue-400 rounded-lg text-xs font-bold hover:bg-blue-600/30 transition-colors flex items-center gap-2">
                        <i class="fas fa-plus"></i> Tambah Produk
                    </button>
                </div>
                
                <div class="bg-[#1e3a8a]/20 border border-blue-900/40 rounded-xl p-4 flex items-center gap-3">
                    <i class="fas fa-info-circle text-blue-400"></i>
                    <p class="text-[12px] text-blue-200">Bahan akan otomatis berkurang saat produk terjual.</p>
                </div>

                <div class="mt-8 flex justify-between items-center pt-6 border-t border-slate-700/50">
                    <button @click="step--" class="px-6 py-3 rounded-lg text-sm font-bold text-slate-300 hover:text-white hover:bg-slate-800 border border-slate-700 transition-all flex items-center gap-2">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </button>
                    <button @click="nextStep()" :disabled="!canContinue" 
                            :class="canContinue ? 'bg-purple-600 hover:bg-purple-500 shadow-lg shadow-purple-500/20 text-white' : 'bg-slate-700 text-slate-500 cursor-not-allowed'"
                            class="px-8 py-3 rounded-lg text-sm font-bold transition-all flex items-center gap-2 group">
                        Selanjutnya <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- STEP 6: Simulasi HPP -->
<div x-show="step === 6" x-cloak x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <div class="lg:col-span-8">
            <div class="bg-[#1e2336]/80 rounded-[2rem] border border-slate-700/50 p-8 shadow-2xl backdrop-blur-sm">
                <span class="text-purple-500 font-bold text-sm mb-2 block">Langkah 6 dari <span x-text="getTotalSteps()"></span></span>
                <h2 class="text-2xl font-bold text-white mb-2">Simulasi HPP per Produk</h2>
                <p class="text-slate-400 text-sm mb-8">Lihat perkiraan margin keuntungan berdasarkan harga pokok penjualan (HPP).</p>

                <div class="bg-[#0f172a]/50 rounded-2xl border border-slate-700/50 p-6 mb-6">
                    <div class="flex justify-between items-center mb-6">
                        <h4 class="text-sm font-bold text-white">Produk: Cetak Foto 4R</h4>
                        <span class="text-xs text-slate-400">Harga Jual: Rp 2.000</span>
                    </div>

                    <p class="text-[11px] text-slate-500 uppercase tracking-wider font-bold mb-3 border-b border-slate-700 pb-2">Rincian Biaya Bahan</p>
                    
                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-white">Kertas Foto Glossy 4R <span class="text-slate-500 text-xs">(1 lembar)</span></span>
                            <span class="font-bold text-white">Rp 100</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-white">Tinta Printer <span class="text-slate-500 text-xs">(0.5 ml)</span></span>
                            <span class="font-bold text-white">Rp 200</span>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-700 flex justify-between items-center mb-6">
                        <span class="font-bold text-white">Total HPP</span>
                        <span class="text-xl font-bold text-rose-500">Rp 300</span>
                    </div>

                    <div class="bg-emerald-900/10 border border-emerald-900/30 rounded-xl p-4 flex justify-between items-center">
                        <span class="text-sm font-bold text-slate-300">Estimasi Margin</span>
                        <div class="text-right">
                            <span class="text-lg font-bold text-emerald-400">Rp 1.700</span>
                            <span class="text-xs text-emerald-500 block">(85%)</span>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex justify-between items-center pt-6 border-t border-slate-700/50">
                    <button @click="step--" class="px-6 py-3 rounded-lg text-sm font-bold text-slate-300 hover:text-white hover:bg-slate-800 border border-slate-700 transition-all flex items-center gap-2">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </button>
                    <button @click="nextStep()" :disabled="!canContinue" 
                            :class="canContinue ? 'bg-purple-600 hover:bg-purple-500 shadow-lg shadow-purple-500/20 text-white' : 'bg-slate-700 text-slate-500 cursor-not-allowed'"
                            class="px-8 py-3 rounded-lg text-sm font-bold transition-all flex items-center gap-2 group">
                        Selanjutnya <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- STEP 7: Review & Simpan -->
<div x-show="step === 7" x-cloak x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <div class="lg:col-span-8">
            <div class="bg-[#1e2336]/80 rounded-[2rem] border border-slate-700/50 p-8 shadow-2xl backdrop-blur-sm">
                <span class="text-purple-500 font-bold text-sm mb-2 block">Langkah 7 dari 7</span>
                <h2 class="text-2xl font-bold text-white mb-2">Review & Simpan</h2>
                <p class="text-slate-400 text-sm mb-8">Periksa kembali detail bahan baku ini sebelum disimpan.</p>

                <div class="bg-slate-800/30 border border-slate-700/50 rounded-2xl p-6 mb-8">
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-[13px] text-slate-400 font-bold">Nama Bahan</span>
                            <span class="text-sm text-white font-medium" x-text="formData.name"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-[13px] text-slate-400 font-bold">Satuan Dasar</span>
                            <span class="text-sm text-white font-medium" x-text="formData.satuan_dasar"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-[13px] text-slate-400 font-bold">Harga per Unit</span>
                            <span class="text-sm text-purple-400 font-bold">Rp <span x-text="formatCurrency(Math.round(formData.amount / Math.max(1, (formData.jumlah_beli * formData.konversi_satuan_dasar))))"></span> / <span x-text="formData.satuan_dasar"></span></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-[13px] text-slate-400 font-bold">Stok Awal</span>
                            <span class="text-sm text-white font-medium"><span x-text="formData.stok_awal"></span> <span x-text="formData.satuan_dasar"></span></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-[13px] text-slate-400 font-bold">Minimum Stok</span>
                            <span class="text-sm text-rose-400 font-medium"><span x-text="formData.minimum_stok"></span> <span x-text="formData.satuan_dasar"></span></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-[13px] text-slate-400 font-bold">Produk Terkait</span>
                            <span class="text-sm text-white font-medium">Cetak Foto 4R (1 <span x-text="formData.satuan_dasar"></span>)</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-[13px] text-slate-400 font-bold">Dampak HPP</span>
                            <span class="text-sm text-emerald-400 font-bold">Ya</span>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex justify-between items-center pt-6 border-t border-slate-700/50">
                    <button @click="step--" class="px-6 py-3 rounded-lg text-sm font-bold text-slate-300 hover:text-white hover:bg-slate-800 border border-slate-700 transition-all flex items-center gap-2">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </button>
                    <button @click="submitForm()" 
                            class="px-8 py-3 rounded-lg text-sm font-bold bg-gradient-to-r from-purple-600 to-indigo-500 hover:from-purple-500 hover:to-indigo-400 shadow-lg shadow-purple-500/30 text-white transition-all flex items-center gap-2">
                        Simpan Bahan Baku <i class="fas fa-check-circle"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
