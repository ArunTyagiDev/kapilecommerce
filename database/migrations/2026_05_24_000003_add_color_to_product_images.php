<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->foreignId('attribute_value_id')
                ->nullable()
                ->after('product_id')
                ->constrained('attribute_values')
                ->nullOnDelete();
            $table->index(['product_id', 'attribute_value_id']);
        });
    }

    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropForeign(['attribute_value_id']);
            $table->dropColumn('attribute_value_id');
        });
    }
};
