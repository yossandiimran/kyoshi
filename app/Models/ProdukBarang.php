<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdukBarang extends Model
{
    use HasFactory;
    protected $table = 'produk_barang';
    protected $primaryKey = 'id_produk_barang';
    protected $guarded = [];
}
