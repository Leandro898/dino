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
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_raffle')->default(false)->after('is_active');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->string('raffle_number', 3)->nullable()->after('subtotal');
            $table->unique(['product_id', 'raffle_number'], 'order_items_product_raffle_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropUnique('order_items_product_raffle_unique');
            $table->dropColumn('raffle_number');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('is_raffle');
        });
    }
};
