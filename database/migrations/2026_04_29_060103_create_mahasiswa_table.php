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
    Schema::create('mahasiswa', function (Blueprint $table) {
        $table->id(); // <--- BARIS INI HARUS ADA PALING ATAS
        $table->string('nim')->unique(); 
        $table->string('nama');
        $table->string('prodi');
        $table->string('ktp_ktm');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mahasiswa');
    }
};
