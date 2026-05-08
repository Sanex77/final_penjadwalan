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
    Schema::create('schedules', function (Blueprint $table) {
        $table->id();
        $table->date('tanggal');
        $table->string('hari'); // Senin - Sabtu
        $table->string('lab'); // Lab 1 - Lab 10
        $table->time('jam_mulai'); // 08:00
        $table->time('jam_selesai'); // Sesuai SKS
        $table->string('nama_asisten');
        $table->string('matkul');
        $table->integer('sks');
        $table->string('dosen');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
