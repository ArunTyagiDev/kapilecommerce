<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'customer', 'vendor') NOT NULL DEFAULT 'customer'");

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('shop_id')->nullable()->after('brand_id')->constrained()->nullOnDelete();
            $table->index('shop_id');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('shop_id')->nullable()->after('product_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['shop_id']);
            $table->dropColumn('shop_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['shop_id']);
            $table->dropColumn('shop_id');
        });

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'customer') NOT NULL DEFAULT 'customer'");
    }
};
