<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMutation;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $query = StockMutation::with(['product', 'user'])->latest();

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

        $mutations = $query->paginate(20)->withQueryString();
        $products = Product::active()->orderBy('name')->get();
        $lowStockProducts = Product::active()->lowStock()->with('category')->get();

        return view('stock.index', compact('mutations', 'products', 'lowStockProducts'));
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
}
