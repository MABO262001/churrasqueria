<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Unit;

class Insumo extends Model
{
    protected $table = 'insumos';

    protected $fillable = [
        'name',
        'number_unit',
        'amount',
        'units_id',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'units_id');
    }
}
