<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$trx = App\Models\Transaction::where('shift_id', 6)->first();
if ($trx) {
    echo "Found trx: {$trx->id}\n";
    try {
        $trx->items()->delete();
        $trx->delete();
        echo "Deleted successfully\n";
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "No trx found\n";
}
