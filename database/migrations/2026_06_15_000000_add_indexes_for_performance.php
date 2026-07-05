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
        // Add indexes for order-related queries (order assignment notifications)
        Schema::table('order_items', function (Blueprint $table) {
            // Speed up: SELECT * FROM order_items WHERE order_id = ?
            $table->index('order_id');
            // Speed up: SELECT * FROM order_items WHERE product_id = ?
            $table->index('product_id');
        });

        // Add indexes for order filtering
        Schema::table('orders', function (Blueprint $table) {
            // Speed up: WHERE status = ?
            $table->index('status');
            // Speed up: WHERE user_id = ? (client orders)
            $table->index('user_id');
        });

        // Add index for products (vendor lookup)
        Schema::table('products', function (Blueprint $table) {
            // Speed up: WHERE user_id = ? (get vendor's products)
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndexIfExists('order_items_order_id_index');
            $table->dropIndexIfExists('order_items_product_id_index');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndexIfExists('orders_status_index');
            $table->dropIndexIfExists('orders_user_id_index');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndexIfExists('products_user_id_index');
        });
    }
};
