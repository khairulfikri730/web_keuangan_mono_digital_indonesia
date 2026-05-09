<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\CashDrawerLog;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Exception;
use Illuminate\Support\Facades\Log;

class PrinterService
{
    /**
     * Open the cash drawer.
     *
     * @param int|null $transactionId
     * @return array
     */
    public function openDrawer($transactionId = null, $force = false)
    {
        $autoOpen = Setting::get('drawer_auto_open', '0');
        if (!$force && $autoOpen !== '1') {
            return ['success' => false, 'message' => 'Auto open drawer is disabled.'];
        }

        $printerName = Setting::get('printer_name', 'POS-58');
        $connectionType = Setting::get('printer_connection', 'windows');
        $pin = (int) Setting::get('drawer_pulse_pin', '0');

        try {
            $connector = $this->getConnector($connectionType, $printerName);
            $printer = new Printer($connector);

            // Pulse to open drawer
            // Blueprint BP-ECO58D typically uses pin 0 or 1
            $printer->pulse($pin);
            
            $printer->close();

            $this->logAction($transactionId, 'success', 'Laci kasir berhasil dibuka.');

            return ['success' => true, 'message' => 'Laci kasir terbuka.'];
        } catch (Exception $e) {
            $this->logAction($transactionId, 'failed', 'Gagal membuka laci: ' . $e->getMessage());
            Log::error('Printer Error: ' . $e->getMessage());
            
            return ['success' => false, 'message' => 'Gagal: ' . $e->getMessage()];
        }
    }

    /**
     * Print receipt and open drawer if cash.
     */
    public function printReceiptAndOpenDrawer($transaction, $openDrawer = true)
    {
        $printerName = Setting::get('printer_name', 'POS-58');
        $connectionType = Setting::get('printer_connection', 'windows');
        $autoOpen = Setting::get('drawer_auto_open', '0');
        $pin = (int) Setting::get('drawer_pulse_pin', '0');

        try {
            $connector = $this->getConnector($connectionType, $printerName);
            $printer = new Printer($connector);

            // --- Receipt Content ---
            $this->generateReceiptContent($printer, $transaction);
            // ------------------------

            // Pulse drawer if payment is cash and setting is ON
            if ($openDrawer && $autoOpen === '1' && $transaction->payment_method === 'cash') {
                $printer->pulse($pin);
                $this->logAction($transaction->id, 'success', 'Laci kasir otomatis terbuka setelah cetak.');
            }

            $printer->cut();
            $printer->close();

            return ['success' => true, 'message' => 'Struk berhasil dicetak.'];
        } catch (Exception $e) {
            $this->logAction($transaction->id, 'failed', 'Printer error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Printer tidak terhubung atau error.'];
        }
    }

    private function getConnector($type, $target)
    {
        switch ($type) {
            case 'windows':
                return new WindowsPrintConnector($target);
            case 'usb':
                // For direct USB on Windows, usually need to share it and use WindowsPrintConnector
                // or use the device path if known (e.g., LPT1, COM1)
                return new FilePrintConnector($target);
            case 'network':
                return new NetworkPrintConnector($target);
            default:
                return new WindowsPrintConnector($target);
        }
    }

    private function generateReceiptContent($printer, $transaction)
    {
        $settings = Setting::getMultiple(['store_name', 'store_address', 'store_phone', 'store_footer']);
        
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
        $printer->text(($settings['store_name'] ?? 'MONOFRAME') . "\n");
        $printer->selectPrintMode();
        $printer->text(($settings['store_address'] ?? '') . "\n");
        $printer->text(($settings['store_phone'] ?? '') . "\n");
        $printer->text(str_repeat("-", 32) . "\n");

        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text("Inv: " . $transaction->invoice_number . "\n");
        $printer->text("Tgl: " . $transaction->created_at->format('d/m/Y H:i') . "\n");
        $printer->text("Ksr: " . ($transaction->user->name ?? 'Admin') . "\n");
        $printer->text(str_repeat("-", 32) . "\n");

        foreach ($transaction->items as $item) {
            $printer->text($item->product_name . "\n");
            $line = str_pad($item->quantity . " x " . number_format($item->price, 0), 20) . 
                    str_pad(number_format($item->subtotal, 0), 12, " ", STR_PAD_LEFT);
            $printer->text($line . "\n");
        }

        $printer->text(str_repeat("-", 32) . "\n");
        
        $summary = [
            'Total' => number_format($transaction->total, 0),
            'Bayar' => number_format($transaction->paid_amount, 0),
            'Kembali' => number_format($transaction->change_amount, 0),
        ];

        foreach ($summary as $label => $val) {
            $line = str_pad($label, 15) . str_pad($val, 17, " ", STR_PAD_LEFT);
            $printer->text($line . "\n");
        }

        $printer->feed(1);
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text(($settings['store_footer'] ?? 'Terima Kasih Atas Kunjungan Anda') . "\n");
        $printer->feed(2);
    }

    private function logAction($transactionId, $status, $message)
    {
        try {
            \App\Models\CashDrawerLog::create([
                'transaction_id' => $transactionId,
                'status' => $status,
                'message' => $message
            ]);
        } catch (Exception $e) {
            Log::error('Log Error: ' . $e->getMessage());
        }
    }
}
