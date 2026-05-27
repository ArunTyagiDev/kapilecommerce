<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('sku')->unique()->nullable();
            $table->string('barcode')->nullable();
            $table->decimal('price', 10, 2)->nullable(); // Override product price if set
            $table->decimal('discount_price', 10, 2)->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->boolean('in_stock')->default(true);
            $table->string('image')->nullable(); // Variant-specific image
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->index(['product_id', 'in_stock']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};

