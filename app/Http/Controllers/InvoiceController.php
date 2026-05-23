<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::query();
        
        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->date_from) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('invoice_number', 'like', "%{$request->search}%")
                  ->orWhere('client_name', 'like', "%{$request->search}%")
                  ->orWhere('client_company', 'like', "%{$request->search}%");
            });
        }
        
        if ($activeWorksheetId = session('active_worksheet_id')) {
            $query->where('worksheet_id', $activeWorksheetId);
        }

        // Calculate Totals (Global, ignoring pagination but respecting other filters)
        $statsQuery = clone $query;
        $totalPiutang = (clone $statsQuery)->where('status', 'pending')->sum('total_amount');
        $totalDibayar = (clone $statsQuery)->sum('paid_amount');
        $totalSisa = (clone $statsQuery)->sum(DB::raw('total_amount - paid_amount'));
        $invoiceMenunggu = (clone $statsQuery)->where('status', 'pending')->count();

        $invoices = $query->with(['items', 'payments'])->latest()->paginate(15)->withQueryString();
        return view('invoices.index', compact('invoices', 'totalPiutang', 'totalDibayar', 'totalSisa', 'invoiceMenunggu'));
    }

    public function create(Request $request)
    {
        $lastInvoice = Invoice::latest()->first();
        $nextNumber = 1;
        if ($lastInvoice) {
            $parts = explode('-', $lastInvoice->invoice_number);
            $lastId = end($parts);
            $nextNumber = (is_numeric($lastId)) ? (int)$lastId + 1 : 1;
        }
        $invoiceNumber = 'INV-' . date('dmy') . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        
        $transaction = null;
        if ($request->transaction_id) {
            $transaction = \App\Models\Transaction::with('items')->find($request->transaction_id);
        }
        
        return view('invoices.create', compact('invoiceNumber', 'transaction'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'invoice_number' => 'required|unique:invoices',
            'date' => 'required|date',
            'client_name' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $invoice = Invoice::create([
                'invoice_number' => $request->invoice_number,
                'date' => $request->date,
                'due_date' => $request->due_date,
                'business_name' => $request->business_name ?? 'MONOFRAME STUDIO',
                'business_email' => $request->business_email,
                'business_phone' => $request->business_phone,
                'business_address' => $request->business_address,
                'client_name' => $request->client_name,
                'client_company' => $request->client_company,
                'client_phone' => $request->client_phone,
                'client_email' => $request->client_email,
                'client_address' => $request->client_address,
                'subtotal' => $request->subtotal ?? 0,
                'discount_type' => $request->discount_type ?? 'fixed',
                'discount_value' => $request->discount_value ?? 0,
                'discount' => $request->discount ?? 0,
                'total_amount' => $request->total_amount ?? 0,
                'notes' => $request->notes,
                'worksheet_id' => session('active_worksheet_id'),
                'created_by' => auth()->id(),
                'status' => $request->status ?? 'pending',
            ]);

            foreach ($request->items as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'name' => $item['name'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['quantity'] * $item['price'],
                ]);
            }

            // If there's an initial payment (DP)
            if ($request->initial_payment > 0) {
                InvoicePayment::create([
                    'invoice_id' => $invoice->id,
                    'amount' => $request->initial_payment,
                    'payment_method' => $request->payment_method ?? 'tunai',
                    'payment_date' => $request->date,
                    'notes' => 'Pembayaran Awal / DP',
                ]);
                
                $invoice->paid_amount = $request->initial_payment;
                $invoice->status = $invoice->paid_amount >= $invoice->total_amount ? 'paid' : 'partial';
                $invoice->save();
            }

            DB::commit();
            
            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'id' => $invoice->id]);
            }

            return redirect()->route('invoices.index')->with('success', 'Invoice berhasil dibuat!');
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->with('error', 'Gagal membuat invoice: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['items', 'payments']);
        return view('invoices.show', compact('invoice'));
    }

    public function addPayment(Request $request, Invoice $invoice)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|string',
            'payment_date' => 'required|date',
        ]);

        try {
            DB::beginTransaction();

            InvoicePayment::create([
                'invoice_id' => $invoice->id,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'payment_date' => $request->payment_date,
                'notes' => $request->notes,
            ]);

            $invoice->paid_amount += $request->amount;
            $invoice->status = $invoice->paid_amount >= $invoice->total_amount ? 'paid' : 'partial';
            $invoice->save();

            DB::commit();
            return back()->with('success', 'Pembayaran berhasil dicatat!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mencatat pembayaran: ' . $e->getMessage());
        }
    }

    public function downloadPdf(Invoice $invoice)
    {
        $invoice->load(['items', 'payments']);
        $pdf = Pdf::loadView('invoices.pdf', compact('invoice'))
                  ->setPaper('a4', 'portrait');
        
        return $pdf->download($invoice->invoice_number . '.pdf');
    }

    public function edit(Invoice $invoice)
    {
        $invoice->load('items');
        return view('invoices.edit', compact('invoice'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $request->validate([
            'invoice_number' => 'required|unique:invoices,invoice_number,' . $invoice->id,
            'date' => 'required|date',
            'client_name' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $invoice->update([
                'invoice_number' => $request->invoice_number,
                'date' => $request->date,
                'due_date' => $request->due_date,
                'business_name' => $request->business_name,
                'business_email' => $request->business_email,
                'business_phone' => $request->business_phone,
                'business_address' => $request->business_address,
                'client_name' => $request->client_name,
                'client_company' => $request->client_company,
                'client_phone' => $request->client_phone,
                'client_email' => $request->client_email,
                'client_address' => $request->client_address,
                'subtotal' => $request->subtotal ?? 0,
                'discount_type' => $request->discount_type ?? 'fixed',
                'discount_value' => $request->discount_value ?? 0,
                'discount' => $request->discount ?? 0,
                'total_amount' => $request->total_amount ?? 0,
                'paid_amount' => $request->initial_payment ?? $invoice->paid_amount,
                'status' => $request->status ?? (($request->initial_payment ?? $invoice->paid_amount) >= ($request->total_amount ?? 0) ? 'paid' : (($request->initial_payment ?? $invoice->paid_amount) > 0 ? 'partial' : 'pending')),
                'notes' => $request->notes,
            ]);

            // Sync items (Delete old and create new)
            $invoice->items()->delete();
            foreach ($request->items as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'name' => $item['name'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['quantity'] * $item['price'],
                ]);
            }

            // Update first payment if exists
            if ($invoice->payments()->exists()) {
                $invoice->payments()->first()->update([
                    'amount' => $request->initial_payment ?? $invoice->paid_amount,
                    'payment_method' => $request->payment_method ?? $invoice->payments()->first()->payment_method
                ]);
            } elseif ($request->initial_payment > 0) {
                // If no payment existed but now we add one
                InvoicePayment::create([
                    'invoice_id' => $invoice->id,
                    'amount' => $request->initial_payment,
                    'payment_method' => $request->payment_method ?? 'tunai',
                    'payment_date' => $invoice->date,
                    'notes' => 'Pembayaran Awal / DP (Updated)',
                ]);
            }

            DB::commit();
            
            if ($request->wantsJson()) {
                return response()->json(['success' => true]);
            }

            return redirect()->route('invoices.index')->with('success', 'Invoice berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', 'Gagal memperbarui invoice: ' . $e->getMessage());
        }
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return redirect()->route('invoices.index')->with('success', 'Invoice berhasil dihapus!');
    }
}
