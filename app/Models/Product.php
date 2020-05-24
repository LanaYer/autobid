<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'barcode', 'price'
    ];

    public function setBarcode($v) {
        $this->barcode = $v;
    }
}
