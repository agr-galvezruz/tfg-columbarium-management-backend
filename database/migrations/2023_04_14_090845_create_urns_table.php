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
        Schema::create('urns', function (Blueprint $table) {
            $table->id();
            $table->string('internal_code');
            $table->enum('status', ['OCCUPIED', 'RESERVED', 'AVAILABLE', 'DISABLED']);
            $table->text('description')->nullable();
            $table->foreignId('niche_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('urns');
    }
};
