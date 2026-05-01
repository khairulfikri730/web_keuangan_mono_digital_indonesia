<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$req = new \Illuminate\Http\Request();
$req->merge(['filter' => 'week']);
$c = new \App\Http\Controllers\CashflowController();
$res = $c->getData($req);

echo "Total Data: " . count(json_decode($res->getContent(), true)['chart']['labels']) . "\n";
$data = json_decode($res->getContent(), true);
echo "Net profit: " . $data['summary']['netProfitFmt'] . "\n";
echo "Transactions count: " . substr_count($data['transactions'], '<tr>');
