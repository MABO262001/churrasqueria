<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Sub_Categorie;

class Categorie extends Model
{
    protected $fillable = [
        'name',
    ];

    public function subCategories()
    {
        return $this->hasMany(Sub_Categorie::class, 'categories_id');
    }
}
