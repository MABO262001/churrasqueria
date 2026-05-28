<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'price',
        'url_photo',
        'amount',
        'sub_categories_id',
    ];

    public function subCategorie()
    {
        return $this->belongsTo(Sub_Categorie::class, 'sub_categories_id');
    }
}
