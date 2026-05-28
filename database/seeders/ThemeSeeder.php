<?php

namespace Database\Seeders;

use App\Models\Theme;
use Illuminate\Database\Seeder;

class ThemeSeeder extends Seeder
{
    public function run(): void
    {
        // Usar firstOrCreate para evitar duplicados
        $administrador = Theme::firstOrCreate([
            'name' => 'Administrador',
        ]);

        $cliente = Theme::firstOrCreate([
            'name' => 'Cliente',
        ]);

        $niño = Theme::firstOrCreate([
            'name' => 'Niño',
        ]);
    }
}
