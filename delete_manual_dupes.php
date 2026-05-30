<?php
use App\Models\Invoice;

$invoices = Invoice::whereIn('id', [28, 29])->get();
$count = 0;
foreach ($invoices as $inv) {
    $inv->items()->delete();
    $inv->delete();
    $count++;
}
echo 'Deleted ' . $count . ' manually created duplicate invoices.';
