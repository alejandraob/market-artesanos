<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('artisan_id')->nullable()->constrained()->cascadeOnDelete();
            $table->enum('shipping_mode', ['coordination', 'flat', 'zone', 'weight'])->default('coordination');
            $table->decimal('shipping_flat_price', 10, 2)->nullable();
            $table->json('shipping_zone_rates')->nullable();
            $table->decimal('shipping_weight_base', 10, 2)->nullable();
            $table->decimal('shipping_weight_rate', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_rules');
    }
};
