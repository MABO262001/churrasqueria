<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Sales_Note;
use App\Models\Details_Reservation;
use App\Models\Reservation;

class Table extends Model
{
    protected $table = 'tables';

    protected $fillable = [
        'name',
        'amount',
        'state',
    ];

    public const STATE_AVAILABLE = 'Disponible';
    public const STATE_INACTIVE = 'Inactiva';
    public const STATE_MAINTENANCE = 'Mantenimiento';

    public static function states(): array
    {
        return [
            self::STATE_AVAILABLE,
            self::STATE_INACTIVE,
            self::STATE_MAINTENANCE,
        ];
    }

    public function details_reservations()
    {
        return $this->hasMany(Details_Reservation::class, 'tables_id');
    }

    public function reservations()
    {
        return $this->belongsToMany(
            Reservation::class,
            'details_reservations',
            'tables_id',
            'reservations_id'
        )->withTimestamps();
    }

    public function sales_notes()
    {
        return $this->hasMany(Sales_Note::class, 'tables_id');
    }
}
