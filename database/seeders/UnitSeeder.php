<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Unit;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['name' => 'Kilogramo', 'abbreviation' => 'KG'],
            ['name' => 'Gramo', 'abbreviation' => 'GR'],
            ['name' => 'Litro', 'abbreviation' => 'LT'],
            ['name' => 'Mililitro', 'abbreviation' => 'ML'],
            ['name' => 'Unidad', 'abbreviation' => 'U'],
            ['name' => 'Paquete', 'abbreviation' => 'pqt'],
            ['name' => 'Saco', 'abbreviation' => 'Saco'],
            ['name' => 'Bolsa', 'abbreviation' => 'Bolsa'],
            ['name' => 'Botella', 'abbreviation' => 'Bot'],
            ['name' => 'Lata', 'abbreviation' => 'Lata'],
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate(
                ['name' => $unit['name']],
                ['abbreviation' => $unit['abbreviation']]
            );
        }
    }
}
