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
    Schema::create('labs', function (Blueprint $table) {
        $table->id(); // Ini adalah Primary Key (#) otomatis
        $table->string('nm_lab')->unique(); // Atribut deskriptor Nama Lab yang unik
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('labs');
    }
};
