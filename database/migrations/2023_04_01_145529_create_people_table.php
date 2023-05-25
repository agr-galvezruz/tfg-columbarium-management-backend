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
        Schema::create('people', function (Blueprint $table) {
            $table->id();
            $table->string('dni')->unique()->nullable();
            $table->string('first_name');
            $table->string('last_name_1');
            $table->string('last_name_2');
            $table->string('address');
            $table->string('city');
            $table->string('state');
            $table->string('postal_code');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->enum('marital_status', ['SINGLE', 'MARRIED', 'UNION', 'SEPARATE', 'DIVORCED', 'WIDOWER'])->nullable();
            $table->date('birthdate')->nullable();
            $table->date('deathdate')->nullable();
            $table->unsignedBigInteger('casket_id')->nullable();
            // $table->foreign('casket_id')->references('id')->on('caskets')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('people');
    }
};
