<?php

namespace Database\Seeders;

use App\Models\Details_Reservation;
use App\Models\Reservation;
use App\Models\Table;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DetailsReservationSeeder extends Seeder
{
    public function run(): void
    {
        $reservas = Reservation::all();
        $mesas = Table::all();

        if ($reservas->isEmpty() || $mesas->isEmpty()) {
            $this->command->error('No hay reservas o mesas. Ejecuta primero TableSeeder y ReservationSeeder.');
            return;
        }

        $duracionHoras = 2;

        $verificarDisponibilidad = function ($mesaId, $fecha, $horaInicio, $duracionHoras, $reservaActualId = null) {
            $inicio = Carbon::parse($fecha . ' ' . $horaInicio);
            $fin = $inicio->copy()->addHours($duracionHoras);

            return !Details_Reservation::where('tables_id', $mesaId)
                ->whereHas('reservations', function ($query) use ($fecha, $inicio, $fin, $duracionHoras, $reservaActualId) {
                    $query->where('date', $fecha)
                        ->where(function ($q) use ($inicio, $fin, $duracionHoras) {
                            $q->whereRaw('TIME_TO_SEC(hour) < TIME_TO_SEC(?)', [$fin->format('H:i:s')])
                              ->whereRaw('TIME_TO_SEC(?) < TIME_TO_SEC(hour) + ?', [
                                  $inicio->format('H:i:s'),
                                  $duracionHoras * 3600
                              ]);
                        });
                    if ($reservaActualId) {
                        $query->where('id', '!=', $reservaActualId);
                    }
                })->exists();
        };

        $reservasConDetalles = 0;

        foreach ($reservas as $reserva) {
            $numMesasDeseadas = rand(1, 3);
            $mesasAsignadas = [];

            for ($i = 0; $i < $numMesasDeseadas; $i++) {
                $mesasDisponibles = $mesas->filter(function ($mesa) use ($mesasAsignadas, $reserva, $verificarDisponibilidad, $duracionHoras) {
                    if (in_array($mesa->id, $mesasAsignadas)) {
                        return false;
                    }
                    return $verificarDisponibilidad($mesa->id, $reserva->date, $reserva->hour, $duracionHoras, $reserva->id);
                });

                if ($mesasDisponibles->isEmpty()) {
                    $this->command->warn("Reserva ID {$reserva->id}: no hay suficientes mesas disponibles para asignar {$numMesasDeseadas} mesas. Se asignaron {$i}.");
                    break;
                }

                $mesa = $mesasDisponibles->random();
                $mesasAsignadas[] = $mesa->id;

                Details_Reservation::firstOrCreate([
                    'reservations_id' => $reserva->id,
                    'tables_id'       => $mesa->id,
                ]);
            }

            if (count($mesasAsignadas) > 0) {
                $reservasConDetalles++;
            }
        }
    }
}
