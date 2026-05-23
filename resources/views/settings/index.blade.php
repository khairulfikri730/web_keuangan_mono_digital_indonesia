@extends('layouts.app')

@section('title', 'Pengaturan Toko')
@section('page-title', 'Pengaturan Toko')
@section('page-subtitle', 'Konfigurasi profil usaha Anda')

@section('content')
<div class="max-w-4xl">
    <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf @method('PUT')

        {{-- Section: Informasi Toko --}}
        <div class="card overflow-hidden border border-slate-700/80 shadow-xl">
            <div class="p-6 border-b border-slate-700 bg-slate-800/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-500/20 flex items-center justify-center text-blue-400">
                        <i class="fas fa-store"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-white">Informasi Toko</h3>
                        <p class="text-xs text-slate-400">Identitas dan kontak utama bisnis Anda</p>
                    </div>
                </div>
            </div>
            <div class="p-6 lg:p-8 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Nama Toko <span class="text-red-400">*</span></label>
                        <input type="text" name="store_name" value="{{ $settings['store_name'] ?? '' }}" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 shadow-inner transition-all" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Mata Uang</label>
                        <input type="text" name="currency" value="{{ $settings['currency'] ?? 'IDR' }}" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-slate-400 text-sm cursor-not-allowed" readonly>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Nomor Telepon</label>
                        <input type="text" name="store_phone" value="{{ $settings['store_phone'] ?? '' }}" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 shadow-inner transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Email Usaha</label>
                        <input type="email" name="store_email" value="{{ $settings['store_email'] ?? '' }}" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 shadow-inner transition-all">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Alamat Lengkap</label>
                    <textarea name="store_address" rows="3" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 shadow-inner transition-all">{{ $settings['store_address'] ?? '' }}</textarea>
                </div>
            </div>
        </div>

        {{-- Section: Struk & Branding --}}
        <div class="card overflow-hidden border border-slate-700/80 shadow-xl">
            <div class="p-6 border-b border-slate-700 bg-slate-800/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-500/20 flex items-center justify-center text-purple-400">
                        <i class="fas fa-print"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-white">Struk & Branding</h3>
                        <p class="text-xs text-slate-400">Atur tampilan fisik nota belanja pelanggan</p>
                    </div>
                </div>
            </div>
            <div class="p-6 lg:p-8 space-y-6">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Logo Toko (Opsional)</label>
                    <div class="flex items-center gap-6">
                        @if(!empty($settings['store_logo']))
                        <div class="relative group">
                            <img src="{{ asset('storage/' . $settings['store_logo']) }}" class="h-20 w-20 object-contain rounded-2xl bg-white p-2 shadow-lg border border-slate-600 transition-transform group-hover:scale-105">
                        </div>
                        @endif
                        <div class="flex-1">
                            <input type="file" name="store_logo" accept="image/*" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-2.5 text-slate-400 text-sm file:mr-4 file:py-1.5 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-black file:bg-blue-600 file:text-white hover:file:bg-blue-500 file:transition-all cursor-pointer">
                            <p class="text-[10px] text-slate-500 mt-2 italic font-medium uppercase tracking-widest">Saran: Gunakan file PNG transparan (Max 2MB)</p>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Pesan Footer Struk</label>
                    <textarea name="store_footer" rows="2" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 shadow-inner transition-all" placeholder="Terima kasih atas kunjungan Anda!">{{ $settings['store_footer'] ?? '' }}</textarea>
                </div>
            </div>
        </div>

        {{-- Section: Keuangan & Pembayaran --}}
        <div class="card overflow-hidden border border-slate-700/80 shadow-xl">
            <div class="p-6 border-b border-slate-700 bg-slate-800/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/20 flex items-center justify-center text-emerald-400">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-white">Keuangan & Pembayaran</h3>
                        <p class="text-xs text-slate-400">Konfigurasi pajak dan metode bayar yang tersedia</p>
                    </div>
                </div>
            </div>
            <div class="p-6 lg:p-8 space-y-8">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Pajak / PPN (%)</label>
                    <div class="relative w-full sm:w-48">
                        <input type="number" step="0.1" min="0" max="100" name="tax_rate" value="{{ $settings['tax_rate'] ?? '0' }}" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl pl-4 pr-12 py-3 text-white font-bold text-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 shadow-inner transition-all">
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-500 font-bold text-lg">%</span>
                    </div>
                    <p class="text-[10px] text-slate-500 mt-2 font-medium uppercase tracking-wider">Otomatis ditambahkan ke total transaksi di POS</p>
                </div>

                @php
                    $activeMethods = json_decode($settings['active_payment_methods'] ?? '["cash","transfer","qris","debit"]', true) ?: [];
                    $methods = [
                        ['id' => 'cash', 'label' => 'Tunai (Cash)', 'icon' => 'fa-money-bill-wave'],
                        ['id' => 'transfer', 'label' => 'Transfer Bank', 'icon' => 'fa-university'],
                        ['id' => 'qris', 'label' => 'QRIS', 'icon' => 'fa-qrcode'],
                        ['id' => 'debit', 'label' => 'Kartu Debit', 'icon' => 'fa-credit-card'],
                    ];
                @endphp

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Metode Pembayaran Aktif</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($methods as $method)
                        <label class="flex items-center justify-between p-4 bg-slate-900/50 border border-slate-700 rounded-2xl cursor-pointer hover:border-blue-500/50 transition-all group">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-slate-800 flex items-center justify-center text-slate-400 group-hover:text-blue-400 transition-colors">
                                    <i class="fas {{ $method['icon'] }}"></i>
                                </div>
                                <span class="text-sm font-bold text-slate-200">{{ $method['label'] }}</span>
                            </div>
                            <div class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="active_payment_methods[]" value="{{ $method['id'] }}" class="sr-only peer" {{ in_array($method['id'], $activeMethods) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
                
                {{-- Cash Out Settings --}}
                <div class="border-t border-slate-700/50 pt-8 mt-6">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-4"><i class="fas fa-hand-holding-dollar mr-1"></i> Pengaturan Pengeluaran (Cash Out)</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Sumber Dana Diizinkan</label>
                            <select name="cashout_source_access" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-blue-500 shadow-inner transition-all">
                                <option value="cash_only" {{ ($settings['cashout_source_access'] ?? 'cash_only') == 'cash_only' ? 'selected' : '' }}>Hanya Tunai (Laci Kasir)</option>
                                <option value="bank_only" {{ ($settings['cashout_source_access'] ?? '') == 'bank_only' ? 'selected' : '' }}>Hanya Saldo Bank</option>
                                <option value="both" {{ ($settings['cashout_source_access'] ?? '') == 'both' ? 'selected' : '' }}>Tunai & Saldo Bank (Pilih Bebas)</option>
                            </select>
                            <p class="text-[10px] text-slate-500 mt-2 font-medium uppercase tracking-wider">Menentukan dari mana dana cash out dapat diambil di POS.</p>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Hak Akses Cash Out</label>
                            <select name="cashout_role_access" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-blue-500 shadow-inner transition-all">
                                <option value="all" {{ ($settings['cashout_role_access'] ?? 'all') == 'all' ? 'selected' : '' }}>Semua Kasir & Admin</option>
                                <option value="admin_owner" {{ ($settings['cashout_role_access'] ?? '') == 'admin_owner' ? 'selected' : '' }}>Hanya Admin & Owner</option>
                                <option value="owner" {{ ($settings['cashout_role_access'] ?? '') == 'owner' ? 'selected' : '' }}>Hanya Owner</option>
                            </select>
                            <p class="text-[10px] text-slate-500 mt-2 font-medium uppercase tracking-wider">Menentukan siapa saja yang boleh melakukan cash out di POS.</p>
                        </div>
                    </div>
                </div>

                {{-- Bank & QRIS Info --}}
                <div class="border-t border-slate-700/50 pt-8 mt-6">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-4"><i class="fas fa-building-columns mr-1"></i> Info Rekening Transfer</label>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Nama Bank</label>
                            <input type="text" name="bank_name" value="{{ $settings['bank_name'] ?? '' }}" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 shadow-inner transition-all" placeholder="BRI">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">No. Rekening</label>
                            <input type="text" name="bank_account" value="{{ $settings['bank_account'] ?? '' }}" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 shadow-inner transition-all" placeholder="1234567890">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Atas Nama</label>
                            <input type="text" name="bank_holder" value="{{ $settings['bank_holder'] ?? '' }}" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 shadow-inner transition-all" placeholder="NAMA PEMILIK">
                        </div>
                    </div>

                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-4"><i class="fas fa-qrcode mr-1"></i> Gambar QRIS</label>
                    <div class="flex items-center gap-6">
                        @if(!empty($settings['qris_image']))
                        <div class="relative group">
                            <img src="{{ asset('storage/' . $settings['qris_image']) }}" class="h-28 w-28 object-contain rounded-2xl bg-white p-2 shadow-lg border border-slate-600 transition-transform group-hover:scale-105">
                        </div>
                        @endif
                        <div class="flex-1">
                            <input type="file" name="qris_image" accept="image/*" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-2.5 text-slate-400 text-sm file:mr-4 file:py-1.5 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-black file:bg-purple-600 file:text-white hover:file:bg-purple-500 file:transition-all cursor-pointer">
                            <p class="text-[10px] text-slate-500 mt-2 italic font-medium uppercase tracking-widest">Upload gambar QR Code QRIS Anda (Max 2MB)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section: Pengaturan Printer --}}
        <div class="card overflow-hidden border border-slate-700/80 shadow-xl mt-6">
            <div class="p-6 border-b border-slate-700 bg-slate-800/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-500/20 flex items-center justify-center text-indigo-400">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-white">Pengaturan Printer</h3>
                        <p class="text-xs text-slate-400">Konfigurasi default untuk printer thermal POS</p>
                    </div>
                </div>
            </div>
            <div class="p-6 lg:p-8 space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    {{-- Lebar Kertas --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Lebar Kertas Default</label>
                        <div class="flex bg-slate-900/50 p-1.5 rounded-2xl border border-slate-700 w-full">
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" name="printer_paper_size" value="58mm" class="sr-only peer" {{ ($settings['printer_paper_size'] ?? '58mm') == '58mm' ? 'checked' : '' }}>
                                <div class="py-3 text-center rounded-xl text-xs font-black uppercase tracking-widest transition-all peer-checked:bg-blue-600 peer-checked:text-white text-slate-500">58mm</div>
                            </label>
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" name="printer_paper_size" value="80mm" class="sr-only peer" {{ ($settings['printer_paper_size'] ?? '') == '80mm' ? 'checked' : '' }}>
                                <div class="py-3 text-center rounded-xl text-xs font-black uppercase tracking-widest transition-all peer-checked:bg-blue-600 peer-checked:text-white text-slate-500">80mm</div>
                            </label>
                        </div>
                    </div>

                    {{-- Jarak Feed --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Jarak Bawah Struk (Feed)</label>
                        <div class="relative w-full">
                            <input type="number" name="printer_feed_lines" min="0" max="15" value="{{ $settings['printer_feed_lines'] ?? '0' }}" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white font-bold text-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 shadow-inner transition-all">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-500 font-bold text-xs uppercase tracking-widest">Baris</span>
                        </div>
                        <p class="text-[10px] text-slate-500 mt-2 font-medium uppercase tracking-wider">Jumlah baris kosong sebelum kertas dipotong</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Auto Print --}}
                    <label class="flex items-center justify-between p-5 bg-slate-900/50 border border-slate-700 rounded-2xl cursor-pointer hover:border-blue-500/50 transition-all group">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-slate-800 flex items-center justify-center text-slate-400 group-hover:text-blue-400 transition-colors">
                                <i class="fas fa-magic"></i>
                            </div>
                            <div>
                                <span class="text-sm font-bold text-slate-200 block">Auto Print</span>
                                <span class="text-[10px] text-slate-500 font-medium uppercase tracking-tight">Cetak otomatis setelah bayar</span>
                            </div>
                        </div>
                        <div class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="printer_auto_print" value="0">
                            <input type="checkbox" name="printer_auto_print" value="1" class="sr-only peer" {{ ($settings['printer_auto_print'] ?? '0') == '1' ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </div>
                    </label>

                    {{-- Font Small --}}
                    <label class="flex items-center justify-between p-5 bg-slate-900/50 border border-slate-700 rounded-2xl cursor-pointer hover:border-blue-500/50 transition-all group">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-slate-800 flex items-center justify-center text-slate-400 group-hover:text-blue-400 transition-colors">
                                <i class="fas fa-font"></i>
                            </div>
                            <div>
                                <span class="text-sm font-bold text-slate-200 block">Font Kecil</span>
                                <span class="text-[10px] text-slate-500 font-medium uppercase tracking-tight">Gunakan Font B (Compressed)</span>
                            </div>
                        </div>
                        <div class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="printer_font_small" value="0">
                            <input type="checkbox" name="printer_font_small" value="1" class="sr-only peer" {{ ($settings['printer_font_small'] ?? '0') == '1' ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </div>
                    </label>
                </div>

                <div class="border-t border-slate-700/50 pt-8 mt-4">
                    <label class="block text-xs font-bold text-indigo-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                        <i class="fas fa-microchip"></i> Hardware & Direct Print (RJ11 Drawer)
                    </label>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Nama Printer (Sesuai Control Panel)</label>
                            <input type="text" name="printer_name" value="{{ $settings['printer_name'] ?? 'POS-58' }}" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-blue-500 shadow-inner transition-all" placeholder="Contoh: POS-58 atau XP-80">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Jenis Koneksi</label>
                            <select name="printer_connection" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-blue-500 shadow-inner transition-all">
                                <option value="windows" {{ ($settings['printer_connection'] ?? 'windows') == 'windows' ? 'selected' : '' }}>Windows Print Connector (Shared)</option>
                                <option value="usb" {{ ($settings['printer_connection'] ?? '') == 'usb' ? 'selected' : '' }}>Direct USB / COM Port</option>
                                <option value="network" {{ ($settings['printer_connection'] ?? '') == 'network' ? 'selected' : '' }}>Network / IP Address</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Auto Open Drawer --}}
                        <label class="flex items-center justify-between p-5 bg-slate-900/50 border border-slate-700 rounded-2xl cursor-pointer hover:border-blue-500/50 transition-all group shadow-lg shadow-indigo-500/5">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-indigo-500/20 flex items-center justify-center text-indigo-400">
                                    <i class="fas fa-door-open"></i>
                                </div>
                                <div>
                                    <span class="text-sm font-bold text-slate-200 block italic">Auto Open Cash Drawer</span>
                                    <span class="text-[10px] text-indigo-500/80 font-black uppercase tracking-widest">Trigger via RJ11 Kabel</span>
                                </div>
                            </div>
                            <div class="relative inline-flex items-center cursor-pointer">
                                <input type="hidden" name="drawer_auto_open" value="0">
                                <input type="checkbox" name="drawer_auto_open" value="1" class="sr-only peer" {{ ($settings['drawer_auto_open'] ?? '0') == '1' ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                            </div>
                        </label>

                        {{-- Pulse Pin --}}
                        <div>
                            <div class="flex bg-slate-900/50 p-1.5 rounded-2xl border border-slate-700 w-full">
                                <div class="flex items-center px-4 text-[10px] font-black text-slate-500 uppercase tracking-widest">Pulse Pin:</div>
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" name="drawer_pulse_pin" value="0" class="sr-only peer" {{ ($settings['drawer_pulse_pin'] ?? '0') == '0' ? 'checked' : '' }}>
                                    <div class="py-2.5 text-center rounded-xl text-xs font-black uppercase tracking-widest transition-all peer-checked:bg-indigo-600 peer-checked:text-white text-slate-500">Pin 0</div>
                                </label>
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" name="drawer_pulse_pin" value="1" class="sr-only peer" {{ ($settings['drawer_pulse_pin'] ?? '') == '1' ? 'checked' : '' }}>
                                    <div class="py-2.5 text-center rounded-xl text-xs font-black uppercase tracking-widest transition-all peer-checked:bg-indigo-600 peer-checked:text-white text-slate-500">Pin 1</div>
                                </label>
                            </div>
                            <p class="text-[9px] text-slate-600 mt-2 italic px-2">Blueprint BP-ECO58D biasanya menggunakan Pin 0</p>
                        </div>
                    </div>

                    <div class="mt-6 pt-6 border-t border-slate-700/30 flex justify-start">
                        <button type="button" onclick="testCashDrawer()" class="flex items-center gap-3 px-6 py-3 bg-slate-800 hover:bg-slate-700 text-indigo-400 font-bold rounded-xl border border-indigo-500/30 transition-all hover:scale-105 active:scale-95 group">
                            <i class="fas fa-plug text-sm group-hover:rotate-12 transition-transform"></i>
                            <span class="text-xs uppercase tracking-widest">Test Buka Laci (Pulse)</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section: Pengaturan Harga Fleksibel --}}
        <div class="card overflow-hidden border border-slate-700/80 shadow-xl mt-6">
            <div class="p-6 border-b border-slate-700 bg-slate-800/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-orange-500/20 flex items-center justify-center text-orange-400">
                        <i class="fas fa-tags"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-white">Pengaturan Harga Fleksibel</h3>
                        <p class="text-xs text-slate-400">Izinkan kasir untuk merubah harga produk pada saat transaksi</p>
                    </div>
                </div>
            </div>
            <div class="p-6 lg:p-8 space-y-8">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Master Toggle --}}
                    <label class="flex items-center justify-between p-5 bg-slate-900/50 border border-slate-700 rounded-2xl cursor-pointer hover:border-orange-500/50 transition-all group">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-slate-800 flex items-center justify-center text-slate-400 group-hover:text-orange-400 transition-colors">
                                <i class="fas fa-toggle-on"></i>
                            </div>
                            <div>
                                <span class="text-sm font-bold text-slate-200 block">Aktifkan Harga Khusus</span>
                                <span class="text-[10px] text-slate-500 font-medium uppercase tracking-tight">Kasir bisa ubah harga jual di POS</span>
                            </div>
                        </div>
                        <div class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="custom_price_enabled" value="0">
                            <input type="checkbox" name="custom_price_enabled" value="1" class="sr-only peer" {{ ($settings['custom_price_enabled'] ?? '0') == '1' ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-500"></div>
                        </div>
                    </label>

                    {{-- Allow HPP Custom --}}
                    <label class="flex items-center justify-between p-5 bg-slate-900/50 border border-slate-700 rounded-2xl cursor-pointer hover:border-orange-500/50 transition-all group">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-slate-800 flex items-center justify-center text-slate-400 group-hover:text-orange-400 transition-colors">
                                <i class="fas fa-calculator"></i>
                            </div>
                            <div>
                                <span class="text-sm font-bold text-slate-200 block">Izinkan HPP Custom</span>
                                <span class="text-[10px] text-slate-500 font-medium uppercase tracking-tight">Kasir bisa tentukan modal (HPP) khusus</span>
                            </div>
                        </div>
                        <div class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="custom_price_allow_hpp" value="0">
                            <input type="checkbox" name="custom_price_allow_hpp" value="1" class="sr-only peer" {{ ($settings['custom_price_allow_hpp'] ?? '0') == '1' ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-500"></div>
                        </div>
                    </label>

                    {{-- Show Badge --}}
                    <label class="flex items-center justify-between p-5 bg-slate-900/50 border border-slate-700 rounded-2xl cursor-pointer hover:border-orange-500/50 transition-all group">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-slate-800 flex items-center justify-center text-slate-400 group-hover:text-orange-400 transition-colors">
                                <i class="fas fa-certificate"></i>
                            </div>
                            <div>
                                <span class="text-sm font-bold text-slate-200 block">Tampilkan Tombol</span>
                                <span class="text-[10px] text-slate-500 font-medium uppercase tracking-tight">Badge Harga Khusus di grid produk</span>
                            </div>
                        </div>
                        <div class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="custom_price_show_badge" value="0">
                            <input type="checkbox" name="custom_price_show_badge" value="1" class="sr-only peer" {{ ($settings['custom_price_show_badge'] ?? '1') == '1' ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-500"></div>
                        </div>
                    </label>

                    {{-- Require Reason --}}
                    <label class="flex items-center justify-between p-5 bg-slate-900/50 border border-slate-700 rounded-2xl cursor-pointer hover:border-orange-500/50 transition-all group">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-slate-800 flex items-center justify-center text-slate-400 group-hover:text-orange-400 transition-colors">
                                <i class="fas fa-comment-dots"></i>
                            </div>
                            <div>
                                <span class="text-sm font-bold text-slate-200 block">Wajib Isi Alasan</span>
                                <span class="text-[10px] text-slate-500 font-medium uppercase tracking-tight">Wajibkan isi alasan ubah harga</span>
                            </div>
                        </div>
                        <div class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="custom_price_require_reason" value="0">
                            <input type="checkbox" name="custom_price_require_reason" value="1" class="sr-only peer" {{ ($settings['custom_price_require_reason'] ?? '0') == '1' ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-500"></div>
                        </div>
                    </label>
                </div>

                {{-- Access Role --}}
                <div class="w-full">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Hak Akses Fitur Harga Khusus</label>
                    <select name="custom_price_access" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-orange-500 shadow-inner transition-all">
                        <option value="all" {{ ($settings['custom_price_access'] ?? 'all') == 'all' ? 'selected' : '' }}>Semua Kasir & Admin</option>
                        <option value="admin_owner" {{ ($settings['custom_price_access'] ?? '') == 'admin_owner' ? 'selected' : '' }}>Hanya Admin & Owner</option>
                        <option value="owner" {{ ($settings['custom_price_access'] ?? '') == 'owner' ? 'selected' : '' }}>Hanya Owner</option>
                    </select>
                </div>
                
            </div>
        </div>

        {{-- Section: Preset Ongkos Kirim --}}
        <div x-data="deliveryPresets()" class="card overflow-hidden border border-slate-700/80 shadow-xl mt-6">
            <div class="p-6 border-b border-slate-700 bg-slate-800/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-orange-500/20 flex items-center justify-center text-orange-400">
                        <i class="fas fa-motorcycle"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-white">Preset Ongkos Kirim</h3>
                        <p class="text-xs text-slate-400">Buat daftar zona ongkir. Kasir bisa pilih preset atau ketik manual saat mode Delivery aktif.</p>
                    </div>
                </div>
            </div>
            <div class="p-6 lg:p-8 space-y-4">
                <input type="hidden" name="delivery_presets" :value="JSON.stringify(presets)">

                <!-- Existing Presets -->
                <div class="space-y-3 mb-6">
                    <template x-for="(preset, index) in presets" :key="index">
                        <div class="flex items-center justify-between p-4 bg-orange-500/5 border border-orange-500/20 rounded-xl">
                            <div>
                                <h4 class="font-bold text-white text-sm" x-text="preset.name"></h4>
                                <p class="text-orange-400 font-black text-xs" x-text="'Rp ' + formatCurrency(preset.price)"></p>
                            </div>
                            <button type="button" @click="removePreset(index)" class="text-red-400 hover:text-red-300 transition-colors p-2">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </template>
                    <div x-show="presets.length === 0" class="text-center p-6 border border-slate-700 border-dashed rounded-xl text-slate-500 text-sm">
                        Belum ada preset ongkos kirim
                    </div>
                </div>

                <!-- Add New Preset -->
                <div class="flex items-end gap-3 pt-4 border-t border-slate-700/50">
                    <div class="flex-1">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Nama Zona</label>
                        <input type="text" x-model="newName" placeholder="Contoh: Dalam Kota" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-orange-500 transition-all">
                    </div>
                    <div class="w-32">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Harga (Rp)</label>
                        <input type="number" x-model="newPrice" placeholder="10000" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-orange-500 transition-all">
                    </div>
                    <button type="button" @click="addPreset()" class="bg-orange-500 hover:bg-orange-600 text-white font-bold px-4 py-2.5 rounded-xl transition-all shadow-lg shadow-orange-500/20 whitespace-nowrap">
                        <i class="fas fa-plus mr-1"></i> Tambah
                    </button>
                </div>
            </div>
        </div>

        @push('scripts')
        <script>
            function testCashDrawer() {
                Swal.fire({
                    title: 'Testing Cash Drawer...',
                    text: 'Pastikan printer terhubung dan drawer tersambung via RJ11',
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Trigger Pulse',
                    confirmButtonColor: '#4f46e5',
                    cancelButtonColor: '#334155',
                    background: '#1e293b',
                    color: '#f8fafc'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch("{{ route('settings.test-drawer') }}", {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    title: 'Berhasil!',
                                    text: data.message,
                                    icon: 'success',
                                    background: '#1e293b',
                                    color: '#f8fafc'
                                });
                            } else {
                                Swal.fire({
                                    title: 'Gagal!',
                                    text: data.message,
                                    icon: 'error',
                                    background: '#1e293b',
                                    color: '#f8fafc'
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                title: 'Error Sistem!',
                                text: 'Terjadi kesalahan pada server atau printer offline.',
                                icon: 'error',
                                background: '#1e293b',
                                color: '#f8fafc'
                            });
                        });
                    }
                });
            }

            document.addEventListener('alpine:init', () => {
                Alpine.data('deliveryPresets', () => ({
                    presets: {!! $settings['delivery_presets'] ?? '[]' !!},
                    newName: '',
                    newPrice: '',
                    
                    addPreset() {
                        if (this.newName.trim() === '' || this.newPrice === '') return;
                        this.presets.push({
                            name: this.newName,
                            price: parseInt(this.newPrice)
                        });
                        this.newName = '';
                        this.newPrice = '';
                    },
                    removePreset(index) {
                        this.presets.splice(index, 1);
                    },
                    formatCurrency(value) {
                        return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                    }
                }));
            });
        </script>
        @endpush

        {{-- Footer Save Button --}}
        <div class="flex justify-end pt-4">
            <button type="submit" class="bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-500 hover:to-blue-400 text-white font-black px-10 py-4 rounded-2xl transition-all shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 hover:scale-105 active:scale-95 flex items-center gap-3 uppercase tracking-[0.1em] text-sm">
                <i class="fas fa-save text-lg"></i> Simpan Semua Pengaturan
            </button>
        </div>
    </form>

</div>
@endsection
