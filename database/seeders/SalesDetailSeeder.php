<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Sales_Detail;
use App\Models\Sales_Note;
use Illuminate\Database\Seeder;

class SalesDetailSeeder extends Seeder
{
    public function run(): void
    {
        $notas = Sales_Note::all();

        if ($notas->isEmpty()) {
            $this->command->error('No hay notas de venta. Ejecuta primero SalesNoteSeeder.');

            return;
        }

        $productos = Product::all();

        if ($productos->isEmpty()) {
            $this->command->error('No hay productos. Ejecuta primero ProductSeeder.');

            return;
        }

        foreach ($notas as $nota) {

            $numProductos = rand(1, 5);
            $productosSeleccionados = [];

            $productosDisponibles = $productos->shuffle();
            foreach ($productosDisponibles as $producto) {
                if (count($productosSeleccionados) >= $numProductos) {
                    break;
                }
                if (in_array($producto->id, $productosSeleccionados)) {
                    continue;
                }

                $cantidad = rand(1, 3);

                if ($producto->amount < $cantidad) {
                    continue;
                }

                $productosSeleccionados[] = $producto->id;

                $precioUnitario = floatval($producto->price);
                $precioVenta = round($precioUnitario * $cantidad, 2);

                Sales_Detail::create([
                    'sales_notes_id' => $nota->id,
                    'products_id' => $producto->id,
                    'amount' => $cantidad,
                    'price_sale' => $precioVenta,
                ]);

                $producto->amount -= $cantidad;
                $producto->save();
            }

            if (empty($productosSeleccionados)) {
                $this->command->warn("Nota ID {$nota->id} no tiene detalles por falta de stock.");

                continue;
            }

            $total = Sales_Detail::where('sales_notes_id', $nota->id)->sum('price_sale');
            $nota->total_price = $total;
            $nota->save();
        }
    }
}
