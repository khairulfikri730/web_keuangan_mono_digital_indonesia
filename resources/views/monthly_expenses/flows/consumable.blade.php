<!-- FLOW: CONSUMABLE -->

<!-- STEP 2: Info Barang -->
<div x-show="step === 2" x-cloak x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Left: Form -->
        <div class="lg:col-span-8">
            <span class="text-yellow-500 font-bold text-sm mb-2 block">Langkah 2 dari <span x-text="getTotalSteps()"></span></span>
            <h2 class="text-2xl font-bold text-white mb-2">Info Barang</h2>
            <p class="text-slate-400 text-sm mb-8">Detail informasi bahan habis pakai (consumable) yang Anda beli.</p>

            <div class="space-y-6">
                <!-- Nama Barang -->
                <div>
                    <label class="block text-sm font-bold text-white mb-2">Nama Barang <span class="text-red-500">*</span></label>
                    <input type="text" x-model="formData.name" placeholder="Tinta Printer Epson" class="w-full bg-[#1e2336]/80 border border-slate-700/70 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-yellow-500 transition-colors shadow-inner">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Kategori -->
                    <div>
                        <label class="block text-sm font-bold text-white mb-2">Kategori</label>
                        <select x-model="formData.sub_category" class="w-full bg-[#1e2336]/80 border border-slate-700/70 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-yellow-500 transition-colors shadow-inner appearance-none">
                            <option value="">Pilih kategori</option>
                            <option value="Tinta">Tinta</option>
                            <option value="Kertas">Kertas</option>
                            <option value="Packaging">Packaging / Kemasan</option>
                            <option value="Alat Tulis">Alat Tulis Kantor</option>
                            <option value="Kebersihan">Alat Kebersihan</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>

                    <!-- Satuan -->
                    <div>
                        <label class="block text-sm font-bold text-white mb-2">Satuan Dasar <span class="text-red-500">*</span></label>
                        <select x-model="formData.satuan" class="w-full bg-[#1e2336]/80 border border-slate-700/70 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-yellow-500 transition-colors shadow-inner appearance-none">
                            <option value="Pcs">Pcs</option>
                            <option value="Botol">Botol</option>
                            <option value="Rim">Rim</option>
                            <option value="Lembar">Lembar</option>
                            <option value="Meter">Meter</option>
                            <option value="Pack">Pack</option>
                            <option value="Roll">Roll</option>
                            <option value="Gram">Gram</option>
                            <option value="Ml">Mililiter (ml)</option>
                        </select>
                    </div>
                </div>

                <!-- Tipe Penggunaan -->
                <div>
                    <label class="block text-sm font-bold text-white mb-2">Tipe Penggunaan</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label class="cursor-pointer">
                            <input type="radio" x-model="formData.tipe_penggunaan" value="dipakai_langsung" class="peer sr-only">
                            <div class="p-4 rounded-xl border-2 border-slate-700/60 bg-slate-800/30 peer-checked:border-yellow-500 peer-checked:bg-yellow-900/10 transition-all">
                                <div class="flex items-center gap-3">
                                    <div class="w-4 h-4 rounded-full border-2 border-slate-500 peer-checked:border-yellow-500 flex items-center justify-center">
                                        <div class="w-2 h-2 rounded-full bg-yellow-500" x-show="formData.tipe_penggunaan === 'dipakai_langsung'"></div>
                                    </div>
                                    <span class="text-sm font-bold text-white">Dipakai Langsung</span>
                                </div>
                                <p class="text-[12px] text-slate-400 mt-2 ml-7">Langsung habis dalam 1 kali pemakaian.</p>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" x-model="formData.tipe_penggunaan" value="bertahap" class="peer sr-only">
                            <div class="p-4 rounded-xl border-2 border-slate-700/60 bg-slate-800/30 peer-checked:border-yellow-500 peer-checked:bg-yellow-900/10 transition-all">
                                <div class="flex items-center gap-3">
                                    <div class="w-4 h-4 rounded-full border-2 border-slate-500 peer-checked:border-yellow-500 flex items-center justify-center">
                                        <div class="w-2 h-2 rounded-full bg-yellow-500" x-show="formData.tipe_penggunaan === 'bertahap'"></div>
                                    </div>
                                    <span class="text-sm font-bold text-white">Bertahap (Tracking Stok)</span>
                                </div>
                                <p class="text-[12px] text-slate-400 mt-2 ml-7">Barang memiliki sisa stok yang akan dilacak.</p>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex justify-between items-center pt-6 border-t border-slate-700/50">
                <button @click="step--" class="px-6 py-3 rounded-lg text-sm font-bold text-slate-300 hover:text-white hover:bg-slate-800 border border-slate-700 transition-all flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i> Kembali
                </button>
                <button @click="nextStep()" :disabled="!canContinue" 
                        :class="canContinue ? 'bg-yellow-600 hover:bg-yellow-500 shadow-lg shadow-yellow-500/20 text-white' : 'bg-slate-700 text-slate-500 cursor-not-allowed'"
                        class="px-8 py-3 rounded-lg text-sm font-bold transition-all flex items-center gap-2 group">
                    Selanjutnya <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                </button>
            </div>
        </div>
        <!-- Right Panel Summary omitted for brevity or implement standard view -->
        <div class="lg:col-span-4">
            <div class="bg-[#1e2336]/80 rounded-2xl border border-slate-700/50 p-6 shadow-xl sticky top-8">
                <h4 class="text-sm font-bold text-white mb-6">Ringkasan Pilihan</h4>
                <div class="flex gap-4">
                    <div class="w-12 h-12 rounded-full bg-[#064e3b] text-emerald-400 flex items-center justify-center shrink-0">
                        <i class="fas fa-box-open text-lg"></i>
                    </div>
                    <div>
                        <h5 class="text-sm font-bold text-white mb-1">Consumable</h5>
                        <p class="text-[13px] text-slate-400 leading-relaxed">Barang habis pakai operasional.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- STEP 3: Pembelian & Stok -->
