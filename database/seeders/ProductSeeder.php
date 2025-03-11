<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::create([
            'name' => 'Cây Bonsai Mini',
            'image' => 'bonsai.jpg',
            'price' => 250000,
            'min_area' => 5,
            'category' => 'Cây cảnh'
        ]);

        Product::create([
            'name' => 'Chậu Gốm Nhật',
            'image' => 'chaugom.jpg',
            'price' => 150000,
            'min_area' => 2,
            'category' => 'Chậu cây'
        ]);
    }
}
