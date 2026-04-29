<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Items extends Model
{
    protected $table = 'items';
    protected $fillable = ['kelas', 'nama_item', 'tipe_maintenance', 'status'];
}