<div x-show="step === 3" x-cloak x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <div class="lg:col-span-8">
            <div class="bg-[#1e2336]/80 rounded-[2rem] border border-slate-700/50 p-8 shadow-2xl backdrop-blur-sm">
                <span class="text-yellow-500 font-bold text-sm mb-2 block">Langkah 3 dari <span x-text="getTotalSteps()"></span></span>
                <h2 class="text-2xl font-bold text-white mb-2">Pembelian & Stok</h2>
                <p class="text-slate-400 text-sm mb-8">Masukkan total pembelian barang ini.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-[13px] font-bold text-white mb-2">Harga Beli Total <span class="text-red-500">*</span></label>
                        <div class="relative w-full">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <span class="text-slate-400 font-bold">Rp</span>
                            </div>
                            <input type="text" x-model="amountFormatted" @input="amountFormatted = formatCurrency($event.target.value); formData.amount = parseInt($event.target.value.replace(/[^0-9]/g, '')) || 0;" placeholder="0" class="w-full bg-[#0f172a]/50 border border-slate-700/70 rounded-xl pl-12 pr-4 py-3 text-white focus:outline-none focus:border-yellow-500 transition-colors shadow-inner font-bold">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[13px] font-bold text-white mb-2">Jumlah Beli <span class="text-red-500">*</span></label>
                        <div class="flex">
                            <input type="number" x-model.number="formData.jumlah_beli" min="1" class="w-full bg-[#0f172a]/50 border border-slate-700/70 rounded-l-xl px-4 py-3 text-white focus:outline-none focus:border-yellow-500 transition-colors shadow-inner font-bold text-center">
                            <div class="bg-slate-800 border-y border-r border-slate-700/70 rounded-r-xl px-4 flex items-center justify-center">
                                <span class="text-slate-400 text-sm font-bold" x-text="formData.satuan"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-8">
                    <label class="block text-[13px] font-bold text-slate-400 mb-2">Harga per unit (Otomatis)</label>
                    <div class="relative w-full md:w-1/2">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <span class="text-yellow-500 font-bold">Rp</span>
                        </div>
                        <input type="text" readonly :value="formatCurrency(Math.round(formData.amount / Math.max(1, formData.jumlah_beli)))" class="w-full bg-yellow-900/10 border border-yellow-700/50 rounded-xl pl-12 pr-4 py-3 text-yellow-400 shadow-inner font-bold cursor-not-allowed">
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                            <span class="text-yellow-500 text-sm">/ <span x-text="formData.satuan"></span></span>
                        </div>
                    </div>
                </div>
                
                <hr class="border-slate-700/50 mb-8" x-show="formData.tipe_penggunaan === 'bertahap'">
                
                <div x-show="formData.tipe_penggunaan === 'bertahap'" class="mb-8">
                    <h4 class="text-white font-bold text-sm mb-6">Informasi Stok</h4>
                    <div class="w-full md:w-1/2">
                        <label class="block text-[13px] font-bold text-white mb-2">Stok Masuk <span class="text-red-500">*</span></label>
                        <div class="flex">
                            <input type="number" x-model.number="formData.stok_awal" class="w-full bg-[#0f172a]/50 border border-slate-700/70 rounded-l-xl px-4 py-3 text-white focus:outline-none focus:border-yellow-500 transition-colors shadow-inner text-center font-bold">
                            <div class="bg-slate-800 border-y border-r border-slate-700/70 rounded-r-xl px-4 flex items-center justify-center">
                                <span class="text-slate-400 text-sm font-bold" x-text="formData.satuan"></span>
                            </div>
                        </div>
                        <p class="text-[12px] text-slate-500 mt-2">Atur berapa <span x-text="formData.satuan"></span> stok yang masuk.</p>
                    </div>
                </div>

                <div class="mt-8 flex justify-between items-center pt-6 border-t border-slate-700/50">
                    <button @click="step--" class="px-6 py-3 rounded-lg text-sm font-bold text-slate-300 hover:text-white hover:bg-slate-800 border border-slate-700 transition-all flex items-center gap-2">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </button>
                    <button @click="nextStep()" :disabled="!canContinue" 
                            :class="canContinue ? 'bg-yellow-600 hover:bg-yellow-500 shadow-lg shadow-yellow-500/20 text-white' : 'bg-slate-700 text-slate-500 cursor-not-allowed'"
                            class="px-8 py-3 rounded-lg text-sm font-bold transition-all flex items-center gap-2 group">
                        Selanjutnya <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <div class="lg:col-span-4">
            <div class="bg-[#1e2336]/80 rounded-2xl border border-slate-700/50 p-6 shadow-xl sticky top-8">
                <h4 class="text-sm font-bold text-white mb-6">Ringkasan Barang</h4>
                <div class="space-y-4">
                    <div>
                        <p class="text-[11px] text-slate-500 uppercase tracking-wider font-bold mb-1">Nama Barang</p>
                        <p class="text-sm text-white font-medium" x-text="formData.name || '-'"></p>
                    </div>
                    <div class="pt-4 border-t border-slate-700/50">
                        <p class="text-[11px] text-slate-500 uppercase tracking-wider font-bold mb-1">Harga Beli Total</p>
                        <p class="text-lg text-white font-bold">Rp <span x-text="formatCurrency(formData.amount)"></span></p>
                    </div>
                    <div class="grid grid-cols-2 gap-4 pt-2">
                        <div>
                            <p class="text-[11px] text-slate-500 uppercase tracking-wider font-bold mb-1">Jumlah</p>
                            <p class="text-sm text-white font-medium"><span x-text="formData.jumlah_beli"></span> <span x-text="formData.satuan"></span></p>
                        </div>
                        <div>
                            <p class="text-[11px] text-slate-500 uppercase tracking-wider font-bold mb-1">Harga/Unit</p>
                            <p class="text-sm text-yellow-400 font-bold">Rp <span x-text="formatCurrency(Math.round(formData.amount / Math.max(1, formData.jumlah_beli)))"></span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- STEP 4: Penggunaan -->
