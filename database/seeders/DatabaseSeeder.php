<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Users
        User::create([
            'name' => 'Owner Admin',
            'email' => 'owner@kasirpro.com',
            'phone' => '081234567890',
            'role' => 'owner',
            'is_active' => true,
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Operator Kasir',
            'email' => 'operator@kasirpro.com',
            'phone' => '082345678901',
            'role' => 'operator',
            'is_active' => true,
            'password' => Hash::make('password'),
        ]);

        // Kategori
        $categories = [
            ['name' => 'Makanan', 'color' => '#f97316', 'slug' => 'makanan'],
            ['name' => 'Minuman', 'color' => '#06b6d4', 'slug' => 'minuman'],
            ['name' => 'Snack', 'color' => '#8b5cf6', 'slug' => 'snack'],
            ['name' => 'Sembako', 'color' => '#10b981', 'slug' => 'sembako'],
            ['name' => 'Elektronik', 'color' => '#3b82f6', 'slug' => 'elektronik'],
        ];

        foreach ($categories as $cat) {
            Category::create($cat + ['is_active' => true]);
        }

        // Produk sample
        $products = [
            ['category_id' => 1, 'name' => 'Nasi Goreng Spesial', 'sku' => 'MKN-001', 'price' => 25000, 'cost_price' => 15000, 'stock' => 50, 'unit' => 'porsi'],
            ['category_id' => 1, 'name' => 'Mie Ayam Bakso', 'sku' => 'MKN-002', 'price' => 20000, 'cost_price' => 12000, 'stock' => 30, 'unit' => 'porsi'],
            ['category_id' => 2, 'name' => 'Es Teh Manis', 'sku' => 'MNM-001', 'price' => 8000, 'cost_price' => 3000, 'stock' => 100, 'unit' => 'cup'],
            ['category_id' => 2, 'name' => 'Jus Alpukat', 'sku' => 'MNM-002', 'price' => 15000, 'cost_price' => 8000, 'stock' => 40, 'unit' => 'cup'],
            ['category_id' => 2, 'name' => 'Air Mineral 600ml', 'sku' => 'MNM-003', 'price' => 5000, 'cost_price' => 2500, 'stock' => 200, 'unit' => 'botol'],
            ['category_id' => 3, 'name' => 'Keripik Singkong', 'sku' => 'SNK-001', 'price' => 12000, 'cost_price' => 7000, 'stock' => 80, 'unit' => 'pcs'],
            ['category_id' => 3, 'name' => 'Choco Wafer', 'sku' => 'SNK-002', 'price' => 5000, 'cost_price' => 3000, 'stock' => 60, 'unit' => 'pcs'],
            ['category_id' => 4, 'name' => 'Beras Premium 5kg', 'sku' => 'SMB-001', 'price' => 75000, 'cost_price' => 62000, 'stock' => 25, 'unit' => 'kg'],
            ['category_id' => 4, 'name' => 'Minyak Goreng 1L', 'sku' => 'SMB-002', 'price' => 18000, 'cost_price' => 15000, 'stock' => 3, 'min_stock' => 5, 'unit' => 'liter'],
        ];

        foreach ($products as $prod) {
            Product::create($prod + [
                'min_stock' => $prod['min_stock'] ?? 5,
                'description' => null,
                'is_active' => true,
            ]);
        }

        // Settings
        $settings = [
            ['key' => 'store_name', 'value' => 'KasirPro Store'],
            ['key' => 'store_address', 'value' => 'Jl. Raya No. 123, Kota, Indonesia'],
            ['key' => 'store_phone', 'value' => '081234567890'],
            ['key' => 'store_email', 'value' => 'info@kasirpro.com'],
            ['key' => 'store_footer', 'value' => 'Terima kasih telah berbelanja!'],
            ['key' => 'currency', 'value' => 'IDR'],
            ['key' => 'tax_rate', 'value' => '0'],
        ];

        foreach ($settings as $s) {
            Setting::create($s);
        }
    }
}
