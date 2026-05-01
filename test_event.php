<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$t = \App\Models\Transaction::first();
event(new \App\Events\TransactionCreated($t));

echo "Cashflow count: " . \App\Models\Cashflow::count() . "\n";