<div x-show="step === 4" x-cloak x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <div class="lg:col-span-8">
            <div class="bg-[#1e2336]/80 rounded-[2rem] border border-slate-700/50 p-8 shadow-2xl backdrop-blur-sm">
                <span class="text-yellow-500 font-bold text-sm mb-2 block">Langkah 4 dari <span x-text="getTotalSteps()"></span></span>
                <h2 class="text-2xl font-bold text-white mb-2">Penggunaan</h2>
                <p class="text-slate-400 text-sm mb-8">Catat penggunaan barang ini untuk hari ini.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-[13px] font-bold text-white mb-2">Tanggal Penggunaan</label>
                        <div class="relative w-full">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="far fa-calendar-alt text-slate-400"></i>
                            </div>
                            <input type="date" x-model="formData.date" class="w-full bg-[#0f172a]/50 border border-slate-700/70 rounded-xl pl-11 pr-4 py-3 text-white focus:outline-none focus:border-yellow-500 transition-colors shadow-inner">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[13px] font-bold text-white mb-2">Jumlah Dipakai</label>
                        <div class="flex">
                            <input type="number" x-model.number="formData.jumlah_dipakai" :max="formData.tipe_penggunaan === 'bertahap' ? formData.stok_awal : formData.jumlah_beli" class="w-full bg-[#0f172a]/50 border border-slate-700/70 rounded-l-xl px-4 py-3 text-white focus:outline-none focus:border-yellow-500 transition-colors shadow-inner font-bold text-center">
                            <div class="bg-slate-800 border-y border-r border-slate-700/70 rounded-r-xl px-4 flex items-center justify-center">
                                <span class="text-slate-400 text-sm font-bold" x-text="formData.satuan"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div x-show="formData.tipe_penggunaan === 'bertahap'">
                        <label class="block text-[13px] font-bold text-slate-400 mb-2">Sisa Stok (Otomatis)</label>
                        <div class="flex">
                            <input type="text" readonly :value="Math.max(0, formData.stok_awal - formData.jumlah_dipakai)" class="w-full bg-[#0f172a]/30 border border-slate-700/30 rounded-l-xl px-4 py-3 text-slate-400 shadow-inner font-bold text-center cursor-not-allowed">
                            <div class="bg-slate-800/50 border-y border-r border-slate-700/30 rounded-r-xl px-4 flex items-center justify-center">
                                <span class="text-slate-500 text-sm font-bold" x-text="formData.satuan"></span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[13px] font-bold text-slate-400 mb-2">Total Biaya Terpakai (Otomatis)</label>
                        <div class="relative w-full">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <span class="text-yellow-500 font-bold">Rp</span>
                            </div>
                            <input type="text" readonly :value="formatCurrency(Math.round((formData.amount / Math.max(1, formData.jumlah_beli)) * formData.jumlah_dipakai))" class="w-full bg-[#0f172a]/30 border border-slate-700/30 rounded-xl pl-12 pr-4 py-3 text-yellow-400 shadow-inner font-bold cursor-not-allowed">
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex justify-between items-center pt-6 border-t border-slate-700/50">
                    <button @click="step--" class="px-6 py-3 rounded-lg text-sm font-bold text-slate-300 hover:text-white hover:bg-slate-800 border border-slate-700 transition-all flex items-center gap-2">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </button>
                    <button @click="nextStep()" :disabled="!canContinue" 
                            :class="canContinue ? 'bg-yellow-600 hover:bg-yellow-500 shadow-lg shadow-yellow-500/20 text-white' : 'bg-slate-700 text-slate-500 cursor-not-allowed'"
                            class="px-8 py-3 rounded-lg text-sm font-bold transition-all flex items-center gap-2 group">
                        Selanjutnya <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <div class="lg:col-span-4">
            <div class="bg-[#1e2336]/80 rounded-2xl border border-slate-700/50 p-6 shadow-xl sticky top-8">
                <h4 class="text-sm font-bold text-white mb-6">Ringkasan Penggunaan</h4>
                <div class="space-y-4">
                    <div>
                        <p class="text-[11px] text-slate-500 uppercase tracking-wider font-bold mb-1">Tanggal</p>
                        <p class="text-sm text-white font-medium" x-text="formatDateIndo(formData.date)"></p>
                    </div>
                    <div class="grid grid-cols-2 gap-4 pt-4 border-t border-slate-700/50">
                        <div>
                            <p class="text-[11px] text-slate-500 uppercase tracking-wider font-bold mb-1">Dipakai</p>
                            <p class="text-sm text-yellow-400 font-bold"><span x-text="formData.jumlah_dipakai"></span> <span x-text="formData.satuan"></span></p>
                        </div>
                        <div x-show="formData.tipe_penggunaan === 'bertahap'">
                            <p class="text-[11px] text-slate-500 uppercase tracking-wider font-bold mb-1">Sisa Stok</p>
                            <p class="text-sm text-white font-medium"><span x-text="Math.max(0, formData.stok_awal - formData.jumlah_dipakai)"></span> <span x-text="formData.satuan"></span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- STEP 5: Integrasi Keuangan -->
