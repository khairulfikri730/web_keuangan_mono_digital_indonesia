<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width={{ $paperSize }}, initial-scale=1.0">
    <title>Struk #{{ $transaction->invoice_number }}</title>
    <style>
        @page {
            margin: 0;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            @if($fontSize === 'small') font-size: 9px; @elseif($fontSize === 'large') font-size: 14px; @else font-size: 11px; @endif
            @if($fontSmall) font-family: 'Arial Narrow', sans-serif; font-size: 9px; @endif
            margin: 0;
            padding: 5px;
            width: {{ $paperSize }};
            color: #000;
            background: #fff;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .mb-1 { margin-bottom: 5px; }
        .mb-2 { margin-bottom: 10px; }
        .mt-2 { margin-top: 10px; }
        .border-top { border-top: 1px dashed #000; padding-top: 5px; margin-top: 5px; }
        .border-bottom { border-bottom: 1px dashed #000; padding-bottom: 5px; margin-bottom: 5px; }
        .flex { display: flex; justify-content: space-between; gap: 5px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 2px 0; vertical-align: top; }
        .btn-print {
            display: block; width: 100%; padding: 10px;
            background: #000; color: #fff; text-align: center;
            text-decoration: none; margin-top: 20px; font-family: sans-serif;
            border-radius: 5px; border: none; cursor: pointer;
        }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="text-center mb-2">
        <h3 style="margin:0 0 2px 0; font-size: 1.2em;">{{ $settings['store_name'] ?? 'MONOFRAME' }}</h3>
        @if(!empty($settings['store_address'])) <p style="margin:0;">{{ $settings['store_address'] }}</p> @endif
        @if(!empty($settings['store_phone'])) <p style="margin:0;">Telp: {{ $settings['store_phone'] }}</p> @endif
    </div>

    <div class="border-top border-bottom">
        <div class="flex"><span>No:</span> <span>{{ $transaction->invoice_number }}</span></div>
        <div class="flex"><span>Tgl:</span> <span>{{ $transaction->created_at->format('d/m/y H:i') }}</span></div>
        <div class="flex"><span>Ksr:</span> <span>{{ $transaction->user->name }}</span></div>
        @if($transaction->customer_name)
        <div class="flex"><span>Plg:</span> <span>{{ $transaction->customer_name }}</span></div>
        @endif
        @if($transaction->customer_phone)
        <div class="flex"><span>Telp Plg:</span> <span>{{ $transaction->customer_phone }}</span></div>
        @endif
        @if($transaction->notes)
        <div class="flex"><span>Catatan:</span> <span>{{ $transaction->notes }}</span></div>
        @endif
    </div>

    <table class="border-bottom">
        @foreach($transaction->items as $item)
        <tr>
            <td colspan="2" class="text-left font-bold">{{ $item->product_name }}</td>
        </tr>
        <tr>
            <td class="text-left">
                {{ $item->quantity }} x {{ number_format($item->price, 0, ',', '.') }}
                @if($item->discount > 0)
                <span style="font-size: 0.8em; opacity: 0.8;">(-{{ number_format($item->discount, 0, ',', '.') }})</span>
                @endif
            </td>
            <td class="text-right font-bold">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </table>

    <div class="space-y-1">
        <div class="flex"><span>Subtotal:</span> <span>{{ number_format($transaction->subtotal, 0, ',', '.') }}</span></div>
        @if($transaction->discount > 0)
        <div class="flex"><span>Diskon:</span> <span>-{{ number_format($transaction->discount, 0, ',', '.') }}</span></div>
        @endif
        @if($transaction->tax > 0)
        <div class="flex"><span>Pajak:</span> <span>{{ number_format($transaction->tax, 0, ',', '.') }}</span></div>
        @endif
        
        <div class="flex font-bold" style="font-size: 1.1em; border-top: 1px solid #000; padding-top: 2px; margin-top: 2px;">
            <span>TOTAL:</span> <span>{{ number_format($transaction->total, 0, ',', '.') }}</span>
        </div>
    </div>

    <div class="border-top" style="margin-top: 8px;">
        <div class="flex"><span>{{ strtoupper($transaction->payment_method) }}:</span> <span>{{ number_format($transaction->paid_amount, 0, ',', '.') }}</span></div>
        <div class="flex"><span>KEMBALI:</span> <span class="font-bold">{{ number_format($transaction->change_amount, 0, ',', '.') }}</span></div>
    </div>

    <div class="text-center border-top mt-2 pt-2">
        <p style="margin:0; font-size: 0.9em;">{{ $settings['store_footer'] ?? 'Terima Kasih Atas Kunjungan Anda!' }}</p>
        <p style="margin:2px 0 0 0; font-size: 0.7em; opacity: 0.5;">Powered by monodev.id</p>
    </div>

    <div class="no-print" style="margin-top: 30px;">
        <button class="btn-print" onclick="window.print()">Cetak Ulang</button>
        <button class="btn-print" style="background: #666; margin-top: 10px;" onclick="window.close()">Tutup</button>
    </div>
    
    <script>
        window.onload = function() { 
            window.print();
            // Automatically close if opened in a popup/tab
            setTimeout(() => {
                if (window.opener || window.history.length === 1) {
                    // window.close(); // Careful with auto-close, might be blocked
                }
            }, 1000);
        }
    </script>
</body>
</html>
