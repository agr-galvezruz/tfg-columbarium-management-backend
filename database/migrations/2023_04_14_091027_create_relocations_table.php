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
        Schema::create('relocations', function (Blueprint $table) {
            $table->id();
            $table->dateTime('date');
            $table->text('description')->nullable();
            $table->foreignId('urn_id');
            $table->foreignId('casket_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('relocations');
    }
};
