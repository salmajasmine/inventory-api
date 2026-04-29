<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Peminjaman extends Model
{
    protected $table = 'peminjaman';
    protected $fillable = ['nim', 'kelas', 'waktu_peminjaman', 'waktu_pengembalian'];
}