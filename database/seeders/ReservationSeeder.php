<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;

class ReservationSeeder extends Seeder
{
    public function run(): void
    {
        $clientes = User::role('Cliente')->get();

        if ($clientes->isEmpty()) {
            $this->command->error('No hay clientes con rol Cliente. Ejecuta primero UsersSeeder.');
            return;
        }

        $descripciones = [
            'Cumpleaños',
            'Cita romántica',
            'Propuesta de matrimonio',
            'Aniversario',
            'Reunión de negocios',
            'Cena familiar',
            'Despedida de soltero/a',
            'Celebración especial',
            'Almuerzo ejecutivo',
            'Evento corporativo',
        ];

        $estados = [
            'Pendiente',
            'En Proceso',
            'Cancelada',
            'Completada',
        ];

        $fechaInicio = Carbon::create(2026, 1, 1);
        $fechaFin    = Carbon::create(2026, 12, 31);
        $diasRango   = $fechaInicio->diffInDays($fechaFin);

        $totalReservas = rand(50, 100);
        $reservasCreadas = 0;

        while ($reservasCreadas < $totalReservas) {

        $cliente = $clientes->random();

            $fecha = $fechaInicio->copy()->addDays(rand(0, $diasRango))->format('Y-m-d');

            $existe = Reservation::where('users_id', $cliente->id)
                                 ->where('date', $fecha)
                                 ->exists();

            if ($existe) {
                continue;
            }

            $hora = rand(17, 23);
            $minuto = rand(0, 59);
            $segundo = rand(0, 59);
            if (rand(1, 100) <= 5) {
                $hora = 0;
            }
            $horaFormateada = sprintf('%02d:%02d:%02d', $hora, $minuto, $segundo);

            $descripcion = $descripciones[array_rand($descripciones)];
            $estado = $estados[array_rand($estados)];

            Reservation::firstOrCreate(
                [
                    'users_id' => $cliente->id,
                    'date'     => $fecha,
                    'hour'     => $horaFormateada,
                ],
                [
                    'descriptions' => $descripcion,
                    'state'        => $estado,
                ]
            );

            $reservasCreadas++;
        }
    }
}
