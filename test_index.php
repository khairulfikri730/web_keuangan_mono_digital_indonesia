<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$req = new \Illuminate\Http\Request();
$c = new \App\Http\Controllers\CashflowController();
$res = $c->index($req);

$html = $res->render();
$scriptPos = strpos($html, '<script>');
echo substr($html, $scriptPos, 1500);
