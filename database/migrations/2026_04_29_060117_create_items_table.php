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
    Schema::create('items', function (Blueprint $table) {
        $table->id();
        $table->string('kelas');
        $table->string('nama_item'); // Contoh: Spidol, Remot AC
        $table->string('tipe_maintenance'); // Contoh: Refill Tinta, Ganti Baterai
        $table->enum('status', ['done', 'progress'])->default('progress');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
