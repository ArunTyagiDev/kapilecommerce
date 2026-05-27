<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Size", "Color", "Material"
            $table->string('slug');
            $table->string('type')->default('select'); // select, text, number, etc.
            $table->boolean('is_required')->default(false);
            $table->boolean('is_filterable')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attributes');
    }
};

