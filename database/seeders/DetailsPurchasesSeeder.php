<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Purchase_Notes;
use App\Models\Insumo;
use App\Models\Details_Purchases;

class DetailsPurchasesSeeder extends Seeder
{
    private $maxDetails = 50;

    public function run(): void
    {
        $notas = Purchase_Notes::all();
        $insumos = Insumo::all();

        if ($notas->isEmpty()) {
            $this->command->error('No hay notas de compra. Ejecuta primero PurchaseNotesSeeder.');
            return;
        }

        if ($insumos->isEmpty()) {
            $this->command->error('No hay insumos. Ejecuta primero InsumoSeeder.');
            return;
        }

        $totalDetalles = 0;

        foreach ($notas as $nota) {
            if ($totalDetalles >= $this->maxDetails) break;

            $numDetalles = rand(1, 5);
            $insumosUsados = [];

            for ($i = 0; $i < $numDetalles; $i++) {
                if ($totalDetalles >= $this->maxDetails) break;

                $insumosDisponibles = $insumos->reject(fn($i) => in_array($i->id, $insumosUsados));
                if ($insumosDisponibles->isEmpty()) {
                    break;
                }
                $insumo = $insumosDisponibles->random();
                $insumosUsados[] = $insumo->id;

                $cantidad = rand(1, 100);
                $precioUnitario = $insumo->price;
                $precioTotalLinea = $cantidad * $precioUnitario;

                Details_Purchases::create([
                    'insumos_id'        => $insumo->id,
                    'purchase_notes_id' => $nota->id,
                    'amount'            => $cantidad,
                    'price_purchase'    => $precioTotalLinea,
                ]);

                $insumo->amount += $cantidad;
                $insumo->save();

                $totalDetalles++;
            }

            $total = Details_Purchases::where('purchase_notes_id', $nota->id)->sum('price_purchase');
            if ($total > 0) {
                $nota->total_price = $total;
                $nota->save();
            }
        }
    }
}