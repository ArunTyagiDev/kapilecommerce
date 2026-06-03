<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_customizable')->default(false)->after('is_featured');
            $table->string('shape_label')->nullable()->after('is_customizable');
            $table->boolean('allows_cod')->default(true)->after('shape_label');
            $table->unsignedTinyInteger('processing_days_min')->default(1)->after('allows_cod');
            $table->unsignedTinyInteger('processing_days_max')->default(7)->after('processing_days_min');
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->string('custom_image_path')->nullable()->after('price');
            $table->json('customization_data')->nullable()->after('custom_image_path');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->string('custom_image_path')->nullable()->after('variant_details');
            $table->json('customization_data')->nullable()->after('custom_image_path');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['custom_image_path', 'customization_data']);
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->dropColumn(['custom_image_path', 'customization_data']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'is_customizable',
                'shape_label',
                'allows_cod',
                'processing_days_min',
                'processing_days_max',
            ]);
        });
    }
};
