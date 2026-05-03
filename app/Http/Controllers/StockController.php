<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMutation;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $stockType = $request->stock_type; // 'habis_pakai', 'unlimited', or null

        $stocklessProductIds = Product::where('product_kind', 'unlimited')
            ->orWhere('product_kind', 'service')
            ->pluck('id');

        $query = StockMutation::with(['product.category', 'user'])->latest();

        if ($stockType === 'unlimited') {
            $query->whereIn('product_id', $stocklessProductIds);
        } else {
            // Default & habis_pakai: show only consumable products
            $query->whereNotIn('product_id', $stocklessProductIds);
        }

        if ($request->product_id) {
            $query->where('product_id', $request->product_id);
        }
        if ($request->type) {
            $query->where('type', $request->type);
        }
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->category_id) {
            $query->whereHas('product', fn($q) => $q->where('category_id', $request->category_id));
        }
        if ($request->search) {
            $query->whereHas('product', fn($q) => $q->where('name', 'like', "%{$request->search}%"));
        }

        $mutations = $query->paginate(20)->withQueryString();
        $products = Product::active()->orderBy('name')->get();
        $lowStockProducts = Product::active()->lowStock()->with('category')->get();
        $categories = \App\Models\Category::where('is_active', true)->orderBy('name')->get();

        return view('stock.index', compact('mutations', 'products', 'lowStockProducts', 'categories'));
    }

    public function adjust(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:in,out,adjustment',
            'quantity' => 'required|integer|min:1',
            'notes' => 'required|string|max:255',
        ]);

        $product = Product::findOrFail($request->product_id);

        if ($product->isStockless()) {
            return back()->with('error', 'Produk ini bertipe Unlimited/Jasa dan tidak memerlukan penyesuaian stok.');
        }

        $stockBefore = $product->stock;

        if ($request->type === 'out' && $product->stock < $request->quantity) {
            return back()->with('error', 'Stok tidak mencukupi!');
        }

        if ($request->type === 'in') {
            $product->increment('stock', $request->quantity);
        } elseif ($request->type === 'out') {
            $product->decrement('stock', $request->quantity);
        } elseif ($request->type === 'adjustment') {
            $product->update(['stock' => $request->quantity]);
        }

        StockMutation::create([
            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'type' => $request->type,
            'quantity' => $request->quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $product->fresh()->stock,
            'notes' => $request->notes,
        ]);

        return back()->with('success', 'Stok berhasil disesuaikan!');
    }

    public function destroy(StockMutation $mutation)
    {
        $product = $mutation->product;

        // Reverse the stock change if product is not stockless
        if ($product && !$product->isStockless()) {
            if ($mutation->type === 'in') {
                $product->decrement('stock', $mutation->quantity);
            } elseif ($mutation->type === 'out') {
                $product->increment('stock', $mutation->quantity);
            } elseif ($mutation->type === 'adjustment') {
                $product->update(['stock' => $mutation->stock_before]);
            }
        }

        $mutation->delete();

        return back()->with('success', 'Riwayat mutasi berhasil dihapus dan stok dikembalikan.');
    }
}
