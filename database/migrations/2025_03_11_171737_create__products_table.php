<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('_products', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Tên sản phẩm
            $table->string('image'); // Đường dẫn hình ảnh
            $table->decimal('price', 10, 2); // Giá sản phẩm
            $table->integer('min_area'); // Diện tích tối thiểu gợi ý sản phẩm
            $table->string('category'); // Loại sản phẩm (cây cảnh, chậu, vật dụng, đá trang trí)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_products');
    }
};
