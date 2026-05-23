<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Cashflow;
use App\Models\Shift;
use App\Models\Transaction;

$activeShift = Shift::where('status', 'open')->first();
if ($activeShift) {
    echo "Active Shift:\n";
    echo "ID: {$activeShift->id}\n";
    echo "Opened at: {$activeShift->opened_at}\n";
    echo "Opening cash: {$activeShift->opening_cash}\n\n";

    echo "Transactions during shift:\n";
    $trxs = Transaction::where('shift_id', $activeShift->id)->get();
    foreach ($trxs as $t) {
        echo "- INV: {$t->invoice_number}, Total: {$t->total}, Method: {$t->payment_method}, Status: {$t->status}, Created: {$t->created_at}\n";
    }

    echo "\nCashflows during shift (created_at >= {$activeShift->opened_at}):\n";
    $cfs = Cashflow::where('created_at', '>=', $activeShift->opened_at)->get();
    foreach ($cfs as $cf) {
        echo "- ID: {$cf->id}, Type: {$cf->type}, Category: {$cf->category}, Amount: {$cf->amount}, Source: {$cf->source}, Sync: {$cf->bank_sync_status}, Desc: {$cf->description}, Created: {$cf->created_at}\n";
    }
} else {
    echo "No active shift found!\n";
}
