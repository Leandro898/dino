<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
        });

        $existingSlugs = [];
        $products = DB::table('products')->orderBy('id')->get();

        foreach ($products as $product) {
            $baseSlug = Str::slug($product->name);
            $slug = $baseSlug ?: 'producto-' . $product->id;
            $suffix = 1;

            while (in_array($slug, $existingSlugs, true) || DB::table('products')->where('slug', $slug)->where('id', '!=', $product->id)->exists()) {
                $slug = $baseSlug . '-' . $suffix++;
            }

            $existingSlugs[] = $slug;

            DB::table('products')
                ->where('id', $product->id)
                ->update(['slug' => $slug]);
        }

        Schema::table('products', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
