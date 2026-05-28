<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Detail_Insumo extends Model
{
    protected $table = 'detail_insumos';
    protected $fillable = [
        'products_id',
        'insumos_id',
        'amount',
    ];

}
