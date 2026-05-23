<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Cashflow;
use Illuminate\Support\Facades\DB;

$stats = Cashflow::select('transaction_category', 'type', DB::raw('count(*) as count'), DB::raw('sum(amount) as total'))
    ->groupBy('transaction_category', 'type')
    ->get();

echo "DISTRIBUSI DATA CASHFLOW (SETELAH FIX):\n";
echo str_repeat("-", 80) . "\n";
foreach($stats as $s) {
    echo sprintf(
        "Category: %-15s | Type: %-10s | Count: %-5d | Total: Rp %s\n",
        $s->transaction_category ?: 'NULL',
        $s->type,
        $s->count,
        number_format($s->total, 0, ',', '.')
    );
}
echo str_repeat("-", 80) . "\n";
