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

        $table->string('nama_barang');

        $table->string('kelas');

        $table->timestamp('waktu_peminjaman');

        $table->timestamp('waktu_pengembalian')->nullable();

        $table->timestamps();
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

