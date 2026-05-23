<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMutation;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Throwable;

class ProductsImport implements ToModel, WithHeadingRow, SkipsOnError, WithBatchInserts, WithChunkReading
{
    use SkipsErrors;

    private array $categoryCache = [];
    public int $importedCount = 0;
    public int $skippedCount = 0;
    public array $errors = [];

    public function model(array $row): ?Product
    {
        // Skip empty rows
        if (empty(trim((string)($row['nama_produk'] ?? '')))) {
            $this->skippedCount++;
            return null;
        }

        try {
            // Resolve category by name
            $categoryId = null;
            $categoryName = trim((string)($row['kategori'] ?? ''));
            if ($categoryName) {
                if (!isset($this->categoryCache[$categoryName])) {
                    $cat = Category::where('name', $categoryName)->first();
                    if (!$cat) {
                        $cat = Category::create([
                            'name'      => $categoryName,
                            'is_active' => true,
                        ]);
                    }
                    $this->categoryCache[$categoryName] = $cat->id;
                }
                $categoryId = $this->categoryCache[$categoryName];
            }

            $name       = trim((string)$row['nama_produk']);
            $price      = (float) str_replace(['.', ',', 'Rp', ' '], ['', '.', '', ''], (string)($row['harga_jual'] ?? 0));
            $costPrice  = (float) str_replace(['.', ',', 'Rp', ' '], ['', '.', '', ''], (string)($row['harga_modal'] ?? 0));
            $stock      = (int)($row['stok'] ?? 0);
            $minStock   = (int)($row['stok_minimum'] ?? 5);
            $unit       = trim((string)($row['satuan'] ?? 'pcs')) ?: 'pcs';
            $sku        = trim((string)($row['sku'] ?? '')) ?: null;
            $barcode    = trim((string)($row['barcode'] ?? '')) ?: null;
            $desc       = trim((string)($row['deskripsi'] ?? '')) ?: null;
            $kind       = trim(strtolower((string)($row['tipe'] ?? 'regular')));
            $isActive   = !in_array(strtolower((string)($row['status'] ?? 'aktif')), ['nonaktif', 'inactive', '0', 'false', 'tidak']);

            // Normalize kind
            $allowedKinds = ['regular', 'unlimited', 'service', 'weight'];
            if (!in_array($kind, $allowedKinds)) {
                $kind = 'regular';
            }

            // Check duplicate SKU
            if ($sku && Product::where('sku', $sku)->exists()) {
                $this->errors[] = "SKU '{$sku}' sudah ada, baris untuk produk '{$name}' dilewati.";
                $this->skippedCount++;
                return null;
            }

            $product = new Product([
                'name'         => $name,
                'category_id'  => $categoryId,
                'sku'          => $sku,
                'barcode'      => $barcode,
                'price'        => $price,
                'cost_price'   => $costPrice,
                'stock'        => in_array($kind, ['unlimited', 'service']) ? 0 : $stock,
                'min_stock'    => in_array($kind, ['unlimited', 'service']) ? 0 : $minStock,
                'unit'         => $unit,
                'description'  => $desc,
                'product_type' => 'finished',
                'product_kind' => $kind,
                'is_active'    => $isActive,
            ]);

            $this->importedCount++;
            return $product;

        } catch (Throwable $e) {
            $this->errors[] = "Baris '{$row['nama_produk']}': " . $e->getMessage();
            $this->skippedCount++;
            return null;
        }
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 200;
    }
}
