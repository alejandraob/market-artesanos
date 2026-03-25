<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('weight')->nullable()->after('stock')->comment('Peso en gramos');
            $table->unsignedInteger('height')->nullable()->after('weight')->comment('Alto en cm');
            $table->unsignedInteger('width')->nullable()->after('height')->comment('Ancho en cm');
            $table->unsignedInteger('depth')->nullable()->after('width')->comment('Profundidad en cm');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['weight', 'height', 'width', 'depth']);
        });
    }
};
