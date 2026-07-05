<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('external_source')->nullable()->after('is_raffle');
            $table->string('external_id')->nullable()->after('external_source');
            $table->string('external_category')->nullable()->after('external_id');
            $table->string('external_url')->nullable()->after('external_category');

            $table->index(['external_source', 'external_category']);
            $table->unique(['external_source', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['external_source', 'external_id']);
            $table->dropIndex(['external_source', 'external_category']);
            $table->dropColumn(['external_source', 'external_id', 'external_category', 'external_url']);
        });
    }
};