<div x-show="step === 5" x-cloak x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <div class="lg:col-span-8">
            <div class="bg-[#1e2336]/80 rounded-[2rem] border border-slate-700/50 p-8 shadow-2xl backdrop-blur-sm">
                <span class="text-yellow-500 font-bold text-sm mb-2 block">Langkah 5 dari <span x-text="getTotalSteps()"></span></span>
                <h2 class="text-2xl font-bold text-white mb-2">Integrasi ke Keuangan</h2>
                <p class="text-slate-400 text-sm mb-8">Tentukan bagaimana biaya barang ini dicatat dalam laporan.</p>

                <div class="bg-slate-800/30 border border-slate-700/50 rounded-2xl p-6 mb-8">
                    <h4 class="text-sm font-bold text-white mb-4">Masukkan ke laporan sebagai:</h4>
                    
                    <div class="space-y-3">
                        <label class="cursor-pointer block">
                            <input type="radio" x-model="formData.integrasi_keuangan" value="operasional" class="peer sr-only">
                            <div class="p-4 rounded-xl border border-slate-700/60 bg-slate-800/50 peer-checked:border-yellow-500 peer-checked:bg-yellow-900/10 flex items-center gap-3 transition-all">
                                <div class="w-4 h-4 rounded-full border-2 border-slate-500 peer-checked:border-yellow-500 flex items-center justify-center">
                                    <div class="w-2 h-2 rounded-full bg-yellow-500" x-show="formData.integrasi_keuangan === 'operasional'"></div>
                                </div>
                                <span class="text-sm font-bold text-white">Biaya Operasional</span>
                            </div>
                        </label>
                        <label class="cursor-pointer block">
                            <input type="radio" x-model="formData.integrasi_keuangan" value="hpp" class="peer sr-only">
                            <div class="p-4 rounded-xl border border-slate-700/60 bg-slate-800/50 peer-checked:border-yellow-500 peer-checked:bg-yellow-900/10 transition-all">
                                <div class="flex items-center gap-3 mb-1">
                                    <div class="w-4 h-4 rounded-full border-2 border-slate-500 peer-checked:border-yellow-500 flex items-center justify-center">
                                        <div class="w-2 h-2 rounded-full bg-yellow-500" x-show="formData.integrasi_keuangan === 'hpp'"></div>
                                    </div>
                                    <span class="text-sm font-bold text-white">HPP (Harga Pokok Penjualan)</span>
                                </div>
                                <p class="text-[12px] text-slate-400 ml-7">Akan mempengaruhi perhitungan harga pokok produk.</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="bg-slate-800/40 border border-slate-700/60 rounded-2xl p-6 mb-8 flex items-center justify-between cursor-pointer transition-all hover:border-slate-600"
                     @click="formData.masuk_laporan_keuangan = !formData.masuk_laporan_keuangan">
                    <div class="flex items-center gap-5">
                        <div class="w-12 h-6 rounded-full relative transition-colors duration-300 ease-in-out"
                             :class="formData.masuk_laporan_keuangan ? 'bg-blue-600' : 'bg-slate-700'">
                            <div class="absolute top-1 w-4 h-4 rounded-full bg-white transition-transform duration-300 ease-in-out shadow-sm"
                                 :class="formData.masuk_laporan_keuangan ? 'left-7' : 'left-1'"></div>
                        </div>
                        <div>
                            <h4 class="text-base font-bold text-white mb-1">Masukkan ke laporan keuangan</h4>
                            <p class="text-[13px] text-slate-400">Total biaya terpakai akan dicatat di laporan laba rugi bulan ini.</p>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex justify-between items-center pt-6 border-t border-slate-700/50">
                    <button @click="step--" class="px-6 py-3 rounded-lg text-sm font-bold text-slate-300 hover:text-white hover:bg-slate-800 border border-slate-700 transition-all flex items-center gap-2">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </button>
                    <button @click="nextStep()" :disabled="!canContinue" 
                            :class="canContinue ? 'bg-yellow-600 hover:bg-yellow-500 shadow-lg shadow-yellow-500/20 text-white' : 'bg-slate-700 text-slate-500 cursor-not-allowed'"
                            class="px-8 py-3 rounded-lg text-sm font-bold transition-all flex items-center gap-2 group">
                        Selanjutnya <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- STEP 6: Review & Simpan -->
