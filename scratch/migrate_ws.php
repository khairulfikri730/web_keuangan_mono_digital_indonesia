<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Worksheet;
use App\Models\Product;
use App\Models\Category;
use App\Models\Cashflow;
use App\Models\Transaction;
use App\Models\Shift;
use App\Models\PosGroup;

$ws = Worksheet::where('name', 'MONOFRAME STUDIO 1')->first();
$id = $ws->id;

$countProducts = Product::withoutGlobalScopes()->whereNull('worksheet_id')->update(['worksheet_id' => $id]);
$countCategories = Category::withoutGlobalScopes()->whereNull('worksheet_id')->update(['worksheet_id' => $id]);
$countCashflows = Cashflow::withoutGlobalScopes()->whereNull('worksheet_id')->update(['worksheet_id' => $id]);
$countTransactions = Transaction::withoutGlobalScopes()->whereNull('worksheet_id')->update(['worksheet_id' => $id]);
$countShifts = Shift::withoutGlobalScopes()->whereNull('worksheet_id')->update(['worksheet_id' => $id]);
$countPosGroups = PosGroup::withoutGlobalScopes()->whereNull('worksheet_id')->update(['worksheet_id' => $id]);

echo "Migrated to Worksheet: {$ws->name} (ID: {$id})\n";
echo "Products: {$countProducts}\n";
echo "Categories: {$countCategories}\n";
echo "Cashflows: {$countCashflows}\n";
echo "Transactions: {$countTransactions}\n";
echo "Shifts: {$countShifts}\n";
echo "PosGroups: {$countPosGroups}\n";
