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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // El vendedor
            $table->string('name');
            $table->text('description');
            $table->decimal('price', 10, 2); // Hasta 99.999.999,99 (ajusta según tu moneda)
            $table->string('image')->nullable(); // Ruta de la foto del producto
            $table->integer('stock')->default(1);
            $table->boolean('is_active')->default(true); // Para que el vendedor pueda ocultarlo
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
