<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Insumo;


class Unit extends Model
{
    protected $table = 'units';
    protected $fillable = [
        'name',
        'abbreviation',
    ];

    public function insumos()
    {
        return $this->hasMany(Insumo::class, 'insumos_id');
    }
}
