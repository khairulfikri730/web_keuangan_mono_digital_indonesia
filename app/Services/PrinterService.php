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
        try {
            $settings = Setting::getMultiple(['printer_connection', 'printer_name', 'drawer_pulse_pin']);
            $type = $settings['printer_connection'] ?? 'windows';
            $name = $settings['printer_name'] ?? 'POS-58';
            $pin = (int) ($settings['drawer_pulse_pin'] ?? 0);

            $connector = $this->getConnector($type, $name);
            $printer = new Printer($connector);
            $printer->pulse($pin, 25, 250);
            $printer->close();

            $this->logAction($transactionId, 'success', "Drawer opened via $type printer: $name, pin: $pin");

            return ['success' => true, 'message' => "Laci berhasil dibuka (Pin $pin via $name)"];
        } catch (Exception $e) {
            Log::error('Drawer open error: ' . $e->getMessage());
            $this->logAction($transactionId, 'failed', $e->getMessage());
            return ['success' => false, 'message' => 'Gagal membuka laci: ' . $e->getMessage()];
        }
    }

    /**
     * Print receipt and open drawer if cash.
     */
    public function printReceiptAndOpenDrawer($transaction, $openDrawer = true)
    {
        try {
            $settings = Setting::getMultiple([
                'store_name', 'store_address', 'store_phone', 'store_footer',
                'printer_connection', 'printer_name', 'printer_paper_size', 'printer_font_small',
                'printer_feed_lines', 'drawer_pulse_pin', 'drawer_auto_open'
            ]);
            $type = $settings['printer_connection'] ?? 'windows';
            $name = $settings['printer_name'] ?? 'POS-58';

            $connector = $this->getConnector($type, $name);
            $printer = new Printer($connector);

            $this->generateReceiptContent($printer, $transaction);

            if ($settings['printer_feed_lines'] ?? 0) {
                $printer->feed((int) $settings['printer_feed_lines']);
            }

            $printer->cut(Printer::CUT_FULL);
            $printer->close();

            $pin = (int) ($settings['drawer_pulse_pin'] ?? 0);
            if ($openDrawer && ($settings['drawer_auto_open'] ?? '0') === '1'
                && $transaction->payment_method === 'cash') {
                $connector2 = $this->getConnector($type, $name);
                $printer2 = new Printer($connector2);
                $printer2->pulse($pin, 25, 250);
                $printer2->close();
            }

            $this->logAction($transaction->id, 'success', "Printed + drawer (if cash) via $name");
            return ['success' => true, 'message' => 'Struk berhasil dicetak via ' . $name];
        } catch (Exception $e) {
            Log::error('Print error: ' . $e->getMessage());
            $this->logAction($transaction->id ?? null, 'failed', $e->getMessage());
            return ['success' => false, 'message' => 'Gagal mencetak: ' . $e->getMessage()];
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
