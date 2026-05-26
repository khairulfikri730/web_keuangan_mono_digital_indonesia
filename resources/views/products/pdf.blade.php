<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Katalog Produk</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; padding: 0; }
        .header p { margin: 5px 0; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge { padding: 2px 5px; border-radius: 3px; font-size: 10px; }
        .badge-success { background-color: #d1fae5; color: #065f46; }
        .badge-danger { background-color: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>

    <div class="header">
        <h2>Katalog Produk - {{ $productType == 'finished' ? 'Produk Jadi' : ($productType == 'semi_finished' ? 'Setengah Jadi' : 'Bahan Baku') }}</h2>
        <p>Dicetak pada: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Produk</th>
                <th>Kategori</th>
                <th>Tipe</th>
                <th class="text-right">Harga Jual</th>
                <th class="text-right">Harga Modal</th>
                <th class="text-center">Stok</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $index => $product)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                    <strong>{{ $product->name }}</strong><br>
                    <span style="font-size: 10px; color: #666;">SKU: {{ $product->sku ?: '-' }}</span>
                </td>
                <td>{{ $product->category ? $product->category->name : 'Tanpa Kategori' }}</td>
                <td>{{ $product->kind_label }}</td>
                <td class="text-right">Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($product->cost_price, 0, ',', '.') }}</td>
                <td class="text-center">
                    @if($product->isStockless())
                        ∞
                    @else
                        {{ $product->stock }} {{ $product->unit }}
                    @endif
                </td>
                <td class="text-center">
                    @if($product->is_active)
                        <span class="badge badge-success">Aktif</span>
                    @else
                        <span class="badge badge-danger">Nonaktif</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">Belum ada produk.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
