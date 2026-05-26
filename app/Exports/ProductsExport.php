<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $productType;

    public function __construct($productType = 'finished')
    {
        $this->productType = $productType;
    }

    public function collection()
    {
        return Product::with('category')
            ->where('product_type', $this->productType)
            ->orderBy('name')
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nama Produk',
            'Kategori',
            'SKU',
            'Barcode',
            'Tipe Produk',
            'Harga Jual',
            'Harga Modal',
            'Stok',
            'Satuan',
            'Status',
            'Deskripsi',
        ];
    }

    public function map($product): array
    {
        return [
            $product->id,
            $product->name,
            $product->category ? $product->category->name : 'Tanpa Kategori',
            $product->sku,
            $product->barcode,
            $product->kind_label,
            $product->price,
            $product->cost_price,
            $product->isStockless() ? 'Unlimited' : $product->stock,
            $product->unit,
            $product->is_active ? 'Aktif' : 'Nonaktif',
            $product->description,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
