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
        Schema::table('users', function (Blueprint $table) {
            $table->time('opening_time_2')->nullable()->after('closing_time');
            $table->time('closing_time_2')->nullable()->after('opening_time_2');
            $table->json('closed_days')->nullable()->after('closing_time_2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['opening_time_2', 'closing_time_2', 'closed_days']);
        });
    }
};
