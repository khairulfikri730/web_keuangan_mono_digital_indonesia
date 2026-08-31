<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    Illuminate\Support\Facades\Mail::raw('Test email', function ($msg) {
        $msg->to('auraashelia24@gmail.com')->subject('Test OTP');
    });
    echo 'Sent';
} catch (\Exception $e) {
    echo $e->getMessage();
}
