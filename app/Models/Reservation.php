<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Details_Reservation;
use App\Models\User;
use App\Models\Sales_Note;
use App\Models\Table;

class Reservation extends Model
{
    protected $table = 'reservations';

    protected $fillable = [
        'descriptions',
        'hour',
        'date',
        'state',
        'users_id',
        'users_cliente_id',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public const STATE_PENDING = 'Pendiente';
    public const STATE_CONFIRMED = 'Confirmada';
    public const STATE_IN_PROCESS = 'En Proceso';
    public const STATE_COMPLETED = 'Completada';
    public const STATE_CANCELLED = 'Cancelada';
    public const STATE_REJECTED = 'Rechazada';

    public static function states(): array
    {
        return [
            self::STATE_PENDING,
            self::STATE_CONFIRMED,
            self::STATE_IN_PROCESS,
            self::STATE_COMPLETED,
            self::STATE_CANCELLED,
            self::STATE_REJECTED,
        ];
    }

    public static function activeStates(): array
    {
        return [
            self::STATE_PENDING,
            self::STATE_CONFIRMED,
            self::STATE_IN_PROCESS,
        ];
    }

    public function users()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function users_cliente_id()
    {
        return $this->belongsTo(User::class, 'users_cliente_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'users_cliente_id');
    }

    public function details_reservations()
    {
        return $this->hasMany(Details_Reservation::class, 'reservations_id');
    }

    public function tables()
    {
        return $this->belongsToMany(
            Table::class,
            'details_reservations',
            'reservations_id',
            'tables_id'
        )->withTimestamps();
    }

    public function sales_notes()
    {
        return $this->hasMany(Sales_Note::class, 'reservations_id');
    }
}
