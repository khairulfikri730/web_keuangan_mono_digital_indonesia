@extends('layouts.app')

@section('content')
<div x-data="createProduct()" class="max-w-5xl mx-auto pb-10">
  
  {{-- Header --}}
  <div class="flex items-center justify-between mb-6">
    <div>
      <a href="{{ route('products.index') }}" class="text-sm font-semibold text-slate-400 hover:text-white transition-colors mb-2 inline-flex items-center gap-2">
        <i class="fas fa-arrow-left"></i> Kembali ke Katalog
      </a>
      <h1 class="text-2xl font-black text-white flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:linear-gradient(135deg,#2563eb,#1d4ed8);box-shadow:0 4px 15px rgba(37,99,235,.35);">
          <i class="fas fa-plus text-sm"></i>
        </div>
        Tambah Produk Baru
      </h1>
    </div>
    <div class="flex gap-2">
      <button type="button" @click="submitForm()" class="px-5 py-2.5 rounded-xl text-white text-sm font-bold transition-all hover:scale-105 active:scale-95 shadow-lg flex items-center gap-2" style="background:linear-gradient(135deg,#2563eb,#1d4ed8);">
        <i class="fas fa-save"></i> Simpan Produk
      </button>
    </div>
  </div>

  {{-- Main Layout --}}
  <form id="productForm" action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" class="flex items-start gap-6">
    @csrf
    
    {{-- Hidden Values for meta --}}
    <input type="hidden" name="product_type" :value="ptype">
    <input type="hidden" name="product_kind" :value="pkind">
    <input type="hidden" name="bundle_items" :value="JSON.stringify(bundleItems)">
    <input type="hidden" name="formula_components" :value="JSON.stringify(formulaItems)">
    <input type="hidden" name="variants" :value="JSON.stringify(variants)">

    {{-- LEFT COLUMN: Main Form --}}
    <div class="flex-1 space-y-6">

      {{-- CARD 1: TIPE & JENIS --}}
      <div class="p-6 rounded-2xl border backdrop-blur-xl" style="background:rgba(30,41,59,.6);border-color:rgba(71,85,105,.4);">
        
        {{-- Tipe --}}
        <div class="mb-6">
          <label class="text-xs font-bold uppercase tracking-wider mb-3 flex items-center gap-2 text-slate-400">
            <i class="fas fa-layer-group text-blue-400"></i> Tipe Produk
          </label>
          <div class="grid grid-cols-3 gap-3">
            <button type="button" @click="ptype='finished'" :class="ptype==='finished'?'ring-2 ring-blue-500 bg-blue-500/10 text-blue-400':'border-slate-600 text-slate-400 hover:border-blue-400/50'" class="p-3 rounded-xl border flex flex-col items-center gap-2 font-semibold text-xs transition-all">
              <i class="fas fa-box-open text-lg"></i> Produk Jadi
            </button>
            <button type="button" @click="ptype='semi_finished'" :class="ptype==='semi_finished'?'ring-2 ring-blue-500 bg-blue-500/10 text-blue-400':'border-slate-600 text-slate-400 hover:border-blue-400/50'" class="p-3 rounded-xl border flex flex-col items-center gap-2 font-semibold text-xs transition-all">
              <i class="fas fa-cogs text-lg"></i> Setengah Jadi
            </button>
            <button type="button" @click="ptype='raw_material'" :class="ptype==='raw_material'?'ring-2 ring-blue-500 bg-blue-500/10 text-blue-400':'border-slate-600 text-slate-400 hover:border-blue-400/50'" class="p-3 rounded-xl border flex flex-col items-center gap-2 font-semibold text-xs transition-all">
              <i class="fas fa-seedling text-lg"></i> Bahan Baku
            </button>
          </div>
        </div>

        {{-- Jenis --}}
        <div>
          <label class="text-xs font-bold uppercase tracking-wider mb-3 flex items-center gap-2 text-slate-400">
            <i class="fas fa-tag text-indigo-400"></i> Jenis Produk
          </label>
          <div class="grid grid-cols-3 md:grid-cols-6 gap-2">
            <template x-for="k in kinds">
              <button type="button" x-show="ptype === 'finished' || ['regular', 'weight', 'unlimited'].includes(k.id)" @click="pkind=k.id" :class="pkind===k.id?'ring-2 ring-indigo-500 bg-indigo-500/10 text-indigo-400':'border-slate-600 text-slate-400 hover:border-indigo-400/50'" class="p-2 rounded-xl border flex flex-col items-center gap-1.5 font-semibold text-[11px] transition-all">
                <i :class="k.icon" class="text-base mb-1"></i> <span x-text="k.label"></span>
              </button>
            </template>
          </div>
        </div>
      </div>

      {{-- Dynamic Alert Info --}}
      <div x-show="ptype === 'semi_finished' || currentKindInfo" x-cloak class="px-5 py-4 rounded-xl text-sm font-medium border flex flex-col gap-2" style="background:rgba(99,102,241,.1);border-color:rgba(99,102,241,.2);color:#a5b4fc;">
        <div x-show="ptype === 'semi_finished'" class="flex items-start gap-3 text-orange-300">
          <i class="fas fa-exclamation-triangle mt-0.5"></i>
          <span>Produk setengah jadi digunakan sebagai bahan olahan internal, bukan untuk langsung dijual ke customer.</span>
        </div>
        <div x-show="ptype !== 'semi_finished' && currentKindInfo" class="flex items-start gap-3">
          <i class="fas fa-info-circle mt-0.5"></i>
          <span x-html="currentKindInfo"></span>
        </div>
      </div>

      {{-- CARD 2: INFO DASAR --}}
      <div class="p-6 rounded-2xl border backdrop-blur-xl space-y-5" style="background:rgba(30,41,59,.6);border-color:rgba(71,85,105,.4);">
        <h2 class="text-sm font-bold text-white flex items-center gap-2 border-b border-slate-700 pb-3"><i class="fas fa-file-alt text-emerald-400"></i> Info Dasar</h2>
        <div class="grid grid-cols-2 gap-5">
          <div class="col-span-2">
            <label class="text-xs font-bold text-slate-400 mb-1.5 block">Nama Produk <span class="text-red-400">*</span></label>
            <input type="text" name="name" x-model="name" class="w-full bg-slate-800/50 border border-slate-600 rounded-xl px-4 py-2.5 text-sm text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-all" placeholder="Contoh: Kopi Susu Aren">
          </div>
          <div>
            <label class="text-xs font-bold text-slate-400 mb-1.5 block">Kategori</label>
            <select name="category_id" class="w-full bg-slate-800/50 border border-slate-600 rounded-xl px-4 py-2.5 text-sm text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-all">
              <option value="">Pilih Kategori</option>
              @foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
            </select>
          </div>
          <div x-show="pkind !== 'service'">
            <label class="text-xs font-bold text-slate-400 mb-1.5 block">Satuan <span class="text-red-400">*</span></label>
            <select name="unit" class="w-full bg-slate-800/50 border border-slate-600 rounded-xl px-4 py-2.5 text-sm text-white focus:border-blue-500 outline-none">
              <template x-if="pkind === 'weight'">
                <optgroup label="Berat">
                  <option value="kg">Kilogram (kg)</option>
                  <option value="gram">Gram (g)</option>
                </optgroup>
              </template>
              <template x-if="pkind !== 'weight'">
                <optgroup label="Satuan Umum">
                  @foreach($unitOptions as $u)<option value="{{$u}}">{{ucfirst($u)}}</option>@endforeach
                </optgroup>
              </template>
            </select>
          </div>
        </div>
      </div>

      {{-- CARD 3: HARGA & STOK (Hidden for bundle/formula components usually, but basic price exists) --}}
      <div class="p-6 rounded-2xl border backdrop-blur-xl space-y-5" style="background:rgba(30,41,59,.6);border-color:rgba(71,85,105,.4);">
        <h2 class="text-sm font-bold text-white flex items-center gap-2 border-b border-slate-700 pb-3"><i class="fas fa-money-bill-wave text-yellow-400"></i> Harga & Stok</h2>
        <div class="grid grid-cols-2 gap-5">
          
          {{-- Harga Jual --}}
          <div x-show="pkind !== 'formula'">
            <label class="text-xs font-bold text-slate-400 mb-1.5 block" x-text="['semi_finished','raw_material'].includes(ptype) ? (pkind==='weight' ? 'Estimasi Harga/Satuan Berat (Opsional)' : 'Estimasi Harga / Nilai (Opsional)') : (pkind==='weight' ? 'Harga per Satuan Berat (Rp) *' : (pkind==='service' ? 'Harga Jasa (Rp) *' : 'Harga Jual (Rp) *'))"></label>
            <input type="number" name="price" x-model.number="price" class="w-full bg-slate-800/50 border border-slate-600 rounded-xl px-4 py-2.5 text-sm text-white outline-none" min="0">
          </div>
          
          {{-- Harga Modal --}}
          <div x-show="pkind !== 'formula' && pkind !== 'bundle'">
            <label class="text-xs font-bold text-slate-400 mb-1.5 block">Harga Modal / HPP (Rp)</label>
            <input type="number" name="cost_price" x-model.number="cost" class="w-full bg-slate-800/50 border border-slate-600 rounded-xl px-4 py-2.5 text-sm text-white outline-none" min="0">
          </div>

          {{-- Stok Awal --}}
          <div x-show="pkind === 'regular' || pkind === 'weight'">
            <label class="text-xs font-bold text-slate-400 mb-1.5 block" x-text="pkind==='weight' ? 'Stok Awal (dlm satuan berat)' : 'Stok Awal'"></label>
            <input type="number" name="stock" value="0" class="w-full bg-slate-800/50 border border-slate-600 rounded-xl px-4 py-2.5 text-sm text-white outline-none" min="0">
          </div>

          {{-- Minimum Stok --}}
          <div x-show="pkind === 'regular' || pkind === 'weight'">
            <label class="text-xs font-bold text-slate-400 mb-1.5 block">Minimum Stok</label>
            <input type="number" name="min_stock" value="5" class="w-full bg-slate-800/50 border border-slate-600 rounded-xl px-4 py-2.5 text-sm text-white outline-none" min="0">
          </div>

        </div>
      </div>

      {{-- CARD 4: BUNDLE ITEMS --}}
      <div x-show="pkind === 'bundle'" x-cloak class="p-6 rounded-2xl border backdrop-blur-xl space-y-5" style="background:rgba(30,41,59,.6);border-color:rgba(71,85,105,.4);">
        <h2 class="text-sm font-bold text-white flex items-center gap-2 border-b border-slate-700 pb-3"><i class="fas fa-boxes text-purple-400"></i> Komponen Bundle</h2>
        <div class="space-y-3">
          <template x-for="(item, idx) in bundleItems" :key="idx">
            <div class="flex items-end gap-3 bg-slate-800/50 p-3 rounded-xl border border-slate-700">
              <div class="flex-1">
                <label class="text-[10px] uppercase font-bold text-slate-500 block mb-1">Pilih Produk</label>
                <select x-model="item.id" @change="updateBundleItem(idx)" class="w-full bg-slate-900 border border-slate-600 rounded-lg px-3 py-2 text-sm text-white outline-none">
                  <option value="">-- Pilih --</option>
                  @foreach($allProducts as $ap)
                    <option value="{{$ap->id}}" data-price="{{$ap->price}}" data-unit="{{$ap->unit}}">{{$ap->name}} (Stok: {{$ap->stock}} {{$ap->unit}})</option>
                  @endforeach
                </select>
              </div>
              <div class="w-24">
                <label class="text-[10px] uppercase font-bold text-slate-500 block mb-1">Qty</label>
                <input type="number" x-model.number="item.qty" class="w-full bg-slate-900 border border-slate-600 rounded-lg px-3 py-2 text-sm text-white outline-none" min="1">
              </div>
              <button type="button" @click="bundleItems.splice(idx,1)" class="w-9 h-9 rounded-lg bg-red-500/10 text-red-400 hover:bg-red-500/20 border border-red-500/20 flex items-center justify-center flex-shrink-0 transition-colors">
                <i class="fas fa-trash text-xs"></i>
              </button>
            </div>
          </template>
          <button type="button" @click="bundleItems.push({id:'', qty:1, price:0})" class="text-sm font-semibold text-purple-400 hover:text-purple-300 flex items-center gap-2"><i class="fas fa-plus"></i> Tambah Produk ke Bundle</button>
        </div>
      </div>

      {{-- CARD 5: FORMULA COMPONENTS --}}
      <div x-show="pkind === 'formula'" x-cloak class="p-6 rounded-2xl border backdrop-blur-xl space-y-5" style="background:rgba(30,41,59,.6);border-color:rgba(71,85,105,.4);">
        <h2 class="text-sm font-bold text-white flex items-center gap-2 border-b border-slate-700 pb-3"><i class="fas fa-flask text-pink-400"></i> Komponen Formula</h2>
        <div class="space-y-4">
          <template x-for="(item, idx) in formulaItems" :key="idx">
            <div class="bg-slate-800/50 p-4 rounded-xl border border-slate-700 flex flex-col gap-4 relative">
              <div class="flex justify-between items-center pb-2 border-b border-slate-700">
                <span class="text-xs font-bold text-pink-400" x-text="'Komponen ' + (idx + 1)"></span>
                <button type="button" @click="formulaItems.splice(idx,1)" class="text-xs text-red-400 hover:text-red-300 font-semibold">Hapus</button>
              </div>
              
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="text-[10px] uppercase font-bold text-slate-500 block mb-1">Nama</label>
                  <input type="text" x-model="item.name" class="w-full bg-slate-900 border border-slate-600 rounded-lg px-3 py-2 text-sm text-white outline-none" placeholder="mis. Luas Stiker">
                </div>
                <div>
                  <label class="text-[10px] uppercase font-bold text-slate-500 block mb-1">Tipe Input</label>
                  <select x-model="item.type" class="w-full bg-slate-900 border border-slate-600 rounded-lg px-3 py-2 text-sm text-white outline-none">
                    <option value="number">Jumlah</option>
                    <option value="time">Durasi</option>
                  </select>
                </div>
                <div>
                  <label class="text-[10px] uppercase font-bold text-slate-500 block mb-1">Satuan</label>
                  <input type="text" x-model="item.unit" class="w-full bg-slate-900 border border-slate-600 rounded-lg px-3 py-2 text-sm text-white outline-none" placeholder="mis. cm², menit">
                </div>
                <div x-show="!item.has_tiers">
                  <label class="text-[10px] uppercase font-bold text-slate-500 block mb-1">Hrg / Satuan</label>
                  <input type="number" x-model.number="item.price" class="w-full bg-slate-900 border border-slate-600 rounded-lg px-3 py-2 text-sm text-white outline-none" min="0">
                </div>
              </div>

              {{-- Toggle Harga Bertingkat --}}
              <div class="flex items-center justify-between p-3 rounded-lg border" :class="item.has_tiers ? 'bg-purple-900/20 border-purple-500/30' : 'bg-slate-900/50 border-slate-700'">
                <span class="text-xs font-semibold" :class="item.has_tiers ? 'text-purple-400' : 'text-slate-400'">Harga Bertingkat <span x-show="item.has_tiers" class="px-1.5 py-0.5 rounded bg-purple-500 text-white ml-1" style="font-size:9px;">Aktif</span></span>
                <label class="relative inline-flex items-center cursor-pointer">
                  <input type="checkbox" x-model="item.has_tiers" class="sr-only peer">
                  <div class="w-9 h-5 bg-slate-700 peer-checked:bg-purple-500 rounded-full transition-colors after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-full"></div>
                </label>
              </div>

              {{-- Tabel Harga Bertingkat --}}
              <div x-show="item.has_tiers" class="rounded-lg border border-slate-700 overflow-hidden bg-slate-900/30">
                <div class="grid grid-cols-2 bg-purple-900/20 px-4 py-2 border-b border-purple-500/20">
                  <div class="text-[10px] uppercase font-bold text-purple-300">Batas Atas (Satuan)</div>
                  <div class="text-[10px] uppercase font-bold text-purple-300">Harga / Satuan</div>
                </div>
                <div class="divide-y divide-slate-700/50">
                  <template x-for="(tier, tidx) in item.tiers" :key="tidx">
                    <div class="grid grid-cols-2 p-2 gap-2 relative group">
                      <div>
                        <input type="number" x-model.number="tier.max_qty" x-show="tier.max_qty !== null" class="w-full bg-slate-800 border border-slate-600 rounded-lg px-3 py-1.5 text-sm text-white outline-none" placeholder="Batas">
                        <span x-show="tier.max_qty === null" class="text-xs italic text-slate-500 py-2 inline-block">∞ lainnya</span>
                      </div>
                      <div class="relative">
                        <input type="number" x-model.number="tier.price" class="w-full bg-slate-800 border border-slate-600 rounded-lg px-3 py-1.5 text-sm text-white outline-none pl-7" placeholder="0">
                        <span class="absolute left-2.5 top-1.5 text-xs text-slate-400">Rp</span>
                      </div>
                      <button type="button" x-show="tier.max_qty !== null" @click="item.tiers.splice(tidx, 1)" class="absolute -right-2 top-2 opacity-0 group-hover:opacity-100 text-red-400 hover:text-red-300 transition-opacity">
                        <i class="fas fa-times-circle"></i>
                      </button>
                    </div>
                  </template>
                </div>
                <button type="button" @click="item.tiers.splice(item.tiers.length-1, 0, {max_qty: 100, price: 0})" class="w-full text-left px-4 py-2 text-xs font-semibold text-purple-400 hover:bg-purple-900/20 transition-colors border-t border-slate-700">
                  <i class="fas fa-plus mr-1"></i> Tambah Baris
                </button>
              </div>

            </div>
          </template>
          <button type="button" @click="formulaItems.push({name:'', type:'number', unit:'', price:0, has_tiers:false, tiers:[{max_qty: 100, price: 0}, {max_qty: null, price: 0}]})" class="text-sm font-semibold text-pink-400 hover:text-pink-300 flex items-center gap-2"><i class="fas fa-plus"></i> Tambah Komponen</button>
        </div>
      </div>

      {{-- CARD 6: SATUAN KEMASAN --}}
      <div x-show="pkind === 'regular'" x-cloak class="p-6 rounded-2xl border backdrop-blur-xl space-y-5" style="background:rgba(30,41,59,.6);border-color:rgba(71,85,105,.4);">
        <h2 class="text-sm font-bold text-white flex items-center gap-2 border-b border-slate-700 pb-3"><i class="fas fa-box text-green-400"></i> Satuan Kemasan (Opsional)</h2>
        <div class="space-y-3">
          <template x-for="(item, idx) in packaging" :key="idx">
            <div class="flex items-end gap-3 bg-slate-800/50 p-3 rounded-xl border border-slate-700">
              <div class="flex-1">
                <label class="text-[10px] uppercase font-bold text-slate-500 block mb-1">Nama Kemasan</label>
                <input type="text" x-model="item.name" class="w-full bg-slate-900 border border-slate-600 rounded-lg px-3 py-2 text-sm text-white outline-none" placeholder="Contoh: Lusin / Dus">
              </div>
              <div class="w-32">
                <label class="text-[10px] uppercase font-bold text-slate-500 block mb-1">Isi (Qty per unit)</label>
                <input type="number" x-model.number="item.qty" class="w-full bg-slate-900 border border-slate-600 rounded-lg px-3 py-2 text-sm text-white outline-none" min="2">
              </div>
              <div class="w-32">
                <label class="text-[10px] uppercase font-bold text-slate-500 block mb-1">Harga Kemasan (Rp)</label>
                <input type="number" x-model.number="item.price" class="w-full bg-slate-900 border border-slate-600 rounded-lg px-3 py-2 text-sm text-white outline-none" min="0">
              </div>
              <button type="button" @click="packaging.splice(idx,1)" class="w-9 h-9 rounded-lg bg-red-500/10 text-red-400 hover:bg-red-500/20 border border-red-500/20 flex items-center justify-center flex-shrink-0 transition-colors">
                <i class="fas fa-trash text-xs"></i>
              </button>
            </div>
          </template>
          <button type="button" @click="packaging.push({name:'', qty:12, price:0})" class="text-sm font-semibold text-green-400 hover:text-green-300 flex items-center gap-2"><i class="fas fa-plus"></i> Tambah Kemasan</button>
        </div>
      </div>

      {{-- CARD 7: DISKON BERTINGKAT --}}
      <div x-show="['regular', 'weight', 'bundle'].includes(pkind)" x-cloak class="p-6 rounded-2xl border backdrop-blur-xl space-y-5" style="background:rgba(30,41,59,.6);border-color:rgba(71,85,105,.4);">
        <h2 class="text-sm font-bold text-white flex items-center gap-2 border-b border-slate-700 pb-3"><i class="fas fa-tags text-orange-400"></i> Diskon Bertingkat</h2>
        <div class="space-y-3">
          <template x-for="(item, idx) in discounts" :key="idx">
            <div class="flex items-end gap-3 bg-slate-800/50 p-3 rounded-xl border border-slate-700">
              <div class="flex-1">
                <label class="text-[10px] uppercase font-bold text-slate-500 block mb-1">Minimal Beli (Qty)</label>
                <input type="number" x-model.number="item.min_qty" class="w-full bg-slate-900 border border-slate-600 rounded-lg px-3 py-2 text-sm text-white outline-none" min="2">
              </div>
              <div class="w-32">
                <label class="text-[10px] uppercase font-bold text-slate-500 block mb-1">Diskon (%)</label>
                <input type="number" x-model.number="item.percentage" class="w-full bg-slate-900 border border-slate-600 rounded-lg px-3 py-2 text-sm text-white outline-none" min="0" max="100">
              </div>
              <button type="button" @click="discounts.splice(idx,1)" class="w-9 h-9 rounded-lg bg-red-500/10 text-red-400 hover:bg-red-500/20 border border-red-500/20 flex items-center justify-center flex-shrink-0 transition-colors">
                <i class="fas fa-trash text-xs"></i>
              </button>
            </div>
          </template>
          <button type="button" @click="discounts.push({min_qty:2, percentage:0})" class="text-sm font-semibold text-orange-400 hover:text-orange-300 flex items-center gap-2"><i class="fas fa-plus"></i> Tambah Rule Diskon</button>
        </div>
      </div>

      {{-- CARD 8: VARIAN PRODUK --}}
      <div x-show="['regular', 'weight', 'unlimited', 'service'].includes(pkind) && ptype === 'finished'" x-cloak class="p-6 rounded-2xl border backdrop-blur-xl space-y-5" style="background:rgba(30,41,59,.6);border-color:rgba(71,85,105,.4);">
        <h2 class="text-sm font-bold text-white flex items-center gap-2 border-b border-slate-700 pb-3"><i class="fas fa-layer-group text-blue-400"></i> Varian Produk (Opsional)</h2>
        <div class="space-y-3">
          <template x-for="(item, idx) in variants" :key="idx">
            <div class="flex items-end gap-3 bg-slate-800/50 p-3 rounded-xl border border-slate-700">
              <div class="flex-1">
                <label class="text-[10px] uppercase font-bold text-slate-500 block mb-1">Nama Varian</label>
                <input type="text" x-model="item.name" class="w-full bg-slate-900 border border-slate-600 rounded-lg px-3 py-2 text-sm text-white outline-none" placeholder="Contoh: Merah / XL">
              </div>
              <div class="w-28" x-show="pkind === 'regular'">
                <label class="text-[10px] uppercase font-bold text-slate-500 block mb-1">Stok (+)</label>
                <input type="number" x-model.number="item.stock" class="w-full bg-slate-900 border border-slate-600 rounded-lg px-3 py-2 text-sm text-white outline-none" min="0">
              </div>
              <div class="w-32">
                <label class="text-[10px] uppercase font-bold text-slate-500 block mb-1" x-text="pkind === 'regular' ? 'Harga (+/-)' : 'Harga Jual (Rp)'"></label>
                <input type="number" x-model.number="item.price_diff" class="w-full bg-slate-900 border border-slate-600 rounded-lg px-3 py-2 text-sm text-white outline-none">
              </div>
              <button type="button" @click="variants.splice(idx,1)" class="w-9 h-9 rounded-lg bg-red-500/10 text-red-400 hover:bg-red-500/20 border border-red-500/20 flex items-center justify-center flex-shrink-0 transition-colors">
                <i class="fas fa-trash text-xs"></i>
              </button>
            </div>
          </template>
          <button type="button" @click="variants.push({name:'', stock:0, price_diff:0})" class="text-sm font-semibold text-blue-400 hover:text-blue-300 flex items-center gap-2"><i class="fas fa-plus"></i> Tambah Varian</button>
        </div>
      </div>

    </div>{{-- End Left Column --}}

    {{-- RIGHT COLUMN: Sidebar (Foto, Options, Submit) --}}
    <div class="w-80 space-y-6 sticky top-6">
      
      {{-- Status & Visibility --}}
      <div class="p-5 rounded-2xl border backdrop-blur-xl" style="background:rgba(30,41,59,.6);border-color:rgba(71,85,105,.4);">
        <label class="flex items-center justify-between cursor-pointer group">
          <span class="text-sm font-bold text-white">Status Aktif</span>
          <div class="relative">
            <input type="checkbox" name="is_active" value="1" checked class="sr-only peer">
            <div class="w-11 h-6 bg-slate-700 peer-checked:bg-blue-600 rounded-full transition-colors after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-5"></div>
          </div>
        </label>
        <p class="text-xs text-slate-400 mt-2">Jika nonaktif, produk tidak akan muncul di halaman kasir POS.</p>
      </div>

      {{-- Identitas --}}
      <div class="p-5 rounded-2xl border backdrop-blur-xl space-y-4" style="background:rgba(30,41,59,.6);border-color:rgba(71,85,105,.4);">
        <div>
          <label class="text-xs font-bold text-slate-400 mb-1.5 block">SKU Produk</label>
          <input type="text" name="sku" class="w-full bg-slate-800/50 border border-slate-600 rounded-xl px-3 py-2 text-sm text-white outline-none" placeholder="Otomatis jika kosong">
        </div>
        <div>
          <label class="text-xs font-bold text-slate-400 mb-1.5 block">Barcode</label>
          <div class="relative">
            <i class="fas fa-barcode absolute left-3 top-1/2 -translate-y-1/2 text-slate-500"></i>
            <input type="text" name="barcode" class="w-full bg-slate-800/50 border border-slate-600 rounded-xl pl-9 pr-3 py-2 text-sm text-white outline-none" placeholder="Scan barcode...">
          </div>
        </div>
      </div>

      {{-- Media --}}
      <div class="p-5 rounded-2xl border backdrop-blur-xl" style="background:rgba(30,41,59,.6);border-color:rgba(71,85,105,.4);">
        <label class="text-xs font-bold text-slate-400 mb-1.5 block">Foto Produk</label>
        <div class="w-full h-32 rounded-xl border-2 border-dashed border-slate-600 bg-slate-800/50 flex flex-col items-center justify-center relative overflow-hidden group hover:border-blue-500 transition-colors cursor-pointer">
          <input type="file" name="image" @change="previewImage" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
          <img :src="imgPreview" x-show="imgPreview" class="absolute inset-0 w-full h-full object-cover z-0">
          <div x-show="!imgPreview" class="text-center">
            <i class="fas fa-cloud-upload-alt text-2xl text-slate-400 mb-2 group-hover:text-blue-400 transition-colors"></i>
            <p class="text-xs text-slate-400 font-medium">Klik atau Drag foto</p>
          </div>
        </div>
      </div>

      {{-- Margin Preview --}}
      <div x-show="pkind!=='formula' && pkind!=='service' && cost > 0" class="p-5 rounded-2xl border backdrop-blur-xl" style="background:rgba(16,185,129,.05);border-color:rgba(16,185,129,.2);">
        <p class="text-[10px] uppercase font-bold text-emerald-500 mb-1">Estimasi Profit / Margin</p>
        <p class="text-2xl font-black text-white" x-text="'Rp ' + formatRibuan(price - cost)"></p>
        <p class="text-xs font-bold mt-1" :class="(price - cost) > 0 ? 'text-emerald-400' : 'text-red-400'" x-text="calculateMargin() + '% Margin'"></p>
      </div>

    </div>{{-- End Right Column --}}

  </form>
