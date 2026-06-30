<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Table;
use App\Models\Reservation;

class Details_Reservation extends Model
{
    protected $table = 'details_reservations';

    protected $fillable = [
        'reservations_id',
        'tables_id',
    ];

    public function reservations()
    {
        return $this->belongsTo(Reservation::class, 'reservations_id');
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class, 'reservations_id');
    }

    public function tables()
    {
        return $this->belongsTo(Table::class, 'tables_id');
    }

    public function table()
    {
        return $this->belongsTo(Table::class, 'tables_id');
    }
}
