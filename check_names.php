<?php
use App\Models\Invoice;

$invoices = Invoice::where('client_name', 'like', '%Danny%')
    ->orWhere('client_name', 'like', '%CINTRIA%')
    ->get(['id', 'invoice_number', 'client_name', 'created_at', 'total_amount', 'status']);

foreach ($invoices as $inv) {
    echo "ID: {$inv->id} | No: {$inv->invoice_number} | Client: {$inv->client_name} | Total: {$inv->total_amount} | Status: {$inv->status} | Created: {$inv->created_at}\n";
}
