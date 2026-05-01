<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\PosGroup;
use App\Models\Product;
use App\Models\Shift;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\StockMutation;
use App\Models\Cashflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function index()
    {
        $activeShift = Shift::activeShift();

        $categories = Category::where('is_active', true)->get();
        $products = Product::active()->with('category')->orderBy('name')->get();
        $posGroups = PosGroup::with('products.category')->orderBy('position')->get();
        $settings = \App\Models\Setting::getMultiple(['tax_rate', 'active_payment_methods']);
        return view('pos.index', compact('activeShift', 'categories', 'products', 'posGroups', 'settings'));
    }

    public function getProducts(Request $request)
    {
        $query = Product::active()->with('category');
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('barcode', $request->search)
                  ->orWhere('sku', 'like', '%' . $request->search . '%');
            });
        }
        return response()->json($query->orderBy('name')->get());
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,transfer,qris,debit',
            'paid_amount' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|in:nominal,percentage',
            'customer_name' => 'nullable|string|max:100',
            'customer_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ]);

        $activeShift = Shift::activeShift();
        if (!$activeShift) {
            return response()->json(['error' => 'Tidak ada shift aktif!'], 422);
        }

        DB::beginTransaction();
        try {
            $subtotal = 0;
            $itemsData = [];

            foreach ($request->items as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);

                if ($product->stock < $item['quantity']) {
                    DB::rollBack();
                    return response()->json([
                        'error' => "Stok {$product->name} tidak cukup! Stok tersisa: {$product->stock}"
                    ], 422);
                }

                $itemSubtotal = ($item['price'] * $item['quantity']) - ($item['discount'] ?? 0);
                $subtotal += $itemSubtotal;

                $stockBefore = $product->stock;
                $product->decrement('stock', $item['quantity']);

                StockMutation::create([
                    'product_id' => $product->id,
                    'user_id' => auth()->id(),
                    'type' => 'out',
                    'quantity' => $item['quantity'],
                    'stock_before' => $stockBefore,
                    'stock_after' => $product->stock,
                    'notes' => 'Terjual via POS',
                ]);

                $itemsData[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $item['price'],
                    'cost_price' => $product->cost_price,
                    'quantity' => $item['quantity'],
                    'discount' => $item['discount'] ?? 0,
                    'subtotal' => $itemSubtotal,
                ];
            }

            $discountType = $request->discount_type ?? 'nominal';
            $discountValue = $request->discount ?? 0;
            $discount = $discountType === 'percentage' ? ($subtotal * ($discountValue / 100)) : $discountValue;
            
            $taxRate = \App\Models\Setting::get('tax_rate', 0);
            $tax = ($subtotal - $discount) * ($taxRate / 100);
            
            $total = $subtotal - $discount + $tax;
            $change = $request->paid_amount - $total;

            $transaction = Transaction::create([
                'invoice_number' => Transaction::generateInvoiceNumber(),
                'shift_id' => $activeShift->id,
                'user_id' => auth()->id(),
                'subtotal' => $subtotal,
                'discount' => $discount, // save as nominal value in db
                'discount_type' => $discountType,
                'tax' => $tax,
                'total' => $total,
                'paid_amount' => $request->paid_amount,
                'change_amount' => max(0, $change),
                'payment_method' => $request->payment_method,
                'status' => 'completed',
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'notes' => $request->notes,
            ]);

            foreach ($itemsData as &$item) {
                $item['transaction_id'] = $transaction->id;
            }
            TransactionItem::insert($itemsData);

            // Dispatch event to handle Cashflow creation
            event(new \App\Events\TransactionCreated($transaction));

            DB::commit();

            $transaction->load('items');
            return response()->json([
                'success' => true,
                'transaction' => $transaction,
                'change' => max(0, $change),
                'invoice_number' => $transaction->invoice_number,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function receipt(Transaction $transaction)
    {
        $transaction->load(['items', 'user', 'shift']);
        $settings = \App\Models\Setting::getMultiple([
            'store_name', 'store_address', 'store_phone', 'store_footer'
        ]);
        return view('pos.receipt', compact('transaction', 'settings'));
    }

    public function storeGroup(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:50',
            'position' => 'nullable|integer',
        ]);

        $group = PosGroup::create($request->all());
        return response()->json(['success' => true, 'group' => $group]);
    }

    public function updateGroup(Request $request, PosGroup $group)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:50',
            'position' => 'nullable|integer',
        ]);

        $group->update($request->all());
        return response()->json(['success' => true, 'group' => $group]);
    }

    public function destroyGroup(PosGroup $group)
    {
        $group->delete();
        return response()->json(['success' => true]);
    }

    public function syncGroupProducts(Request $request, PosGroup $group)
    {
        $request->validate([
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.position' => 'required|integer',
        ]);

        $syncData = [];
        foreach ($request->products as $product) {
            $syncData[$product['id']] = ['position' => $product['position']];
        }

        $group->products()->sync($syncData);
        return response()->json(['success' => true]);
    }
    public function syncAllGroups(Request $request)
    {
        $request->validate([
            'groups' => 'present|array',
            'groups.*.id' => 'required',
            'groups.*.name' => 'required|string',
            'groups.*.color' => 'nullable|string',
            'groups.*.products' => 'present|array',
        ]);

        DB::beginTransaction();
        try {
            $activeGroupIds = [];
            foreach ($request->groups as $index => $groupData) {
                if (is_numeric($groupData['id'])) {
                    $group = PosGroup::find($groupData['id']);
                } else {
                    $group = new PosGroup();
                }

                if ($group) {
                    $group->name = $groupData['name'];
                    $group->color = $groupData['color'] ?? '#10b981';
                    $group->position = $index;
                    $group->save();

                    $activeGroupIds[] = $group->id;

                    $syncData = [];
                    foreach ($groupData['products'] as $pIndex => $product) {
                        $syncData[$product['id']] = ['position' => $pIndex];
                    }
                    $group->products()->sync($syncData);
                }
            }

            // Delete removed groups
            PosGroup::whereNotIn('id', $activeGroupIds)->delete();

            DB::commit();
            // Fetch the updated groups with products
            $updatedGroups = PosGroup::with('products.category')->orderBy('position')->get();
            return response()->json(['success' => true, 'posGroups' => $updatedGroups]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
