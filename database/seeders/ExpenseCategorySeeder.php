<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $worksheets = \App\Models\Worksheet::all();
        if ($worksheets->isEmpty()) return;

        $defaults = [
            'operasional' => ['Listrik', 'WiFi', 'Air', 'Gaji Karyawan', 'Sewa Tempat', 'Adobe Creative Cloud', 'Software POS'],
            'consumable' => ['Sabun Cuci', 'Pengharum Ruangan', 'Tisu', 'Plastik Packing', 'Lakban'],
            'bahan_baku' => ['Kertas Foto A4', 'Tinta Epson L805', 'Bingkai 4R', 'Laminasi Dingin'],
            'variabel' => ['Bensin Operasional', 'Ongkir Kirim Barang', 'Parkir', 'Konsumsi Lembur']
        ];

        foreach ($worksheets as $ws) {
            foreach ($defaults as $parent => $names) {
                foreach ($names as $name) {
                    \App\Models\ExpenseCategory::firstOrCreate([
                        'worksheet_id' => $ws->id,
                        'parent_category' => $parent,
                        'name' => $name
                    ]);
                }
            }
        }
    }
}
