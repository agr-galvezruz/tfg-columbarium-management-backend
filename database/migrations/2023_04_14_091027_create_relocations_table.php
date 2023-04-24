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
            $table->unsignedBigInteger('urn_id');
            // $table->foreign('urn_id')->references('id')->on('urns')->onDelete('cascade');
            $table->unsignedBigInteger('casket_id');
            // $table->foreign('casket_id')->references('id')->on('caskets')->onDelete('cascade');
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
