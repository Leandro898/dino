<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('street_zones', function (Blueprint $table) {
            $table->unsignedInteger('price')->default(5000)->after('zone_key');
        });
    }

    public function down(): void
    {
        Schema::table('street_zones', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }
};
