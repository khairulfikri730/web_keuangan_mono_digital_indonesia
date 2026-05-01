<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMutation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');

        // Filter by product type (tab)
        $productType = $request->get('product_type', 'finished');
        $query->where('product_type', $productType);

        // Search
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('sku', 'like', '%' . $request->search . '%')
                  ->orWhere('barcode', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by category
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by stock status
        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'low') $query->lowStock();
            elseif ($request->stock_status === 'empty') $query->outOfStock();
            elseif ($request->stock_status === 'safe') {
                $query->where('stock', '>', 0)->whereColumn('stock', '>', 'min_stock');
            }
        }

        // Sorting
        $sort = $request->get('sort', 'name');
        $dir  = $request->get('dir', 'asc');
        $allowedSorts = ['name', 'price', 'stock', 'created_at'];
        if (in_array($sort, $allowedSorts)) {
            $query->orderBy($sort, $dir === 'desc' ? 'desc' : 'asc');
        }

        $products   = $query->paginate(15)->withQueryString();
        $categories = Category::where('is_active', true)->get();

        // Stats — computed across ALL types for global overview
        $allProducts    = Product::all();
        $stats = [
            'total'       => Product::where('product_type', $productType)->count(),
            'total_stock' => Product::where('product_type', $productType)->sum('stock'),
            'low_stock'   => Product::where('product_type', $productType)->where('stock', '>', 0)->whereColumn('stock', '<=', 'min_stock')->count(),
            'out_stock'   => Product::where('product_type', $productType)->where('stock', '<=', 0)->count(),
            'stock_value' => Product::where('product_type', $productType)->selectRaw('SUM(stock * cost_price) as val')->value('val') ?? 0,
        ];

        return view('products.index', compact('products', 'categories', 'stats', 'productType'));
    }

    public function create()
    {
        $categories   = Category::where('is_active', true)->get();
        $allProducts  = Product::select('id','name','unit','price','stock')->orderBy('name')->get();
        $unitOptions  = ['pcs','buah','kg','gram','liter','ml','box','pak','lusin','lembar','meter','cm','botol','karung','karton','cup','porsi','jam','sesi'];
        return view('products.create', compact('categories','allProducts','unitOptions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'category_id'  => 'nullable|exists:categories,id',
            'sku'          => 'nullable|string|unique:products,sku',
            'barcode'      => 'nullable|string',
            'product_type' => 'nullable|string|in:finished,semi_finished,raw_material',
            'product_kind' => 'nullable|string|in:regular,weight,unlimited,service,bundle,formula',
            'price'        => 'exclude_if:product_kind,formula|required|numeric|min:0',
            'cost_price'   => 'exclude_if:product_kind,formula,bundle|nullable|numeric|min:0',
            'stock'        => 'exclude_unless:product_kind,regular,weight|nullable|integer|min:0',
            'min_stock'    => 'exclude_unless:product_kind,regular,weight|nullable|integer|min:0',
            'unit'         => 'nullable|string|max:20',
            'description'  => 'nullable|string',
            'image'        => 'nullable|image|max:2048',
            'is_active'    => 'boolean',
            'bundle_items' => 'exclude_if:product_type,semi_finished,raw_material|exclude_unless:product_kind,bundle|nullable|string',
            'formula_components' => 'exclude_if:product_type,semi_finished,raw_material|exclude_unless:product_kind,formula|nullable|string',
            'variants'     => 'exclude_if:product_type,semi_finished,raw_material|exclude_unless:product_kind,regular,weight,unlimited,service|nullable|string',
            'discounts'    => 'exclude_unless:product_kind,regular,weight,bundle,formula|nullable|string',
            'packaging'    => 'exclude_unless:product_kind,regular|nullable|string',
        ]);

        $kind = $request->input('product_kind', 'regular');

        // Build meta JSON from advanced fields
        $meta = [];
        if ($request->filled('bundle_items')) {
            $meta['bundle_items'] = json_decode($request->bundle_items, true);
        }
        if ($request->filled('formula_components')) {
            $meta['formula_components'] = json_decode($request->formula_components, true);
        }
        if ($request->filled('variants')) {
            $meta['variants'] = json_decode($request->variants, true);
        }
        if ($request->filled('discounts')) {
            $meta['discounts'] = json_decode($request->discounts, true);
        }
        if ($request->filled('packaging')) $meta['packaging'] = json_decode($request->packaging, true);
        
        if (!empty($meta)) $validated['meta'] = $meta;

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }
        $validated['is_active']    = $request->boolean('is_active', true);
        $validated['product_type'] = $request->input('product_type', 'finished');
        $validated['product_kind'] = $kind;
        
        // Atur default sesuai tipe
        if (in_array($kind, ['unlimited', 'service', 'bundle', 'formula'])) {
            $validated['stock'] = 0;
            $validated['min_stock'] = 0;
        } else {
            $validated['stock'] = (int)($validated['stock'] ?? 0);
            $validated['min_stock'] = (int)($validated['min_stock'] ?? 5);
        }

        if ($kind === 'formula') $validated['price'] = 0;
        if (in_array($kind, ['bundle', 'formula'])) $validated['cost_price'] = 0;
        
        $validated['unit'] = $validated['unit'] ?? 'pcs';

        $product = Product::create($validated);

        if ($validated['stock'] > 0) {
            StockMutation::create([
                'product_id'   => $product->id,
                'user_id'      => auth()->id(),
                'type'         => 'in',
                'quantity'     => $validated['stock'],
                'stock_before' => 0,
                'stock_after'  => $validated['stock'],
                'notes'        => 'Stok awal produk',
            ]);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Produk berhasil ditambahkan!']);
        }

        return redirect()->route('products.index', ['product_type' => $validated['product_type']])
                         ->with('success', '✅ Produk <strong>'.$product->name.'</strong> berhasil ditambahkan!');
    }

    public function edit(Product $product)
    {
        $categories = Category::where('is_active', true)->get();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'category_id'  => 'nullable|exists:categories,id',
            'sku'          => 'nullable|string|unique:products,sku,' . $product->id,
            'barcode'      => 'nullable|string',
            'product_type' => 'nullable|string|in:finished,semi_finished,raw_material',
            'product_kind' => 'nullable|string|in:regular,weight,unlimited,service,bundle,formula',
            'price'        => 'exclude_if:product_kind,formula|required|numeric|min:0',
            'cost_price'   => 'exclude_if:product_kind,formula,bundle|nullable|numeric|min:0',
            'min_stock'    => 'exclude_unless:product_kind,regular,weight|nullable|integer|min:0',
            'unit'         => 'nullable|string|max:20',
            'description'  => 'nullable|string',
            'image'        => 'nullable|image|max:2048',
            'is_active'    => 'boolean',
            'bundle_items' => 'exclude_if:product_type,semi_finished,raw_material|exclude_unless:product_kind,bundle|nullable|string',
            'formula_components' => 'exclude_if:product_type,semi_finished,raw_material|exclude_unless:product_kind,formula|nullable|string',
            'variants'     => 'exclude_if:product_type,semi_finished,raw_material|exclude_unless:product_kind,regular,weight,unlimited,service|nullable|string',
            'discounts'    => 'exclude_unless:product_kind,regular,weight,bundle,formula|nullable|string',
            'packaging'    => 'exclude_unless:product_kind,regular|nullable|string',
        ]);

        $kind = $request->input('product_kind', 'regular');
        
        $meta = [];
        if ($request->filled('bundle_items')) {
            $meta['bundle_items'] = json_decode($request->bundle_items, true);
        }
        if ($request->filled('formula_components')) {
            $meta['formula_components'] = json_decode($request->formula_components, true);
        }
        if ($request->filled('variants')) {
            $meta['variants'] = json_decode($request->variants, true);
        }
        if ($request->filled('discounts')) {
            $meta['discounts'] = json_decode($request->discounts, true);
        }
        if ($request->filled('packaging')) $meta['packaging'] = json_decode($request->packaging, true);
        
        if (!empty($meta)) $validated['meta'] = $meta;

        if ($request->hasFile('image')) {
            if ($product->image) Storage::disk('public')->delete($product->image);
            $validated['image'] = $request->file('image')->store('products', 'public');
        }
        
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['product_kind'] = $kind;
        
        if (in_array($kind, ['unlimited', 'service', 'bundle', 'formula'])) {
            $validated['min_stock'] = 0;
            // Kita tidak mengubah stock pada update melalui form input standar untuk mencegah inkonsistensi
        } else {
            $validated['min_stock'] = (int)($validated['min_stock'] ?? 5);
        }

        if ($kind === 'formula') $validated['price'] = 0;
        if (in_array($kind, ['bundle', 'formula'])) $validated['cost_price'] = 0;
        $validated['unit'] = $validated['unit'] ?? 'pcs';

        $product->update($validated);
        return redirect()->route('products.index', ['product_type' => $product->product_type])
                         ->with('success', 'Produk berhasil diperbarui!');
    }

    public function destroy(Product $product)
    {
        if ($product->image) Storage::disk('public')->delete($product->image);
        $product->delete();
        return back()->with('success', 'Produk berhasil dihapus!');
    }

    public function export(Request $request, string $format)
    {
        // Placeholder — install maatwebsite/excel & barryvdh/laravel-dompdf for full implementation
        return back()->with('error', 'Fitur export ' . strtoupper($format) . ' sedang dalam pengembangan.');
    }
}
