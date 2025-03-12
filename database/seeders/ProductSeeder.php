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
            'image' => 'cayluoiho.jfif',
            'price' => 250000,
            'min_area' => 5,
            'category' => 'Cây cảnh'
        ]);

        Product::create([
            'name' => 'Chậu Gốm Nhật',
            'image' => 'chuaugomnhat.jfif',
            'price' => 150000,
            'min_area' => 2,
            'category' => 'Cây cảnh'
        ]);

        Product::create([
            'name' => 'cây sung cảnh',
            'image' => 'chuaugomnhat.jfif',
            'price' => 150000,
            'min_area' => 2,
            'category' => 'Cây cảnh'
        ]);
        Product::create([
            'name' => 'cây trầu bà lá xẻ',
            'image' => 'caytraulaxe.jfif',
            'price' => 150000,
            'min_area' => 2,
            'category' => 'Cây cảnh'
        ]);
        Product::create([
            'name' => 'cây phát tài khúc',
            'image' => 'cayphattai.jfif',
            'price' => 150000,
            'min_area' => 2,
            'category' => 'Cây cảnh'
        ]);
        Product::create([
            'name' => 'cây cao su lá lớn',
            'image' => 'caycaosulalon.jfif',
            'price' => 150000,
            'min_area' => 2,
            'category' => 'Cây cảnh'
        ]);
        Product::create([
            'name' => 'cây trầu bà vàng',
            'image' => 'caybatraulavang.webp',
            'price' => 150000,
            'min_area' => 2,
            'category' => 'Cây cảnh'
        ]);
        Product::create([
            'name' => 'cây kim tiền',
            'image' => 'caykimtien.webp',
            'price' => 150000,
            'min_area' => 2,
            'category' => 'Cây cảnh'
        ]);
        Product::create([
            'name' => 'cây thiết mộc lan',
            'image' => 'caythietmocan.jfif',
            'price' => 150000,
            'min_area' => 2,
            'category' => 'Cây cảnh'
        ]);
        Product::create([
            'name' => 'cây đuôi phượng',
            'image' => 'caydduoiphuong.jfif',
            'price' => 150000,
            'min_area' => 2,
            'category' => 'Cây cảnh'
        ]);
        Product::create([
            'name' => 'cây lan ý',
            'image' => 'câylamy.jfif',
            'price' => 150000,
            'min_area' => 2,
            'category' => 'Cây cảnh'
        ]);
        Product::create([
            'name' => 'chậu nhựa trắng hình trứng',
            'image' => 'chaunhuatranghinhtrung.jfif',
            'price' => 150000,
            'min_area' => 2,
            'category' => 'chậu cây'
        ]);
        Product::create([
            'name' => 'chậu nhựa trắng hình trụ bầu',
            'image' => 'chaunhuatranghinhbau.jfif',
            'price' => 150000,
            'min_area' => 2,
            'category' => 'chậu cây'
        ]);
    }
}
