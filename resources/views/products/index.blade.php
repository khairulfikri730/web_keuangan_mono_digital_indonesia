@extends('layouts.app')
@section('title','Katalog Produk')
@section('page-title','Katalog Produk')
@section('content')
@php
$types=['finished'=>'Produk Jadi','semi_finished'=>'Setengah Jadi','raw_material'=>'Bahan Baku'];
$kinds=['regular'=>'Biasa','weight'=>'Timbangan','unlimited'=>'Unlimited','service'=>'Jasa','bundle'=>'Bundle','formula'=>'Formula'];
@endphp
<style>
.stat-card{background:linear-gradient(135deg,rgba(30,41,59,.8),rgba(15,23,42,.6));border:1px solid rgba(71,85,105,.4);border-radius:1rem;padding:1rem;display:flex;align-items:center;gap:.75rem;transition:all .2s;}
.stat-card:hover{border-color:rgba(99,102,241,.4);transform:translateY(-1px);}
.tab-pill{padding:.5rem 1.25rem;border-radius:.75rem;font-size:.8rem;font-weight:600;cursor:pointer;transition:all .2s;color:#94a3b8;}
.tab-pill.active{background:#2563eb;color:#fff;box-shadow:0 4px 15px rgba(37,99,235,.35);}
.tab-pill:hover:not(.active){color:#fff;background:rgba(71,85,105,.5);}
.prod-row:hover{background:rgba(30,41,59,.8);border-left-color:#3b82f6 !important;}
.badge{display:inline-flex;align-items:center;gap:.25rem;padding:.2rem .65rem;border-radius:.5rem;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;}
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:100;display:flex;align-items:center;justify-content:center;padding:1rem;}
.modal-box{background:#0f172a;border:1px solid rgba(71,85,105,.5);border-radius:1.25rem;width:100%;max-width:680px;max-height:90vh;overflow-y:auto;}
.form-field{width:100%;background:rgba(15,23,42,.8);border:1px solid rgba(71,85,105,.5);border-radius:.65rem;padding:.6rem .9rem;color:#e2e8f0;font-size:.85rem;outline:none;transition:border-color .2s;}
.form-field:focus{border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.15);}
.kind-btn{flex:1;padding:.5rem;border-radius:.65rem;border:1px solid rgba(71,85,105,.5);background:transparent;color:#94a3b8;font-size:.75rem;font-weight:600;cursor:pointer;transition:all .2s;text-align:center;}
.kind-btn.selected{border-color:#3b82f6;background:rgba(37,99,235,.2);color:#60a5fa;}
</style>

<div x-data="prodPage()" class="space-y-5">

{{-- STATS --}}
<div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3">
  <div class="stat-card">
    <div class="w-10 h-10 rounded-xl flex-shrink-0 flex items-center justify-center" style="background:rgba(59,130,246,.15)"><i class="fas fa-box text-blue-400"></i></div>
    <div><p class="text-xs text-slate-400 mb-0.5">Total Produk</p><p class="text-2xl font-black text-white">{{ $stats['total'] }}</p></div>
  </div>
  <div class="stat-card">
    <div class="w-10 h-10 rounded-xl flex-shrink-0 flex items-center justify-center" style="background:rgba(16,185,129,.15)"><i class="fas fa-cubes text-emerald-400"></i></div>
    <div><p class="text-xs text-slate-400 mb-0.5">Total Stok</p><p class="text-2xl font-black text-white">{{ number_format($stats['total_stock']) }}</p></div>
  </div>
  <a href="{{ route('products.index', array_merge(request()->except(['stock_status','page']), ['stock_status'=>'low'])) }}" class="stat-card cursor-pointer group hover:bg-slate-800/80">
    <div class="w-10 h-10 rounded-xl flex-shrink-0 flex items-center justify-center transition-colors group-hover:bg-amber-500/20" style="background:rgba(234,179,8,.15)"><i class="fas fa-triangle-exclamation text-yellow-400"></i></div>
    <div><p class="text-xs text-slate-400 mb-0.5 group-hover:text-yellow-400 transition-colors">Stok Rendah <i class="fas fa-chevron-right text-[10px] ml-0.5 opacity-0 group-hover:opacity-100 transition-opacity"></i></p><p class="text-2xl font-black text-yellow-400">{{ $stats['low_stock'] }}</p></div>
  </a>
  <a href="{{ route('products.index', array_merge(request()->except(['stock_status','page']), ['stock_status'=>'empty'])) }}" class="stat-card cursor-pointer group hover:bg-slate-800/80">
    <div class="w-10 h-10 rounded-xl flex-shrink-0 flex items-center justify-center transition-colors group-hover:bg-red-500/20" style="background:rgba(239,68,68,.15)"><i class="fas fa-ban text-red-400"></i></div>
    <div><p class="text-xs text-slate-400 mb-0.5 group-hover:text-red-400 transition-colors">Produk Habis <i class="fas fa-chevron-right text-[10px] ml-0.5 opacity-0 group-hover:opacity-100 transition-opacity"></i></p><p class="text-2xl font-black text-red-400">{{ $stats['out_stock'] }}</p></div>
  </a>
  <a href="{{ route('products.index', array_merge(request()->except(['stock_status','page']), ['stock_status'=>'unlimited'])) }}" class="stat-card cursor-pointer group hover:bg-slate-800/80">
    <div class="w-10 h-10 rounded-xl flex-shrink-0 flex items-center justify-center transition-colors group-hover:bg-cyan-500/20" style="background:rgba(6,182,212,.15)"><i class="fas fa-infinity text-cyan-400"></i></div>
    <div><p class="text-xs text-slate-400 mb-0.5 group-hover:text-cyan-400 transition-colors">Unlimited <i class="fas fa-chevron-right text-[10px] ml-0.5 opacity-0 group-hover:opacity-100 transition-opacity"></i></p><p class="text-2xl font-black text-cyan-400">{{ $stats['unlimited'] }}</p></div>
  </a>
  <div class="stat-card">
    <div class="w-10 h-10 rounded-xl flex-shrink-0 flex items-center justify-center" style="background:rgba(139,92,246,.15)"><i class="fas fa-coins text-violet-400"></i></div>
    <div><p class="text-xs text-slate-400 mb-0.5">Nilai Stok</p><p class="text-base font-black text-white">Rp {{ number_format($stats['stock_value'],0,',','.') }}</p></div>
  </div>
</div>

{{-- MAIN CARD --}}
<div style="background:rgba(15,23,42,.6);border:1px solid rgba(71,85,105,.4);border-radius:1.25rem;overflow:hidden;">

  {{-- TABS + ACTIONS --}}
  {{-- ROW 1: TABS --}}
  <div class="px-4 pt-4 pb-3 border-b border-slate-700/50">
    <div class="flex gap-1 p-1 rounded-xl w-fit" style="background:rgba(30,41,59,.8);border:1px solid rgba(71,85,105,.4);">
      @foreach($types as $t=>$label)
      <a href="{{ route('products.index', array_merge(request()->except(['product_type','page']),['product_type'=>$t])) }}"
         class="tab-pill {{ $productType==$t?'active':'' }}">
        @if($t=='finished')<i class="fas fa-box-open mr-1.5"></i>
        @elseif($t=='semi_finished')<i class="fas fa-cogs mr-1.5"></i>
        @else<i class="fas fa-seedling mr-1.5"></i>@endif
        {{ $label }}
      </a>
      @endforeach
    </div>
  </div>

  {{-- ROW 2: COMPACT FILTER + ACTIONS --}}
  <form method="GET" action="{{ route('products.index') }}"
        class="px-4 py-3 border-b border-slate-700/50 flex items-center gap-2 flex-wrap">
    <input type="hidden" name="product_type" value="{{ $productType }}">

    {{-- Search --}}
    <div class="relative flex-shrink-0">
      <i class="fas fa-search absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-500 text-[11px] pointer-events-none"></i>
      <input type="text" name="search" value="{{ request('search') }}"
             placeholder="Cari..."
             class="py-1.5 pl-7 pr-3 text-sm rounded-xl text-white placeholder-slate-500 outline-none transition-all"
             style="background:rgba(15,23,42,.7);border:1px solid rgba(71,85,105,.5);width:170px;">
    </div>

    {{-- Kategori --}}
    <select name="category_id" onchange="this.form.submit()"
            class="py-1.5 px-3 text-sm rounded-xl text-white outline-none cursor-pointer appearance-none flex-shrink-0"
            style="background:rgba(15,23,42,.7);border:1px solid rgba(71,85,105,.5);width:145px;">
      <option value="">Semua Kategori</option>
      @foreach($categories as $c)
      <option value="{{ $c->id }}" {{ request('category_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>
      @endforeach
    </select>

    {{-- Status --}}
    <select name="stock_status" onchange="this.form.submit()"
            class="py-1.5 px-3 text-sm rounded-xl text-white outline-none cursor-pointer appearance-none flex-shrink-0"
            style="background:rgba(15,23,42,.7);border:1px solid rgba(71,85,105,.5);width:130px;">
      <option value="">Semua Status</option>
      <option value="safe"      {{ request('stock_status')=='safe'      ?'selected':'' }}>✅ Aman</option>
      <option value="low"       {{ request('stock_status')=='low'       ?'selected':'' }}>⚠️ Rendah</option>
      <option value="empty"     {{ request('stock_status')=='empty'     ?'selected':'' }}>🚫 Habis</option>
      <option value="unlimited" {{ request('stock_status')=='unlimited' ?'selected':'' }}>♾️ Unlimited</option>
    </select>

    {{-- Sort --}}
    <select name="sort" onchange="this.form.submit()"
            class="py-1.5 px-3 text-sm rounded-xl text-white outline-none cursor-pointer appearance-none flex-shrink-0"
            style="background:rgba(15,23,42,.7);border:1px solid rgba(71,85,105,.5);width:115px;">
      <option value="name"       {{ request('sort','name')=='name'      ?'selected':'' }}>Nama A-Z</option>
      <option value="price"      {{ request('sort')=='price'            ?'selected':'' }}>Harga</option>
      <option value="stock"      {{ request('sort')=='stock'            ?'selected':'' }}>Stok</option>
      <option value="created_at" {{ request('sort')=='created_at'      ?'selected':'' }}>Terbaru</option>
    </select>

    {{-- Filter submit --}}
    <button type="submit" title="Cari"
            class="w-8 h-8 rounded-xl flex items-center justify-center text-white text-xs transition-all hover:opacity-90 flex-shrink-0"
            style="background:rgba(37,99,235,.7);border:1px solid rgba(59,130,246,.4);">
      <i class="fas fa-search"></i>
    </button>
    @if(request()->hasAny(['search','category_id','stock_status','sort']))
    <a href="{{ route('products.index',['product_type'=>$productType]) }}" title="Reset Filter"
       class="w-8 h-8 rounded-xl flex items-center justify-center text-slate-400 hover:text-white text-xs transition-all flex-shrink-0"
       style="background:rgba(71,85,105,.3);border:1px solid rgba(71,85,105,.4);">
      <i class="fas fa-times"></i>
    </a>
    @endif

    {{-- Spacer + Divider --}}
    <div class="flex-1 min-w-0"></div>
    <div class="w-px h-5 flex-shrink-0" style="background:rgba(71,85,105,.5);"></div>

    {{-- Export & Add --}}
    <a href="{{ route('products.export','excel').'?product_type='.$productType }}"
       class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-emerald-400 flex-shrink-0 transition-all hover:scale-105"
       style="background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.25);">
      <i class="fas fa-file-excel"></i> Excel
    </a>
    <a href="{{ route('products.export','pdf').'?product_type='.$productType }}"
       class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-red-400 flex-shrink-0 transition-all hover:scale-105"
       style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25);">
      <i class="fas fa-file-pdf"></i> PDF
    </a>
    <a href="{{ route('products.create') }}"
            class="flex items-center gap-2 px-4 py-1.5 rounded-xl text-sm font-bold text-white flex-shrink-0 transition-all hover:opacity-90 hover:scale-105 active:scale-95"
            style="background:linear-gradient(135deg,#2563eb,#1d4ed8);box-shadow:0 4px 15px rgba(37,99,235,.35);">
      <i class="fas fa-plus text-xs"></i> Tambah Produk
    </a>
  </form>

  {{-- TABLE --}}
  <div class="overflow-x-auto">
    <table class="w-full text-left">
      <thead>
        <tr style="background:rgba(30,41,59,.6);border-bottom:1px solid rgba(71,85,105,.4);">
          <th class="px-4 py-3 text-xs font-black uppercase tracking-wider text-slate-400">Produk</th>
          <th class="px-4 py-3 text-xs font-black uppercase tracking-wider text-slate-400">Kategori</th>
          <th class="px-4 py-3 text-xs font-black uppercase tracking-wider text-slate-400">Tipe</th>
          <th class="px-4 py-3 text-xs font-black uppercase tracking-wider text-slate-400">Harga & Modal</th>
          <th class="px-4 py-3 text-xs font-black uppercase tracking-wider text-slate-400 text-center">Stok</th>
          <th class="px-4 py-3 text-xs font-black uppercase tracking-wider text-slate-400 text-center">Status</th>
          <th class="px-4 py-3 text-xs font-black uppercase tracking-wider text-slate-400 text-right">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($products as $p)
        <tr class="prod-row group border-l-2 border-transparent transition-all duration-200" style="border-bottom:1px solid rgba(71,85,105,.2);">
          <td class="px-4 py-3">
            <div class="flex items-center gap-3">
              <div class="w-11 h-11 rounded-xl overflow-hidden flex-shrink-0 flex items-center justify-center font-black text-sm group-hover:scale-105 transition-transform" style="background:rgba(30,41,59,.9);border:1px solid rgba(71,85,105,.4);">
                @if($p->image)<img src="{{ asset('storage/'.$p->image) }}" class="w-full h-full object-cover">
                @else<span class="text-slate-400">{{ strtoupper(substr($p->name,0,2)) }}</span>@endif
              </div>
              <div>
                <p class="font-semibold text-white text-sm">{{ $p->name }}</p>
                <p class="text-xs text-slate-500">SKU: {{ $p->sku??'-' }} | Bcd: {{ $p->barcode??'-' }}</p>
              </div>
            </div>
          </td>
          <td class="px-4 py-3">
            @if($p->category)
            <span class="badge" style="background:{{ $p->category->color }}18;color:{{ $p->category->color }};border:1px solid {{ $p->category->color }}30;">{{ $p->category->name }}</span>
            @else<span class="badge" style="background:rgba(71,85,105,.3);color:#94a3b8;">Tanpa Kategori</span>@endif
          </td>
          <td class="px-4 py-3">
            <span class="badge" style="background:rgba(99,102,241,.15);color:#a5b4fc;border:1px solid rgba(99,102,241,.25);">{{ $p->kind_label }}</span>
          </td>
          <td class="px-4 py-3">
            <p class="font-bold text-emerald-400">Rp {{ number_format($p->price,0,',','.') }}</p>
            <p class="text-xs text-slate-500">Modal: Rp {{ number_format($p->cost_price,0,',','.') }}</p>
          </td>
          <td class="px-4 py-3 text-center">
            @if($p->isStockless())
            <span class="text-lg font-black text-blue-400">∞</span>
            <p class="text-xs text-slate-500 uppercase">Unlimited</p>
            @else
            <span class="text-lg font-black {{ $p->stock<=0?'text-red-400':($p->stock<=$p->min_stock?'text-yellow-400':'text-slate-200') }}">{{ $p->stock }}</span>
            <p class="text-xs text-slate-500 uppercase">{{ $p->unit }}</p>
            @endif
          </td>
          <td class="px-4 py-3 text-center">
            @if(!$p->is_active)
            <span class="badge" style="background:rgba(71,85,105,.3);color:#94a3b8;border:1px solid rgba(71,85,105,.4);">Nonaktif</span>
            @elseif($p->isStockless())
            <span class="badge" style="background:rgba(59,130,246,.1);color:#60a5fa;border:1px solid rgba(59,130,246,.25);"><i class="fas fa-infinity"></i> Unlimited</span>
            @elseif($p->stock<=0)
            <span class="badge" style="background:rgba(239,68,68,.1);color:#f87171;border:1px solid rgba(239,68,68,.25);"><i class="fas fa-xmark"></i> Habis</span>
            @elseif($p->stock<=$p->min_stock)
            <span class="badge" style="background:rgba(234,179,8,.1);color:#facc15;border:1px solid rgba(234,179,8,.25);"><i class="fas fa-exclamation"></i> Rendah</span>
            @else
            <span class="badge" style="background:rgba(16,185,129,.1);color:#34d399;border:1px solid rgba(16,185,129,.25);"><i class="fas fa-check"></i> Aktif</span>
            @endif
          </td>
          <td class="px-4 py-3 text-right">
            <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
              @if(!$p->isStockless())
              <a href="{{ route('stock.index', ['action' => 'restock', 'product_id' => $p->id]) }}" class="w-8 h-8 rounded-lg flex items-center justify-center text-emerald-400 hover:text-emerald-300 transition-all hover:scale-110" style="background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.25);" title="Restock / Sesuaikan Stok"><i class="fas fa-plus text-xs"></i></a>
              @endif
              <a href="{{ route('products.edit',$p) }}" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-300 hover:text-white transition-all hover:scale-110" style="background:rgba(71,85,105,.5);border:1px solid rgba(71,85,105,.5);" title="Edit"><i class="fas fa-pen text-xs"></i></a>
              <form action="{{ route('products.destroy',$p) }}" method="POST" onsubmit="return confirm('Hapus produk {{ addslashes($p->name) }}?')">
                @csrf @method('DELETE')
                <button type="submit" class="w-8 h-8 rounded-lg flex items-center justify-center text-red-400 hover:text-red-300 transition-all hover:scale-110" style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);" title="Hapus"><i class="fas fa-trash text-xs"></i></button>
              </form>
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="7" class="py-16 text-center">
          <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-slate-700/50" style="background:rgba(30,41,59,.8);"><i class="fas fa-box-open text-2xl text-slate-500"></i></div>
          <p class="text-slate-300 font-bold uppercase tracking-wider mb-1">Katalog Produk Kosong</p>
          <p class="text-slate-500 text-sm mb-6">Worksheet ini belum memiliki data produk. Silakan tambahkan produk pertama Anda.</p>
          <a href="{{ route('products.create') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-bold text-white transition-all hover:scale-105 active:scale-95"
             style="background:linear-gradient(135deg,#2563eb,#1d4ed8);box-shadow:0 4px 15px rgba(37,99,235,.35);">
            <i class="fas fa-plus text-xs"></i> Tambah Produk Baru
          </a>
        </td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if($products->hasPages())
  <div class="p-4 border-t border-slate-700/50">{{ $products->links('pagination::tailwind') }}</div>
  @endif
</div>


@endsection

