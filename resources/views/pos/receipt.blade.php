<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=80mm, initial-scale=1.0">
    <title>Struk #{{ $transaction->invoice_number }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            margin: 0;
            padding: 10px;
            width: 80mm;
            color: #000;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .mb-1 { margin-bottom: 5px; }
        .mb-2 { margin-bottom: 10px; }
        .mt-2 { margin-top: 10px; }
        .border-top { border-top: 1px dashed #000; padding-top: 5px; mt-2; }
        .border-bottom { border-bottom: 1px dashed #000; padding-bottom: 5px; mb-2; }
        .flex { display: flex; justify-content: space-between; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 2px 0; }
        .btn-print {
            display: block; width: 100%; padding: 10px;
            background: #000; color: #fff; text-align: center;
            text-decoration: none; margin-top: 20px; font-family: sans-serif;
            border-radius: 5px;
        }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="text-center mb-2">
        <h2 style="margin:0 0 5px 0;">{{ $settings['store_name'] ?? 'KasirPro' }}</h2>
        @if(!empty($settings['store_address'])) <p style="margin:0;">{{ $settings['store_address'] }}</p> @endif
        @if(!empty($settings['store_phone'])) <p style="margin:0;">Telp: {{ $settings['store_phone'] }}</p> @endif
    </div>

    <div class="border-top border-bottom">
        <div class="flex"><span>No:</span> <span>{{ $transaction->invoice_number }}</span></div>
        <div class="flex"><span>Tgl:</span> <span>{{ $transaction->created_at->format('d/m/Y H:i') }}</span></div>
        <div class="flex"><span>Ksr:</span> <span>{{ $transaction->user->name }}</span></div>
        @if($transaction->customer_name)
        <div class="flex"><span>Plg:</span> <span>{{ $transaction->customer_name }}</span></div>
        @endif
        @if($transaction->customer_phone)
        <div class="flex"><span>No. HP:</span> <span>{{ $transaction->customer_phone }}</span></div>
        @endif
    </div>

    <table class="border-bottom">
        @foreach($transaction->items as $item)
        <tr>
            <td colspan="3" class="text-left font-bold">{{ $item->product_name }}</td>
        </tr>
        <tr>
            <td class="text-left">{{ $item->quantity }} x {{ number_format($item->price, 0, ',', '.') }}</td>
            <td class="text-right">
                @if($item->discount > 0)
                <br>Disc: -{{ number_format($item->discount, 0, ',', '.') }}
                @endif
            </td>
            <td class="text-right">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </table>

    <div class="flex"><span>Subtotal:</span> <span>{{ number_format($transaction->subtotal, 0, ',', '.') }}</span></div>
    @if($transaction->discount > 0)
    <div class="flex"><span>Diskon:</span> <span>-{{ number_format($transaction->discount, 0, ',', '.') }}</span></div>
    @endif
    @if($transaction->tax > 0)
    <div class="flex"><span>Pajak:</span> <span>{{ number_format($transaction->tax, 0, ',', '.') }}</span></div>
    @endif
    
    <div class="flex font-bold" style="font-size: 14px; margin-top: 5px;">
        <span>TOTAL:</span> <span>{{ number_format($transaction->total, 0, ',', '.') }}</span>
    </div>

    <div class="border-top mt-2">
        <div class="flex"><span>Tunai ({{ strtoupper($transaction->payment_method) }}):</span> <span>{{ number_format($transaction->paid_amount, 0, ',', '.') }}</span></div>
        <div class="flex"><span>Kembali:</span> <span>{{ number_format($transaction->change_amount, 0, ',', '.') }}</span></div>
    </div>

    <div class="text-center border-top mt-2 pt-2">
        <p style="margin:0;">{{ $settings['store_footer'] ?? 'Terima Kasih Atas Kunjungan Anda!' }}</p>
    </div>

    <button class="no-print btn-print" onclick="window.print()">Cetak (Print)</button>
    
    <script>
        window.onload = function() { window.print(); }
    </script>
</body>
</html>
