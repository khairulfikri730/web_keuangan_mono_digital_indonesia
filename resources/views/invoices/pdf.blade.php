<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        @page { margin: 0; }
        body { font-family: 'Helvetica', sans-serif; margin: 0; padding: 40px; color: #334155; line-height: 1.5; background: #fff; }
        
        .header { margin-bottom: 50px; }
        .header table { width: 100%; border-collapse: collapse; }
        .business-name { font-size: 24px; font-weight: bold; color: #2563eb; margin-bottom: 5px; }
        .business-info { font-size: 11px; color: #64748b; }
        
        .invoice-title { font-size: 32px; font-weight: bold; color: #0f172a; margin-bottom: 10px; }
        .invoice-meta { font-size: 11px; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; }
        .invoice-value { font-size: 13px; font-weight: bold; color: #0f172a; margin-bottom: 15px; }
        
        .section-title { font-size: 10px; font-weight: bold; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; border-bottom: 1px solid #f1f5f9; padding-bottom: 5px; }
        .client-info { margin-bottom: 40px; }
        .client-name { font-size: 16px; font-weight: bold; color: #0f172a; }
        .client-details { font-size: 11px; color: #64748b; }
        
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .items-table th { background: #f8fafc; padding: 12px 15px; text-align: left; font-size: 10px; font-weight: bold; color: #64748b; text-transform: uppercase; letter-spacing: 1px; border-bottom: 2px solid #e2e8f0; }
        .items-table td { padding: 15px; font-size: 12px; border-bottom: 1px solid #f1f5f9; }
        .item-name { font-weight: bold; color: #1e293b; }
        
        .summary { width: 300px; margin-left: auto; }
        .summary-row { padding: 8px 0; border-bottom: 1px solid #f1f5f9; }
        .summary-label { font-size: 11px; color: #64748b; }
        .summary-value { font-size: 12px; font-weight: bold; color: #1e293b; text-align: right; }
        .summary-total { padding: 15px 0; color: #2563eb; border-bottom: none; }
        .summary-total .summary-label { font-size: 13px; font-weight: bold; color: #0f172a; }
        .summary-total .summary-value { font-size: 20px; }
        
        .payment-status { position: absolute; top: 40px; left: 40px; text-align: left; }
        .status-badge { display: inline-block; padding: 5px 15px; border-radius: 20px; font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .status-paid { background: #ecfdf5; color: #059669; border: 1px solid #10b981; }
        .status-partial { background: #eff6ff; color: #2563eb; border: 1px solid #3b82f6; }
        .status-pending { background: #fffbeb; color: #d97706; border: 1px solid #f59e0b; }
        
        .footer { margin-top: 100px; text-align: center; font-size: 11px; color: #94a3b8; }
        .notes { margin-top: 50px; font-size: 11px; color: #64748b; font-style: italic; border-top: 1px solid #f1f5f9; padding-top: 20px; }
        
        .progress-container { margin-top: 15px; }
        .progress-bar { width: 100%; height: 6px; background: #f1f5f9; border-radius: 3px; overflow: hidden; margin-top: 5px; }
        .progress-fill { height: 100%; background: #2563eb; }
    </style>
</head>
<body>

    <div class="payment-status">
        @php
            $statusClass = 'status-' . $invoice->status;
            $statusLabel = [
                'pending' => 'Menunggu Pembayaran',
                'partial' => 'DP ' . $invoice->payment_percentage . '% Diterima',
                'paid' => 'Lunas',
                'overdue' => 'Jatuh Tempo'
            ][$invoice->status] ?? 'Pending';
        @endphp
        <div class="status-badge {{ $statusClass }}">{{ $statusLabel }}</div>
    </div>

    <div class="header">
        <table>
            <tr>
                <td>
                    <div class="business-name">{{ $invoice->business_name }}</div>
                    <div class="business-info">
                        {{ $invoice->business_email }}<br>
                        {{ $invoice->business_phone }}<br>
                        {{ $invoice->business_address }}
                    </div>
                </td>
                <td style="text-align: right; vertical-align: top;">
                    <div class="invoice-title">INVOICE</div>
                    <div class="invoice-meta">No. Invoice</div>
                    <div class="invoice-value">{{ $invoice->invoice_number }}</div>
                    <div class="invoice-meta">Tanggal</div>
                    <div class="invoice-value">{{ $invoice->date->format('d F Y') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="client-info">
        <div class="section-title">Ditagihkan Kepada</div>
        <div class="client-name">{{ $invoice->client_name }}</div>
        <div class="client-details">
            @if($invoice->client_company) {{ $invoice->client_company }}<br> @endif
            {{ $invoice->client_phone }}<br>
            {{ $invoice->client_email }}<br>
            {{ $invoice->client_address }}
        </div>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th>Deskripsi</th>
                <th style="text-align: center; width: 60px;">Qty</th>
                <th style="text-align: right; width: 120px;">Harga</th>
                <th style="text-align: right; width: 120px;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
            <tr>
                <td><span class="item-name">{{ $item->name }}</span></td>
                <td style="text-align: center;">{{ $item->quantity }}</td>
                <td style="text-align: right;">{{ number_format($item->price, 0, ',', '.') }}</td>
                <td style="text-align: right;">{{ number_format($item->total, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <table style="width: 100%;">
            <tr class="summary-row">
                <td class="summary-label">Subtotal</td>
                <td class="summary-value">{{ number_format($invoice->subtotal, 0, ',', '.') }}</td>
            </tr>
            @if($invoice->discount > 0)
            <tr class="summary-row">
                <td class="summary-label" style="color: #ef4444;">
                    Diskon {{ $invoice->discount_type === 'percent' ? '(' . (float)$invoice->discount_value . '%)' : '' }}
                </td>
                <td class="summary-value" style="color: #ef4444;">- {{ number_format($invoice->discount, 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr class="summary-row summary-total">
                <td class="summary-label">TOTAL TAGIHAN</td>
                <td class="summary-value">{{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
            </tr>
            
            @if($invoice->paid_amount > 0)
            <tr class="summary-row">
                <td class="summary-label" style="color: #059669;">Jumlah Dibayar</td>
                <td class="summary-value" style="color: #059669;">{{ number_format($invoice->paid_amount, 0, ',', '.') }}</td>
            </tr>
            <tr class="summary-row" style="border-top: 1px solid #e2e8f0; margin-top: 5px;">
                <td class="summary-label">Sisa Tagihan</td>
                <td class="summary-value" style="color: #dc2626;">{{ number_format($invoice->balance_remaining, 0, ',', '.') }}</td>
            </tr>
            @endif
        </table>

        @if($invoice->paid_amount > 0 && $invoice->status != 'paid')
        <div class="progress-container">
            <div style="font-size: 9px; font-weight: bold; color: #94a3b8; text-transform: uppercase;">Progress Pembayaran: {{ $invoice->payment_percentage }}%</div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: {{ $invoice->payment_percentage }}%;"></div>
            </div>
        </div>
        @endif
    </div>

    @if($invoice->notes)
    <div class="notes">
        <div style="font-weight: bold; margin-bottom: 5px;">Catatan:</div>
        {{ $invoice->notes }}
    </div>
    @endif

    <div class="footer">
        Terima kasih atas kerja samanya!<br>
        <strong>{{ $invoice->business_name }}</strong>
    </div>

</body>
</html>
