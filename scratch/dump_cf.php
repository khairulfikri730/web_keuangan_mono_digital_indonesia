<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo App\Models\Cashflow::get(['id', 'shift_id', 'type', 'transaction_category', 'reference_id', 'reference_type', 'category', 'amount'])->toJson();
