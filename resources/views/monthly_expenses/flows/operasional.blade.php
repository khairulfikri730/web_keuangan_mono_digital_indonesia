        <!-- STEP 2 CONTENT -->
        <div x-show="step === 2" x-cloak x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- Left: Form -->
                <div class="lg:col-span-8">
                    <span class="text-blue-500 font-bold text-sm mb-2 block">Langkah 2 dari 7</span>
                    <h2 class="text-2xl font-bold text-white mb-2">Info Dasar</h2>
                    <p class="text-slate-400 text-sm mb-8">Isi informasi dasar biaya yang akan Anda catat.</p>

                    <div class="space-y-6">
                        <!-- Nama Biaya -->
                        <div>
                            <label class="block text-sm font-bold text-white mb-2">Nama Biaya <span class="text-red-500">*</span></label>
                            <input type="text" x-model="formData.name" placeholder="Listrik Toko" class="w-full bg-[#1e2336]/80 border border-slate-700/70 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-blue-500 transition-colors shadow-inner">
                            <p class="text-[13px] text-slate-500 mt-2">Contoh: Listrik Toko, Gaji Karyawan, Wi-Fi, dll</p>
                        </div>

                        <!-- Deskripsi -->
                        <div>
                            <label class="block text-sm font-bold text-white mb-2">Deskripsi <span class="text-slate-500 font-normal">(Opsional)</span></label>
                            <textarea x-model="formData.description" placeholder="Pembayaran listrik toko periode 1-2 Mei 2026" rows="3" class="w-full bg-[#1e2336]/80 border border-slate-700/70 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-blue-500 transition-colors shadow-inner"></textarea>
                            <p class="text-[13px] text-slate-500 mt-2">Berikan keterangan tambahan jika diperlukan.</p>
                        </div>

                        <!-- Tanggal -->
                        <div>
                            <label class="block text-sm font-bold text-white mb-2">Tanggal <span class="text-red-500">*</span></label>
                            <div class="relative w-full md:w-64">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <i class="far fa-calendar-alt text-slate-400"></i>
                                </div>
                                <input type="date" x-model="formData.date" class="w-full bg-[#1e2336]/80 border border-slate-700/70 rounded-xl pl-11 pr-4 py-3 text-white focus:outline-none focus:border-blue-500 transition-colors shadow-inner">
                            </div>
                            <p class="text-[13px] text-slate-500 mt-2">Tanggal terjadinya biaya</p>
                        </div>

                        <!-- Catatan -->
                        <div>
                            <label class="block text-sm font-bold text-white mb-2">Catatan <span class="text-slate-500 font-normal">(Opsional)</span></label>
                            <textarea x-model="formData.notes" placeholder="Tambah catatan tambahan jika ada" rows="3" class="w-full bg-[#1e2336]/80 border border-slate-700/70 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-blue-500 transition-colors shadow-inner"></textarea>
                            <p class="text-[13px] text-slate-500 mt-2">Catatan tidak wajib diisi</p>
                        </div>
                    </div>
                    
                    <!-- NAVIGATION BUTTONS STEP 2 -->
                    <div class="mt-8 flex justify-between items-center pt-6 border-t border-slate-700/50">
                        <button @click="step--" class="px-6 py-3 rounded-lg text-sm font-bold text-slate-300 hover:text-white hover:bg-slate-800 border border-slate-700 transition-all flex items-center gap-2">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </button>
                        <button @click="nextStep()" :disabled="!canContinue" 
                                :class="canContinue ? 'bg-blue-600 hover:bg-blue-500 shadow-lg shadow-blue-500/20' : 'bg-slate-700 text-slate-500 cursor-not-allowed'"
                                class="px-8 py-3 rounded-lg text-sm font-bold text-white transition-all flex items-center gap-2 group">
                            Selanjutnya <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                        </button>
                    </div>
                </div>

                <!-- Right: Summary Panel -->
                <div class="lg:col-span-4">
                    <div class="bg-[#1e2336]/80 rounded-2xl border border-slate-700/50 p-6 shadow-xl sticky top-8">
                        <h4 class="text-sm font-bold text-white mb-6">Ringkasan Pilihan</h4>
                        
                        <!-- Dynamic Category Card -->
                        <div x-show="formData.category === 'operasional'" class="flex gap-4">
                            <div class="w-12 h-12 rounded-full bg-[#1e3a8a] text-blue-400 flex items-center justify-center shrink-0">
                                <i class="fas fa-bolt text-lg"></i>
                            </div>
                            <div>
                                <h5 class="text-sm font-bold text-white mb-1">Operasional</h5>
                                <p class="text-[13px] text-slate-400 leading-relaxed">Biaya rutin operasional bisnis yang bersifat tetap atau terjadwal.</p>
                            </div>
                        </div>

                        <div x-show="formData.category === 'consumable'" class="flex gap-4">
                            <div class="w-12 h-12 rounded-full bg-[#064e3b] text-emerald-400 flex items-center justify-center shrink-0">
                                <i class="fas fa-box-open text-lg"></i>
                            </div>
                            <div>
                                <h5 class="text-sm font-bold text-white mb-1">Consumable</h5>
                                <p class="text-[13px] text-slate-400 leading-relaxed">Barang habis pakai yang tidak masuk ke proses produksi utama.</p>
                            </div>
                        </div>

                        <div x-show="formData.category === 'bahan_baku'" class="flex gap-4">
                            <div class="w-12 h-12 rounded-full bg-[#4c1d95] text-purple-400 flex items-center justify-center shrink-0">
                                <i class="fas fa-cube text-lg"></i>
                            </div>
                            <div>
                                <h5 class="text-sm font-bold text-white mb-1">Bahan Baku</h5>
                                <p class="text-[13px] text-slate-400 leading-relaxed">Barang untuk produksi yang menjadi bagian dari HPP.</p>
                            </div>
                        </div>

                        <div x-show="formData.category === 'variabel'" class="flex gap-4">
                            <div class="w-12 h-12 rounded-full bg-[#7c2d12] text-orange-400 flex items-center justify-center shrink-0">
                                <i class="fas fa-car text-lg"></i>
                            </div>
                            <div>
                                <h5 class="text-sm font-bold text-white mb-1">Variabel</h5>
                                <p class="text-[13px] text-slate-400 leading-relaxed">Biaya tidak tetap yang terjadi sesuai kebutuhan atau aktivitas.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- STEP 3 CONTENT -->
        <div x-show="step === 3" x-cloak x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- Left: Form -->
                <div class="lg:col-span-8">
                    <div class="bg-[#1e2336]/80 rounded-[2rem] border border-slate-700/50 p-8 shadow-2xl backdrop-blur-sm">
                        <span class="text-blue-500 font-bold text-sm mb-2 block">Langkah 3 dari 7</span>
                        <h2 class="text-2xl font-bold text-white mb-2">Detail Biaya</h2>
                        <p class="text-slate-400 text-sm mb-8">Lengkapi detail biaya sesuai jenis yang Anda pilih.</p>

                        <!-- Selected Category Info Box -->
                        <div class="bg-slate-800/50 border border-slate-700 rounded-xl p-4 flex gap-4 mb-8">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center shrink-0"
                                 :class="{
                                     'bg-[#1e3a8a] text-blue-400': formData.category === 'operasional',
                                     'bg-[#064e3b] text-emerald-400': formData.category === 'consumable',
                                     'bg-[#4c1d95] text-purple-400': formData.category === 'bahan_baku',
                                     'bg-[#7c2d12] text-orange-400': formData.category === 'variabel'
                                 }">
                                <i class="fas text-lg"
                                   :class="{
                                       'fa-bolt': formData.category === 'operasional',
                                       'fa-box-open': formData.category === 'consumable',
                                       'fa-cube': formData.category === 'bahan_baku',
                                       'fa-car': formData.category === 'variabel'
                                   }"></i>
                            </div>
                            <div>
                                <h5 class="text-sm font-bold text-white mb-1" 
                                    x-text="formData.category === 'operasional' ? 'Operasional' : (formData.category === 'consumable' ? 'Consumable' : (formData.category === 'bahan_baku' ? 'Bahan Baku' : 'Variabel'))"></h5>
                                <p class="text-[13px] text-slate-400 leading-relaxed" 
                                   x-text="formData.category === 'operasional' ? 'Biaya rutin operasional bisnis yang bersifat tetap atau terjadwal.' : (formData.category === 'consumable' ? 'Barang habis pakai yang tidak masuk ke proses produksi utama.' : (formData.category === 'bahan_baku' ? 'Barang untuk produksi yang menjadi bagian dari HPP.' : 'Biaya tidak tetap yang terjadi sesuai kebutuhan atau aktivitas.'))"></p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            <!-- Frekuensi -->
                            <div>
                                <label class="block text-[13px] font-bold text-white mb-2">Frekuensi <span class="text-red-500">*</span></label>
                                <select x-model="formData.frequency" class="w-full bg-[#0f172a]/50 border border-slate-700/70 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-blue-500 transition-colors shadow-inner appearance-none">
                                    <option value="harian">Harian</option>
                                    <option value="2 harian">2 Harian</option>
                                    <option value="mingguan">Mingguan</option>
                                    <option value="bulanan">Bulanan</option>
                                    <option value="sekali beli">Sekali Beli</option>
                                </select>
                                <p class="text-[12px] text-slate-500 mt-2" x-text="'Biaya akan dicatat setiap ' + (formData.frequency === 'sekali beli' ? 'saat ini saja' : formData.frequency) + ' mulai tanggal di atas.'"></p>
                            </div>

                            <!-- Tanggal Mulai -->
                            <div>
                                <label class="block text-[13px] font-bold text-white mb-2">Tanggal Mulai <span class="text-red-500">*</span></label>
                                <div class="relative w-full">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <i class="far fa-calendar-alt text-slate-400"></i>
                                    </div>
                                    <input type="date" x-model="formData.date" class="w-full bg-[#0f172a]/50 border border-slate-700/70 rounded-xl pl-11 pr-4 py-3 text-white focus:outline-none focus:border-blue-500 transition-colors shadow-inner">
                                </div>
                            </div>
                        </div>

                        <hr class="border-slate-700/50 mb-8">

                        <h4 class="text-white font-bold text-sm mb-6">Informasi Nominal</h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            <!-- Nominal per periode -->
                            <div>
                                <label class="block text-[13px] font-bold text-white mb-2">Nominal per periode (<span x-text="formData.frequency" class="capitalize"></span>) <span class="text-red-500">*</span></label>
                                <div class="relative w-full">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <span class="text-slate-400 font-bold">Rp</span>
                                    </div>
                                    <input type="text" x-model="amountFormatted" @input="amountFormatted = formatCurrency($event.target.value); formData.amount = parseInt($event.target.value.replace(/[^0-9]/g, '')) || 0;" placeholder="0" class="w-full bg-[#0f172a]/50 border border-slate-700/70 rounded-xl pl-12 pr-4 py-3 text-white focus:outline-none focus:border-blue-500 transition-colors shadow-inner font-bold">
                                </div>
                            </div>

                            <!-- Estimasi -->
                            <div>
                                <label class="block text-[13px] font-bold text-slate-400 mb-2">Biaya per bulan (estimasi)</label>
                                <div class="relative w-full">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <span class="text-slate-500 font-bold">Rp</span>
                                    </div>
                                    <input type="text" readonly :value="formatCurrency(estimatedMonthly)" class="w-full bg-[#0f172a]/30 border border-slate-700/30 rounded-xl pl-12 pr-4 py-3 text-slate-400 shadow-inner font-bold cursor-not-allowed">
                                </div>
                                <p class="text-[12px] text-slate-500 mt-2">Estimasi otomatis berdasarkan frekuensi.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            <!-- Kategori -->
                            <div>
                                <label class="block text-[13px] font-bold text-slate-300 mb-2">Kategori (Opsional)</label>
                                <select x-model="formData.account_category" class="w-full bg-[#0f172a]/50 border border-slate-700/70 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-blue-500 transition-colors shadow-inner appearance-none">
                                    <option value="">Pilih kategori</option>
                                    <option value="Umum">Umum</option>
                                    <option value="Operasi">Operasi</option>
                                </select>
                            </div>
                            <!-- Sub Kategori -->
                            <div>
                                <label class="block text-[13px] font-bold text-slate-300 mb-2">Sub Kategori (Opsional)</label>
                                <select x-model="formData.sub_category" class="w-full bg-[#0f172a]/50 border border-slate-700/70 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-blue-500 transition-colors shadow-inner appearance-none">
                                    <option value="">Pilih sub kategori</option>
                                </select>
                            </div>
                        </div>

                        <!-- Akun/Pos -->
                        <div class="mb-8">
                            <label class="block text-[13px] font-bold text-slate-300 mb-2">Akun/Pos (Opsional)</label>
                            <select x-model="formData.account_pos" class="w-full bg-[#0f172a]/50 border border-slate-700/70 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-blue-500 transition-colors shadow-inner appearance-none">
                                <option value="">Pilih akun/pos</option>
                                <option value="Kasir">Kasir</option>
                            </select>
                        </div>

                        <!-- Catatan Detail -->
                        <div class="mb-8">
                            <label class="block text-[13px] font-bold text-slate-300 mb-2">Catatan (Opsional)</label>
                            <textarea x-model="formData.details_notes" placeholder="Tambahkan catatan detail biaya jika ada" rows="3" class="w-full bg-[#0f172a]/50 border border-slate-700/70 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-blue-500 transition-colors shadow-inner"></textarea>
                            <p class="text-[12px] text-slate-500 mt-2">Catatan tidak wajib diisi</p>
                        </div>

                        <!-- NAVIGATION BUTTONS STEP 3 -->
                        <div class="mt-8 flex justify-between items-center pt-6 border-t border-slate-700/50">
                            <button @click="step--" class="px-6 py-3 rounded-lg text-sm font-bold text-slate-300 hover:text-white hover:bg-slate-800 border border-slate-700 transition-all flex items-center gap-2">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </button>
                            <button @click="nextStep()" :disabled="!canContinue" 
                                    :class="canContinue ? 'bg-blue-600 hover:bg-blue-500 shadow-lg shadow-blue-500/20' : 'bg-slate-700 text-slate-500 cursor-not-allowed'"
                                    class="px-8 py-3 rounded-lg text-sm font-bold text-white transition-all flex items-center gap-2 group">
                                Selanjutnya <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Right: Summary Panel Step 3 -->
                <div class="lg:col-span-4">
                    <div class="bg-[#1e2336]/80 rounded-2xl border border-slate-700/50 p-6 shadow-xl sticky top-8">
                        <h4 class="text-sm font-bold text-white mb-6">Ringkasan Biaya</h4>
                        
                        <div class="space-y-5">
                            <div>
                                <p class="text-[11px] text-slate-500 uppercase tracking-wider font-bold mb-2">Jenis Biaya</p>
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0"
                                        :class="{
                                            'bg-[#1e3a8a] text-blue-400': formData.category === 'operasional',
                                            'bg-[#064e3b] text-emerald-400': formData.category === 'consumable',
                                            'bg-[#4c1d95] text-purple-400': formData.category === 'bahan_baku',
                                            'bg-[#7c2d12] text-orange-400': formData.category === 'variabel'
                                        }">
                                        <i class="fas text-sm"
                                        :class="{
                                            'fa-bolt': formData.category === 'operasional',
                                            'fa-box-open': formData.category === 'consumable',
                                            'fa-cube': formData.category === 'bahan_baku',
                                            'fa-car': formData.category === 'variabel'
                                        }"></i>
                                    </div>
                                    <span class="text-sm font-bold text-white" 
                                        x-text="formData.category === 'operasional' ? 'Operasional' : (formData.category === 'consumable' ? 'Consumable' : (formData.category === 'bahan_baku' ? 'Bahan Baku' : 'Variabel'))"></span>
                                </div>
                            </div>

                            <div>
                                <p class="text-[11px] text-slate-500 uppercase tracking-wider font-bold mb-1">Nama Biaya</p>
                                <p class="text-sm text-white font-medium" x-text="formData.name || '-'"></p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-[11px] text-slate-500 uppercase tracking-wider font-bold mb-1">Frekuensi</p>
                                    <p class="text-sm text-white font-medium flex items-center gap-2">
                                        <i class="far fa-calendar text-slate-400"></i>
                                        <span x-text="formData.frequency" class="capitalize"></span>
                                    </p>
                                </div>
                                <div>
                                    <p class="text-[11px] text-slate-500 uppercase tracking-wider font-bold mb-1">Tanggal Mulai</p>
                                    <p class="text-sm text-white font-medium flex items-center gap-2">
                                        <i class="far fa-calendar-alt text-slate-400"></i>
                                        <span x-text="formatDateIndo(formData.date)"></span>
                                    </p>
                                </div>
                            </div>

                            <div class="pt-4 border-t border-slate-700/50">
                                <p class="text-[11px] text-slate-500 uppercase tracking-wider font-bold mb-1">Nominal per periode</p>
                                <p class="text-lg text-white font-bold">Rp <span x-text="formatCurrency(formData.amount)"></span></p>
                            </div>

                            <div class="pt-2">
                                <p class="text-[11px] text-slate-500 uppercase tracking-wider font-bold mb-1">Estimasi per bulan</p>
                                <p class="text-lg text-blue-400 font-bold">Rp <span x-text="formatCurrency(estimatedMonthly)"></span></p>
                            </div>

                            <div class="bg-[#1e3a8a]/20 border border-blue-900/40 rounded-xl p-4 mt-2">
                                <div class="flex items-start gap-3">
                                    <i class="fas fa-info-circle text-blue-400 mt-0.5 text-sm"></i>
                                    <p class="text-[12px] text-slate-300 leading-relaxed">
                                        Biaya akan otomatis dibuat setiap <span x-text="formData.frequency === 'sekali beli' ? 'saat ini saja' : formData.frequency"></span> selama masih aktif. Anda dapat mengubah atau menghentikan pengulangan kapan saja.
                                    </p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- STEP 4 CONTENT -->
        <div x-show="step === 4" x-cloak x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- Left: Form -->
                <div class="lg:col-span-8">
                    <div class="bg-[#1e2336]/80 rounded-[2rem] border border-slate-700/50 p-8 shadow-2xl backdrop-blur-sm">
                        <span class="text-blue-500 font-bold text-sm mb-2 block">Langkah 4 dari 7</span>
                        <h2 class="text-2xl font-bold text-white mb-2">Metode Pembayaran</h2>
                        <p class="text-slate-400 text-sm mb-8">Pilih metode pembayaran untuk biaya ini.</p>

                        <div class="space-y-4 mb-8">
                            <!-- Option 1: Tunai -->
                            <div @click="formData.payment_method = 'tunai'" 
                                 :class="formData.payment_method === 'tunai' ? 'border-blue-500 bg-blue-900/20' : 'border-slate-700/60 bg-slate-800/30 hover:border-slate-500/80'"
                                 class="relative p-5 rounded-2xl border-2 cursor-pointer transition-all duration-300 flex items-center gap-5 group">
                                
                                <!-- Radio indicator -->
                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0"
                                     :class="formData.payment_method === 'tunai' ? 'border-blue-500 bg-[#1e2336]' : 'border-slate-500 group-hover:border-slate-400'">
                                    <div x-show="formData.payment_method === 'tunai'" class="w-2.5 h-2.5 rounded-full bg-blue-500"></div>
                                </div>

                                <!-- Icon -->
                                <div class="w-12 h-12 rounded-xl bg-[#1e3a8a]/40 text-blue-400 flex items-center justify-center shrink-0 border border-blue-900/50">
                                    <i class="fas fa-wallet text-xl"></i>
                                </div>

                                <!-- Text -->
                                <div class="flex-grow">
                                    <h4 class="text-base font-bold text-white mb-1">Tunai</h4>
                                    <p class="text-[13px] text-slate-400">Pembayaran dilakukan secara tunai.</p>
                                </div>

                                <!-- Selected Badge -->
                                <div x-show="formData.payment_method === 'tunai'" class="absolute right-5 top-1/2 -translate-y-1/2 px-3 py-1 bg-emerald-500/20 text-emerald-400 text-xs font-bold rounded-lg border border-emerald-500/30">
                                    Dipilih
                                </div>
                            </div>

                            <!-- Option 2: QRIS -->
                            <div @click="formData.payment_method = 'qris'" 
                                 :class="formData.payment_method === 'qris' ? 'border-blue-500 bg-blue-900/20' : 'border-slate-700/60 bg-slate-800/30 hover:border-slate-500/80'"
                                 class="relative p-5 rounded-2xl border-2 cursor-pointer transition-all duration-300 flex items-center gap-5 group">
                                
                                <!-- Radio indicator -->
                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0"
                                     :class="formData.payment_method === 'qris' ? 'border-blue-500 bg-[#1e2336]' : 'border-slate-500 group-hover:border-slate-400'">
                                    <div x-show="formData.payment_method === 'qris'" class="w-2.5 h-2.5 rounded-full bg-blue-500"></div>
                                </div>

                                <!-- Icon -->
                                <div class="w-12 h-12 rounded-xl bg-[#4c1d95]/40 text-purple-400 flex items-center justify-center shrink-0 border border-purple-900/50">
                                    <i class="fas fa-qrcode text-xl"></i>
                                </div>

                                <!-- Text -->
                                <div class="flex-grow">
                                    <h4 class="text-base font-bold text-white mb-1">QRIS</h4>
                                    <p class="text-[13px] text-slate-400">Pembayaran menggunakan QR Code (e-wallet/bank).</p>
                                </div>

                                <!-- Selected Badge -->
                                <div x-show="formData.payment_method === 'qris'" class="absolute right-5 top-1/2 -translate-y-1/2 px-3 py-1 bg-emerald-500/20 text-emerald-400 text-xs font-bold rounded-lg border border-emerald-500/30">
                                    Dipilih
                                </div>
                            </div>

                            <!-- Option 3: Transfer Bank -->
                            <div @click="formData.payment_method = 'bank'" 
                                 :class="formData.payment_method === 'bank' ? 'border-blue-500 bg-blue-900/20' : 'border-slate-700/60 bg-slate-800/30 hover:border-slate-500/80'"
                                 class="relative p-5 rounded-2xl border-2 cursor-pointer transition-all duration-300 flex items-center gap-5 group">
                                
                                <!-- Radio indicator -->
                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0"
                                     :class="formData.payment_method === 'bank' ? 'border-blue-500 bg-[#1e2336]' : 'border-slate-500 group-hover:border-slate-400'">
                                    <div x-show="formData.payment_method === 'bank'" class="w-2.5 h-2.5 rounded-full bg-blue-500"></div>
                                </div>

                                <!-- Icon -->
                                <div class="w-12 h-12 rounded-xl bg-[#064e3b]/40 text-emerald-400 flex items-center justify-center shrink-0 border border-emerald-900/50">
                                    <i class="fas fa-university text-xl"></i>
                                </div>

                                <!-- Text -->
                                <div class="flex-grow">
                                    <h4 class="text-base font-bold text-white mb-1">Transfer Bank</h4>
                                    <p class="text-[13px] text-slate-400">Pembayaran melalui transfer rekening bank.</p>
                                </div>

                                <!-- Selected Badge -->
                                <div x-show="formData.payment_method === 'bank'" class="absolute right-5 top-1/2 -translate-y-1/2 px-3 py-1 bg-emerald-500/20 text-emerald-400 text-xs font-bold rounded-lg border border-emerald-500/30">
                                    Dipilih
                                </div>
                            </div>
                        </div>

                        <!-- Info Alert -->
                        <div class="bg-[#1e3a8a]/20 border border-blue-900/40 rounded-xl p-4 flex items-start md:items-center gap-4">
                            <i class="fas fa-info-circle text-blue-500 text-lg mt-0.5 md:mt-0"></i>
                            <div>
                                <h5 class="text-[13px] font-bold text-blue-400 mb-0.5">Informasi</h5>
                                <p class="text-[12px] text-slate-300">Metode pembayaran dapat mempengaruhi pencatatan arus kas dan laporan keuangan Anda.</p>
                            </div>
                        </div>

                        <!-- NAVIGATION BUTTONS STEP 4 -->
                        <div class="mt-8 flex justify-between items-center pt-6 border-t border-slate-700/50">
                            <button @click="step--" class="px-6 py-3 rounded-lg text-sm font-bold text-slate-300 hover:text-white hover:bg-slate-800 border border-slate-700 transition-all flex items-center gap-2">
                                <i class="fas fa-arrow-left"></i> Sebelumnya
                            </button>
                            <button @click="nextStep()" :disabled="!canContinue" 
                                    :class="canContinue ? 'bg-blue-600 hover:bg-blue-500 shadow-lg shadow-blue-500/20' : 'bg-slate-700 text-slate-500 cursor-not-allowed'"
                                    class="px-8 py-3 rounded-lg text-sm font-bold text-white transition-all flex items-center gap-2 group">
                                Selanjutnya <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Right: Summary Panel Step 4 -->
                <div class="lg:col-span-4">
                    <div class="bg-[#1e2336]/80 rounded-2xl border border-slate-700/50 p-6 shadow-xl sticky top-8">
                        <h4 class="text-sm font-bold text-white mb-6">Ringkasan Biaya</h4>
                        
                        <div class="space-y-5">
                            <div>
                                <p class="text-[11px] text-slate-500 uppercase tracking-wider font-bold mb-2">Jenis Biaya</p>
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0"
                                        :class="{
                                            'bg-[#1e3a8a] text-blue-400': formData.category === 'operasional',
                                            'bg-[#064e3b] text-emerald-400': formData.category === 'consumable',
                                            'bg-[#4c1d95] text-purple-400': formData.category === 'bahan_baku',
                                            'bg-[#7c2d12] text-orange-400': formData.category === 'variabel'
                                        }">
                                        <i class="fas text-sm"
                                        :class="{
                                            'fa-bolt': formData.category === 'operasional',
                                            'fa-box-open': formData.category === 'consumable',
                                            'fa-cube': formData.category === 'bahan_baku',
                                            'fa-car': formData.category === 'variabel'
                                        }"></i>
                                    </div>
                                    <span class="text-sm font-bold text-white" 
                                        x-text="formData.category === 'operasional' ? 'Operasional' : (formData.category === 'consumable' ? 'Consumable' : (formData.category === 'bahan_baku' ? 'Bahan Baku' : 'Variabel'))"></span>
                                </div>
                            </div>

                            <div>
                                <p class="text-[11px] text-slate-500 uppercase tracking-wider font-bold mb-1">Nama Biaya</p>
                                <p class="text-sm text-white font-medium" x-text="formData.name || '-'"></p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-[11px] text-slate-500 uppercase tracking-wider font-bold mb-1">Frekuensi</p>
                                    <p class="text-sm text-white font-medium flex items-center gap-2">
                                        <i class="far fa-calendar text-slate-400"></i>
                                        <span x-text="formData.frequency" class="capitalize"></span>
                                    </p>
                                </div>
                                <div>
                                    <p class="text-[11px] text-slate-500 uppercase tracking-wider font-bold mb-1">Tanggal Mulai</p>
                                    <p class="text-sm text-white font-medium flex items-center gap-2">
                                        <i class="far fa-calendar-alt text-slate-400"></i>
                                        <span x-text="formatDateIndo(formData.date)"></span>
                                    </p>
                                </div>
                            </div>

                            <div class="pt-4 border-t border-slate-700/50">
                                <p class="text-[11px] text-slate-500 uppercase tracking-wider font-bold mb-1">Nominal per periode</p>
                                <p class="text-lg text-white font-bold">Rp <span x-text="formatCurrency(formData.amount)"></span></p>
                            </div>

                            <div class="pt-2">
                                <p class="text-[11px] text-slate-500 uppercase tracking-wider font-bold mb-1">Estimasi per bulan</p>
                                <p class="text-lg text-blue-400 font-bold">Rp <span x-text="formatCurrency(estimatedMonthly)"></span></p>
                            </div>

                            <div class="pt-4 border-t border-slate-700/50">
                                <p class="text-[11px] text-slate-500 uppercase tracking-wider font-bold mb-1">Metode Pembayaran</p>
                                <div class="flex items-center gap-2 mt-1">
                                    <template x-if="formData.payment_method === 'tunai'">
                                        <div class="w-6 h-6 rounded-md bg-blue-900/50 flex items-center justify-center border border-blue-800/50">
                                            <i class="fas fa-wallet text-[10px] text-blue-400"></i>
                                        </div>
                                    </template>
                                    <template x-if="formData.payment_method === 'qris'">
                                        <div class="w-6 h-6 rounded-md bg-purple-900/50 flex items-center justify-center border border-purple-800/50">
                                            <i class="fas fa-qrcode text-[10px] text-purple-400"></i>
                                        </div>
                                    </template>
                                    <template x-if="formData.payment_method === 'bank'">
                                        <div class="w-6 h-6 rounded-md bg-emerald-900/50 flex items-center justify-center border border-emerald-800/50">
                                            <i class="fas fa-university text-[10px] text-emerald-400"></i>
                                        </div>
                                    </template>
                                    <template x-if="!formData.payment_method">
                                        <div class="w-6 h-6 rounded-md bg-slate-800 flex items-center justify-center border border-slate-700">
                                            <i class="fas fa-minus text-[10px] text-slate-500"></i>
                                        </div>
                                    </template>
                                    <p class="text-sm text-white font-medium" x-text="formData.payment_method === 'tunai' ? 'Tunai' : (formData.payment_method === 'qris' ? 'QRIS' : (formData.payment_method === 'bank' ? 'Transfer Bank' : '-'))"></p>
                                </div>
                            </div>

                            <div class="bg-[#1e3a8a]/20 border border-blue-900/40 rounded-xl p-4 mt-2">
                                <div class="flex items-start gap-3">
                                    <i class="fas fa-info-circle text-blue-400 mt-0.5 text-sm"></i>
                                    <p class="text-[12px] text-slate-300 leading-relaxed">
                                        Biaya akan dicatat setiap <span x-text="formData.frequency === 'sekali beli' ? 'saat ini saja' : formData.frequency"></span> mulai dari <span x-text="formatDateIndo(formData.date)"></span>.
                                    </p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- STEP 5 CONTENT -->
        <div x-show="step === 6" x-cloak x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- Left: Form -->
                <div class="lg:col-span-8">
                    <div class="bg-[#1e2336]/80 rounded-[2rem] border border-slate-700/50 p-8 shadow-2xl backdrop-blur-sm">
                        <span class="text-blue-500 font-bold text-sm mb-2 block">Langkah 7 dari 7</span>
                        <h2 class="text-2xl font-bold text-white mb-2">Pengulangan (Recurring)</h2>
                        <p class="text-slate-400 text-sm mb-8">Atur pengulangan biaya ini jika biaya bersifat rutin/berulang.</p>

                        <!-- Toggle Box -->
                        <div class="bg-slate-800/40 border border-slate-700/60 rounded-2xl p-6 mb-8 flex items-center justify-between cursor-pointer transition-all hover:border-slate-600"
                             @click="formData.is_recurring = !formData.is_recurring">
                            <div class="flex items-center gap-5">
                                <!-- Toggle Switch -->
                                <div class="w-12 h-6 rounded-full relative transition-colors duration-300 ease-in-out"
                                     :class="formData.is_recurring ? 'bg-blue-600' : 'bg-slate-700'">
                                    <div class="absolute top-1 w-4 h-4 rounded-full bg-white transition-transform duration-300 ease-in-out shadow-sm"
                                         :class="formData.is_recurring ? 'left-7' : 'left-1'"></div>
                                </div>
                                <div>
                                    <h4 class="text-base font-bold text-white mb-1">Aktifkan Pengulangan</h4>
                                    <p class="text-[13px] text-slate-400">Biaya akan dicatat secara otomatis sesuai jadwal yang Anda atur.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Recurring Settings -->
                        <div x-show="formData.is_recurring" x-collapse>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <!-- Frekuensi -->
                                <div>
                                    <label class="block text-[13px] font-bold text-white mb-2">Frekuensi</label>
                                    <p class="text-[12px] text-slate-400 mb-3">Pilih seberapa sering biaya ini terjadi.</p>
                                    <div class="relative w-full">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                            <i class="far fa-clock text-slate-400"></i>
                                        </div>
                                        <select x-model="formData.frequency" class="w-full bg-[#0f172a]/50 border border-slate-700/70 rounded-xl pl-11 pr-4 py-3 text-white focus:outline-none focus:border-blue-500 transition-colors shadow-inner appearance-none">
                                            <option value="harian">Setiap Hari</option>
                                            <option value="2 harian">Setiap 2 Hari Sekali</option>
                                            <option value="mingguan">Setiap Minggu</option>
                                            <option value="bulanan">Setiap Bulan</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Mulai dari tanggal -->
                                <div>
                                    <label class="block text-[13px] font-bold text-white mb-2">Mulai dari tanggal</label>
                                    <p class="text-[12px] text-slate-400 mb-3">Tanggal mulai berlaku pengulangan.</p>
                                    <div class="relative w-full">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                            <i class="far fa-calendar-alt text-slate-400"></i>
                                        </div>
                                        <input type="date" x-model="formData.date" class="w-full bg-[#0f172a]/50 border border-slate-700/70 rounded-xl pl-11 pr-4 py-3 text-white focus:outline-none focus:border-blue-500 transition-colors shadow-inner">
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                                <!-- Berakhir pada -->
                                <div>
                                    <label class="block text-[13px] font-bold text-white mb-2">Berakhir pada <span class="text-slate-500 font-normal">(Opsional)</span></label>
                                    <p class="text-[12px] text-slate-400 mb-3">Kosongkan jika ingin pengulangan tanpa batas.</p>
                                    <div class="relative w-full">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                            <i class="far fa-calendar-times text-slate-400"></i>
                                        </div>
                                        <input type="date" x-model="formData.end_date" placeholder="Tanpa Batas" class="w-full bg-[#0f172a]/50 border border-slate-700/70 rounded-xl pl-11 pr-4 py-3 text-white focus:outline-none focus:border-blue-500 transition-colors shadow-inner">
                                        <div x-show="!formData.end_date" class="absolute inset-y-0 left-11 flex items-center pointer-events-none">
                                            <span class="text-slate-400">Tanpa Batas</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Jadwal Berikutnya Preview -->
                                <div class="bg-blue-900/10 border border-blue-900/30 rounded-xl p-5 flex flex-col justify-center">
                                    <div class="flex items-center gap-2 mb-2">
                                        <i class="fas fa-info-circle text-blue-500 text-sm"></i>
                                        <h5 class="text-[13px] font-bold text-blue-400">Jadwal berikutnya</h5>
                                    </div>
                                    <p class="text-lg font-bold text-white mb-1" x-text="nextScheduleDate"></p>
                                    <p class="text-[12px] text-slate-400" x-text="'Biaya akan dicatat otomatis setiap ' + (formData.frequency === '2 harian' ? '2 hari sekali' : (formData.frequency === 'harian' ? 'hari' : (formData.frequency === 'mingguan' ? 'minggu' : 'bulan'))) + '.'"></p>
                                </div>
                            </div>

                            <!-- Purple Info Alert -->
                            <div class="bg-[#4c1d95]/20 border border-purple-900/40 rounded-xl p-4 flex items-center gap-3">
                                <i class="fas fa-info-circle text-purple-400"></i>
                                <p class="text-[12px] text-purple-200">Pengulangan dapat dihentikan atau diubah nanti dari daftar biaya bulanan.</p>
                            </div>
                        </div>

                        <!-- NAVIGATION BUTTONS STEP 5 -->
                        <div class="mt-8 flex justify-between items-center pt-6 border-t border-slate-700/50">
                            <button @click="step--" class="px-6 py-3 rounded-lg text-sm font-bold text-slate-300 hover:text-white hover:bg-slate-800 border border-slate-700 transition-all flex items-center gap-2">
                                <i class="fas fa-arrow-left"></i> Sebelumnya
                            </button>
                            <button @click="nextStep()" :disabled="!canContinue" 
                                    :class="canContinue ? 'bg-blue-600 hover:bg-blue-500 shadow-lg shadow-blue-500/20' : 'bg-slate-700 text-slate-500 cursor-not-allowed'"
                                    class="px-8 py-3 rounded-lg text-sm font-bold text-white transition-all flex items-center gap-2 group">
                                Selanjutnya <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Right: Summary Panel Step 5 -->
                <div class="lg:col-span-4">
                    <div class="bg-[#1e2336]/80 rounded-2xl border border-slate-700/50 p-6 shadow-xl sticky top-8">
                        <h4 class="text-sm font-bold text-white mb-6">Ringkasan Biaya</h4>
                        
                        <div class="space-y-5">
                            <div>
                                <p class="text-[11px] text-slate-500 uppercase tracking-wider font-bold mb-2">Jenis Biaya</p>
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0"
                                        :class="{
                                            'bg-[#1e3a8a] text-blue-400': formData.category === 'operasional',
                                            'bg-[#064e3b] text-emerald-400': formData.category === 'consumable',
                                            'bg-[#4c1d95] text-purple-400': formData.category === 'bahan_baku',
                                            'bg-[#7c2d12] text-orange-400': formData.category === 'variabel'
                                        }">
                                        <i class="fas text-sm"
                                        :class="{
                                            'fa-bolt': formData.category === 'operasional',
                                            'fa-box-open': formData.category === 'consumable',
                                            'fa-cube': formData.category === 'bahan_baku',
                                            'fa-car': formData.category === 'variabel'
                                        }"></i>
                                    </div>
                                    <span class="text-sm font-bold text-white" 
                                        x-text="formData.category === 'operasional' ? 'Operasional' : (formData.category === 'consumable' ? 'Consumable' : (formData.category === 'bahan_baku' ? 'Bahan Baku' : 'Variabel'))"></span>
                                </div>
                            </div>

                            <div>
                                <p class="text-[11px] text-slate-500 uppercase tracking-wider font-bold mb-1">Nama Biaya</p>
                                <p class="text-sm text-white font-medium" x-text="formData.name || '-'"></p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-[11px] text-slate-500 uppercase tracking-wider font-bold mb-1">Frekuensi</p>
                                    <p class="text-sm text-white font-medium flex items-center gap-2">
                                        <i class="far fa-calendar text-slate-400"></i>
                                        <span x-text="formData.frequency" class="capitalize"></span>
                                    </p>
                                </div>
                                <div>
                                    <p class="text-[11px] text-slate-500 uppercase tracking-wider font-bold mb-1">Tanggal Mulai</p>
                                    <p class="text-sm text-white font-medium flex items-center gap-2">
                                        <i class="far fa-calendar-alt text-slate-400"></i>
                                        <span x-text="formatDateIndo(formData.date)"></span>
                                    </p>
                                </div>
                            </div>

                            <div class="pt-4 border-t border-slate-700/50">
                                <p class="text-[11px] text-slate-500 uppercase tracking-wider font-bold mb-1">Nominal per periode</p>
                                <p class="text-lg text-white font-bold">Rp <span x-text="formatCurrency(formData.amount)"></span></p>
                            </div>

                            <div class="pt-2">
                                <p class="text-[11px] text-slate-500 uppercase tracking-wider font-bold mb-1">Estimasi per bulan</p>
                                <p class="text-lg text-blue-400 font-bold">Rp <span x-text="formatCurrency(estimatedMonthly)"></span></p>
                            </div>

                            <div class="pt-4 border-t border-slate-700/50" x-show="formData.payment_method">
                                <p class="text-[11px] text-slate-500 uppercase tracking-wider font-bold mb-1">Metode Pembayaran</p>
                                <div class="flex items-center gap-2 mt-1">
                                    <template x-if="formData.payment_method === 'tunai'">
                                        <div class="w-6 h-6 rounded-md bg-blue-900/50 flex items-center justify-center border border-blue-800/50">
                                            <i class="fas fa-wallet text-[10px] text-blue-400"></i>
                                        </div>
                                    </template>
                                    <template x-if="formData.payment_method === 'qris'">
                                        <div class="w-6 h-6 rounded-md bg-purple-900/50 flex items-center justify-center border border-purple-800/50">
                                            <i class="fas fa-qrcode text-[10px] text-purple-400"></i>
                                        </div>
                                    </template>
                                    <template x-if="formData.payment_method === 'bank'">
                                        <div class="w-6 h-6 rounded-md bg-emerald-900/50 flex items-center justify-center border border-emerald-800/50">
                                            <i class="fas fa-university text-[10px] text-emerald-400"></i>
                                        </div>
                                    </template>
                                    <p class="text-sm text-white font-medium" x-text="formData.payment_method === 'tunai' ? 'Tunai' : (formData.payment_method === 'qris' ? 'QRIS' : (formData.payment_method === 'bank' ? 'Transfer Bank' : '-'))"></p>
                                </div>
                            </div>
                            
                            <!-- Pengulangan Details -->
                            <div class="pt-4 border-t border-slate-700/50" x-show="formData.is_recurring">
                                <p class="text-[11px] text-slate-500 uppercase tracking-wider font-bold mb-3">Pengulangan</p>
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2 text-[13px]">
                                        <i class="far fa-clock text-slate-400 w-4 text-center"></i>
                                        <span class="text-white font-medium" x-text="formData.frequency === 'harian' ? 'Setiap Hari' : (formData.frequency === '2 harian' ? 'Setiap 2 Hari Sekali' : (formData.frequency === 'mingguan' ? 'Setiap Minggu' : 'Setiap Bulan'))"></span>
                                    </div>
                                    <div class="flex items-center gap-2 text-[13px]">
                                        <i class="far fa-calendar-check text-slate-400 w-4 text-center"></i>
                                        <span class="text-white font-medium" x-text="'Mulai dari ' + formatDateIndo(formData.date)"></span>
                                    </div>
                                    <div class="flex items-center gap-2 text-[13px]">
                                        <i class="far fa-calendar-times text-slate-400 w-4 text-center"></i>
                                        <span class="text-white font-medium" x-text="formData.end_date ? 'Berakhir pada ' + formatDateIndo(formData.end_date) : 'Tanpa Batas'"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-[#4c1d95]/20 border border-purple-900/40 rounded-xl p-4 mt-2" x-show="formData.is_recurring">
                                <div class="flex items-start gap-3">
                                    <i class="fas fa-info-circle text-purple-400 mt-0.5 text-sm"></i>
                                    <p class="text-[12px] text-purple-200 leading-relaxed">
                                        Biaya akan dicatat otomatis setiap <span x-text="formData.frequency === '2 harian' ? '2 hari sekali' : (formData.frequency === 'harian' ? 'hari' : (formData.frequency === 'mingguan' ? 'minggu' : 'bulan'))"></span> mulai dari <span x-text="formatDateIndo(formData.date)"></span>.
                                    </p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- STEP 7 CONTENT (Review & Konfirmasi) -->
        <div x-show="step === 7" x-cloak x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- Left: Form -->
                <div class="lg:col-span-8">
                    <div class="bg-[#1e2336]/80 rounded-[2rem] border border-slate-700/50 p-8 shadow-2xl backdrop-blur-sm">
                        <span class="text-blue-500 font-bold text-sm mb-2 block">Langkah 7 dari 7</span>
                        <h2 class="text-2xl font-bold text-white mb-2">Review & Konfirmasi</h2>
                        <p class="text-slate-400 text-sm mb-8">Periksa kembali detail biaya berikut sebelum disimpan.</p>

                        <div class="space-y-4 mb-8">
                            <!-- Section 1: Jenis Biaya -->
                            <div class="bg-slate-800/30 border border-slate-700/50 rounded-2xl p-5 flex flex-col md:flex-row md:items-start gap-4 transition-all hover:bg-slate-800/50">
                                <div class="w-10 h-10 rounded-xl bg-[#1e3a8a]/40 text-blue-400 flex items-center justify-center shrink-0 border border-blue-900/50 mt-1">
                                    <i class="fas fa-bolt text-sm"></i>
                                </div>
                                <div class="flex-grow">
                                    <h4 class="text-sm font-bold text-blue-400 mb-2">1. Jenis Biaya</h4>
                                    <p class="text-sm text-white font-medium mb-1" x-text="formData.category === 'operasional' ? 'Operasional' : (formData.category === 'consumable' ? 'Consumable' : (formData.category === 'bahan_baku' ? 'Bahan Baku' : 'Variabel'))"></p>
                                    <p class="text-[12px] text-slate-400" x-text="formData.category === 'operasional' ? 'Biaya rutin operasional bisnis yang bersifat tetap atau terjadwal.' : (formData.category === 'consumable' ? 'Barang habis pakai yang tidak masuk ke proses produksi utama.' : (formData.category === 'bahan_baku' ? 'Barang untuk produksi yang menjadi bagian dari HPP.' : 'Biaya tidak tetap yang terjadi sesuai kebutuhan atau aktivitas.'))"></p>
                                </div>
                                <button @click="step = 1" class="px-4 py-2 rounded-lg text-xs font-bold text-slate-300 hover:text-white bg-slate-800 hover:bg-slate-700 border border-slate-700 transition-all self-start shrink-0">
                                    Edit
                                </button>
                            </div>

                            <!-- Section 2: Info Dasar -->
                            <div class="bg-slate-800/30 border border-slate-700/50 rounded-2xl p-5 flex flex-col md:flex-row md:items-start gap-4 transition-all hover:bg-slate-800/50">
                                <div class="w-10 h-10 rounded-xl bg-blue-500/20 text-blue-400 flex items-center justify-center shrink-0 border border-blue-500/30 mt-1">
                                    <i class="fas fa-info-circle text-sm"></i>
                                </div>
                                <div class="flex-grow">
                                    <h4 class="text-sm font-bold text-blue-400 mb-3">2. Info Dasar</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                        <div>
                                            <p class="text-[11px] text-slate-500 uppercase tracking-wider font-bold">Nama Biaya</p>
                                            <p class="text-[13px] text-white font-medium mt-0.5" x-text="formData.name || '-'"></p>
                                        </div>
                                        <div class="md:col-span-2">
                                            <p class="text-[11px] text-slate-500 uppercase tracking-wider font-bold">Deskripsi</p>
                                            <p class="text-[13px] text-white font-medium mt-0.5" x-text="formData.description || '-'"></p>
                                        </div>
                                        <div>
                                            <p class="text-[11px] text-slate-500 uppercase tracking-wider font-bold">Tanggal</p>
                                            <p class="text-[13px] text-white font-medium mt-0.5" x-text="formatDateIndo(formData.date)"></p>
                                        </div>
                                    </div>
                                </div>
                                <button @click="step = 2" class="px-4 py-2 rounded-lg text-xs font-bold text-slate-300 hover:text-white bg-slate-800 hover:bg-slate-700 border border-slate-700 transition-all self-start shrink-0">
                                    Edit
                                </button>
                            </div>

                            <!-- Section 3: Detail Biaya -->
                            <div class="bg-slate-800/30 border border-slate-700/50 rounded-2xl p-5 flex flex-col md:flex-row md:items-start gap-4 transition-all hover:bg-slate-800/50">
                                <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0 border border-emerald-500/30 mt-1">
                                    <i class="fas fa-clipboard-list text-sm"></i>
                                </div>
                                <div class="flex-grow">
                                    <h4 class="text-sm font-bold text-emerald-400 mb-3">3. Detail Biaya</h4>
                                    <div class="grid grid-cols-2 gap-y-3 gap-x-4">
                                        <div>
                                            <p class="text-[11px] text-slate-500 uppercase tracking-wider font-bold">Frekuensi</p>
                                            <p class="text-[13px] text-white font-medium mt-0.5 capitalize" x-text="formData.frequency === 'harian' ? 'Setiap Hari' : (formData.frequency === '2 harian' ? 'Setiap 2 Hari Sekali' : (formData.frequency === 'mingguan' ? 'Setiap Minggu' : 'Setiap Bulan'))"></p>
                                        </div>
                                        <div>
                                            <p class="text-[11px] text-slate-500 uppercase tracking-wider font-bold">Tanggal Mulai</p>
                                            <p class="text-[13px] text-white font-medium mt-0.5" x-text="formatDateIndo(formData.date)"></p>
                                        </div>
                                        <div>
                                            <p class="text-[11px] text-slate-500 uppercase tracking-wider font-bold">Nominal per periode</p>
                                            <p class="text-[13px] text-white font-medium mt-0.5" x-text="'Rp ' + formatCurrency(formData.amount)"></p>
                                        </div>
                                        <div>
                                            <p class="text-[11px] text-slate-500 uppercase tracking-wider font-bold">Estimasi per bulan</p>
                                            <p class="text-[13px] text-blue-400 font-bold mt-0.5" x-text="'Rp ' + formatCurrency(estimatedMonthly)"></p>
                                        </div>
                                    </div>
                                </div>
                                <button @click="step = 3" class="px-4 py-2 rounded-lg text-xs font-bold text-slate-300 hover:text-white bg-slate-800 hover:bg-slate-700 border border-slate-700 transition-all self-start shrink-0">
                                    Edit
                                </button>
                            </div>

                            <!-- Section 4: Metode Pembayaran -->
                            <div class="bg-slate-800/30 border border-slate-700/50 rounded-2xl p-5 flex flex-col md:flex-row md:items-start gap-4 transition-all hover:bg-slate-800/50">
                                <div class="w-10 h-10 rounded-xl bg-orange-500/20 text-orange-400 flex items-center justify-center shrink-0 border border-orange-500/30 mt-1">
                                    <i class="fas fa-wallet text-sm"></i>
                                </div>
                                <div class="flex-grow">
                                    <h4 class="text-sm font-bold text-orange-400 mb-2">4. Metode Pembayaran</h4>
                                    <p class="text-sm text-white font-medium mb-1" x-text="formData.payment_method === 'tunai' ? 'Tunai' : (formData.payment_method === 'qris' ? 'QRIS' : 'Transfer Bank')"></p>
                                    <p class="text-[12px] text-slate-400" x-text="formData.payment_method === 'tunai' ? 'Pembayaran dilakukan secara tunai.' : (formData.payment_method === 'qris' ? 'Pembayaran menggunakan QR Code (e-wallet/bank).' : 'Pembayaran melalui transfer rekening bank.')"></p>
                                </div>
                                <button @click="step = 4" class="px-4 py-2 rounded-lg text-xs font-bold text-slate-300 hover:text-white bg-slate-800 hover:bg-slate-700 border border-slate-700 transition-all self-start shrink-0">
                                    Edit
                                </button>
                            </div>

                                                        <!-- Section 4: Status Pembayaran -->
                            <div class="bg-slate-800/30 border border-slate-700/50 rounded-2xl p-5 flex flex-col md:flex-row md:items-start gap-4 transition-all hover:bg-slate-800/50">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 border mt-1"
                                     :class="formData.payment_status === 'paid' ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30' : 'bg-rose-500/20 text-rose-400 border-rose-500/30'">
                                    <i class="fas text-sm" :class="formData.payment_status === 'paid' ? 'fa-check-circle' : 'fa-clock'"></i>
                                </div>
                                <div class="flex-grow">
                                    <h4 class="text-sm font-bold mb-2" :class="formData.payment_status === 'paid' ? 'text-emerald-400' : 'text-rose-400'">4. Status Pembayaran</h4>
                                    <p class="text-sm text-white font-medium mb-1" x-text="formData.payment_status === 'paid' ? 'Bayar Sekarang (PAID)' : 'Bayar Nanti (UNPAID)'"></p>
                                    <p class="text-[12px] text-slate-400" x-text="formData.payment_status === 'paid' ? 'Biaya akan langsung masuk ke Cashflow (Out).' : 'Biaya dicatat sebagai hutang yang belum dibayar.'"></p>
                                </div>
                                <button @click="step = 5" class="px-4 py-2 rounded-lg text-xs font-bold text-slate-300 hover:text-white bg-slate-800 hover:bg-slate-700 border border-slate-700 transition-all self-start shrink-0">
                                    Edit
                                </button>
                            </div>

                            <!-- Section 5: Pengulangan -->
                            <div class="bg-slate-800/30 border border-slate-700/50 rounded-2xl p-5 flex flex-col md:flex-row md:items-start gap-4 transition-all hover:bg-slate-800/50">
                                <div class="w-10 h-10 rounded-xl bg-pink-500/20 text-pink-400 flex items-center justify-center shrink-0 border border-pink-500/30 mt-1">
                                    <i class="fas fa-sync text-sm"></i>
                                </div>
                                <div class="flex-grow">
                                    <h4 class="text-sm font-bold text-pink-400 mb-3">5. Pengulangan</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-y-3 gap-x-4">
                                        <div>
                                            <p class="text-[11px] text-slate-500 uppercase tracking-wider font-bold">Aktifkan Pengulangan</p>
                                            <p class="text-[13px] text-white font-medium mt-0.5" x-text="formData.is_recurring ? 'Ya' : 'Tidak'"></p>
                                        </div>
                                        <template x-if="formData.is_recurring">
                                            <div>
                                                <p class="text-[11px] text-slate-500 uppercase tracking-wider font-bold">Berakhir Pada</p>
                                                <p class="text-[13px] text-white font-medium mt-0.5" x-text="formData.end_date ? formatDateIndo(formData.end_date) : 'Tanpa Batas'"></p>
                                            </div>
                                        </template>
                                        <template x-if="formData.is_recurring">
                                            <div class="md:col-span-2">
                                                <p class="text-[11px] text-slate-500 uppercase tracking-wider font-bold">Jadwal Berikutnya</p>
                                                <p class="text-[13px] text-white font-medium mt-0.5">
                                                    <span x-text="nextScheduleDate"></span> 
                                                    <span class="text-slate-400"> (<span x-text="formData.frequency === 'harian' ? 'Setiap Hari' : (formData.frequency === '2 harian' ? 'Setiap 2 Hari Sekali' : (formData.frequency === 'mingguan' ? 'Setiap Minggu' : 'Setiap Bulan'))"></span>)</span>
                                                </p>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                                <button @click="step = 5" class="px-4 py-2 rounded-lg text-xs font-bold text-slate-300 hover:text-white bg-slate-800 hover:bg-slate-700 border border-slate-700 transition-all self-start shrink-0">
                                    Edit
                                
                            </div>
                        </div>

                        <!-- NAVIGATION BUTTONS STEP 7 -->
                        <div class="mt-8 flex justify-between items-center pt-6 border-t border-slate-700/50">
                            <button @click="step--" class="px-6 py-3 rounded-lg text-sm font-bold text-slate-300 hover:text-white hover:bg-slate-800 border border-slate-700 transition-all flex items-center gap-2">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </button>
                            <button @click="submitForm()" 
                                    class="px-8 py-3 rounded-lg text-sm font-bold text-white bg-blue-600 hover:bg-blue-500 shadow-lg shadow-blue-500/20 transition-all flex items-center gap-2 group">
                                <i class="fas fa-save"></i> Simpan Biaya
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Right: Summary Panel Step 7 -->
                <div class="lg:col-span-4">
                    <div class="bg-[#1e2336]/80 rounded-2xl border border-slate-700/50 p-6 shadow-xl sticky top-8">
                        <h4 class="text-sm font-bold text-white mb-6">Ringkasan Biaya</h4>
                        
                        <div class="space-y-5">
                            <div>
                                <p class="text-[11px] text-slate-500 uppercase tracking-wider font-bold mb-2">Jenis Biaya</p>
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0"
                                        :class="{
                                            'bg-[#1e3a8a] text-blue-400': formData.category === 'operasional',
                                            'bg-[#064e3b] text-emerald-400': formData.category === 'consumable',
                                            'bg-[#4c1d95] text-purple-400': formData.category === 'bahan_baku',
                                            'bg-[#7c2d12] text-orange-400': formData.category === 'variabel'
                                        }">
                                        <i class="fas text-sm"
                                        :class="{
                                            'fa-bolt': formData.category === 'operasional',
                                            'fa-box-open': formData.category === 'consumable',
                                            'fa-cube': formData.category === 'bahan_baku',
                                            'fa-car': formData.category === 'variabel'
                                        }"></i>
                                    </div>
                                    <span class="text-sm font-bold text-white" 
                                        x-text="formData.category === 'operasional' ? 'Operasional' : (formData.category === 'consumable' ? 'Consumable' : (formData.category === 'bahan_baku' ? 'Bahan Baku' : 'Variabel'))"></span>
                                </div>
                            </div>

                            <div>
                                <p class="text-[11px] text-slate-500 uppercase tracking-wider font-bold mb-1">Nama Biaya</p>
                                <p class="text-sm text-white font-medium" x-text="formData.name || '-'"></p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-[11px] text-slate-500 uppercase tracking-wider font-bold mb-1">Frekuensi</p>
                                    <p class="text-sm text-white font-medium flex items-center gap-2">
                                        <i class="far fa-calendar text-slate-400"></i>
                                        <span x-text="formData.frequency" class="capitalize"></span>
                                    </p>
                                </div>
                                <div>
                                    <p class="text-[11px] text-slate-500 uppercase tracking-wider font-bold mb-1">Tanggal Mulai</p>
                                    <p class="text-sm text-white font-medium flex items-center gap-2">
                                        <i class="far fa-calendar-alt text-slate-400"></i>
                                        <span x-text="formatDateIndo(formData.date)"></span>
                                    </p>
                                </div>
                            </div>

                            <div class="pt-4 border-t border-slate-700/50">
                                <p class="text-[11px] text-slate-500 uppercase tracking-wider font-bold mb-1">Nominal per periode</p>
                                <p class="text-lg text-white font-bold">Rp <span x-text="formatCurrency(formData.amount)"></span></p>
                            </div>

                            <div class="pt-2">
                                <p class="text-[11px] text-slate-500 uppercase tracking-wider font-bold mb-1">Estimasi per bulan</p>
                                <p class="text-lg text-blue-400 font-bold">Rp <span x-text="formatCurrency(estimatedMonthly)"></span></p>
                            </div>

                            <div class="pt-4 border-t border-slate-700/50" x-show="formData.payment_method">
                                <p class="text-[11px] text-slate-500 uppercase tracking-wider font-bold mb-1">Metode Pembayaran</p>
                                <div class="flex items-center gap-2 mt-1">
                                    <template x-if="formData.payment_method === 'tunai'">
                                        <div class="w-6 h-6 rounded-md bg-blue-900/50 flex items-center justify-center border border-blue-800/50">
                                            <i class="fas fa-wallet text-[10px] text-blue-400"></i>
                                        </div>
                                    </template>
                                    <template x-if="formData.payment_method === 'qris'">
                                        <div class="w-6 h-6 rounded-md bg-purple-900/50 flex items-center justify-center border border-purple-800/50">
                                            <i class="fas fa-qrcode text-[10px] text-purple-400"></i>
                                        </div>
                                    </template>
                                    <template x-if="formData.payment_method === 'bank'">
                                        <div class="w-6 h-6 rounded-md bg-emerald-900/50 flex items-center justify-center border border-emerald-800/50">
                                            <i class="fas fa-university text-[10px] text-emerald-400"></i>
                                        </div>
                                    </template>
                                    <p class="text-sm text-white font-medium" x-text="formData.payment_method === 'tunai' ? 'Tunai' : (formData.payment_method === 'qris' ? 'QRIS' : (formData.payment_method === 'bank' ? 'Transfer Bank' : '-'))"></p>
                                </div>
                            </div>
                            
                            <!-- Pengulangan Details -->
                            <div class="pt-4 border-t border-slate-700/50" x-show="formData.is_recurring">
                                <div class="flex items-center gap-2 mb-3">
                                    <p class="text-[11px] text-slate-500 uppercase tracking-wider font-bold">Pengulangan</p>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-400">Aktif</span>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2 text-[13px]">
                                        <i class="far fa-calendar-check text-slate-400 w-4 text-center"></i>
                                        <span class="text-white font-medium" x-text="'Mulai dari ' + formatDateIndo(formData.date)"></span>
                                    </div>
                                    <div class="flex items-center gap-2 text-[13px]">
                                        <i class="far fa-calendar-times text-slate-400 w-4 text-center"></i>
                                        <span class="text-white font-medium" x-text="formData.end_date ? 'Berakhir pada ' + formatDateIndo(formData.end_date) : 'Tanpa Batas'"></span>
                                    </div>
                                    <div class="flex items-center gap-2 text-[13px]">
                                        <i class="far fa-clock text-slate-400 w-4 text-center"></i>
                                        <span class="text-slate-400" x-text="'Jadwal berikutnya: ' + nextScheduleDate"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-[#4c1d95]/20 border border-purple-900/40 rounded-xl p-4 mt-2">
                                <div class="flex items-start gap-3">
                                    <i class="fas fa-info-circle text-purple-400 mt-0.5 text-sm"></i>
                                    <p class="text-[12px] text-purple-200 leading-relaxed">
                                        Pastikan semua informasi sudah benar sebelum disimpan, biaya akan tercatat secara otomatis sesuai jadwal.
                                    </p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- STEP 7 CONTENT (Selesai) -->