<div x-show="step === 6" x-cloak x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <div class="lg:col-span-8">
            <div class="bg-[#1e2336]/80 rounded-[2rem] border border-slate-700/50 p-8 shadow-2xl backdrop-blur-sm">
                <span class="text-yellow-500 font-bold text-sm mb-2 block">Langkah 6 dari 6</span>
                <h2 class="text-2xl font-bold text-white mb-2">Review & Simpan</h2>
                <p class="text-slate-400 text-sm mb-8">Periksa kembali detail bahan habis pakai ini sebelum disimpan.</p>

                <div class="bg-slate-800/30 border border-slate-700/50 rounded-2xl p-6 mb-8">
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-[13px] text-slate-400 font-bold">Nama Barang</span>
                            <span class="text-sm text-white font-medium" x-text="formData.name"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-[13px] text-slate-400 font-bold">Kategori & Satuan</span>
                            <span class="text-sm text-white font-medium" x-text="(formData.sub_category || 'Lainnya') + ' - ' + formData.satuan"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-[13px] text-slate-400 font-bold">Harga Beli</span>
                            <span class="text-sm text-white font-medium">Rp <span x-text="formatCurrency(formData.amount)"></span></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-[13px] text-slate-400 font-bold">Harga per Unit</span>
                            <span class="text-sm text-yellow-400 font-bold">Rp <span x-text="formatCurrency(Math.round(formData.amount / Math.max(1, formData.jumlah_beli)))"></span> / <span x-text="formData.satuan"></span></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-[13px] text-slate-400 font-bold">Penggunaan Hari Ini</span>
                            <span class="text-sm text-white font-medium"><span x-text="formData.jumlah_dipakai"></span> <span x-text="formData.satuan"></span> (Rp <span x-text="formatCurrency(Math.round((formData.amount / Math.max(1, formData.jumlah_beli)) * formData.jumlah_dipakai))"></span>)</span>
                        </div>
                        <div class="flex justify-between items-center" x-show="formData.tipe_penggunaan === 'bertahap'">
                            <span class="text-[13px] text-slate-400 font-bold">Sisa Stok</span>
                            <span class="text-sm text-emerald-400 font-bold"><span x-text="Math.max(0, formData.stok_awal - formData.jumlah_dipakai)"></span> <span x-text="formData.satuan"></span></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-[13px] text-slate-400 font-bold">Integrasi Keuangan</span>
                            <span class="text-sm text-white font-medium" x-text="formData.integrasi_keuangan === 'operasional' ? 'Biaya Operasional' : 'HPP'"></span>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex justify-between items-center pt-6 border-t border-slate-700/50">
                    <button @click="step--" class="px-6 py-3 rounded-lg text-sm font-bold text-slate-300 hover:text-white hover:bg-slate-800 border border-slate-700 transition-all flex items-center gap-2">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </button>
                    <button @click="submitForm()" 
                            class="px-8 py-3 rounded-lg text-sm font-bold bg-gradient-to-r from-yellow-600 to-orange-500 hover:from-yellow-500 hover:to-orange-400 shadow-lg shadow-yellow-500/30 text-white transition-all flex items-center gap-2">
                        Simpan Barang <i class="fas fa-check-circle"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