</div>

@push('scripts')
<script>
function createProduct() {
  return {
    ptype: 'finished',
    pkind: 'regular',
    name: '',
    price: 0,
    cost: 0,
    imgPreview: null,
    
    bundleItems: [],
    formulaItems: [],
    variants: [],
    discounts: [],
    packaging: [],

    kinds: [
      { id:'regular', label:'Biasa', icon:'fas fa-cube', info:'<strong>Produk Biasa:</strong> Stok berkurang otomatis per 1 unit saat ada transaksi.' },
      { id:'weight', label:'Timbangan', icon:'fas fa-weight-hanging', info:'<strong>Timbangan:</strong> Harga dan stok berdasarkan berat (kg/gram).' },
      { id:'unlimited', label:'Unlimited', icon:'fas fa-infinity', info:'<strong>Unlimited:</strong> Produk digital atau lisensi tanpa batasan stok fisik.' },
      { id:'service', label:'Jasa', icon:'fas fa-wrench', info:'<strong>Jasa:</strong> Layanan tanpa stok fisik (contoh: Pemasangan, Perbaikan).' },
      { id:'bundle', label:'Bundle', icon:'fas fa-layer-group', info:'<strong>Bundle / Paket:</strong> HPP dan ketersediaan stok bergantung penuh pada produk komponen di dalamnya.' },
      { id:'formula', label:'Formula', icon:'fas fa-flask', info:'<strong>Formula:</strong> Harga dinamis yang dihitung saat transaksi berdasarkan bahan dan ukuran (p x l).' }
    ],

    init() {
      this.$watch('pkind', value => {
        if(!['bundle'].includes(value)) this.bundleItems = [];
        if(!['formula'].includes(value)) this.formulaItems = [];
        if(!['regular', 'weight', 'bundle'].includes(value)) this.discounts = [];
        if(!['regular', 'weight', 'unlimited', 'service'].includes(value)) this.variants = [];
        if(!['regular'].includes(value)) this.packaging = [];
      });
      this.$watch('ptype', value => {
        if(value === 'semi_finished' || value === 'raw_material') {
           if(!['regular', 'weight', 'unlimited'].includes(this.pkind)) {
             this.pkind = 'regular';
           }
           this.variants = [];
           this.bundleItems = [];
           this.formulaItems = [];
        }
      });
    },

    get currentKindInfo() {
      const k = this.kinds.find(x => x.id === this.pkind);
      return k ? k.info : '';
    },

    updateBundleItem(idx) {
      const select = event.target;
      const option = select.options[select.selectedIndex];
      if(option.value) {
        this.bundleItems[idx].price = parseFloat(option.dataset.price) || 0;
      }
    },

    previewImage(e) {
      const file = e.target.files[0];
      if (file) {
        this.imgPreview = URL.createObjectURL(file);
      }
    },

    formatRibuan(num) {
      return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    },

    calculateMargin() {
      if(this.cost <= 0 || this.price <= 0) return 0;
      const margin = ((this.price - this.cost) / this.price) * 100;
      return margin.toFixed(1);
    },

    submitForm() {
      if(!this.name.trim()) { alert('Nama produk wajib diisi!'); return; }
      if(this.pkind !== 'formula' && this.price <= 0) { alert('Harga jual wajib diisi dan > 0!'); return; }
      if(this.pkind === 'bundle' && this.bundleItems.length === 0) { alert('Bundle harus memiliki minimal 1 produk komponen!'); return; }
      if(this.pkind === 'formula' && this.formulaItems.length === 0) { alert('Formula harus memiliki minimal 1 komponen variabel!'); return; }
      
      document.getElementById('productForm').submit();
    }
  }
}
</script>
@endpush
@endsection
