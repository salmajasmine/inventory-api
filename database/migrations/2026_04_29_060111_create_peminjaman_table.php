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
    Schema::create('peminjaman', function (Blueprint $table) {
        $table->id();
        $table->string('nim'); 
        $table->string('kelas');
        $table->dateTime('waktu_peminjaman');
        $table->dateTime('waktu_pengembalian')->nullable();
        $table->timestamps();

        // Relasi ke tabel mahasiswa
        $table->foreign('nim')->references('nim')->on('mahasiswa')->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peminjaman');
    }
};
