<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Payment;
use App\Models\Cashflow;
use App\Models\Shift;
use App\Models\StockMutation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransactionController extends Controller
{
    protected $financialService;

    public function __construct(\App\Services\FinancialReportService $financialService)
    {
        $this->financialService = $financialService;
    }

    public function index(Request $request)
    {
        $users = \App\Models\User::all();
        $worksheetId = session('active_worksheet_id');

        $activeShift = Shift::activeShiftForUser(auth()->id());
        $isLiveShift = $request->shift === 'live' || $request->period === 'live';
        $period = $request->period ?? 'today';

        $dfParam = is_array($request->date_from) ? null : $request->date_from;
        $dtParam = is_array($request->date_to) ? null : $request->date_to;

        if ($isLiveShift && $activeShift) {
            $dateFrom = $activeShift->opened_at->copy()->startOfDay();
            $dateTo = now()->endOfDay();
        } else {
            if ($dfParam && $dtParam) {
                $dateFrom = Carbon::parse($dfParam)->startOfDay();
                $dateTo = Carbon::parse($dtParam)->endOfDay();
            } else {
                switch ($period) {
                    case 'yesterday':
                        $dateFrom = now()->subDay()->startOfDay();
                        $dateTo = now()->subDay()->endOfDay();
                        break;
                    case 'week':
                        $dateFrom = now()->startOfWeek();
                        $dateTo = now()->endOfWeek();
                        break;
                    case 'month':
                        $dateFrom = now()->startOfMonth();
                        $dateTo = now()->endOfMonth();
                        break;
                    case 'year':
                        $dateFrom = now()->startOfYear();
                        $dateTo = now()->endOfYear();
                        break;
                    case 'today':
                    default:
                        $dateFrom = now()->startOfDay();
                        $dateTo = now()->endOfDay();
                        break;
                }
            }
        }

        // --- Filter by status pill ---
        $sParam = $request->status;
        $statusFilter = is_array($sParam) ? null : $sParam; 

        // --- Build transaction items ---
        $openedAt = $activeShift?->opened_at;
        $closedAt = $activeShift?->closed_at ?? now();

        $txQuery = Transaction::withoutGlobalScopes()->with(['user', 'items'])
            ->when($statusFilter !== 'piutang', function($query) use ($isLiveShift, $activeShift, $openedAt, $closedAt, $dateFrom, $dateTo) {
                $query->when($isLiveShift && $activeShift, function($q) use ($activeShift, $openedAt, $closedAt) {
                    $q->where(function($sq) use ($activeShift, $openedAt, $closedAt) {
                        $sq->where('shift_id', $activeShift->id)
                          ->orWhere(fn($q2) => $q2->whereNull('shift_id')->whereBetween('created_at', [$openedAt, $closedAt]));
                    });
                })
                ->when(!($isLiveShift && $activeShift), function($q) use ($dateFrom, $dateTo) {
                    $q->whereDate('created_at', '>=', $dateFrom)
                      ->whereDate('created_at', '<=', $dateTo);
                });
            })
            ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
            ->when($request->payment_method, fn($q) => $q->where('payment_method', $request->payment_method))
            ->when($statusFilter === 'piutang', fn($q) => $q->where('status', 'pending'))
            ->when($statusFilter === 'lunas', fn($q) => $q->where('status', 'completed')->where('payment_method', 'piutang'))
            ->when($request->search, fn($q) => $q->where(function($sq) use ($request) {
                $sq->where('invoice_number', 'like', "%{$request->search}%")
                   ->orWhere('customer_name', 'like', "%{$request->search}%");
            }))
            ->when($worksheetId && !$isLiveShift, fn($q) => $q->where('worksheet_id', $worksheetId));

        // --- Build expense items ---
        $expenseQuery = Cashflow::withoutGlobalScopes()->with(['user', 'worksheet'])
            ->where('transaction_category', 'expense')
            ->whereNull('reference')
            ->when($isLiveShift && $activeShift, function($q) use ($activeShift, $openedAt, $closedAt) {
                $q->where(function($sq) use ($activeShift, $openedAt, $closedAt) {
                    $sq->where('shift_id', $activeShift->id)
                      ->orWhere(fn($q2) => $q2->whereNull('shift_id')->whereBetween('created_at', [$openedAt, $closedAt]));
                });
            })
            ->when(!($isLiveShift && $activeShift), function($q) use ($dateFrom, $dateTo) {
                $q->whereDate('transaction_date', '>=', $dateFrom)
                  ->whereDate('transaction_date', '<=', $dateTo);
            })
            ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
            ->when($request->payment_method, function($q) use ($request) {
                if ($request->payment_method === 'cash') {
                    $q->where('source', 'pos_cash');
                } elseif ($request->payment_method === 'bank') {
                    $q->whereIn('source', ['pos_bank', 'transfer']);
                }
            })
            ->when($request->search, fn($q) => $q->where('description', 'like', "%{$request->search}%"))
            ->when($worksheetId && !$isLiveShift, fn($q) => $q->where('worksheet_id', $worksheetId));

        // --- Compile based on type filter ---
        $items = collect();
        $type = $request->type;

        if (!$type || $type === 'penjualan') {
            $txItems = $txQuery->get()->map(function($t) {
                return (object)[
                    'sort_date' => $t->created_at,
                    'type' => 'penjualan',
                    'model' => $t,
                ];
            });
            $items = $items->merge($txItems);
        }

        if ((!$type || $type === 'expense') && !$statusFilter) {
            $expItems = $expenseQuery->get()->map(function($e) {
                return (object)[
                    'sort_date' => $e->transaction_date,
                    'type' => 'expense',
                    'model' => $e,
                ];
            });
            $items = $items->merge($expItems);
        }

        // Sort descending by date
        $items = $items->sortByDesc('sort_date')->values();

        // Manual pagination
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 20);
        $total = $items->count();
        $paginatedItems = $items->slice(($page - 1) * $perPage, $perPage)->values();
        $transactions = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedItems, $total, $perPage, $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // --- Pill filter counts ---
        $baseTx = Transaction::when($worksheetId && !$isLiveShift, fn($q) => $q->where('worksheet_id', $worksheetId))
            ->when($isLiveShift && $activeShift, function($q) use ($activeShift, $openedAt, $closedAt) {
                $q->where(function($sq) use ($activeShift, $openedAt, $closedAt) {
                    $sq->where('shift_id', $activeShift->id)
                      ->orWhere(fn($q2) => $q2->whereNull('shift_id')->whereBetween('created_at', [$openedAt, $closedAt]));
                });
            })
            ->when(!($isLiveShift && $activeShift), function($q) use ($dateFrom, $dateTo) {
                $q->whereDate('created_at', '>=', $dateFrom)
                  ->whereDate('created_at', '<=', $dateTo);
            });

        $baseExp = Cashflow::withoutGlobalScopes()->where('transaction_category', 'expense')->whereNull('reference')
            ->when($worksheetId && !$isLiveShift, fn($q) => $q->where('worksheet_id', $worksheetId))
            ->when($isLiveShift && $activeShift, function($q) use ($activeShift, $openedAt, $closedAt) {
                $q->where(function($sq) use ($activeShift, $openedAt, $closedAt) {
                    $sq->where('shift_id', $activeShift->id)
                      ->orWhere(fn($q2) => $q2->whereNull('shift_id')->whereBetween('created_at', [$openedAt, $closedAt]));
                });
            })
            ->when(!($isLiveShift && $activeShift), function($q) use ($dateFrom, $dateTo) {
                $q->whereDate('transaction_date', '>=', $dateFrom)
                  ->whereDate('transaction_date', '<=', $dateTo);
            })
            ->when($request->payment_method, function($q) use ($request) {
                if ($request->payment_method === 'cash') {
                    $q->where('source', 'pos_cash');
                } elseif ($request->payment_method === 'bank') {
                    $q->whereIn('source', ['pos_bank', 'transfer']);
                }
            });

        $countPenjualan = (clone $txQuery)->count();
        $countExpense = (clone $baseExp)->count();
        $countAll = $countPenjualan + $countExpense;

        $countPiutang = Transaction::withoutGlobalScopes()
            ->when($worksheetId && !$isLiveShift, fn($q) => $q->where('worksheet_id', $worksheetId))
            ->where('status', 'pending')->count();
        $countLunas = (clone $txQuery)->where('status', 'completed')->where('payment_method', 'piutang')->count();
        $countCash = (clone $txQuery)->where('payment_method', 'cash')->count();
        $countQris = (clone $txQuery)->where('payment_method', 'qris')->count();
        $countTransfer = (clone $txQuery)->where('payment_method', 'transfer')->count();
        $countDebit = (clone $txQuery)->where('payment_method', 'debit')->count();

        // --- Stats ---
        $activeShift = Shift::activeShiftForUser(auth()->id());

        $saldoLaci = 0;
        $saldoLaciAwal = 0;
        if ($activeShift) {
            $summary = $this->financialService->getShiftSummary($activeShift->id, $worksheetId);
            $saldoLaciAwal = (float) $activeShift->opening_cash;
            
            $transfers = (float) \App\Models\Cashflow::withoutGlobalScopes()
                ->where('shift_id', $activeShift->id)
                ->where('source', 'pos_cash')
                ->where('category', '!=', 'Penjualan')
                ->where('transaction_category', '!=', 'expense')
                ->sum(\Illuminate\Support\Facades\DB::raw('CASE WHEN type = "income" THEN amount ELSE -amount END'));

            $saldoLaci = $saldoLaciAwal + $summary->cash_sales - $summary->cash_expense + $transfers;
        }

        $baseBank = Cashflow::withoutGlobalScopes()->whereIn('source', ['pos_bank', 'transfer'])
            ->when($worksheetId && !$isLiveShift, fn($q) => $q->where('worksheet_id', $worksheetId));
        $bankIncome = (clone $baseBank)->where('type', 'income')->sum('amount');
        $bankExpense = (clone $baseBank)->where('type', 'expense')->sum('amount');
        $saldoBank = $bankIncome - $bankExpense;

        // --- Stats from Unified Service ---
        $todaySummary = $isLiveShift && $activeShift 
            ? $this->financialService->getShiftSummary($activeShift->id, $worksheetId)
            : $this->financialService->getSummary($dateFrom, $dateTo, $worksheetId);

        $todayTotalSales = $todaySummary->total_income;
        $todayExpenses = $todaySummary->total_expense;
        $todayCashExpense = $todaySummary->cash_expense;
        $todayBankExpense = $todaySummary->bank_expense;
        $todayNet = $todaySummary->net_profit;
        
        $totalPiutang = Transaction::withoutGlobalScopes()
            ->when($worksheetId && !$isLiveShift, fn($q) => $q->where('worksheet_id', $worksheetId))
            ->piutang()->get()->sum(fn($t) => $t->total - $t->paid_so_far);

        $todayQris = (clone $txQuery)->completed()->where('payment_method', 'qris')->sum('total');
        $todayCash = (clone $txQuery)->completed()->where('payment_method', 'cash')->sum('total');
        $todayTransfer = (clone $txQuery)->completed()->where('payment_method', 'transfer')->sum('total');
        $todayDebit = (clone $txQuery)->completed()->where('payment_method', 'debit')->sum('total');

        return view('transactions.index', compact(
            'transactions', 'users', 'todayTotalSales', 'todayExpenses', 'todayCashExpense', 'todayBankExpense', 'todayNet',
            'saldoLaci', 'saldoLaciAwal', 'saldoBank', 'totalPiutang', 'activeShift',
            'countAll', 'countPenjualan', 'countExpense', 'countPiutang', 'countLunas', 'countCash', 'countQris', 'countTransfer', 'countDebit',
            'todayQris', 'todayCash', 'todayTransfer', 'todayDebit'
        ));
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['items.product', 'user', 'shift', 'payments.user']);
        
        if (request()->wantsJson()) {
            $settings = \App\Models\Setting::getMultiple([
                'store_name', 'store_address', 'store_phone', 'store_footer'
            ]);

            return response()->json([
                'invoice_number' => $transaction->invoice_number,
                'created_at' => $transaction->created_at->format('d/m/y H:i'),
                'store_name' => $settings['store_name'] ?? 'MONOFRAME STUDIO',
                'store_address' => $settings['store_address'] ?? '',
                'store_phone' => $settings['store_phone'] ?? '',
                'store_footer' => $settings['store_footer'] ?? 'Terima kasih telah berbelanja!',
                'subtotal' => $transaction->subtotal,
                'discount' => $transaction->discount,
                'tax' => $transaction->tax,
                'delivery_fee' => $transaction->delivery_fee,
                'delivery_destination' => $transaction->delivery_destination,
                'total' => $transaction->total,
                'paid_amount' => $transaction->paid_amount,
                'change_amount' => $transaction->change_amount,
                'payment_method' => strtoupper($transaction->payment_method),
                'status' => $transaction->status === 'completed' ? 'LUNAS' : 'PENDING',
                'customer_name' => $transaction->customer_name,
                'customer_phone' => $transaction->customer_phone,
                'notes' => $transaction->notes,
                'user' => $transaction->user,
                'items' => $transaction->items->map(fn($i) => [
                    'product_name' => $i->product_name,
                    'quantity' => $i->quantity,
                    'price' => $i->price,
                    'subtotal' => $i->subtotal
                ])
            ]);
        }
        
        return view('transactions.show', compact('transaction'));
    }

    public function receiptText(Transaction $transaction)
    {
        $transaction->load(['items', 'user']);
        $settings = \App\Models\Setting::getMultiple([
            'store_name', 'store_address', 'store_phone', 'store_footer'
        ]);

        $lines = [];

        $storeName = $settings['store_name'] ?? 'MONOFRAME STUDIO';
        $lines[] = "*{$storeName}*";

        if ($settings['store_address'] ?? false) {
            $lines[] = $settings['store_address'];
        }
        if ($settings['store_phone'] ?? false) {
            $lines[] = "Telp: {$settings['store_phone']}";
        }

        $lines[] = str_repeat('-', 32);

        $lines[] = "No      : {$transaction->invoice_number}";
        $lines[] = "Tgl     : {$transaction->created_at->format('d/m/Y H:i')}";
        $lines[] = "Kasir   : " . ($transaction->user->name ?? 'Admin');

        if ($transaction->customer_name) {
            $lines[] = "Pelanggan: {$transaction->customer_name}";
        }

        $lines[] = str_repeat('-', 32);

        foreach ($transaction->items as $item) {
            $lines[] = $item->product_name;
            $line = str_pad("{$item->quantity} x " . number_format($item->price, 0), 20)
                  . str_pad(number_format($item->subtotal, 0), 12, ' ', STR_PAD_LEFT);
            $lines[] = $line;
        }

        $lines[] = str_repeat('-', 32);

        $lines[] = str_pad('Subtotal', 20) . str_pad(number_format($transaction->subtotal, 0), 12, ' ', STR_PAD_LEFT);

        if ($transaction->delivery_fee > 0) {
            $dest = $transaction->delivery_destination ? " ({$transaction->delivery_destination})" : '';
            $lines[] = str_pad("Ongkir{$dest}", 20) . str_pad(number_format($transaction->delivery_fee, 0), 12, ' ', STR_PAD_LEFT);
        }

        if ($transaction->discount > 0) {
            $lines[] = str_pad('Diskon', 20) . str_pad('-'.number_format($transaction->discount, 0), 12, ' ', STR_PAD_LEFT);
        }

        if ($transaction->tax > 0) {
            $lines[] = str_pad('Pajak', 20) . str_pad(number_format($transaction->tax, 0), 12, ' ', STR_PAD_LEFT);
        }

        $lines[] = str_pad('TOTAL', 20) . str_pad(number_format($transaction->total, 0), 12, ' ', STR_PAD_LEFT);
        $lines[] = str_repeat('-', 32);

        $methodLabel = strtoupper($transaction->payment_method);
        $lines[] = str_pad($methodLabel, 20) . str_pad(number_format($transaction->paid_amount, 0), 12, ' ', STR_PAD_LEFT);

        if ($transaction->change_amount > 0) {
            $lines[] = str_pad('KEMBALI', 20) . str_pad(number_format($transaction->change_amount, 0), 12, ' ', STR_PAD_LEFT);
        }

        $lines[] = str_repeat('-', 32);
        $lines[] = $settings['store_footer'] ?? 'Terima Kasih Atas Kunjungan Anda';

        return response()->json([
            'phone' => $transaction->customer_phone,
            'message' => implode("\n", $lines),
        ]);
    }

    /**
     * Pay piutang (partial or full)
     */
    public function payPiutang(Request $request, Transaction $transaction)
    {
        if ($transaction->status !== 'pending') {
            return back()->with('error', 'Transaksi ini bukan piutang!');
        }

        $remaining = $transaction->total - $transaction->paid_so_far;

        $request->validate([
            'amount' => 'required|numeric|min:1|max:' . $remaining,
            'payment_method' => 'required|in:cash,transfer,qris,debit',
            'notes' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            // Record the payment
            Payment::create([
                'transaction_id' => $transaction->id,
                'user_id' => auth()->id(),
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes,
            ]);

            $newPaidSoFar = $transaction->paid_so_far + $request->amount;
            $transaction->update(['paid_so_far' => $newPaidSoFar]);

            // If fully paid, mark as completed
            if ($newPaidSoFar >= $transaction->total) {
                $transaction->update(['status' => 'completed']);
            }

            // Record income in cashflow
            $sourceMap = [
                'cash' => 'pos_cash',
                'qris' => 'pos_bank',
                'debit' => 'pos_bank',
                'transfer' => 'transfer',
            ];
            $source = $sourceMap[$request->payment_method] ?? 'pos';

            Cashflow::create([
                'user_id' => auth()->id(),
                'shift_id' => Shift::activeShiftForUser(auth()->id())?->id,
                'worksheet_id' => $transaction->worksheet_id,
                'type' => 'income',
                'transaction_category' => 'income',
                'category' => 'Pelunasan Piutang',
                'description' => 'Pelunasan ' . $transaction->invoice_number . ($newPaidSoFar >= $transaction->total ? ' (LUNAS)' : ' (Sebagian)'),
                'amount' => $request->amount,
                'reference' => $transaction->invoice_number,
                'reference_id' => $transaction->id,
                'source' => $source,
                'transaction_date' => today(),
            ]);

            DB::commit();
            return back()->with('success', 'Pembayaran piutang berhasil dicatat!' . ($newPaidSoFar >= $transaction->total ? ' Piutang LUNAS.' : ''));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function cancel(Transaction $transaction)
    {
        if ($transaction->status !== 'completed') {
            return back()->with('error', 'Transaksi tidak bisa dibatalkan!');
        }

        // Kembalikan stok
        foreach ($transaction->items as $item) {
            $product = $item->product;
            if ($product && !$product->isStockless()) {
                $stockBefore = $product->stock;
                $product->increment('stock', $item->quantity);
                StockMutation::create([
                    'product_id' => $product->id,
                    'user_id' => auth()->id(),
                    'type' => 'in',
                    'quantity' => $item->quantity,
                    'stock_before' => $stockBefore,
                    'stock_after' => $product->fresh()->stock,
                    'reference' => $transaction->invoice_number,
                    'notes' => 'Pengembalian stok - transaksi dibatalkan',
                ]);
            }
        }

        $transaction->update(['status' => 'cancelled']);

        $sourceMap = [
            'cash' => 'pos_cash',
            'qris' => 'pos_bank',
            'debit' => 'pos_bank',
            'transfer' => 'transfer',
        ];
        $source = $sourceMap[$transaction->payment_method] ?? 'pos';

        Cashflow::create([
            'user_id' => auth()->id(),
            'shift_id' => $transaction->shift_id,
            'worksheet_id' => $transaction->worksheet_id,
            'type' => 'expense',
            'transaction_category' => 'expense',
            'category' => 'Refund / Retur',
            'description' => 'Refund POS - Batal ' . $transaction->invoice_number,
            'amount' => $transaction->total,
            'reference' => $transaction->invoice_number,
            'reference_id' => $transaction->id,
            'source' => $source,
            'transaction_date' => today(),
        ]);

        return back()->with('success', 'Transaksi berhasil dibatalkan dan stok dikembalikan!');
    }

    public function destroy(Transaction $transaction)
    {
        DB::beginTransaction();
        try {
            Cashflow::where('reference', $transaction->invoice_number)
                    ->orWhere('reference_id', $transaction->id)
                    ->delete();
            StockMutation::where('reference', $transaction->invoice_number)->delete();
            $transaction->delete();

            DB::commit();
            return back()->with('success', 'Transaksi ' . $transaction->invoice_number . ' berhasil dihapus permanen!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus transaksi: ' . $e->getMessage());
        }
    }
}
