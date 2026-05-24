@extends('layouts.app')

@section('title', 'Pengaturan Toko')
@section('page-title', 'Pengaturan Toko')
@section('page-subtitle', 'Konfigurasi profil usaha Anda')

@section('content')
<div x-data="{ scrolled: false }" x-init="window.addEventListener('scroll', () => { scrolled = window.scrollY > 200 })" class="space-y-6">
    <form id="settings-form" action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT')

        {{-- Sticky Save Button --}}
        <div x-show="scrolled" x-transition class="fixed top-4 right-4 z-[110]">
            <button type="submit" class="bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-500 hover:to-blue-400 text-white font-black px-6 py-3 rounded-2xl transition-all shadow-2xl shadow-blue-500/40 flex items-center gap-2 text-xs uppercase tracking-wider active:scale-95">
                <i class="fas fa-save"></i> Simpan
            </button>
        </div>

        {{-- ROW 1: Informasi Toko + Keuangan --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Informasi Toko --}}
            <div class="card overflow-hidden border border-slate-700/80 shadow-xl rounded-2xl">
                <div class="p-5 border-b border-slate-700 bg-slate-800/50">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-500/20 flex items-center justify-center text-blue-400"><i class="fas fa-store"></i></div>
                        <div>
                            <h3 class="text-base font-black text-white">Informasi Toko</h3>
                            <p class="text-[10px] text-slate-400">Identitas dan kontak utama bisnis</p>
                        </div>
                    </div>
                </div>
                <div class="p-5 space-y-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Nama Toko <span class="text-red-400">*</span></label>
                        <input type="text" name="store_name" value="{{ $settings['store_name'] ?? '' }}" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 shadow-inner transition-all" required>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Mata Uang</label>
                            <input type="text" name="currency" value="{{ $settings['currency'] ?? 'IDR' }}" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-slate-400 text-sm cursor-not-allowed" readonly>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Nomor Telepon</label>
                            <input type="text" name="store_phone" value="{{ $settings['store_phone'] ?? '' }}" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 shadow-inner transition-all">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Email Usaha</label>
                        <input type="email" name="store_email" value="{{ $settings['store_email'] ?? '' }}" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 shadow-inner transition-all">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Alamat Lengkap</label>
                        <textarea name="store_address" rows="2" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 shadow-inner transition-all">{{ $settings['store_address'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Keuangan & Pembayaran --}}
            <div class="card overflow-hidden border border-slate-700/80 shadow-xl rounded-2xl">
                <div class="p-5 border-b border-slate-700 bg-slate-800/50">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/20 flex items-center justify-center text-emerald-400"><i class="fas fa-wallet"></i></div>
                        <div>
                            <h3 class="text-base font-black text-white">Keuangan & Pembayaran</h3>
                            <p class="text-[10px] text-slate-400">Pajak, metode bayar, rekening</p>
                        </div>
                    </div>
                </div>
                <div class="p-5 space-y-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Pajak / PPN (%)</label>
                        <div class="relative w-full sm:w-48">
                            <input type="number" step="0.1" min="0" max="100" name="tax_rate" value="{{ $settings['tax_rate'] ?? '0' }}" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl pl-4 pr-12 py-2.5 text-white font-bold text-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 shadow-inner transition-all">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-500 font-bold text-lg">%</span>
                        </div>
                        <p class="text-[9px] text-slate-500 mt-1 font-medium">Otomatis ditambahkan ke total transaksi POS</p>
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
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Metode Pembayaran Aktif</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach($methods as $method)
                            <label class="flex items-center justify-between p-3 bg-slate-900/50 border border-slate-700 rounded-xl cursor-pointer hover:border-blue-500/50 transition-all">
                                <div class="flex items-center gap-2">
                                    <i class="fas {{ $method['icon'] }} text-slate-400 text-sm w-5 text-center"></i>
                                    <span class="text-xs font-bold text-slate-200">{{ $method['label'] }}</span>
                                </div>
                                <div class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="active_payment_methods[]" value="{{ $method['id'] }}" class="sr-only peer" {{ in_array($method['id'], $activeMethods) ? 'checked' : '' }}>
                                    <div class="w-10 h-5 bg-slate-700 rounded-full peer-checked:bg-blue-600 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-5"></div>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="border-t border-slate-700/50 pt-4">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2"><i class="fas fa-hand-holding-dollar mr-1"></i> Pengaturan Cash Out</label>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[9px] font-bold text-slate-500 uppercase mb-1">Sumber Dana</label>
                                <select name="cashout_source_access" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-2 text-white text-xs focus:outline-none focus:border-blue-500 transition-all">
                                    <option value="cash_only" {{ ($settings['cashout_source_access'] ?? 'cash_only') == 'cash_only' ? 'selected' : '' }}>Hanya Tunai</option>
                                    <option value="bank_only" {{ ($settings['cashout_source_access'] ?? '') == 'bank_only' ? 'selected' : '' }}>Hanya Bank</option>
                                    <option value="both" {{ ($settings['cashout_source_access'] ?? '') == 'both' ? 'selected' : '' }}>Tunai & Bank</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[9px] font-bold text-slate-500 uppercase mb-1">Hak Akses</label>
                                <select name="cashout_role_access" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-2 text-white text-xs focus:outline-none focus:border-blue-500 transition-all">
                                    <option value="all" {{ ($settings['cashout_role_access'] ?? 'all') == 'all' ? 'selected' : '' }}>Semua</option>
                                    <option value="admin_owner" {{ ($settings['cashout_role_access'] ?? '') == 'admin_owner' ? 'selected' : '' }}>Admin & Owner</option>
                                    <option value="owner" {{ ($settings['cashout_role_access'] ?? '') == 'owner' ? 'selected' : '' }}>Hanya Owner</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-slate-700/50 pt-4">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2"><i class="fas fa-building-columns mr-1"></i> Info Rekening Transfer</label>
                        <div class="grid grid-cols-3 gap-2 mb-3">
                            <div>
                                <label class="block text-[9px] font-bold text-slate-500 uppercase mb-1">Bank</label>
                                <input type="text" name="bank_name" value="{{ $settings['bank_name'] ?? '' }}" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-2 text-white text-xs focus:outline-none focus:border-blue-500 transition-all" placeholder="BRI">
                            </div>
                            <div>
                                <label class="block text-[9px] font-bold text-slate-500 uppercase mb-1">No. Rekening</label>
                                <input type="text" name="bank_account" value="{{ $settings['bank_account'] ?? '' }}" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-2 text-white text-xs focus:outline-none focus:border-blue-500 transition-all" placeholder="1234567890">
                            </div>
                            <div>
                                <label class="block text-[9px] font-bold text-slate-500 uppercase mb-1">Atas Nama</label>
                                <input type="text" name="bank_holder" value="{{ $settings['bank_holder'] ?? '' }}" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-2 text-white text-xs focus:outline-none focus:border-blue-500 transition-all" placeholder="NAMA PEMILIK">
                            </div>
                        </div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2"><i class="fas fa-qrcode mr-1"></i> Gambar QRIS</label>
                        <div class="flex items-center gap-4">
                            @if(!empty($settings['qris_image']))
                            <img src="{{ asset('storage/' . $settings['qris_image']) }}" class="h-20 w-20 object-contain rounded-xl bg-white p-1.5 shadow">
                            @endif
                            <div class="flex-1">
                                <input type="file" name="qris_image" accept="image/*" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-2 text-slate-400 text-xs file:mr-3 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-[10px] file:font-black file:bg-purple-600 file:text-white hover:file:bg-purple-500 file:transition-all cursor-pointer">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ROW 2: Struk + Printer --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Struk & Branding --}}
            <div class="card overflow-hidden border border-slate-700/80 shadow-xl rounded-2xl">
                <div class="p-5 border-b border-slate-700 bg-slate-800/50">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-purple-500/20 flex items-center justify-center text-purple-400"><i class="fas fa-print"></i></div>
                        <div>
                            <h3 class="text-base font-black text-white">Struk & Branding</h3>
                            <p class="text-[10px] text-slate-400">Tampilan fisik nota belanja</p>
                        </div>
                    </div>
                </div>
                <div class="p-5 space-y-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Logo Toko</label>
                        <div class="flex items-center gap-4">
                            @if(!empty($settings['store_logo']))
                            <img src="{{ asset('storage/' . $settings['store_logo']) }}" class="h-16 w-16 object-contain rounded-xl bg-white p-1.5 shadow border border-slate-600">
                            @endif
                            <div class="flex-1">
                                <input type="file" name="store_logo" accept="image/*" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-2 text-slate-400 text-xs file:mr-3 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-[10px] file:font-black file:bg-blue-600 file:text-white hover:file:bg-blue-500 file:transition-all cursor-pointer">
                                <p class="text-[9px] text-slate-500 mt-1">PNG transparan, Max 2MB</p>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Pesan Footer Struk</label>
                        <textarea name="store_footer" rows="2" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 shadow-inner transition-all" placeholder="Terima kasih atas kunjungan Anda!">{{ $settings['store_footer'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Printer --}}
            <div class="card overflow-hidden border border-slate-700/80 shadow-xl rounded-2xl">
                <div class="p-5 border-b border-slate-700 bg-slate-800/50">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-500/20 flex items-center justify-center text-indigo-400"><i class="fas fa-bolt"></i></div>
                        <div>
                            <h3 class="text-base font-black text-white">Pengaturan Printer</h3>
                            <p class="text-[10px] text-slate-400">Konfigurasi printer thermal POS</p>
                        </div>
                    </div>
                </div>
                <div class="p-5 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Lebar Kertas</label>
                            <div class="flex bg-slate-900/50 p-1 rounded-xl border border-slate-700">
                                <label class="flex-1 cursor-pointer"><input type="radio" name="printer_paper_size" value="58mm" class="sr-only peer" {{ ($settings['printer_paper_size'] ?? '58mm') == '58mm' ? 'checked' : '' }}><div class="py-2 text-center rounded-lg text-[10px] font-black uppercase peer-checked:bg-blue-600 peer-checked:text-white text-slate-500">58mm</div></label>
                                <label class="flex-1 cursor-pointer"><input type="radio" name="printer_paper_size" value="80mm" class="sr-only peer" {{ ($settings['printer_paper_size'] ?? '') == '80mm' ? 'checked' : '' }}><div class="py-2 text-center rounded-lg text-[10px] font-black uppercase peer-checked:bg-blue-600 peer-checked:text-white text-slate-500">80mm</div></label>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Feed (Baris Kosong)</label>
                            <div class="relative">
                                <input type="number" name="printer_feed_lines" min="0" max="15" value="{{ $settings['printer_feed_lines'] ?? '0' }}" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-2 text-white font-bold text-sm focus:outline-none focus:border-blue-500 transition-all">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 text-[10px] font-bold">Baris</span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center justify-between p-3 bg-slate-900/50 border border-slate-700 rounded-xl cursor-pointer hover:border-blue-500/50 transition-all">
                            <div><span class="text-xs font-bold text-slate-200">Auto Print</span><p class="text-[9px] text-slate-500">Cetak otomatis</p></div>
                            <input type="hidden" name="printer_auto_print" value="0"><input type="checkbox" name="printer_auto_print" value="1" class="sr-only peer" {{ ($settings['printer_auto_print'] ?? '0') == '1' ? 'checked' : '' }}><div class="w-10 h-5 bg-slate-700 rounded-full peer-checked:bg-blue-600 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-5 relative"></div>
                        </label>
                        <label class="flex items-center justify-between p-3 bg-slate-900/50 border border-slate-700 rounded-xl cursor-pointer hover:border-blue-500/50 transition-all">
                            <div><span class="text-xs font-bold text-slate-200">Font Kecil</span><p class="text-[9px] text-slate-500">Compressed</p></div>
                            <input type="hidden" name="printer_font_small" value="0"><input type="checkbox" name="printer_font_small" value="1" class="sr-only peer" {{ ($settings['printer_font_small'] ?? '0') == '1' ? 'checked' : '' }}><div class="w-10 h-5 bg-slate-700 rounded-full peer-checked:bg-blue-600 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-5 relative"></div>
                        </label>
                    </div>

                    <div class="border-t border-slate-700/50 pt-4">
                        <label class="block text-[10px] font-bold text-indigo-400 uppercase tracking-wider mb-3"><i class="fas fa-microchip mr-1"></i> Hardware & Direct Print</label>
                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div>
                                <label class="block text-[9px] font-bold text-slate-500 uppercase mb-1">Nama Printer</label>
                                <input type="text" name="printer_name" value="{{ $settings['printer_name'] ?? 'POS-58' }}" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-2 text-white text-xs focus:outline-none focus:border-blue-500 transition-all" placeholder="POS-58">
                            </div>
                            <div>
                                <label class="block text-[9px] font-bold text-slate-500 uppercase mb-1">Jenis Koneksi</label>
                                <select name="printer_connection" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-2 text-white text-xs focus:outline-none focus:border-blue-500 transition-all">
                                    <option value="windows" {{ ($settings['printer_connection'] ?? 'windows') == 'windows' ? 'selected' : '' }}>Windows Shared</option>
                                    <option value="usb" {{ ($settings['printer_connection'] ?? '') == 'usb' ? 'selected' : '' }}>Direct USB/COM</option>
                                    <option value="network" {{ ($settings['printer_connection'] ?? '') == 'network' ? 'selected' : '' }}>Network/IP</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="flex items-center justify-between p-3 bg-slate-900/50 border border-slate-700 rounded-xl cursor-pointer hover:border-indigo-500/50 transition-all">
                                <div><span class="text-xs font-bold text-slate-200">Auto Open Drawer</span><p class="text-[9px] text-indigo-400">RJ11 Trigger</p></div>
                                <input type="hidden" name="drawer_auto_open" value="0"><input type="checkbox" name="drawer_auto_open" value="1" class="sr-only peer" {{ ($settings['drawer_auto_open'] ?? '0') == '1' ? 'checked' : '' }}><div class="w-10 h-5 bg-slate-700 rounded-full peer-checked:bg-indigo-600 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-5 relative"></div>
                            </label>
                            <div>
                                <label class="block text-[9px] font-bold text-slate-500 uppercase mb-1">Pulse Pin</label>
                                <div class="flex bg-slate-900/50 p-0.5 rounded-xl border border-slate-700">
                                    <label class="flex-1 cursor-pointer"><input type="radio" name="drawer_pulse_pin" value="0" class="sr-only peer" {{ ($settings['drawer_pulse_pin'] ?? '0') == '0' ? 'checked' : '' }}><div class="py-2 text-center rounded-lg text-[10px] font-black uppercase peer-checked:bg-indigo-600 peer-checked:text-white text-slate-500">Pin 0</div></label>
                                    <label class="flex-1 cursor-pointer"><input type="radio" name="drawer_pulse_pin" value="1" class="sr-only peer" {{ ($settings['drawer_pulse_pin'] ?? '') == '1' ? 'checked' : '' }}><div class="py-2 text-center rounded-lg text-[10px] font-black uppercase peer-checked:bg-indigo-600 peer-checked:text-white text-slate-500">Pin 1</div></label>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="button" onclick="testCashDrawer()" class="flex items-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-indigo-400 font-bold rounded-xl border border-indigo-500/30 transition-all text-[10px] uppercase tracking-wider">
                                <i class="fas fa-plug"></i> Test Buka Laci
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ROW 3: Harga Fleksibel (full width) --}}
        <div class="card overflow-hidden border border-slate-700/80 shadow-xl rounded-2xl">
            <div class="p-5 border-b border-slate-700 bg-slate-800/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-orange-500/20 flex items-center justify-center text-orange-400"><i class="fas fa-tags"></i></div>
                    <div>
                        <h3 class="text-base font-black text-white">Pengaturan Harga Fleksibel</h3>
                        <p class="text-[10px] text-slate-400">Izinkan kasir mengubah harga produk saat transaksi</p>
                    </div>
                </div>
            </div>
            <div class="p-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
                    <label class="flex items-center justify-between p-3 bg-slate-900/50 border border-slate-700 rounded-xl cursor-pointer hover:border-orange-500/50 transition-all">
                        <div><span class="text-xs font-bold text-slate-200">Aktifkan Harga Khusus</span></div>
                        <input type="hidden" name="custom_price_enabled" value="0"><input type="checkbox" name="custom_price_enabled" value="1" class="sr-only peer" {{ ($settings['custom_price_enabled'] ?? '0') == '1' ? 'checked' : '' }}><div class="w-10 h-5 bg-slate-700 rounded-full peer-checked:bg-orange-500 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-5 relative shrink-0 ml-2"></div>
                    </label>
                    <label class="flex items-center justify-between p-3 bg-slate-900/50 border border-slate-700 rounded-xl cursor-pointer hover:border-orange-500/50 transition-all">
                        <div><span class="text-xs font-bold text-slate-200">Izinkan HPP Custom</span></div>
                        <input type="hidden" name="custom_price_allow_hpp" value="0"><input type="checkbox" name="custom_price_allow_hpp" value="1" class="sr-only peer" {{ ($settings['custom_price_allow_hpp'] ?? '0') == '1' ? 'checked' : '' }}><div class="w-10 h-5 bg-slate-700 rounded-full peer-checked:bg-orange-500 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-5 relative shrink-0 ml-2"></div>
                    </label>
                    <label class="flex items-center justify-between p-3 bg-slate-900/50 border border-slate-700 rounded-xl cursor-pointer hover:border-orange-500/50 transition-all">
                        <div><span class="text-xs font-bold text-slate-200">Tampilkan Badge</span></div>
                        <input type="hidden" name="custom_price_show_badge" value="0"><input type="checkbox" name="custom_price_show_badge" value="1" class="sr-only peer" {{ ($settings['custom_price_show_badge'] ?? '1') == '1' ? 'checked' : '' }}><div class="w-10 h-5 bg-slate-700 rounded-full peer-checked:bg-orange-500 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-5 relative shrink-0 ml-2"></div>
                    </label>
                    <label class="flex items-center justify-between p-3 bg-slate-900/50 border border-slate-700 rounded-xl cursor-pointer hover:border-orange-500/50 transition-all">
                        <div><span class="text-xs font-bold text-slate-200">Wajib Isi Alasan</span></div>
                        <input type="hidden" name="custom_price_require_reason" value="0"><input type="checkbox" name="custom_price_require_reason" value="1" class="sr-only peer" {{ ($settings['custom_price_require_reason'] ?? '0') == '1' ? 'checked' : '' }}><div class="w-10 h-5 bg-slate-700 rounded-full peer-checked:bg-orange-500 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-5 relative shrink-0 ml-2"></div>
                    </label>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Hak Akses Fitur</label>
                    <select name="custom_price_access" class="w-full sm:w-64 bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-2 text-white text-xs focus:outline-none focus:border-orange-500 transition-all">
                        <option value="all" {{ ($settings['custom_price_access'] ?? 'all') == 'all' ? 'selected' : '' }}>Semua Kasir & Admin</option>
                        <option value="admin_owner" {{ ($settings['custom_price_access'] ?? '') == 'admin_owner' ? 'selected' : '' }}>Hanya Admin & Owner</option>
                        <option value="owner" {{ ($settings['custom_price_access'] ?? '') == 'owner' ? 'selected' : '' }}>Hanya Owner</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- ROW 4: Preset Ongkos Kirim (full width) --}}
        <div x-data="deliveryPresets()" class="card overflow-hidden border border-slate-700/80 shadow-xl rounded-2xl">
            <div class="p-5 border-b border-slate-700 bg-slate-800/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-orange-500/20 flex items-center justify-center text-orange-400"><i class="fas fa-motorcycle"></i></div>
                    <div>
                        <h3 class="text-base font-black text-white">Preset Ongkos Kirim</h3>
                        <p class="text-[10px] text-slate-400">Daftar zona ongkir. Kasir bisa pilih preset atau ketik manual saat mode Delivery.</p>
                    </div>
                </div>
            </div>
            <div class="p-5 space-y-3">
                <input type="hidden" name="delivery_presets" :value="JSON.stringify(presets)">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    <template x-for="(preset, index) in presets" :key="index">
                        <div class="flex items-center justify-between p-3 bg-orange-500/5 border border-orange-500/20 rounded-xl">
                            <div>
                                <h4 class="font-bold text-white text-xs" x-text="preset.name"></h4>
                                <p class="text-orange-400 font-black text-[11px]" x-text="'Rp ' + formatCurrency(preset.price)"></p>
                            </div>
                            <button type="button" @click="removePreset(index)" class="text-red-400 hover:text-red-300 transition-colors p-1"><i class="fas fa-trash-alt text-xs"></i></button>
                        </div>
                    </template>
                    <div x-show="presets.length === 0" class="col-span-full text-center p-4 border border-slate-700 border-dashed rounded-xl text-slate-500 text-xs">Belum ada preset ongkos kirim</div>
                </div>
                <div class="flex items-end gap-3 pt-3 border-t border-slate-700/50">
                    <div class="flex-1">
                        <label class="block text-[9px] font-bold text-slate-500 uppercase mb-1">Nama Zona</label>
                        <input type="text" x-model="newName" placeholder="Contoh: Dalam Kota" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-2 text-white text-xs focus:outline-none focus:border-orange-500 transition-all">
                    </div>
                    <div class="w-28">
                        <label class="block text-[9px] font-bold text-slate-500 uppercase mb-1">Harga (Rp)</label>
                        <input type="number" x-model="newPrice" placeholder="10000" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-2 text-white text-xs focus:outline-none focus:border-orange-500 transition-all">
                    </div>
                    <button type="button" @click="addPreset()" class="bg-orange-500 hover:bg-orange-600 text-white font-bold px-3 py-2 rounded-xl transition-all shadow-lg shadow-orange-500/20 text-xs whitespace-nowrap">
                        <i class="fas fa-plus mr-1"></i> Tambah
                    </button>
                </div>
            </div>
        </div>

    </form>

    {{-- Danger Zone --}}
    <div class="card overflow-hidden border border-red-500/30 shadow-xl shadow-red-500/10 rounded-2xl bg-red-950/10 backdrop-blur-md">
        <div class="p-5 border-b border-red-500/20 bg-red-900/20">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-red-500/20 flex items-center justify-center text-red-500"><i class="fas fa-triangle-exclamation"></i></div>
                <div>
                    <h3 class="text-base font-black text-red-500">Danger Zone — Reset Sistem</h3>
                    <p class="text-[10px] text-red-400/80">Tindakan di bawah ini akan menghapus data secara permanen</p>
                </div>
            </div>
        </div>
        <div class="p-5">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3">
                <div class="p-4 border border-red-500/20 rounded-xl bg-slate-900/40 hover:border-red-500/50 transition-all">
                    <h4 class="text-white font-black text-xs mb-2">Reset Semua Data</h4>
                    <p class="text-[10px] text-slate-500 mb-3 leading-relaxed">Hapus semua riwayat transaksi & katalog produk.</p>
                    <button type="button" onclick="confirmReset('all_data', 'Semua Data Transaksi & Katalog Produk')" class="w-full py-2 bg-red-500/10 hover:bg-red-500 text-red-500 hover:text-white border border-red-500/50 rounded-lg text-[10px] font-black uppercase tracking-wider transition-all"><i class="fas fa-trash-alt mr-1"></i> Reset Total</button>
                </div>
                <div class="p-4 border border-red-500/20 rounded-xl bg-slate-900/40 hover:border-red-500/50 transition-all">
                    <h4 class="text-white font-black text-xs mb-2">Semua Transaksi</h4>
                    <p class="text-[10px] text-slate-500 mb-3 leading-relaxed">Hapus riwayat transaksi, invoice, arus kas, shift. Produk tetap aman.</p>
                    <button type="button" onclick="confirmReset('all_transactions', 'Semua Riwayat Transaksi')" class="w-full py-2 bg-red-500/10 hover:bg-red-500 text-red-500 hover:text-white border border-red-500/50 rounded-lg text-[10px] font-black uppercase tracking-wider transition-all"><i class="fas fa-receipt mr-1"></i> Hapus Transaksi</button>
                </div>
                <div class="p-4 border border-amber-500/20 rounded-xl bg-slate-900/40 hover:border-amber-500/50 transition-all">
                    <h4 class="text-white font-black text-xs mb-2">Pemasukan Saja</h4>
                    <p class="text-[10px] text-slate-500 mb-3 leading-relaxed">Hapus penjualan, arus kas masuk, invoice.</p>
                    <button type="button" onclick="confirmReset('income_only', 'Hanya Data Pemasukan')" class="w-full py-2 bg-amber-500/10 hover:bg-amber-500 text-amber-500 hover:text-white border border-amber-500/50 rounded-lg text-[10px] font-black uppercase tracking-wider transition-all"><i class="fas fa-arrow-down mr-1"></i> Hapus Pemasukan</button>
                </div>
                <div class="p-4 border border-orange-500/20 rounded-xl bg-slate-900/40 hover:border-orange-500/50 transition-all">
                    <h4 class="text-white font-black text-xs mb-2">Pengeluaran Saja</h4>
                    <p class="text-[10px] text-slate-500 mb-3 leading-relaxed">Hapus pengeluaran bulanan & arus kas keluar.</p>
                    <button type="button" onclick="confirmReset('expense_only', 'Hanya Data Pengeluaran')" class="w-full py-2 bg-orange-500/10 hover:bg-orange-500 text-orange-500 hover:text-white border border-orange-500/50 rounded-lg text-[10px] font-black uppercase tracking-wider transition-all"><i class="fas fa-arrow-up mr-1"></i> Hapus Pengeluaran</button>
                </div>
                <div class="p-4 border border-purple-500/20 rounded-xl bg-slate-900/40 hover:border-purple-500/50 transition-all">
                    <h4 class="text-white font-black text-xs mb-2">Katalog Produk</h4>
                    <p class="text-[10px] text-slate-500 mb-3 leading-relaxed">Hapus semua data produk & kategori.</p>
                    <button type="button" onclick="confirmReset('product_catalog', 'Katalog Produk & Kategori')" class="w-full py-2 bg-purple-500/10 hover:bg-purple-500 text-purple-500 hover:text-white border border-purple-500/50 rounded-lg text-[10px] font-black uppercase tracking-wider transition-all"><i class="fas fa-box-open mr-1"></i> Hapus Produk</button>
                </div>
            </div>
        </div>
    </div>

    <form id="form-reset-system" action="{{ route('settings.reset') }}" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="reset_type" id="input-reset-type">
    </form>

    {{-- Footer Save --}}
    <div class="flex justify-end">
        <button type="submit" form="settings-form" class="bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-500 hover:to-blue-400 text-white font-black px-8 py-3.5 rounded-2xl transition-all shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 hover:scale-105 active:scale-95 flex items-center gap-2 uppercase tracking-wider text-sm">
            <i class="fas fa-save text-lg"></i> Simpan Semua Pengaturan
        </button>
    </div>

    @push('scripts')
    <script>
        function confirmReset(type, typeLabel) {
            Swal.fire({
                title: 'PERINGATAN BAHAYA!',
                html: `Anda akan menghapus secara permanen:<br><b class="text-red-400 mt-2 block text-lg">${typeLabel}</b><br><span class="text-sm text-slate-400 mt-2 block">Tindakan ini tidak dapat dibatalkan. Ketik <strong>RESET</strong> di bawah ini untuk mengonfirmasi.</span>`,
                icon: 'warning',
                input: 'text',
                inputPlaceholder: 'Ketik RESET',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#334155',
                confirmButtonText: 'Ya, Hapus Permanen',
                cancelButtonText: 'Batal',
                background: '#1e293b',
                color: '#f8fafc',
                inputValidator: (value) => {
                    if (value !== 'RESET') {
                        return 'Anda harus mengetik RESET dengan huruf kapital!';
                    }
                }
            }).then((result) => {
                if (result.isConfirmed && result.value === 'RESET') {
                    document.getElementById('input-reset-type').value = type;
                    document.getElementById('form-reset-system').submit();
                    Swal.fire({
                        title: 'Memproses...',
                        text: 'Sedang menghapus data, mohon tunggu.',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); },
                        background: '#1e293b',
                        color: '#f8fafc'
                    });
                }
            });
        }

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
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                    })
                    .then(r => r.json())
                    .then(data => {
                        Swal.fire({ title: data.success ? 'Berhasil!' : 'Gagal!', text: data.message, icon: data.success ? 'success' : 'error', background: '#1e293b', color: '#f8fafc' });
                    })
                    .catch(() => {
                        Swal.fire({ title: 'Error!', text: 'Kesalahan sistem atau printer offline.', icon: 'error', background: '#1e293b', color: '#f8fafc' });
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
                    this.presets.push({ name: this.newName, price: parseInt(this.newPrice) });
                    this.newName = '';
                    this.newPrice = '';
                },
                removePreset(index) { this.presets.splice(index, 1); },
                formatCurrency(value) { return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "."); }
            }));
        });
    </script>
    @endpush
</div>
@endsection
