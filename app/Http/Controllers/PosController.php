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
        $activeShift = Shift::activeShiftForUser(auth()->id());

        $categories = Category::where('is_active', true)->get();
        $products = Product::active()->with('category')->orderBy('name')->get();
        $posGroups = PosGroup::with('products.category')->orderBy('position')->get();
        $settings = \App\Models\Setting::getMultiple([
            'tax_rate', 'active_payment_methods', 'bank_name', 'bank_account', 'bank_holder', 'qris_image',
            'custom_price_enabled', 'custom_price_allow_hpp', 'custom_price_show_badge',
            'custom_price_require_reason', 'custom_price_access', 'delivery_presets',
            'cashout_source_access', 'cashout_role_access'
        ]);
        
        // BEP Analysis Data
        $totalCapital = \App\Models\Capital::sum('total_amount');
        $monthlyRevenue = Transaction::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total');

        // Promo Products
        $promoProductIds = Product::active()->where('is_promo', true)->pluck('id')->toArray();

        // Best Seller Products (Top 10 by quantity in last 30 days)
        $bestSellerProductIds = TransactionItem::select('product_id', DB::raw('SUM(quantity) as total_sold'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->limit(10)
            ->pluck('product_id')
            ->toArray();

        // Expense Categories for Cash Out
        $expenseCategories = \App\Models\ExpenseCategory::where('is_active', true)
            ->get()
            ->unique('name')
            ->groupBy('parent_category');

        return view('pos.index', compact(
            'activeShift', 'categories', 'products', 'posGroups', 'settings', 
            'totalCapital', 'monthlyRevenue', 'promoProductIds', 'bestSellerProductIds',
            'expenseCategories'
        ));
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
            'payment_method' => 'required|in:cash,transfer,qris,debit,piutang',
            'paid_amount' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|in:nominal,percentage',
            'delivery_fee' => 'nullable|numeric|min:0',
            'customer_name' => 'nullable|string|max:100',
            'customer_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ]);

        $activeShift = Shift::activeShiftForUser(auth()->id());
        if (!$activeShift) {
            return response()->json(['error' => 'Tidak ada shift aktif!'], 422);
        }

        DB::beginTransaction();
        try {
            $subtotal = 0;
            $itemsData = [];

            foreach ($request->items as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);

                $isStockless = $product->isStockless();

                if (!$isStockless && $product->stock < $item['quantity']) {
                    DB::rollBack();
                    return response()->json([
                        'error' => "Stok {$product->name} tidak cukup! Stok tersisa: {$product->stock}"
                    ], 422);
                }

                $isCustomPrice = !empty($item['is_custom_price']) ? true : false;
                $customPrice = $isCustomPrice ? $item['custom_price'] : null;
                $customHpp = $isCustomPrice && isset($item['custom_hpp']) ? $item['custom_hpp'] : null;
                $customPriceReason = $isCustomPrice && isset($item['custom_price_reason']) ? $item['custom_price_reason'] : null;

                $usedPrice = $isCustomPrice ? $customPrice : $item['price'];
                $usedCostPrice = $isCustomPrice && $customHpp !== null ? $customHpp : $product->cost_price;

                $itemSubtotal = ($usedPrice * $item['quantity']) - ($item['discount'] ?? 0);
                $subtotal += $itemSubtotal;

                if (!$isStockless) {
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
                }

                $itemsData[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $usedPrice,
                    'cost_price' => $usedCostPrice,
                    'quantity' => $item['quantity'],
                    'discount' => $item['discount'] ?? 0,
                    'subtotal' => $itemSubtotal,
                    'is_custom_price' => $isCustomPrice,
                    'custom_price' => $customPrice,
                    'custom_hpp' => $customHpp,
                    'custom_price_reason' => $customPriceReason,
                ];
            }

            $discountType = $request->discount_type ?? 'nominal';
            $discountValue = $request->discount ?? 0;
            $discount = $discountType === 'percentage' ? ($subtotal * ($discountValue / 100)) : $discountValue;
            
            $taxRate = \App\Models\Setting::get('tax_rate', 0);
            $tax = ($subtotal - $discount) * ($taxRate / 100);
            
            $deliveryFee = $request->delivery_fee ?? 0;
            
            $total = $subtotal - $discount + $tax + $deliveryFee;
            $change = $request->paid_amount - $total;

            $isPiutang = $request->payment_method === 'piutang';
            $dpAmount = $isPiutang ? max(0, $request->paid_amount) : 0;

            $transaction = Transaction::create([
                'invoice_number' => Transaction::generateInvoiceNumber(true),
                'shift_id' => $activeShift->id,
                'user_id' => auth()->id(),
                'subtotal' => $subtotal,
                'discount' => $discount,
                'discount_type' => $discountType,
                'tax' => $tax,
                'delivery_fee' => $deliveryFee,
                'total' => $total,
                'paid_amount' => $isPiutang ? $dpAmount : $request->paid_amount,
                'change_amount' => $isPiutang ? 0 : max(0, $change),
                'paid_so_far' => $isPiutang ? $dpAmount : $total,
                'payment_method' => $request->payment_method,
                'status' => ($isPiutang && $dpAmount < $total) ? 'pending' : 'completed',
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'notes' => $request->notes,
                'worksheet_id' => $activeShift->worksheet_id,
            ]);

            foreach ($itemsData as &$item) {
                $item['transaction_id'] = $transaction->id;
            }
            TransactionItem::insert($itemsData);

            // Dispatch event to handle Cashflow creation (only for non-piutang or piutang with DP)
            if (!$isPiutang) {
                event(new \App\Events\TransactionCreated($transaction));
            } elseif ($dpAmount > 0) {
                // Record DP as income
                Cashflow::create([
                    'user_id' => auth()->id(),
                    'shift_id' => $activeShift->id,
                    'type' => 'income',
                    'category' => 'Uang Muka (DP)',
                    'description' => 'DP pesanan #' . $transaction->invoice_number,
                    'amount' => $dpAmount,
                    'source' => $request->dp_method ?? 'pos_cash',
                    'transaction_date' => today(),
                    'reference_id' => $transaction->id,
                    'transaction_category' => 'income',
                    'worksheet_id' => $activeShift->worksheet_id,
                ]);
            }

            DB::commit();

            $transaction->load('items');

            // --- AUTO OPEN CASH DRAWER LOGIC (TEMPORARILY DISABLED) ---
            $printerStatus = null;
            /*
            if ($request->payment_method === 'cash') {
                try {
                    $printerService = app(\App\Services\PrinterService::class);
                    $printResult = $printerService->openDrawer($transaction->id);
                    $printerStatus = $printResult;
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Drawer Error: ' . $e->getMessage());
                    $printerStatus = ['success' => false, 'message' => 'Gagal membuka laci kasir (System Error)'];
                }
            }
            */
            // ------------------------------------

            return response()->json([
                'success' => true,
                'transaction' => $transaction,
                'change' => max(0, $change),
                'invoice_number' => $transaction->invoice_number,
                'printer_status' => $printerStatus
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function printReceipt(Request $request, Transaction $transaction)
    {
        try {
            $transaction->load(['items', 'user']);
            $printerService = app(\App\Services\PrinterService::class);
            
            // Check if payment was cash to open drawer
            $shouldOpenDrawer = $transaction->payment_method === 'cash';
            
            $result = $printerService->printReceiptAndOpenDrawer($transaction, $shouldOpenDrawer);
            
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function receipt(Request $request, Transaction $transaction)
    {
        $transaction->load(['items', 'user', 'shift']);
        $settings = \App\Models\Setting::getMultiple([
            'store_name', 'store_address', 'store_phone', 'store_footer'
        ]);

        $paperSize = $request->query('paper', '80mm');
        $fontSize = $request->query('font', 'medium');
        $fontSmall = $request->query('small_font') === 'true';

        return view('pos.receipt', compact('transaction', 'settings', 'paperSize', 'fontSize', 'fontSmall'));
    }

    public function testReceipt(Request $request)
    {
        $settings = \App\Models\Setting::getMultiple([
            'store_name', 'store_address', 'store_phone', 'store_footer'
        ]);

        $paperSize = $request->query('paper', '58mm');
        $fontSize = $request->query('font', 'medium');
        $fontSmall = $request->query('small_font') === 'true';

        // Create a dummy transaction object for the view
        $transaction = new \stdClass();
        $transaction->invoice_number = 'TEST-PRINT';
        $transaction->created_at = now();
        $transaction->subtotal = 10000;
        $transaction->discount = 0;
        $transaction->tax = 0;
        $transaction->total = 10000;
        $transaction->paid_amount = 10000;
        $transaction->change_amount = 0;
        $transaction->payment_method = 'tunai';
        $transaction->customer_name = 'Customer Test';
        $transaction->user = (object) ['name' => auth()->user()->name ?? 'Kasir'];
        
        $transaction->items = collect([
            (object) [
                'product_name' => 'Item Testing Printer',
                'quantity' => 1,
                'price' => 10000,
                'discount' => 0,
                'subtotal' => 10000
            ]
        ]);

        return view('pos.receipt', compact('transaction', 'settings', 'paperSize', 'fontSize', 'fontSmall'));
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

    /**
     * POS Quick Expense — kasir bisa catat pengeluaran dari laci
     */
    public function storeExpense(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:1',
            'category' => 'nullable|string|max:100',
        ]);

        $activeShift = Shift::activeShiftForUser(auth()->id());
        if (!$activeShift) {
            return response()->json(['error' => 'Tidak ada shift aktif!'], 422);
        }

        Cashflow::create([
            'user_id' => auth()->id(),
            'shift_id' => $activeShift->id,
            'type' => 'expense',
            'transaction_category' => 'expense',
            'category' => $request->category ?? 'Pengeluaran Kasir',
            'description' => $request->description,
            'amount' => $request->amount,
            'source' => 'pos_cash',
            'transaction_date' => today(),
            'worksheet_id' => $activeShift->worksheet_id,
        ]);

        return response()->json(['success' => true, 'message' => 'Pengeluaran berhasil dicatat.']);
    }
}
