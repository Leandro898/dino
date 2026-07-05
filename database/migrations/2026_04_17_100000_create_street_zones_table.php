<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('street_zones', function (Blueprint $table) {
            $table->id();
            $table->string('street_name');          // normalizado: minúsculas, sin tildes
            $table->unsignedInteger('number_from')->nullable(); // null = toda la calle
            $table->unsignedInteger('number_to')->nullable();   // null = toda la calle
            $table->string('zone_key');             // centro | belgrano_melipal | exterior
            $table->timestamps();

            $table->index('street_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('street_zones');
    }
};
