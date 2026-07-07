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
        Schema::dropIfExists('raffle_controls');

        if (Schema::hasColumn('products', 'is_raffle')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('is_raffle');
            });
        }

        if (Schema::hasColumn('order_items', 'raffle_number')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->dropUnique('order_items_product_raffle_unique');
                $table->dropColumn('raffle_number');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('raffle_controls', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('Sorteo Principal');
            $table->boolean('sales_enabled')->default(true);
            $table->decimal('free_shipping_value', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_raffle')->default(false)->after('is_active');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->string('raffle_number', 3)->nullable()->after('subtotal');
        });
    }
};
