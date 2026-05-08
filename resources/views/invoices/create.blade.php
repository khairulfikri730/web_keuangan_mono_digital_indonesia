@extends('layouts.app')

@section('title', 'Buat Invoice — MONOFRAME')

@section('page-title', 'Buat Invoice Baru')

@section('content')
<div class="h-full pb-10" x-data="invoiceGenerator()">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        {{-- LEFT COLUMN: FORM (7/12) --}}
        <div class="lg:col-span-7 space-y-6 overflow-y-auto custom-scrollbar pr-2 lg:max-h-[calc(100vh-140px)]">
            
            {{-- SECTION 1: INFO BISNIS --}}
            <div class="bg-slate-800/40 backdrop-blur-md border border-slate-700/50 rounded-[2rem] p-8 shadow-xl">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-12 h-12 rounded-2xl bg-blue-600/20 flex items-center justify-center text-blue-400">
                        <i class="fas fa-store"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-white uppercase tracking-widest">Info Bisnis</h3>
                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mt-0.5">Identitas pengirim invoice</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="col-span-2">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 ml-1">Nama Bisnis</label>
                        <input type="text" x-model="business.name" class="w-full bg-slate-900/50 border border-slate-700 rounded-2xl px-5 py-4 text-white text-sm focus:outline-none focus:border-blue-500 transition-all font-bold">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 ml-1">Email</label>
                        <input type="email" x-model="business.email" class="w-full bg-slate-900/50 border border-slate-700 rounded-2xl px-5 py-4 text-white text-sm focus:outline-none focus:border-blue-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 ml-1">WhatsApp</label>
                        <input type="text" x-model="business.phone" class="w-full bg-slate-900/50 border border-slate-700 rounded-2xl px-5 py-4 text-white text-sm focus:outline-none focus:border-blue-500 transition-all">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 ml-1">Alamat Bisnis</label>
                        <textarea x-model="business.address" rows="2" class="w-full bg-slate-900/50 border border-slate-700 rounded-2xl px-5 py-4 text-white text-sm focus:outline-none focus:border-blue-500 transition-all"></textarea>
                    </div>
                </div>
            </div>

            {{-- SECTION 2: DATA CLIENT --}}
            <div class="bg-slate-800/40 backdrop-blur-md border border-slate-700/50 rounded-[2rem] p-8 shadow-xl">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-600/20 flex items-center justify-center text-emerald-400">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-white uppercase tracking-widest">Data Client</h3>
                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mt-0.5">Identitas penerima tagihan</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 ml-1">Nama Client</label>
                        <input type="text" x-model="client.name" placeholder="John Doe" class="w-full bg-slate-900/50 border border-slate-700 rounded-2xl px-5 py-4 text-white text-sm focus:outline-none focus:border-emerald-500 transition-all font-bold">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 ml-1">Nama Usaha / PT</label>
                        <input type="text" x-model="client.company" placeholder="ABC Corp" class="w-full bg-slate-900/50 border border-slate-700 rounded-2xl px-5 py-4 text-white text-sm focus:outline-none focus:border-emerald-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 ml-1">Nomor HP</label>
                        <input type="text" x-model="client.phone" class="w-full bg-slate-900/50 border border-slate-700 rounded-2xl px-5 py-4 text-white text-sm focus:outline-none focus:border-emerald-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 ml-1">Email</label>
                        <input type="email" x-model="client.email" class="w-full bg-slate-900/50 border border-slate-700 rounded-2xl px-5 py-4 text-white text-sm focus:outline-none focus:border-emerald-500 transition-all">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 ml-1">Alamat Client</label>
                        <textarea x-model="client.address" rows="2" class="w-full bg-slate-900/50 border border-slate-700 rounded-2xl px-5 py-4 text-white text-sm focus:outline-none focus:border-emerald-500 transition-all"></textarea>
                    </div>
                </div>
            </div>

            {{-- SECTION 3: DETAIL INVOICE --}}
            <div class="bg-slate-800/40 backdrop-blur-md border border-slate-700/50 rounded-[2rem] p-8 shadow-xl">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-12 h-12 rounded-2xl bg-amber-600/20 flex items-center justify-center text-amber-400">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-white uppercase tracking-widest">Detail Invoice</h3>
                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mt-0.5">Penomoran dan tanggal</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 ml-1">No. Invoice</label>
                        <input type="text" x-model="invoiceNumber" class="w-full bg-slate-900/50 border border-slate-700 rounded-2xl px-5 py-4 text-white text-sm font-bold focus:outline-none focus:border-amber-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 ml-1">Tanggal</label>
                        <input type="date" x-model="date" class="w-full bg-slate-900/50 border border-slate-700 rounded-2xl px-5 py-4 text-white text-sm focus:outline-none focus:border-amber-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 ml-1">Jatuh Tempo</label>
                        <input type="date" x-model="dueDate" class="w-full bg-slate-900/50 border border-slate-700 rounded-2xl px-5 py-4 text-white text-sm focus:outline-none focus:border-amber-500 transition-all">
                    </div>
                </div>
            </div>

            {{-- SECTION 4: ITEM TAGIHAN --}}
            <div class="bg-slate-800/40 backdrop-blur-md border border-slate-700/50 rounded-[2rem] p-8 shadow-xl">
                <div class="flex justify-between items-center mb-8">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-purple-600/20 flex items-center justify-center text-purple-400">
                            <i class="fas fa-list-ul"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-white uppercase tracking-widest">Item Tagihan</h3>
                            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mt-0.5">Daftar layanan/produk</p>
                        </div>
                    </div>
                    <button @click="addItem()" class="px-5 py-3 bg-purple-600 hover:bg-purple-500 text-white text-[10px] font-black rounded-2xl transition-all shadow-xl shadow-purple-500/20 uppercase tracking-widest">
                        <i class="fas fa-plus mr-2"></i> Tambah Item
                    </button>
                </div>

                <div class="space-y-6">
                    <template x-for="(item, index) in items" :key="index">
                        <div class="grid grid-cols-12 gap-4 p-6 bg-slate-900/40 rounded-3xl border border-slate-700/30 relative group transition-all">
                            <div class="col-span-12 md:col-span-6">
                                <label class="block text-[9px] font-black text-slate-600 uppercase tracking-widest mb-2 ml-1">Layanan</label>
                                <input type="text" x-model="item.name" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white text-xs focus:outline-none focus:border-purple-500 transition-all">
                            </div>
                            <div class="col-span-4 md:col-span-2">
                                <label class="block text-[9px] font-black text-slate-600 uppercase tracking-widest mb-2 ml-1">Qty</label>
                                <input type="number" x-model.number="item.quantity" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white text-xs focus:outline-none focus:border-purple-500 transition-all text-center">
                            </div>
                            <div class="col-span-8 md:col-span-4">
                                <label class="block text-[9px] font-black text-slate-600 uppercase tracking-widest mb-2 ml-1">Harga</label>
                                <input type="number" x-model.number="item.price" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white text-xs focus:outline-none focus:border-purple-500 transition-all font-black">
                            </div>
                            <button @click="removeItem(index)" class="absolute -top-3 -right-3 w-8 h-8 bg-red-600 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all shadow-xl">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            {{-- SECTION 5: DP & DISKON --}}
            <div class="bg-slate-800/40 backdrop-blur-md border border-slate-700/50 rounded-[2rem] p-8 shadow-xl">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-12 h-12 rounded-2xl bg-cyan-600/20 flex items-center justify-center text-cyan-400">
                        <i class="fas fa-hand-holding-dollar"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-white uppercase tracking-widest">DP & Diskon</h3>
                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mt-0.5">Pengaturan pembayaran awal</p>
                    </div>
                </div>

                <div class="space-y-8">
                    <div class="flex items-center justify-between p-6 bg-slate-900/40 rounded-3xl border border-slate-700/30">
                        <div>
                            <p class="text-xs font-black text-white uppercase tracking-widest">Aktifkan Sistem DP?</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" x-model="useDP" class="sr-only peer">
                            <div class="w-14 h-7 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-cyan-600"></div>
                        </label>
                    </div>

                    <div x-show="useDP" x-transition class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3 ml-1">DP (%)</label>
                            <div class="flex gap-2 mb-3">
                                <template x-for="p in [25, 50, 75]">
                                    <button @click="applyDPPercent(p)" class="flex-1 py-3 bg-slate-900/50 border border-slate-700 rounded-xl text-[10px] font-black text-slate-400 hover:border-cyan-500 hover:text-cyan-400 transition-all uppercase" x-text="p + '%'"></button>
                                </template>
                            </div>
                            <input type="number" x-model.number="dpPercent" @input="calculateFromPercent()" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-cyan-500 transition-all text-center">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3 ml-1">Nominal DP (Rp)</label>
                            <div class="mt-14"></div>
                            <input type="number" x-model.number="initialPayment" @input="calculateFromAmount()" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm font-black focus:outline-none focus:border-cyan-500 transition-all">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3 ml-1">Diskon</label>
                            <div class="flex bg-slate-900/50 p-1 rounded-2xl border border-slate-700 mb-3">
                                <button @click="discountType = 'fixed'" :class="discountType === 'fixed' ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-500'" class="flex-1 py-2.5 rounded-xl text-[11px] font-black uppercase transition-all">Rp</button>
                                <button @click="discountType = 'percent'" :class="discountType === 'percent' ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-500'" class="flex-1 py-2.5 rounded-xl text-[11px] font-black uppercase transition-all">%</button>
                            </div>
                            <input type="number" x-model.number="discountValue" class="w-full bg-slate-900/50 border border-slate-700 rounded-2xl px-5 py-4 text-white text-sm focus:outline-none focus:border-blue-500 transition-all font-bold">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3 ml-1">Metode Pembayaran</label>
                            <div class="mt-14"></div>
                            <select x-model="paymentMethod" class="w-full bg-slate-900/50 border border-slate-700 rounded-2xl px-5 py-4 text-white text-sm focus:outline-none focus:border-blue-500 transition-all cursor-pointer uppercase font-black">
                                <option value="tunai">TUNAI</option>
                                <option value="transfer">TRANSFER BANK</option>
                                <option value="qris">QRIS</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SECTION 6: CATATAN --}}
            <div class="bg-slate-800/40 backdrop-blur-md border border-slate-700/50 rounded-[2rem] p-8 shadow-xl">
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3 ml-1">Catatan Invoice</label>
                <textarea x-model="notes" rows="3" class="w-full bg-slate-900/50 border border-slate-700 rounded-2xl px-5 py-4 text-white text-sm focus:outline-none focus:border-blue-500 transition-all"></textarea>
            </div>

        </div>

        {{-- RIGHT COLUMN: LIVE PREVIEW (5/12) --}}
        <div class="lg:col-span-5 relative flex flex-col" style="height: calc(100vh - 140px);">
            
            {{-- Mobile Toggle --}}
            <div class="lg:hidden mb-6">
                <button @click="mobilePreviewOpen = !mobilePreviewOpen" class="w-full bg-blue-600 py-4 rounded-2xl text-xs font-black uppercase tracking-widest text-white flex items-center justify-center gap-3 shadow-xl shadow-blue-500/20">
                    <i class="fas fa-eye"></i> Lihat Preview
                </button>
            </div>

            {{-- PREVIEW CONTAINER --}}
            <div x-show="mobilePreviewOpen || window.innerWidth >= 1024" 
                 x-transition 
                 class="flex-1 bg-slate-900/40 rounded-[2.5rem] border border-slate-700/30 overflow-hidden flex flex-col shadow-2xl">
                
                {{-- SCROLL AREA --}}
                <div class="flex-1 overflow-y-auto custom-scrollbar p-12 flex justify-center items-start bg-[radial-gradient(circle_at_top_right,_var(--tw-gradient-stops))] from-blue-900/10 to-transparent">
                    
                    {{-- WRAPPER TO FIX TRANSFORM OVERFLOW --}}
                    <div style="width: calc(210mm * 0.75); margin-bottom: 200px;" class="shrink-0">
                        <div id="invoice-preview-container" 
                             class="bg-white shadow-[0_50px_100px_-20px_rgba(0,0,0,0.8)] origin-top-left flex flex-col" 
                             style="width: 210mm; min-height: 297mm; transform: scale(0.75); border-radius: 4px;">
                            
                            <div class="p-16 text-slate-800 relative flex-1 flex flex-col">
                                
                                {{-- Status Badge Row --}}
                                <div class="flex justify-start mb-10">
                                    <div :class="{
                                        'bg-amber-50 text-amber-600 border-amber-200': status === 'pending',
                                        'bg-blue-50 text-blue-600 border-blue-200': status === 'partial',
                                        'bg-emerald-50 text-emerald-600 border-emerald-200': status === 'paid'
                                    }" class="px-5 py-2 rounded-full border text-[12px] font-black uppercase tracking-widest shadow-sm" x-html="statusLabel"></div>
                                </div>

                                {{-- Header --}}
                                <div class="flex justify-between items-start mb-20 gap-10">
                                    <div class="flex-1">
                                        <h2 class="text-4xl font-black tracking-tighter text-blue-600 leading-tight mb-2" x-text="business.name"></h2>
                                        <div class="space-y-1">
                                            <p class="text-[14px] text-slate-600 font-bold" x-text="business.email"></p>
                                            <p class="text-[14px] text-slate-600 font-bold" x-text="business.phone"></p>
                                            <p class="text-[14px] text-slate-400 mt-4 leading-relaxed max-w-xs italic" x-text="business.address"></p>
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <h1 class="text-6xl font-black text-slate-900 tracking-tighter mb-8 leading-none">INVOICE</h1>
                                        <div class="space-y-4">
                                            <div>
                                                <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest mb-1">Nomor Tagihan</p>
                                                <p class="text-xl font-black text-slate-900" x-text="invoiceNumber"></p>
                                            </div>
                                            <div>
                                                <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest mb-1">Tanggal Terbit</p>
                                                <p class="text-lg font-bold text-slate-800" x-text="formatDate(date)"></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Client Info --}}
                                <div class="mb-20 border-t border-slate-100 pt-10">
                                    <p class="text-[11px] text-slate-400 font-black uppercase tracking-[0.2em] mb-6">DITAGIHKAN KEPADA:</p>
                                    <div class="grid grid-cols-2 gap-8">
                                        <div>
                                            <h3 class="text-2xl font-black text-slate-900 mb-1" x-text="client.name || 'Nama Pelanggan'"></h3>
                                            <p class="text-[16px] font-bold text-slate-600 mb-4" x-text="client.company"></p>
                                            <p class="text-[14px] text-slate-500 italic leading-relaxed" x-text="client.address"></p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-[11px] text-slate-400 font-black uppercase tracking-widest mb-1">Kontak Person</p>
                                            <p class="text-[14px] font-black text-slate-700" x-text="client.phone"></p>
                                            <p class="text-[14px] text-slate-500 font-medium" x-text="client.email"></p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Items Table with BLUE HEADER --}}
                                <div class="flex-1">
                                    <table class="w-full mb-16 border-collapse overflow-hidden rounded-xl">
                                        <thead>
                                            <tr class="bg-blue-600 text-white">
                                                <th class="text-left py-6 px-8 text-[12px] font-black uppercase tracking-[0.2em]">DESKRIPSI</th>
                                                <th class="text-center py-6 px-4 text-[12px] font-black uppercase tracking-[0.2em] w-24">QTY</th>
                                                <th class="text-right py-6 px-4 text-[12px] font-black uppercase tracking-[0.2em] w-40">HARGA</th>
                                                <th class="text-right py-6 px-8 text-[12px] font-black uppercase tracking-[0.2em] w-40">TOTAL</th>
                                            </tr>
                                        </thead>
                                        <tbody class="border-x border-b border-slate-100">
                                            <template x-for="item in items">
                                                <tr class="border-b border-slate-50 hover:bg-slate-50/50">
                                                    <td class="py-8 px-8 text-[17px] font-black text-slate-800" x-text="item.name || 'Nama Item'"></td>
                                                    <td class="py-8 px-4 text-[17px] text-center font-bold text-slate-600" x-text="item.quantity"></td>
                                                    <td class="py-8 px-4 text-[17px] text-right font-bold text-slate-500" x-text="formatCurrency(item.price)"></td>
                                                    <td class="py-8 px-8 text-[17px] text-right font-black text-slate-900" x-text="formatCurrency(item.quantity * item.price)"></td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>

                                {{-- Summary & Notes --}}
                                <div class="grid grid-cols-2 gap-20 pt-10 border-t border-slate-100 mt-auto">
                                    <div>
                                        <p class="text-[11px] text-slate-400 font-black uppercase tracking-widest mb-4">CATATAN:</p>
                                        <p class="text-[15px] text-slate-600 italic leading-relaxed" x-text="notes"></p>
                                    </div>
                                    <div class="space-y-4">
                                        <div class="flex justify-between text-[15px] font-bold text-slate-500">
                                            <span>Subtotal</span>
                                            <span class="text-slate-900" x-text="formatCurrency(subtotal)"></span>
                                        </div>
                                        <div class="flex justify-between text-[15px] font-black text-red-500" x-show="discount > 0">
                                            <span x-text="'Diskon ' + (discountType === 'percent' ? discountValue + '%' : '')"></span>
                                            <span x-text="'- ' + formatCurrency(discount)"></span>
                                        </div>
                                        <div class="flex justify-between py-5 border-t-2 border-slate-900 items-center">
                                            <span class="text-[13px] font-black uppercase tracking-[0.2em]">TOTAL TAGIHAN</span>
                                            <span class="text-2xl font-black text-slate-900" x-text="formatCurrency(total)"></span>
                                        </div>
                                        
                                        <div x-show="initialPayment > 0" x-transition class="space-y-4 pt-4 border-t border-slate-100">
                                            <div class="flex justify-between text-[15px] font-black text-emerald-600">
                                                <span>JUMLAH DIBAYAR</span>
                                                <span x-text="formatCurrency(initialPayment)"></span>
                                            </div>
                                            <div class="flex justify-between text-[15px]">
                                                <span class="text-[13px] font-black text-slate-500 uppercase tracking-widest">METODE PEMBAYARAN</span>
                                                <span class="text-[14px] font-black text-slate-900 uppercase" x-text="paymentMethod"></span>
                                            </div>
                                            <div class="flex justify-between text-[15px]">
                                                <span class="text-[13px] font-black text-slate-500 uppercase tracking-widest">SISA TAGIHAN</span>
                                                <span class="text-2xl font-black text-red-500" x-text="formatCurrency(total - initialPayment)"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Footer Text --}}
                                <div class="text-center mt-20 pt-10 border-t border-slate-50">
                                    <p class="text-[14px] text-slate-400 mb-2 font-medium">Terima kasih atas kerja samanya!</p>
                                    <p class="text-[15px] font-black text-blue-600 uppercase tracking-[0.3em]" x-text="business.name"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- STICKY ACTIONS --}}
                <div class="bg-slate-800/95 backdrop-blur-xl border-t border-slate-700/50 p-6 shadow-2xl">
                    <button @click="submitAndDownload()" :disabled="isSubmitting" class="w-full bg-emerald-600 hover:bg-emerald-500 disabled:bg-slate-700 text-white py-4 rounded-2xl font-black uppercase tracking-widest text-sm transition-all shadow-xl shadow-emerald-500/20 flex items-center justify-center gap-2 mb-4">
                        <i class="fas fa-file-pdf"></i> Download PDF Invoice
                    </button>
                    <div class="flex gap-3">
                        <button @click="submitInvoice()" :disabled="isSubmitting" class="flex-1 bg-slate-700 hover:bg-slate-600 text-white py-3.5 rounded-xl text-xs font-black uppercase tracking-widest border border-slate-600 transition-all">
                            <i class="fas fa-save mr-2"></i> Simpan
                        </button>
                        <button @click="resetForm()" class="flex-1 bg-slate-900/50 hover:bg-slate-800 text-slate-400 py-3.5 rounded-xl text-xs font-black uppercase tracking-widest border border-slate-700 transition-all">
                            <i class="fas fa-undo mr-2"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function invoiceGenerator() {
        return {
            isSubmitting: false,
            mobilePreviewOpen: false,
            invoiceNumber: '{{ $invoiceNumber }}',
            date: new Date().toISOString().split('T')[0],
            dueDate: '',
            business: {
                name: '{{ \App\Models\Setting::get('store_name', 'MONOFRAME STUDIO') }}',
                email: 'monoframestudio01@gmail.com',
                phone: '082323426600',
                address: 'Jl. Srigunting No.6, Air Tawar Bar., Kec. Padang Utara, Kota Padang, Sumatera Barat 25132'
            },
            client: {
                name: '',
                company: '',
                phone: '',
                email: '',
                address: ''
            },
            items: [
                { name: '', quantity: 1, price: 0 }
            ],
            
            discountType: 'fixed',
            discountValue: 0,
            
            useDP: false,
            dpPercent: 0,
            initialPayment: 0,
            paymentMethod: 'transfer',
            notes: 'Terima kasih telah menggunakan layanan MONOFRAME STUDIO.',

            addItem() {
                this.items.push({ name: '', quantity: 1, price: 0 });
            },

            removeItem(index) {
                if (this.items.length > 1) {
                    this.items.splice(index, 1);
                }
            },

            get subtotal() {
                return this.items.reduce((sum, item) => sum + (item.quantity * item.price), 0);
            },

            get discount() {
                if (this.discountType === 'percent') {
                    return Math.round((this.discountValue / 100) * this.subtotal);
                }
                return this.discountValue;
            },

            get total() {
                return Math.max(0, this.subtotal - this.discount);
            },

            get progress() {
                if (this.total <= 0) return 0;
                return Math.min(100, Math.round((this.initialPayment / this.total) * 100));
            },

            get status() {
                if (this.initialPayment >= this.total && this.total > 0) return 'paid';
                if (this.initialPayment > 0) return 'partial';
                return 'pending';
            },

            get statusLabel() {
                if (this.status === 'paid') return 'Lunas';
                if (this.status === 'partial') return 'DP ' + this.progress + '% Diterima';
                return 'Pending';
            },

            get statusBadgeIcon() {
                if (this.status === 'paid') return '🟢';
                if (this.status === 'partial') return '🔵';
                return '🟡';
            },

            applyDPPercent(p) {
                this.dpPercent = p;
                this.calculateFromPercent();
            },

            calculateFromPercent() {
                this.initialPayment = Math.round((this.dpPercent / 100) * this.total);
            },

            calculateFromAmount() {
                if (this.total > 0) {
                    this.dpPercent = Math.round((this.initialPayment / this.total) * 100);
                }
            },

            formatDate(d) {
                if (!d) return '-';
                return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
            },

            formatCurrency(val) {
                return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
            },

            resetForm() {
                if(confirm('Reset semua data form?')) {
                    location.reload();
                }
            },

            async submitInvoice(redirect = true) {
                if (!this.client.name) return Toast.fire({ icon: 'error', title: 'Nama Client harus diisi!' });
                if (this.items.some(i => !i.name)) return Toast.fire({ icon: 'error', title: 'Nama semua item harus diisi!' });

                this.isSubmitting = true;
                
                try {
                    const response = await fetch('{{ route("invoices.store") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            invoice_number: this.invoiceNumber,
                            date: this.date,
                            due_date: this.dueDate,
                            business_name: this.business.name,
                            business_email: this.business.email,
                            business_phone: this.business.phone,
                            business_address: this.business.address,
                            client_name: this.client.name,
                            client_company: this.client.company,
                            client_phone: this.client.phone,
                            client_email: this.client.email,
                            client_address: this.client.address,
                            subtotal: this.subtotal,
                            discount_type: this.discountType,
                            discount_value: this.discountValue,
                            discount: this.discount,
                            total_amount: this.total,
                            initial_payment: this.initialPayment,
                            payment_method: this.paymentMethod,
                            notes: this.notes,
                            items: this.items
                        })
                    });

                    const data = await response.json();
                    
                    if (response.ok) {
                        if (redirect) {
                            window.location.href = '{{ route("invoices.index") }}';
                        }
                        return data.id; 
                    } else {
                        Toast.fire({ icon: 'error', title: data.message || 'Gagal menyimpan invoice.' });
                        this.isSubmitting = false;
                    }
                } catch (e) {
                    Toast.fire({ icon: 'error', title: 'Terjadi kesalahan sistem.' });
                    this.isSubmitting = false;
                }
                return null;
            },

            async submitAndDownload() {
                const id = await this.submitInvoice(false);
                if (id) {
                    window.location.href = `/invoices/${id}/pdf`;
                }
            }
        }
    }
</script>
@endpush

<style>
    @media (min-width: 1024px) {
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(30, 41, 59, 0.5); border-radius: 10px; }
    }
</style>
@endsection
