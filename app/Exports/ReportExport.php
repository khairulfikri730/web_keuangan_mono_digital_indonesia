<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReportExport implements WithMultipleSheets
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function sheets(): array
    {
        $sheets = [];
        
        // 1. Executive Summary
        $sheets[] = new SummarySheet($this->data);
        
        // 2. Sales Transactions
        if (!empty($this->data['transactions'])) {
            $sheets[] = new TransactionSheet($this->data['transactions']);
        }
        
        // 3. Cashflow Details
        if (!empty($this->data['full_cashflow'])) {
            $sheets[] = new CashflowSheet($this->data['full_cashflow']);
        }

        // 4. Top Products
        if (!empty($this->data['top_products'])) {
            $sheets[] = new TopProductsSheet($this->data['top_products']);
        }
        
        // 5. Shift Reports
        if (!empty($this->data['shifts'])) {
            $sheets[] = new ShiftReportSheet($this->data['shifts']);
        }

        return $sheets;
    }
}

class SummarySheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        $summary = $this->data['summary'];
        $growth = $this->data['growth'];

        return collect([
            ['METRIK UTAMA', 'NILAI', 'PERTUMBUHAN (%)'],
            ['Total Omzet (Pemasukan)', $summary->total_income, ($growth['income']['percentage'] ?? 0) . '%'],
            ['Total Pengeluaran (Beban)', $summary->total_expense, ($growth['expense']['percentage'] ?? 0) . '%'],
            ['Laba/Rugi Bersih', $summary->net_profit, ($growth['profit']['percentage'] ?? 0) . '%'],
            ['', '', ''],
            ['STATUS OPERASIONAL', '', ''],
            ['Jumlah Transaksi Selesai', $summary->total_count ?? 0, ''],
            ['Rata-rata Penjualan Harian', ($summary->total_income / max(1, $this->data['meta']['period_days'] ?? 1)), ''],
            ['', '', ''],
            ['INFORMASI PERIODE', '', ''],
            ['Rentang Tanggal', $this->data['meta']['date_from']->format('d M Y') . ' - ' . $this->data['meta']['date_to']->format('d M Y'), ''],
            ['Unit Bisnis', $this->data['meta']['worksheet_name'] ?? 'Seluruh Cabang', ''],
            ['Generate At', now()->format('d/m/Y H:i:s'), ''],
        ]);
    }

    public function headings(): array { return ['EXECUTIVE SUMMARY - MONOFRAME ENTERPRISE']; }
    public function title(): string { return 'Executive Summary'; }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:C1');
        return [
            1 => ['font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E40AF']]],
            2 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']]],
        ];
    }
}

class TransactionSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    protected $data;
    public function __construct($data) { $this->data = $data; }
    public function collection() { return collect($this->data)->map(fn($item) => (array)$item); }
    public function headings(): array { return ['Invoice', 'Tanggal', 'Kasir', 'Pelanggan', 'Item', 'Metode', 'Total Price', 'Status']; }
    public function title(): string { return 'Penjualan'; }
    public function styles(Worksheet $sheet) { return [1 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'CBD5E1']]]]; }
}

class CashflowSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    protected $data;
    public function __construct($data) { $this->data = $data; }
    public function collection() { return collect($this->data)->map(fn($item) => (array)$item); }
    public function headings(): array { return ['ID', 'Waktu', 'Jenis', 'Kategori', 'Deskripsi', 'Nominal', 'Metode']; }
    public function title(): string { return 'Arus Kas'; }
    public function styles(Worksheet $sheet) { return [1 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'CBD5E1']]]]; }
}

class TopProductsSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    protected $data;
    public function __construct($data) { $this->data = $data; }
    public function collection() { return collect($this->data); }
    public function headings(): array { return ['Nama Produk', 'Total Terjual (Qty)', 'Total Omzet (Rp)', 'Total Margin (Laba)']; }
    public function title(): string { return 'Ranking Produk'; }
    public function styles(Worksheet $sheet) { return [1 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FDE68A']]]]; }
}

class ShiftReportSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    protected $data;
    public function __construct($data) { $this->data = $data; }
    public function collection() { 
        return collect($this->data)->map(function($s) {
            $rowCashSales = \App\Models\Transaction::withoutGlobalScopes()->where('shift_id', $s->id)->where('payment_method', 'cash')->where('status', 'completed')->sum('total');
            $rowBankSales = \App\Models\Transaction::withoutGlobalScopes()->where('shift_id', $s->id)->where('payment_method', '!=', 'cash')->where('status', 'completed')->sum('total');
            
            $rowCashExpenses = \App\Models\Cashflow::withoutGlobalScopes()->where('shift_id', $s->id)->where('type', 'expense')->where('source', 'pos_cash')->sum('amount');
            $rowBankExpenses = \App\Models\Cashflow::withoutGlobalScopes()->where('shift_id', $s->id)->where('type', 'expense')->whereIn('source', ['pos_bank', 'transfer'])->sum('amount');
            
            $expected = $s->opening_cash + $rowCashSales - $rowCashExpenses; 
            $selisih = $s->closed_at ? ($s->closing_cash - $expected) : 0;

            return [
                'ID' => $s->id,
                'Kasir' => $s->opener->name,
                'Waktu Buka' => $s->opened_at->format('Y-m-d H:i:s'),
                'Waktu Tutup' => $s->closed_at ? $s->closed_at->format('Y-m-d H:i:s') : 'Masih Aktif',
                'Kas Awal' => $s->opening_cash,
                'Penjualan Tunai' => $rowCashSales,
                'Penjualan Non-Tunai' => $rowBankSales,
                'Pengeluaran Tunai' => $rowCashExpenses,
                'Pengeluaran Bank' => $rowBankExpenses,
                'Estimasi Kas Akhir' => $expected,
                'Aktual Kas Akhir' => $s->closing_cash ?? 0,
                'Selisih Kas' => $selisih
            ];
        }); 
    }
    public function headings(): array { return ['ID', 'Kasir', 'Waktu Buka', 'Waktu Tutup', 'Kas Awal', 'Penjualan Tunai', 'Penjualan Non-Tunai', 'Pengeluaran Tunai', 'Pengeluaran Bank', 'Estimasi Kas Akhir', 'Aktual Kas Akhir', 'Selisih Kas']; }
    public function title(): string { return 'Laporan Shift'; }
    public function styles(Worksheet $sheet) { return [1 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'CBD5E1']]]]; }
}
