<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('style_filter')->nullable()->after('shape_label')->index();
            $table->string('omgs_source_url')->nullable()->after('style_filter');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->string('hub_route_slug')->nullable()->after('slug');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['style_filter', 'omgs_source_url']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('hub_route_slug');
        });
    }
};
