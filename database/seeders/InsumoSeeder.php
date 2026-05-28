<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Insumo;
use App\Models\Unit;

class InsumoSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener las unidades que acabamos de crear
        $kg = Unit::where('abbreviation', 'KG')->first();
        $g = Unit::where('abbreviation', 'GR')->first();
        $L = Unit::where('abbreviation', 'LT')->first();
        $mL = Unit::where('abbreviation', 'ML')->first();
        $ud = Unit::where('abbreviation', 'U')->first();
        $pqt = Unit::where('abbreviation', 'pqt')->first();
        $saco = Unit::where('abbreviation', 'Saco')->first();
        $bolsa = Unit::where('abbreviation', 'Bolsa')->first();
        $bot = Unit::where('abbreviation', 'Bot')->first();
        $lata = Unit::where('abbreviation', 'Lata')->first();

        $insumos = [
            // Carnes (kg)
            ['name' => 'Pollo entero', 'number_unit' => 25, 'amount' => 25, 'units_id' => $kg->id],
            ['name' => 'Carne de res (falda)', 'number_unit' => 30, 'amount' => 30, 'units_id' => $kg->id],
            ['name' => 'Cerdo (paleta)', 'number_unit' => 15, 'amount' => 15, 'units_id' => $kg->id],
            ['name' => 'Chorizo', 'number_unit' => 10, 'amount' => 10, 'units_id' => $kg->id],
            ['name' => 'Morcilla', 'number_unit' => 8, 'amount' => 8, 'units_id' => $kg->id],
            ['name' => 'Pescado (filete)', 'number_unit' => 5, 'amount' => 5, 'units_id' => $kg->id],

            // Verduras y acompañamientos
            ['name' => 'Papas para parrilla', 'number_unit' => 20, 'amount' => 20, 'units_id' => $kg->id],
            ['name' => 'Cebolla', 'number_unit' => 10, 'amount' => 10, 'units_id' => $kg->id],
            ['name' => 'Morrón', 'number_unit' => 5, 'amount' => 5, 'units_id' => $kg->id],
            ['name' => 'Tomate', 'number_unit' => 8, 'amount' => 8, 'units_id' => $kg->id],
            ['name' => 'Lechuga', 'number_unit' => 5, 'amount' => 5, 'units_id' => $ud->id],       // unidades
            ['name' => 'Zanahoria', 'number_unit' => 3, 'amount' => 3, 'units_id' => $kg->id],

            // Carbón y leña
            ['name' => 'Carbón vegetal', 'number_unit' => 50, 'amount' => 50, 'units_id' => $kg->id],
            ['name' => 'Leña de quebracho', 'number_unit' => 30, 'amount' => 30, 'units_id' => $kg->id],
            ['name' => 'Encendedor líquido', 'number_unit' => 5, 'amount' => 5, 'units_id' => $L->id],

            // Salsas y aderezos
            ['name' => 'Chimichurri (preparado)', 'number_unit' => 3, 'amount' => 3, 'units_id' => $L->id],
            ['name' => 'Salsa barbacoa', 'number_unit' => 2, 'amount' => 2, 'units_id' => $L->id],
            ['name' => 'Aceite de oliva', 'number_unit' => 4, 'amount' => 4, 'units_id' => $L->id],
            ['name' => 'Vinagre', 'number_unit' => 2, 'amount' => 2, 'units_id' => $L->id],

            // Condimentos secos
            ['name' => 'Sal gruesa', 'number_unit' => 10, 'amount' => 10, 'units_id' => $kg->id],
            ['name' => 'Pimienta', 'number_unit' => 1, 'amount' => 1, 'units_id' => $kg->id],
            ['name' => 'Pimentón', 'number_unit' => 1, 'amount' => 1, 'units_id' => $kg->id],
            ['name' => 'Azúcar', 'number_unit' => 5, 'amount' => 5, 'units_id' => $kg->id],

            // Bebidas (insumos para preparar o vender)
            ['name' => 'Agua mineral', 'number_unit' => 20, 'amount' => 20, 'units_id' => $L->id],
            ['name' => 'Gaseosa cola', 'number_unit' => 15, 'amount' => 15, 'units_id' => $L->id],
            ['name' => 'Cerveza (lata)', 'number_unit' => 100, 'amount' => 100, 'units_id' => $lata->id],
            ['name' => 'Hielo', 'number_unit' => 10, 'amount' => 10, 'units_id' => $kg->id],

            // Postres
            ['name' => 'Helado (balde)', 'number_unit' => 5, 'amount' => 5, 'units_id' => $pqt->id],
            ['name' => 'Gelatina en polvo', 'number_unit' => 20, 'amount' => 20, 'units_id' => $ud->id],
            ['name' => 'Dulce de leche (pote)', 'number_unit' => 4, 'amount' => 4, 'units_id' => $kg->id],

            // Utensilios desechables (opcional)
            ['name' => 'Platos descartables', 'number_unit' => 200, 'amount' => 200, 'units_id' => $ud->id],
            ['name' => 'Vasos', 'number_unit' => 200, 'amount' => 200, 'units_id' => $ud->id],
            ['name' => 'Servilletas', 'number_unit' => 100, 'amount' => 100, 'units_id' => $pqt->id],
            ['name' => 'Carbón de arranque rápido', 'number_unit' => 10, 'amount' => 10, 'units_id' => $bolsa->id],
        ];

        foreach ($insumos as $insumo) {
            Insumo::firstOrCreate(
                [
                    'name' => $insumo['name'],
                    'units_id' => $insumo['units_id'],
                ],
                [
                    'number_unit' => $insumo['number_unit'],
                    'amount' => $insumo['amount'],
                ]
            );
        }
    }
}
