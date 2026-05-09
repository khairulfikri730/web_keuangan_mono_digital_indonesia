<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Bisnis - {{ $meta['settings']['store_name'] }}</title>
    <style>
        /* PDF Basic Reset & Fonts */
        @page { margin: 1cm 1.5cm; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
            @if($meta['theme'] == 'dark')
                background-color: #0f172a;
                color: #f1f5f9;
            @elseif($meta['theme'] == 'blue')
                background-color: #1e3a8a;
                color: #eff6ff;
            @else
                background-color: #ffffff;
                color: #334155;
            @endif
        }

        /* Helpers */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-emerald { color: #10b981; }
        .text-red { color: #ef4444; }
        .text-blue { color: #3b82f6; }
        .font-black { font-weight: 900; }
        .font-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        .mb-2 { margin-bottom: 10px; }
        .mb-4 { margin-bottom: 20px; }
        .mt-8 { margin-top: 40px; }
        
        /* COVER PAGE */
        .cover-page {
            height: 100%;
            display: table;
            width: 100%;
            text-align: center;
            vertical-align: middle;
            @if($meta['theme'] == 'dark')
                background: linear-gradient(to bottom, #0f172a, #1e293b);
            @elseif($meta['theme'] == 'blue')
                background: linear-gradient(to bottom, #1e3a8a, #1e40af);
            @else
                background: linear-gradient(to bottom, #ffffff, #f8fafc);
            @endif
        }
        .cover-content { display: table-cell; vertical-align: middle; }
        .cover-logo { width: 120px; margin-bottom: 30px; }
        .cover-title { font-size: 32px; font-weight: 900; margin: 10px 0; letter-spacing: 2px; }
        .cover-subtitle { font-size: 16px; opacity: 0.8; margin-bottom: 50px; }
        .cover-meta { margin-top: 50px; border-top: 1px solid rgba(128,128,128,0.2); padding-top: 20px; display: inline-block; width: 300px; }

        /* HEADER & FOOTER */
        .page-header {
            position: fixed;
            top: -30px;
            left: 0;
            right: 0;
            height: 30px;
            font-size: 8px;
            opacity: 0.5;
            border-bottom: 0.5px solid rgba(128,128,128,0.2);
        }
        .page-footer {
            position: fixed;
            bottom: -30px;
            left: 0;
            right: 0;
            height: 30px;
            font-size: 8px;
            opacity: 0.5;
            text-align: center;
            border-top: 0.5px solid rgba(128,128,128,0.2);
            padding-top: 10px;
        }

        /* SECTION STYLING */
        .section-header {
            margin-top: 30px;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 2px solid #3b82f6;
            font-size: 14px;
            font-weight: 900;
        }

        /* SUMMARY CARDS */
        .card-grid { width: 100%; border-spacing: 10px; margin-left: -10px; margin-right: -10px; }
        .card {
            padding: 15px;
            border-radius: 12px;
            @if($meta['theme'] == 'white')
                background-color: #f8fafc;
                border: 1px solid #e2e8f0;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            @else
                background-color: rgba(255, 255, 255, 0.05);
                border: 1px solid rgba(255, 255, 255, 0.1);
            @endif
        }
        .card-label { font-size: 8px; font-weight: bold; opacity: 0.6; margin-bottom: 5px; }
        .card-value { font-size: 18px; font-weight: 900; margin-bottom: 2px; }
        .card-growth { font-size: 9px; font-weight: bold; }

        /* TABLES */
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th {
            text-align: left;
            padding: 12px 10px;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 1px;
            @if($meta['theme'] == 'white')
                background-color: #1e293b;
                color: #ffffff;
            @else
                background-color: rgba(255, 255, 255, 0.15);
                color: #ffffff;
            @endif
        }
        td {
            padding: 10px;
            border-bottom: 1px solid rgba(128, 128, 128, 0.1);
            vertical-align: middle;
        }
        .zebra tr:nth-child(even) {
            background-color: rgba(128, 128, 128, 0.05);
        }
        .grand-total {
            background-color: rgba(59, 130, 246, 0.1) !important;
            font-weight: 900;
            font-size: 11px;
        }

        /* INSIGHT BOX */
        .insight-box {
            padding: 15px;
            border-radius: 12px;
            background-color: rgba(59, 130, 246, 0.1);
            border: 1px dashed #3b82f6;
            margin-bottom: 20px;
        }
        .insight-item { margin-bottom: 5px; padding-left: 15px; position: relative; }
        .insight-item::before { content: '•'; position: absolute; left: 0; color: #3b82f6; font-weight: bold; }

        /* CHARTS */
        .chart-grid { width: 100%; margin-bottom: 20px; }
        .chart-card { text-align: center; padding: 10px; }
        .chart-img { max-width: 100%; border-radius: 8px; }

        /* MISC */
        .badge { padding: 2px 6px; border-radius: 4px; font-size: 7px; font-weight: bold; }
        .page-break { page-break-after: always; }
        .signature-grid { width: 100%; margin-top: 50px; }
        .signature-box { text-align: center; }
        .sig-line { border-top: 1px solid currentColor; width: 140px; margin: 60px auto 5px auto; }
        
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100px;
            opacity: 0.03;
            font-weight: 900;
            z-index: -1000;
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <div class="watermark">MONOFRAME VERIFIED</div>

    {{-- COVER PAGE --}}
    <div class="cover-page">
        <div class="cover-content">
            @if($meta['settings']['store_logo'])
                <img src="{{ public_path('storage/' . $meta['settings']['store_logo']) }}" class="cover-logo">
            @endif
            <h1 class="cover-title">{{ $meta['settings']['store_name'] }}</h1>
            <p class="cover-subtitle">LAPORAN ANALISA BISNIS PROFESIONAL</p>
            
            <div class="cover-meta">
                <table style="width: 100%; border: none;">
                    <tr><td style="border: none; opacity: 0.6; text-align: left;">PERIODE</td><td style="border: none; font-weight: 900; text-align: right;">{{ $meta['period_label'] }}</td></tr>
                    <tr><td style="border: none; opacity: 0.6; text-align: left;">RENTANG WAKTU</td><td style="border: none; font-weight: 900; text-align: right;">{{ $meta['date_range'] }}</td></tr>
                    <tr><td style="border: none; opacity: 0.6; text-align: left;">CABANG</td><td style="border: none; font-weight: 900; text-align: right;">{{ $meta['worksheet_name'] }}</td></tr>
                    <tr><td style="border: none; opacity: 0.6; text-align: left;">DIBUAT OLEH</td><td style="border: none; font-weight: 900; text-align: right;">{{ $meta['admin_name'] }}</td></tr>
                    <tr><td style="border: none; opacity: 0.6; text-align: left;">WAKTU EXPORT</td><td style="border: none; font-weight: 900; text-align: right;">{{ $meta['export_date'] }}</td></tr>
                </table>
            </div>
            
            <p style="margin-top: 100px; font-size: 9px; opacity: 0.4;">
                SECURITY HASH: {{ $meta['hash'] }}<br>
                © {{ date('Y') }} MONOFRAME POS SYSTEM
            </p>
        </div>
    </div>

    <div class="page-break"></div>

    {{-- HEADER & FOOTER ON SUBSEQUENT PAGES --}}
    <div class="page-header">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="border: none; padding: 0;">{{ $meta['settings']['store_name'] }} - Laporan Bisnis</td>
                <td style="border: none; padding: 0; text-align: right;">Periode: {{ $meta['date_range'] }}</td>
            </tr>
        </table>
    </div>

    <div class="page-footer">
        Halaman <script type="text/php">if (isset($pdf)) { $text = "{PAGE_NUM} / {PAGE_COUNT}"; $font = null; $size = 8; $color = array(0.5,0.5,0.5); $word_space = 0.0; $char_space = 0.0; $angle = 0.0; $pdf->page_text($pdf->get_width()/2 - 10, $pdf->get_height() - 35, $text, $font, $size, $color, $word_space, $char_space, $angle); }</script> | {{ $meta['hash'] }}
    </div>

    {{-- SECTION: AI INSIGHTS --}}
    @if(isset($ai_insights) && is_array($ai_insights) && count($ai_insights))
    <div class="section-header uppercase">Insight Bisnis Otomatis</div>
    <div class="insight-box" style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 20px; border-radius: 12px; margin-bottom: 30px;">
        @foreach($ai_insights as $insight)
            @if(is_array($insight))
                @php
                    $borderColor = '#3b82f6'; // default info/blue
                    if(($insight['type'] ?? '') == 'success') $borderColor = '#10b981';
                    if(($insight['type'] ?? '') == 'warning') $borderColor = '#f59e0b';
                    if(($insight['type'] ?? '') == 'danger') $borderColor = '#ef4444';
                @endphp
                <div class="insight-item" style="margin-bottom: 15px; padding: 12px 15px; background: white; border-left: 4px solid {{ $borderColor }}; border-radius: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                    <div style="font-size: 10px; font-weight: 900; color: #1e293b; margin-bottom: 3px; text-transform: uppercase; letter-spacing: 0.5px;">{{ $insight['title'] ?? 'Analisa Sistem' }}</div>
                    <div style="font-size: 9px; color: #64748b; line-height: 1.4;">{{ $insight['text'] ?? ($insight['description'] ?? '-') }}</div>
                </div>
            @else
                <div class="insight-item" style="margin-bottom: 10px; padding: 10px 15px; background: white; border-left: 4px solid #10b981; border-radius: 4px; font-size: 9px; color: #334155;">
                    {{ $insight }}
                </div>
            @endif
        @endforeach
    </div>
    @else
    <div class="section-header uppercase">Insight Bisnis Otomatis</div>
    <div class="insight-box" style="background: #f8fafc; border: 1px dashed #cbd5e1; padding: 20px; border-radius: 12px; text-align: center; color: #94a3b8; font-style: italic;">
        Tidak ada analisa insight tersedia untuk periode ini.
    </div>
    @endif

    {{-- SECTION: SUMMARY CARDS --}}
    @if(in_array('summary', $meta['sections']))
    <div class="section-header uppercase">Ikhtisar Performa Bisnis</div>
    <table class="card-grid">
        <tr>
            <td style="border: none; width: 33.3%;">
                <div class="card">
                    <div class="card-label">TOTAL OMZET</div>
                    <div class="card-value text-emerald">Rp {{ number_format($summary_data->total_income, 0, ',', '.') }}</div>
                    <div class="card-growth {{ $growth['income'] >= 0 ? 'text-emerald' : 'text-red' }}">
                        {{ $growth['income'] >= 0 ? '↑' : '↓' }} {{ abs($growth['income']) }}% vs periode lalu
                    </div>
                </div>
            </td>
            <td style="border: none; width: 33.3%;">
                <div class="card">
                    <div class="card-label">TOTAL PENGELUARAN</div>
                    <div class="card-value text-red">Rp {{ number_format($summary_data->total_expense, 0, ',', '.') }}</div>
                    <div class="card-growth {{ $growth['expense'] <= 0 ? 'text-emerald' : 'text-red' }}">
                        {{ $growth['expense'] >= 0 ? '↑' : '↓' }} {{ abs($growth['expense']) }}% vs periode lalu
                    </div>
                </div>
            </td>
            <td style="border: none; width: 33.3%;">
                <div class="card">
                    <div class="card-label">LABA BERSIH</div>
                    <div class="card-value text-blue">Rp {{ number_format($summary_data->net_profit, 0, ',', '.') }}</div>
                    <div class="card-growth {{ $growth['profit'] >= 0 ? 'text-emerald' : 'text-red' }}">
                        {{ $growth['profit'] >= 0 ? '↑' : '↓' }} {{ abs($growth['profit']) }}% vs periode lalu
                    </div>
                </div>
            </td>
        </tr>
    </table>
    @endif

    {{-- SECTION: BALANCES --}}
    @if(in_array('balances', $meta['sections']))
    <table class="card-grid" style="margin-top: -10px;">
        <tr>
            <td style="border: none; width: 50%;">
                <div class="card">
                    <div class="card-label">SALDO LACI / KASIR</div>
                    <div class="card-value">Rp {{ number_format($saldo_laci, 0, ',', '.') }}</div>
                </div>
            </td>
            <td style="border: none; width: 50%;">
                <div class="card">
                    <div class="card-label">SALDO BANK / DIGITAL</div>
                    <div class="card-value text-blue">Rp {{ number_format($saldo_bank, 0, ',', '.') }}</div>
                </div>
            </td>
        </tr>
    </table>
    @endif

    {{-- SECTION: CHARTS --}}
    @if(count($chart_images) > 0)
    <div class="section-header uppercase">Analisa Grafik Visual</div>
    <div class="chart-grid">
        @php $charts = array_chunk($chart_images, 2, true); @endphp
        @foreach($charts as $row)
            <table style="width: 100%; border: none;">
                <tr>
                    @foreach($row as $id => $img)
                        <td style="border: none; width: 50%;" class="chart-card">
                            <p class="font-bold mb-2 uppercase" style="font-size: 8px;">{{ strtoupper(str_replace('_', ' ', $id)) }}</p>
                            <img src="{{ $img }}" class="chart-img">
                        </td>
                    @endforeach
                </tr>
            </table>
        @endforeach
    </div>
    @endif

    {{-- SECTION: ROI --}}
    @if(in_array('roi', $meta['sections']) && isset($roi_data))
    <div class="section-header uppercase">Analisa ROI & Balik Modal</div>
    <table class="card-grid">
        <tr>
            <td style="border: none; width: 33.3%;">
                <div class="card">
                    <div class="card-label">MODAL INVESTASI</div>
                    <div class="card-value">Rp {{ number_format($roi_data['total_capital'], 0, ',', '.') }}</div>
                </div>
            </td>
            <td style="border: none; width: 33.3%;">
                <div class="card">
                    <div class="card-label">PROFIT AKUMULASI</div>
                    <div class="card-value text-emerald">Rp {{ number_format($roi_data['total_profit'], 0, ',', '.') }}</div>
                </div>
            </td>
            <td style="border: none; width: 33.3%;">
                <div class="card">
                    <div class="card-label">STATUS INVESTASI</div>
                    <div class="card-value text-blue">{{ $roi_data['status'] }}</div>
                </div>
            </td>
        </tr>
    </table>
    @endif

    {{-- SECTION: TOP PRODUCTS --}}
    @if(in_array('top_products', $meta['sections']) && isset($top_products))
    <div class="section-header uppercase">Ranking Produk Terlaris</div>
    <table class="zebra">
        <thead>
            <tr>
                <th>Produk</th>
                <th class="text-center">Kuantitas</th>
                <th class="text-right">Kontribusi Omzet</th>
            </tr>
        </thead>
        <tbody>
            @foreach($top_products as $p)
            <tr>
                <td class="font-bold">{{ $p->product_name }}</td>
                <td class="text-center">{{ number_format($p->total_qty) }} Unit</td>
                <td class="text-right">Rp {{ number_format($p->total_revenue, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- SECTION: INTERNAL MUTATIONS --}}
    @if(in_array('internal_mutations', $meta['sections']) && isset($internal_mutations) && $internal_mutations->count() > 0)
    <div class="section-header uppercase">Mutasi Saldo Internal (Non-Operasional)</div>
    <table class="zebra">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Kategori</th>
                <th>Keterangan</th>
                <th>Sumber</th>
                <th class="text-right">Nominal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($internal_mutations as $im)
            <tr>
                <td>{{ $im->transaction_date->format('d/m/Y H:i') }}</td>
                <td>{{ $im->category }}</td>
                <td style="font-size: 8px;">{{ $im->description }}</td>
                <td>{{ strtoupper($im->source) }}</td>
                <td class="text-right font-bold">Rp {{ number_format($im->amount, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- SECTION: HISTORY TRANSACTIONS --}}
    @if(in_array('history_trx', $meta['sections']) && isset($transactions))
    <div class="page-break"></div>
    <div class="section-header uppercase">Detail Riwayat Transaksi Penjualan</div>
    <table class="zebra">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>No. Invoice</th>
                <th>Pelanggan</th>
                <th>Metode</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $t)
            <tr>
                <td>{{ $t->created_at->format('d/m/Y H:i') }}</td>
                <td class="font-bold">{{ $t->invoice_number }}</td>
                <td>{{ $t->customer_name ?: 'Umum' }}</td>
                <td><span class="badge" style="background: rgba(59,130,246,0.1); color: #3b82f6;">{{ strtoupper($t->payment_method) }}</span></td>
                <td class="text-right font-bold">Rp {{ number_format($t->total, 0, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr class="grand-total">
                <td colspan="4" class="text-right uppercase">Total Omzet Penjualan</td>
                <td class="text-right">Rp {{ number_format($transactions->sum('total'), 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
    @endif

    {{-- SECTION: CASHFLOW --}}
    @if(in_array('full_cashflow', $meta['sections']) && isset($full_cashflow))
    <div class="page-break"></div>
    <div class="section-header uppercase">Arus Kas (Cashflow) Lengkap</div>
    <table class="zebra">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Jenis</th>
                <th>Kategori</th>
                <th>Keterangan</th>
                <th class="text-right">Masuk (+)</th>
                <th class="text-right">Keluar (-)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($full_cashflow as $cf)
            <tr>
                <td>{{ $cf->transaction_date->format('d/m/Y H:i') }}</td>
                <td><span class="badge {{ $cf->type == 'income' ? 'text-emerald' : 'text-red' }}" style="background: rgba(128,128,128,0.1);">{{ strtoupper($cf->type) }}</span></td>
                <td>{{ $cf->category }}</td>
                <td style="font-size: 8px;">{{ $cf->description }}</td>
                <td class="text-right text-emerald">{{ $cf->type == 'income' ? 'Rp '.number_format($cf->amount, 0, ',', '.') : '-' }}</td>
                <td class="text-right text-red">{{ $cf->type == 'expense' ? 'Rp '.number_format($cf->amount, 0, ',', '.') : '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- SIGNATURE AREA --}}
    @if($meta['include_signature'])
    <div class="mt-8">
        <table class="signature-grid">
            <tr>
                @foreach($meta['signature_roles'] as $role)
                <td style="border: none;" class="signature-box">
                    <p class="font-bold">{{ $role }}</p>
                    <div class="sig-line"></div>
                    <p style="opacity: 0.5; font-size: 8px;">( .................................. )</p>
                </td>
                @endforeach
            </tr>
        </table>
    </div>
    @endif

</body>
</html>

