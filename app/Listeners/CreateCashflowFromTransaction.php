<?php

namespace App\Listeners;

use App\Events\TransactionCreated;
use App\Models\Cashflow;

class CreateCashflowFromTransaction
{
    public function handle(TransactionCreated $event): void
    {
        $transaction = $event->transaction;

        $sourceMap = [
            'cash' => 'pos_cash',
            'qris' => 'pos_bank',
            'debit' => 'pos_bank',
            'transfer' => 'transfer',
        ];
        $source = $sourceMap[$transaction->payment_method] ?? 'pos';

        // Auto-record ke cashflow
        \App\Models\Cashflow::create([
            'user_id' => $transaction->user_id,
            'shift_id' => $transaction->shift_id,
            'type' => 'income', // Penjualan -> income
            'category' => 'Penjualan',
            'description' => 'Penjualan POS #' . $transaction->invoice_number,
            'amount' => $transaction->total,
            'reference' => $transaction->invoice_number,
            'reference_id' => $transaction->id,
            'source' => $source,
            'transaction_date' => $transaction->created_at ? $transaction->created_at->format('Y-m-d') : today(),
        ]);
    }
}
