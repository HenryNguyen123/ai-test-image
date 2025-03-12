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
            'name' => 'cây lưỡi hổ',
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
            'category' => 'Cây cảnh'
        ]);

        Product::create([
            'name' => 'cây sung cảnh',
            'image' => 'chaugom.jpg',
            'price' => 150000,
            'min_area' => 2,
            'category' => 'Cây cảnh'
        ]);
        Product::create([
            'name' => 'cây trầu bà lá xẻ',
            'image' => 'chaugom.jpg',
            'price' => 150000,
            'min_area' => 2,
            'category' => 'Cây cảnh'
        ]);
        Product::create([
            'name' => 'cây phát tài khúc',
            'image' => 'chaugom.jpg',
            'price' => 150000,
            'min_area' => 2,
            'category' => 'Cây cảnh'
        ]);
        Product::create([
            'name' => 'cây cao su lá lớn',
            'image' => 'chaugom.jpg',
            'price' => 150000,
            'min_area' => 2,
            'category' => 'Cây cảnh'
        ]);
        Product::create([
            'name' => 'cây trầu bà vàng',
            'image' => 'chaugom.jpg',
            'price' => 150000,
            'min_area' => 2,
            'category' => 'Cây cảnh'
        ]);
        Product::create([
            'name' => 'cây kim tiền',
            'image' => 'chaugom.jpg',
            'price' => 150000,
            'min_area' => 2,
            'category' => 'Cây cảnh'
        ]);
        Product::create([
            'name' => 'cây thiết mộc lan',
            'image' => 'chaugom.jpg',
            'price' => 150000,
            'min_area' => 2,
            'category' => 'Cây cảnh'
        ]);
        Product::create([
            'name' => 'cây đuôi phượng',
            'image' => 'chaugom.jpg',
            'price' => 150000,
            'min_area' => 2,
            'category' => 'Cây cảnh'
        ]);
        Product::create([
            'name' => 'cây lan ý',
            'image' => 'chaugom.jpg',
            'price' => 150000,
            'min_area' => 2,
            'category' => 'Cây cảnh'
        ]);
        Product::create([
            'name' => 'chậu nhựa trắng hình trứng',
            'image' => 'chaugom.jpg',
            'price' => 150000,
            'min_area' => 2,
            'category' => 'chậu cây'
        ]);
        Product::create([
            'name' => 'chậu nhựa trắng hình trụ bầu',
            'image' => 'chaugom.jpg',
            'price' => 150000,
            'min_area' => 2,
            'category' => 'chậu cây'
        ]);
    }
}
