<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Categorie;
use App\Models\Product;

class Sub_Categorie extends Model
{
    protected $table = 'sub_categories';
    protected $fillable = [
        'name',
        'url_photo',
        'categories_id',
    ];

    public function categorie()
    {
        return $this->belongsTo(Categorie::class, 'categories_id');
    }
    public function products()
    {
        return $this->hasMany(Product::class, 'sub_categories_id');
    }

}
