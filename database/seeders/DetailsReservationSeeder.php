<?php

namespace Database\Seeders;

use App\Models\Details_Reservation;
use App\Models\Reservation;
use App\Models\Table;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DetailsReservationSeeder extends Seeder
{
    private $maxDetails = 70;

    public function run(): void
    {
        $reservas = Reservation::all();
        $mesas = Table::all();
        $duracionHoras = 2;
        $totalDetalles = 0;

        $verificarDisponibilidad = function ($mesaId, $fecha, $horaInicio, $duracionHoras, $reservaActualId = null) {
            $inicio = Carbon::parse($fecha . ' ' . $horaInicio);
            $fin = $inicio->copy()->addHours($duracionHoras);

            return !Details_Reservation::where('tables_id', $mesaId)
                ->whereHas('reservations', function ($query) use ($fecha, $inicio, $fin, $duracionHoras, $reservaActualId) {
                    $query->where('date', $fecha)
                        ->where(function ($q) use ($inicio, $fin, $duracionHoras) {
                            $q->whereRaw('EXTRACT(EPOCH FROM hour) < EXTRACT(EPOCH FROM ?::time)', [$fin->format('H:i:s')])
                              ->whereRaw('EXTRACT(EPOCH FROM ?::time) < EXTRACT(EPOCH FROM hour) + ?', [
                                  $inicio->format('H:i:s'),
                                  $duracionHoras * 3600
                              ]);
                        });
                    if ($reservaActualId) {
                        $query->where('id', '!=', $reservaActualId);
                    }
                })->exists();
        };

        foreach ($reservas as $reserva) {
            if ($totalDetalles >= $this->maxDetails) break;

            $numMesasDeseadas = rand(1, 3);
            $mesasAsignadas = [];

            for ($i = 0; $i < $numMesasDeseadas; $i++) {
                if ($totalDetalles >= $this->maxDetails) break;

                $mesasDisponibles = $mesas->filter(function ($mesa) use ($mesasAsignadas, $reserva, $verificarDisponibilidad, $duracionHoras) {
                    if (in_array($mesa->id, $mesasAsignadas)) return false;
                    return $verificarDisponibilidad($mesa->id, $reserva->date, $reserva->hour, $duracionHoras, $reserva->id);
                });

                if ($mesasDisponibles->isEmpty()) {
                    $this->command->warn("Reserva ID {$reserva->id}: no hay suficientes mesas disponibles.");
                    break;
                }

                $mesa = $mesasDisponibles->random();
                $mesasAsignadas[] = $mesa->id;

                Details_Reservation::firstOrCreate([
                    'reservations_id' => $reserva->id,
                    'tables_id'       => $mesa->id,
                ]);
                $totalDetalles++;
            }
        }
    }
}