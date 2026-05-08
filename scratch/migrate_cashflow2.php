<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach(\App\Models\Shift::all() as $s) {
    if($s->opening_cash > 0 && !\App\Models\Cashflow::where('shift_id', $s->id)->where('category', 'Modal Awal Kasir')->exists()) {
        \App\Models\Cashflow::create([
            'user_id' => $s->opened_by,
            'shift_id' => $s->id,
            'type' => 'income',
            'category' => 'Modal Awal Kasir',
            'description' => 'Kas Awal Shift',
            'amount' => $s->opening_cash,
            'source' => 'pos_cash',
            'transaction_date' => $s->opened_at ? $s->opened_at->format('Y-m-d') : date('Y-m-d')
        ]);
    }
}
echo 'done';
