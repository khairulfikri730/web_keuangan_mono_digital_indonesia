<div x-show="step === getTotalSteps() + 1" x-cloak x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                <!-- Left: Success Content -->
                <div class="lg:col-span-8 flex flex-col gap-6">
                    <!-- Main Success Card -->
                    <div class="bg-[#1e2336]/80 rounded-[2rem] border border-slate-700/50 p-12 shadow-2xl backdrop-blur-sm flex flex-col items-center justify-center text-center relative overflow-hidden">
                        
                        <!-- Confetti/Decorative Dots -->
                        <div class="absolute inset-0 pointer-events-none opacity-50">
                            <div class="absolute top-1/4 left-1/4 w-2 h-2 rounded-full bg-blue-500"></div>
                            <div class="absolute top-1/3 right-1/4 w-2 h-2 rounded-sm bg-emerald-400 rotate-45"></div>
                            <div class="absolute bottom-1/3 left-1/3 w-1.5 h-1.5 rounded-full bg-yellow-400"></div>
                            <div class="absolute bottom-1/4 right-1/3 w-2 h-2 rounded-sm bg-purple-500 -rotate-12"></div>
                            <div class="absolute top-1/2 left-1/5 w-2 h-2 rounded-full bg-pink-500"></div>
                        </div>

                        <!-- Success Icon -->
                        <div class="w-28 h-28 bg-emerald-500 rounded-full flex items-center justify-center shadow-[0_0_40px_rgba(16,185,129,0.3)] mb-8 relative z-10 animate-[bounce_1s_ease-in-out]">
                            <i class="fas fa-check text-5xl text-white"></i>
                        </div>

                        <h2 class="text-3xl font-bold text-white mb-3">Berhasil!</h2>
                        <p class="text-slate-400 text-base mb-6" x-text="formData.category === 'consumable' ? 'Bahan habis pakai berhasil disimpan.' : (formData.category === 'bahan_baku' ? 'Bahan baku berhasil disimpan.' : 'Biaya bulanan telah berhasil disimpan.')"></p>

                        <!-- ID Badge -->
                        <div class="px-4 py-2 bg-emerald-900/30 border border-emerald-500/30 rounded-lg mb-12">
                            <p class="text-sm font-bold text-emerald-400">ID Biaya: #EXP-2505-0007</p>
                        </div>

                        <!-- Action Cards -->
                        <div class="w-full">
                            <p class="text-white font-bold mb-4">Apa selanjutnya?</p>
                            <p class="text-sm text-slate-400 mb-6">Pilih aksi yang ingin Anda lakukan.</p>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- Action 1 -->
                                <button @click="window.location.reload()" class="bg-slate-800/40 border border-slate-700/60 rounded-xl p-5 hover:bg-slate-800/80 hover:border-slate-500 transition-all flex flex-col items-center text-center gap-3 group">
                                    <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-white group-hover:scale-110 transition-transform">
                                        <i class="fas fa-wallet text-sm"></i>
                                    </div>
                                    <div>
                                        <h5 class="text-sm font-bold text-white mb-1" x-text="formData.category === 'consumable' ? 'Tambah Bahan Lagi' : (formData.category === 'bahan_baku' ? 'Tambah Bahan Baku Lagi' : 'Tambah Biaya Lagi')"></h5>
                                        <p class="text-[11px] text-slate-400" x-text="formData.category === 'consumable' ? 'Catat pembelian consumable lain' : (formData.category === 'bahan_baku' ? 'Catat pembelian bahan baku lain' : 'Buat biaya bulanan baru')"></p>
                                    </div>
                                </button>
                                
                                <!-- Action 2 -->
                                <a href="{{ route('monthly_expenses.index') }}" class="bg-slate-800/40 border border-slate-700/60 rounded-xl p-5 hover:bg-slate-800/80 hover:border-slate-500 transition-all flex flex-col items-center text-center gap-3 group">
                                    <div class="w-10 h-10 rounded-full bg-emerald-600 flex items-center justify-center text-white group-hover:scale-110 transition-transform">
                                        <i class="fas fa-list text-sm"></i>
                                    </div>
                                    <div>
                                        <h5 class="text-sm font-bold text-white mb-1" x-text="formData.category === 'consumable' || formData.category === 'bahan_baku' ? 'Lihat Stok & Inventaris' : 'Lihat Daftar Biaya'"></h5>
                                        <p class="text-[11px] text-slate-400" x-text="formData.category === 'consumable' || formData.category === 'bahan_baku' ? 'Kelola persediaan barang' : 'Kelola semua biaya bulanan'"></p>
                                    </div>
                                </a>

                                <!-- Action 3 -->
                                <a href="{{ url('/') }}" class="bg-slate-800/40 border border-slate-700/60 rounded-xl p-5 hover:bg-slate-800/80 hover:border-slate-500 transition-all flex flex-col items-center text-center gap-3 group">
                                    <div class="w-10 h-10 rounded-full bg-purple-600 flex items-center justify-center text-white group-hover:scale-110 transition-transform">
                                        <i class="fas fa-home text-sm"></i>
                                    </div>
                                    <div>
                                        <h5 class="text-sm font-bold text-white mb-1">Kembali ke Dashboard</h5>
                                        <p class="text-[11px] text-slate-400">Lihat ringkasan keuangan</p>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Feedback Box -->
                    <div class="bg-[#1e2336]/80 rounded-[1.5rem] border border-slate-700/50 p-6 flex flex-col md:flex-row md:items-center justify-between gap-6 shadow-xl">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-xl bg-slate-800 flex items-center justify-center text-slate-400 shrink-0">
                                <i class="fas fa-star"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-white mb-1">Bantu kami jadi lebih baik!</h4>
                                <p class="text-[13px] text-slate-400">Apakah proses menambahkan biaya ini mudah digunakan?</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <button class="px-4 py-2 rounded-lg text-[13px] font-bold text-slate-300 hover:text-emerald-400 border border-slate-700 hover:border-emerald-500/50 transition-colors flex items-center gap-2">
                                <i class="far fa-thumbs-up"></i> Ya, mudah!
                            </button>
                            <button class="px-4 py-2 rounded-lg text-[13px] font-bold text-slate-300 hover:text-rose-400 border border-slate-700 hover:border-rose-500/50 transition-colors flex items-center gap-2">
                                <i class="far fa-thumbs-down"></i> Masih membingungkan
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Right: Summary Panel Step 7 -->
                <div class="lg:col-span-4">
                    <div class="bg-[#1e2336]/80 rounded-2xl border border-slate-700/50 p-6 shadow-xl sticky top-8">
                        <div class="flex items-center justify-between mb-6">
                            <h4 class="text-sm font-bold text-white">Ringkasan Biaya yang Disimpan</h4>
                            <span class="px-2 py-1 bg-emerald-500/20 text-emerald-400 text-[10px] font-bold rounded">Tersimpan</span>
                        </div>
                        
                        <div class="space-y-4">
                            <!-- Jenis Biaya -->
                            <div class="flex items-start gap-3">
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
                                <div>
                                    <p class="text-[11px] text-slate-500 uppercase font-bold mb-0.5">Jenis Biaya</p>
                                    <p class="text-sm font-bold text-white" x-text="formData.category === 'operasional' ? 'Operasional' : (formData.category === 'consumable' ? 'Consumable' : (formData.category === 'bahan_baku' ? 'Bahan Baku' : 'Variabel'))"></p>
                                </div>
                            </div>

                            <hr class="border-slate-700/50">

                            <!-- Nama Biaya -->
                            <div>
                                <p class="text-[11px] text-slate-500 uppercase font-bold mb-1">Nama Biaya</p>
                                <p class="text-[13px] text-white font-medium" x-text="formData.name || '-'"></p>
                            </div>

                            <!-- Deskripsi -->
                            <div>
                                <p class="text-[11px] text-slate-500 uppercase font-bold mb-1">Deskripsi</p>
                                <p class="text-[13px] text-white font-medium" x-text="formData.description || '-'"></p>
                            </div>

                            <hr class="border-slate-700/50">

                            <!-- Nominal -->
                            <div>
                                <p class="text-[11px] text-slate-500 uppercase font-bold mb-1">Nominal per periode</p>
                                <p class="text-base text-white font-bold">Rp <span x-text="formatCurrency(formData.amount)"></span></p>
                            </div>

                            <!-- Frekuensi & Tanggal -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-[11px] text-slate-500 uppercase font-bold mb-1">Frekuensi</p>
                                    <p class="text-[13px] text-white font-medium capitalize" x-text="formData.frequency === 'harian' ? 'Setiap Hari' : (formData.frequency === '2 harian' ? 'Setiap 2 Hari Sekali' : (formData.frequency === 'mingguan' ? 'Setiap Minggu' : 'Setiap Bulan'))"></p>
                                </div>
                                <div>
                                    <p class="text-[11px] text-slate-500 uppercase font-bold mb-1">Mulai dari</p>
                                    <p class="text-[13px] text-white font-medium" x-text="formatDateIndo(formData.date)"></p>
                                </div>
                            </div>

                            <!-- Metode Pembayaran -->
                            <div>
                                <p class="text-[11px] text-slate-500 uppercase font-bold mb-1">Metode Pembayaran</p>
                                <div class="flex items-center gap-2 mt-1">
                                    <template x-if="formData.payment_method === 'tunai'">
                                        <div class="w-5 h-5 rounded bg-blue-900/50 flex items-center justify-center border border-blue-800/50">
                                            <i class="fas fa-wallet text-[9px] text-blue-400"></i>
                                        </div>
                                    </template>
                                    <template x-if="formData.payment_method === 'qris'">
                                        <div class="w-5 h-5 rounded bg-purple-900/50 flex items-center justify-center border border-purple-800/50">
                                            <i class="fas fa-qrcode text-[9px] text-purple-400"></i>
                                        </div>
                                    </template>
                                    <template x-if="formData.payment_method === 'bank'">
                                        <div class="w-5 h-5 rounded bg-emerald-900/50 flex items-center justify-center border border-emerald-800/50">
                                            <i class="fas fa-university text-[9px] text-emerald-400"></i>
                                        </div>
                                    </template>
                                    <p class="text-[13px] text-white font-medium" x-text="formData.payment_method === 'tunai' ? 'Tunai' : (formData.payment_method === 'qris' ? 'QRIS' : (formData.payment_method === 'bank' ? 'Transfer Bank' : '-'))"></p>
                                </div>
                            </div>

                            <hr class="border-slate-700/50">

                            <!-- Estimasi -->
                            <div>
                                <p class="text-[11px] text-slate-500 uppercase font-bold mb-1">Estimasi per bulan</p>
                                <p class="text-base text-blue-400 font-bold">Rp <span x-text="formatCurrency(estimatedMonthly)"></span></p>
                            </div>

                            <!-- Info Box -->
                            <div class="bg-[#4c1d95]/20 border border-purple-900/40 rounded-xl p-4 mt-4" x-show="formData.is_recurring">
                                <div class="flex items-start gap-3">
                                    <i class="fas fa-info-circle text-purple-400 mt-0.5 text-sm"></i>
                                    <p class="text-[11px] text-purple-200 leading-relaxed">
                                        Biaya akan dicatat otomatis setiap <span x-text="formData.frequency === '2 harian' ? '2 hari sekali' : (formData.frequency === 'harian' ? 'hari' : (formData.frequency === 'mingguan' ? 'minggu' : 'bulan'))"></span> mulai dari <span x-text="formatDateIndo(formData.date)"></span>.
                                    </p>
                                </div>
                            </div>
                            
                            <div class="bg-blue-900/20 border border-blue-900/40 rounded-xl p-4 mt-4" x-show="!formData.is_recurring">
                                <div class="flex items-start gap-3">
                                    <i class="fas fa-info-circle text-blue-400 mt-0.5 text-sm"></i>
                                    <p class="text-[11px] text-blue-200 leading-relaxed">
                                        Biaya ini hanya akan dicatat sekali.
                                    </p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
