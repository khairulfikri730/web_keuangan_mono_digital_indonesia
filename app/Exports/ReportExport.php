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

        $sheets[] = new SummarySheet($this->data);

        if (!empty($this->data['transactions'])) {
            $sheets[] = new TransactionSheet($this->data['transactions']);
        }

        if (!empty($this->data['full_cashflow'])) {
            $sheets[] = new CashflowSheet($this->data['full_cashflow']);
        }

        if (!empty($this->data['expense_details'])) {
            $sheets[] = new ExpenseDetailSheet($this->data['expense_details']);
        }

        if (!empty($this->data['top_products'])) {
            $sheets[] = new TopProductsSheet($this->data['top_products']);
        }

        if (!empty($this->data['shifts'])) {
            $sheets[] = new ShiftReportSheet($this->data['shifts'], $this->data['summary_data'] ?? null);
        }

        if (!empty($this->data['internal_mutations'])) {
            $sheets[] = new InternalMutationsSheet($this->data['internal_mutations']);
        }

        if (!empty($this->data['invoices'])) {
            $sheets[] = new InvoiceAnalyticsSheet($this->data['invoices']);
        }

        if (!empty($this->data['roi_data'])) {
            $sheets[] = new RoiSheet($this->data['roi_data']);
        }

        if (!empty($this->data['ai_insights'])) {
            $sheets[] = new AiInsightsSheet($this->data['ai_insights']);
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
        $summary = $this->data['summary_data'];
        $growth = $this->data['growth'];
        $dateFrom = $this->data['meta']['date_from'];
        $dateTo = $this->data['meta']['date_to'];
        $periodDays = $dateFrom->diffInDays($dateTo) + 1;

        return collect([
            ['METRIK UTAMA', 'NILAI', 'PERTUMBUHAN (%)'],
            ['Total Omzet (Pemasukan)', $summary->total_income, ($growth['income'] ?? 0) . '%'],
            ['Total Pengeluaran (Beban)', $summary->total_expense, ($growth['expense'] ?? 0) . '%'],
            ['Laba/Rugi Bersih', $summary->net_profit, ($growth['profit'] ?? 0) . '%'],
            ['', '', ''],
            ['STATUS OPERASIONAL', '', ''],
            ['Jumlah Transaksi Selesai', $summary->total_count ?? 0, ''],
            ['Rata-rata Penjualan Harian', ($summary->total_income / max(1, $periodDays)), ''],
            ['', '', ''],
            ['INFORMASI PERIODE', '', ''],
            ['Rentang Tanggal', $dateFrom->format('d M Y') . ' - ' . $dateTo->format('d M Y'), ''],
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

class ExpenseDetailSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    protected $data;
    public function __construct($data) { $this->data = $data; }
    public function collection() { return collect($this->data)->map(function($e) {
        return [
            'Tanggal' => $e->transaction_date->format('Y-m-d H:i'),
            'Kategori' => $e->category ?? '-',
            'Keterangan' => $e->description ?? '-',
            'Sumber' => \App\Models\Cashflow::sourceLabels()[$e->source] ?? ucfirst($e->source ?? '-'),
            'Nominal' => $e->amount ?? 0,
        ];
    }); }
    public function headings(): array { return ['Tanggal', 'Kategori', 'Keterangan', 'Sumber', 'Nominal']; }
    public function title(): string { return 'Detail Pengeluaran'; }
    public function styles(Worksheet $sheet) { return [1 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FECACA']]]]; }
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
    protected $summary;
    public function __construct($data, $summary = null) { $this->data = $data; $this->summary = $summary; }
    public function collection() { 
        $rows = collect($this->data)->map(function($s) {
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

        if ($this->summary && isset($this->summary->total_expense)) {
            $totalShiftExp = $rows->sum('Pengeluaran Tunai') + $rows->sum('Pengeluaran Bank');
            $unallocatedExp = max(0, $this->summary->total_expense - $totalShiftExp);
            if ($unallocatedExp > 0) {
                $rows->push([
                    'ID' => '', 'Kasir' => 'TOTAL PENGELUARAN DALAM SHIFT', 'Waktu Buka' => '', 'Waktu Tutup' => '',
                    'Kas Awal' => '', 'Penjualan Tunai' => '', 'Penjualan Non-Tunai' => '',
                    'Pengeluaran Tunai' => $rows->sum('Pengeluaran Tunai'),
                    'Pengeluaran Bank' => $rows->sum('Pengeluaran Bank'),
                    'Estimasi Kas Akhir' => $totalShiftExp, 'Aktual Kas Akhir' => '', 'Selisih Kas' => '',
                ]);
                $rows->push([
                    'ID' => '', 'Kasir' => 'PENGELUARAN DI LUAR SHIFT', 'Waktu Buka' => '', 'Waktu Tutup' => '',
                    'Kas Awal' => '', 'Penjualan Tunai' => '', 'Penjualan Non-Tunai' => '',
                    'Pengeluaran Tunai' => $unallocatedExp,
                    'Pengeluaran Bank' => '', 'Estimasi Kas Akhir' => '', 'Aktual Kas Akhir' => '', 'Selisih Kas' => '',
                ]);
                $rows->push([
                    'ID' => '', 'Kasir' => 'TOTAL SELURUH PENGELUARAN', 'Waktu Buka' => '', 'Waktu Tutup' => '',
                    'Kas Awal' => '', 'Penjualan Tunai' => '', 'Penjualan Non-Tunai' => '',
                    'Pengeluaran Tunai' => $this->summary->total_expense,
                    'Pengeluaran Bank' => '', 'Estimasi Kas Akhir' => '', 'Aktual Kas Akhir' => '', 'Selisih Kas' => '',
                ]);
            }
        }

        return $rows;
    }
    public function headings(): array { return ['ID', 'Kasir', 'Waktu Buka', 'Waktu Tutup', 'Kas Awal', 'Penjualan Tunai', 'Penjualan Non-Tunai', 'Pengeluaran Tunai', 'Pengeluaran Bank', 'Estimasi Kas Akhir', 'Aktual Kas Akhir', 'Selisih Kas']; }
    public function title(): string { return 'Laporan Shift'; }
    public function styles(Worksheet $sheet) { return [1 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'CBD5E1']]]]; }
}

class InternalMutationsSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    protected $data;
    public function __construct($data) { $this->data = $data; }
    public function collection() { return collect($this->data)->map(function($m) {
        return [
            'Tanggal' => $m->transaction_date instanceof \Carbon\Carbon ? $m->transaction_date->format('Y-m-d H:i') : $m->transaction_date,
            'Tipe' => $m->type ?? '-',
            'Kategori' => $m->category ?? '-',
            'Deskripsi' => $m->description ?? '-',
            'Sumber' => $m->source ?? '-',
            'Nominal' => $m->amount ?? 0,
        ];
    }); }
    public function headings(): array { return ['Tanggal', 'Tipe', 'Kategori', 'Deskripsi', 'Sumber', 'Nominal']; }
    public function title(): string { return 'Mutasi Internal'; }
    public function styles(Worksheet $sheet) { return [1 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DDD6FE']]]]; }
}

class InvoiceAnalyticsSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    protected $data;
    public function __construct($data) { $this->data = $data; }
    public function collection() {
        return collect([
            ['Invoice Lunas', $this->data['lunas'] ?? 0],
            ['Invoice Piutang', $this->data['piutang'] ?? 0],
            ['Total Piutang (Rp)', $this->data['total_piutang'] ?? 0],
            ['Uang Muka / DP', $this->data['dp'] ?? 0],
        ]);
    }
    public function headings(): array { return ['ANALISA INVOICE', 'NILAI']; }
    public function title(): string { return 'Analisa Invoice'; }
    public function styles(Worksheet $sheet) {
        $sheet->mergeCells('A1:B1');
        return [1 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'BFDBFE']]]];
    }
}

class RoiSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    protected $data;
    public function __construct($data) { $this->data = $data; }
    public function collection() {
        return collect([
            ['Modal Investasi (Rp)', $this->data['total_capital'] ?? 0],
            ['Profit Akumulasi (Rp)', $this->data['total_profit'] ?? 0],
            ['Status Investasi', $this->data['status'] ?? '-'],
        ]);
    }
    public function headings(): array { return ['ANALISA ROI & BEP', 'NILAI']; }
    public function title(): string { return 'Analisa ROI'; }
    public function styles(Worksheet $sheet) {
        $sheet->mergeCells('A1:B1');
        return [1 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1FAE5']]]];
    }
}

class AiInsightsSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    protected $data;
    public function __construct($data) { $this->data = $data; }
    public function collection() {
        $rows = [];
        foreach ($this->data as $insight) {
            if (is_array($insight)) {
                $rows[] = [
                    strtoupper($insight['type'] ?? '-'),
                    $insight['title'] ?? '-',
                    $insight['text'] ?? '-',
                ];
            }
        }
        return collect($rows);
    }
    public function headings(): array { return ['TIPE', 'JUDUL', 'DESKRIPSI']; }
    public function title(): string { return 'AI Insight'; }
    public function styles(Worksheet $sheet) { return [1 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF3C7']]]]; }
}
