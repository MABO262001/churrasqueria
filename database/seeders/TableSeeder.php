<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Table;

class TableSeeder extends Seeder
{
    public function run(): void
    {
        // Posibles estados
        // $estados = ['disponible', 'reservada', 'ocupada'];

        $mesas = [];

        for ($i = 1; $i <= 25; $i++) {
            // Capacidad según rango
            if ($i <= 5) {
                $capacidad = 2;
            } elseif ($i <= 12) {
                $capacidad = 4;
            } elseif ($i <= 18) {
                $capacidad = 6;
            } elseif ($i <= 22) {
                $capacidad = 8;
            } else {
                $capacidad = 10;
            }

            // Estado aleatorio
            // $estado = $estados[array_rand($estados)];

            $mesas[] = [
                'name'   => "Mesa {$i}",
                'amount' => $capacidad,
            ];
        }

        foreach ($mesas as $mesa) {
            Table::firstOrCreate(
                ['name' => $mesa['name']],
                [
                    'amount' => $mesa['amount'],
                ]
            );
        }
    }
}
