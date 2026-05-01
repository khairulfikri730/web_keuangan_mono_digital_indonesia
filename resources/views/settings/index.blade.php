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
            </div>
        </div>

        {{-- Footer Save Button --}}
        <div class="flex justify-end pt-4">
            <button type="submit" class="bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-500 hover:to-blue-400 text-white font-black px-10 py-4 rounded-2xl transition-all shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 hover:scale-105 active:scale-95 flex items-center gap-3 uppercase tracking-[0.1em] text-sm">
                <i class="fas fa-save text-lg"></i> Simpan Semua Pengaturan
            </button>
        </div>
    </form>
</div>
@endsection
