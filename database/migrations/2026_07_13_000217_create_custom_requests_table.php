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
        Schema::create('custom_requests', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->index(); // ID de sesión para tracking anónimo
            $table->string('status')->default('open'); // open, quoted, accepted, closed
            $table->decimal('quoted_price', 10, 2)->nullable();
            $table->text('quote_description')->nullable();
            $table->boolean('has_unread_admin')->default(false); // Admin tiene mensajes sin leer
            $table->boolean('has_unread_user')->default(false); // User tiene mensajes sin leer
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_requests');
    }
};
