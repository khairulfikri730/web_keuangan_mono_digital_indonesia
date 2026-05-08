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
            'cash'     => 'pos_cash',
            'qris'     => 'pos_bank',
            'debit'    => 'pos_bank',
            'transfer' => 'transfer',
        ];
        $source = $sourceMap[$transaction->payment_method] ?? 'pos';

        // All POS transactions start as "pending" sync until owner confirms them
        $bankSyncStatus = 'pending';

        Cashflow::create([
            'user_id'          => $transaction->user_id,
            'shift_id'         => $transaction->shift_id,
            'type'             => 'income',
            'category'         => 'Penjualan',
            'description'      => 'Penjualan POS #' . $transaction->invoice_number,
            'amount'           => $transaction->total,
            'reference'        => $transaction->invoice_number,
            'reference_id'     => $transaction->id,
            'source'           => $source,
            'bank_sync_status' => $bankSyncStatus,
            'transaction_date' => $transaction->created_at
                ? $transaction->created_at->format('Y-m-d')
                : today(),
        ]);
    }
}
