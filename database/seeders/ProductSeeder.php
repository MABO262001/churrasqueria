<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sub_Categorie;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // ========== OBTENER SUBCATEGORÍAS ==========
        // Carnes
        $res = Sub_Categorie::where('name', 'Res')->first();
        $cerdo = Sub_Categorie::where('name', 'Cerdo')->first();
        $pollo = Sub_Categorie::where('name', 'Pollo')->first();
        $pescado = Sub_Categorie::where('name', 'Pescado')->first();
        $embutidos = Sub_Categorie::where('name', 'Embutidos')->first();
        $parrilladas = Sub_Categorie::where('name', 'Parrilladas')->first();

        // Acompañamientos
        $ensaladas = Sub_Categorie::where('name', 'Ensaladas')->first();
        $arroces = Sub_Categorie::where('name', 'Arroces')->first();
        $papas = Sub_Categorie::where('name', 'Papas')->first();
        $yuca = Sub_Categorie::where('name', 'Yuca')->first();
        $sopas = Sub_Categorie::where('name', 'Sopas')->first();

        // Bebidas
        $sodas = Sub_Categorie::where('name', 'Sodas')->first();
        $cervezas = Sub_Categorie::where('name', 'Cervezas')->first();
        $vinos = Sub_Categorie::where('name', 'Vinos')->first();
        $licores = Sub_Categorie::where('name', 'Licores')->first();
        $jugos = Sub_Categorie::where('name', 'Jugos')->first();
        $aguasFrescas = Sub_Categorie::where('name', 'Aguas frescas')->first();

        // Postres
        $gelatina = Sub_Categorie::where('name', 'Gelatina')->first();
        $helado = Sub_Categorie::where('name', 'Helado')->first();
        $flan = Sub_Categorie::where('name', 'Flan')->first();
        $torta = Sub_Categorie::where('name', 'Torta')->first();
        $dulceLeche = Sub_Categorie::where('name', 'Dulce de leche')->first();

        // Validación rápida
        if (!$res || !$cerdo || !$pollo || !$embutidos || !$parrilladas ||
            !$ensaladas || !$arroces || !$papas || !$yuca || !$sopas ||
            !$sodas || !$cervezas || !$vinos || !$licores || !$jugos || !$aguasFrescas ||
            !$gelatina || !$helado || !$flan || !$torta || !$dulceLeche) {
            $this->command->error('Faltan subcategorías. Ejecuta primero SubCategorieSeeder.');
            return;
        }

        // ========== PRODUCTOS ==========
        $products = [
            // ------------------------------
            // CARNES - Res
            // ------------------------------
            ['name' => 'Churrasco de Cuadril (para compartir)', 'amount' => 10, 'price' => '145.00', 'sub_categories_id' => $res->id, 'url_photo' => null],
            ['name' => 'Bife Chorizo Premium', 'amount' => 20, 'price' => '37.00', 'sub_categories_id' => $res->id, 'url_photo' => null],
            ['name' => 'Picaña', 'amount' => 15, 'price' => '38.00', 'sub_categories_id' => $res->id, 'url_photo' => null],
            ['name' => 'Asado de Tira', 'amount' => 12, 'price' => '45.00', 'sub_categories_id' => $res->id, 'url_photo' => null],
            ['name' => 'Tomahawk (compartir)', 'amount' => 5, 'price' => '120.00', 'sub_categories_id' => $res->id, 'url_photo' => null],

            // CARNES - Cerdo
            ['name' => 'Chanchito a la Cruz (1/2 cerdo)', 'amount' => 4, 'price' => '180.00', 'sub_categories_id' => $cerdo->id, 'url_photo' => null],
            ['name' => 'Costillas de Cerdo BBQ', 'amount' => 18, 'price' => '22.00', 'sub_categories_id' => $cerdo->id, 'url_photo' => null],
            ['name' => 'Lomo de Cerdo a la Parrilla', 'amount' => 12, 'price' => '28.00', 'sub_categories_id' => $cerdo->id, 'url_photo' => null],

            // CARNES - Pollo
            ['name' => 'Pollo a la Parrilla (1/2 pollo)', 'amount' => 25, 'price' => '18.00', 'sub_categories_id' => $pollo->id, 'url_photo' => null],
            ['name' => 'Alitas BBQ (6 uds)', 'amount' => 30, 'price' => '15.00', 'sub_categories_id' => $pollo->id, 'url_photo' => null],

            // CARNES - Pescado
            ['name' => 'Parrillada de Pescado (surubí)', 'amount' => 8, 'price' => '45.00', 'sub_categories_id' => $pescado->id, 'url_photo' => null],

            // CARNES - Embutidos
            ['name' => 'Chorizo Fresco (unidad)', 'amount' => 60, 'price' => '7.00', 'sub_categories_id' => $embutidos->id, 'url_photo' => null],
            ['name' => 'Salchicha Alemana (unidad)', 'amount' => 50, 'price' => '5.00', 'sub_categories_id' => $embutidos->id, 'url_photo' => null],
            ['name' => 'Jamón Cocido (100g)', 'amount' => 20, 'price' => '8.00', 'sub_categories_id' => $embutidos->id, 'url_photo' => null],
            ['name' => 'Mortadela (100g)', 'amount' => 20, 'price' => '6.00', 'sub_categories_id' => $embutidos->id, 'url_photo' => null],
            ['name' => 'Tocino Ahumado (100g)', 'amount' => 15, 'price' => '10.00', 'sub_categories_id' => $embutidos->id, 'url_photo' => null],
            ['name' => 'Salame (100g)', 'amount' => 15, 'price' => '9.00', 'sub_categories_id' => $embutidos->id, 'url_photo' => null],

            // CARNES - Parrilladas
            ['name' => 'Parrillada Mixta (1 persona)', 'amount' => 20, 'price' => '55.00', 'sub_categories_id' => $parrilladas->id, 'url_photo' => null],
            ['name' => 'Parrillada Familiar (4 personas)', 'amount' => 10, 'price' => '180.00', 'sub_categories_id' => $parrilladas->id, 'url_photo' => null],

            // ------------------------------
            // ACOMPAÑAMIENTOS
            // ------------------------------
            // Ensaladas
            ['name' => 'Ensalada Mixta (lechuga, tomate, cebolla)', 'amount' => 40, 'price' => '12.00', 'sub_categories_id' => $ensaladas->id, 'url_photo' => null],
            ['name' => 'Ensalada Rusa', 'amount' => 30, 'price' => '14.00', 'sub_categories_id' => $ensaladas->id, 'url_photo' => null],

            // Arroces
            ['name' => 'Arroz Blanco', 'amount' => 50, 'price' => '8.00', 'sub_categories_id' => $arroces->id, 'url_photo' => null],
            ['name' => 'Arroz con Queso (típico cruceño)', 'amount' => 40, 'price' => '12.00', 'sub_categories_id' => $arroces->id, 'url_photo' => null],

            // Papas
            ['name' => 'Papas Fritas (porción)', 'amount' => 60, 'price' => '10.00', 'sub_categories_id' => $papas->id, 'url_photo' => null],
            ['name' => 'Papas Doradas al Horno', 'amount' => 40, 'price' => '12.00', 'sub_categories_id' => $papas->id, 'url_photo' => null],

            // Yuca
            ['name' => 'Yuca Frita (porción)', 'amount' => 45, 'price' => '11.00', 'sub_categories_id' => $yuca->id, 'url_photo' => null],
            ['name' => 'Yuca Cocida con Salsa', 'amount' => 40, 'price' => '9.00', 'sub_categories_id' => $yuca->id, 'url_photo' => null],

            // Sopas
            ['name' => 'Sopa de Maní (entrada)', 'amount' => 25, 'price' => '15.00', 'sub_categories_id' => $sopas->id, 'url_photo' => null],
            ['name' => 'Locro de Gallina', 'amount' => 20, 'price' => '18.00', 'sub_categories_id' => $sopas->id, 'url_photo' => null],

            // ------------------------------
            // BEBIDAS - Sodas (muchas)
            // ------------------------------
            ['name' => 'Coca-Cola (350ml)', 'amount' => 100, 'price' => '5.00', 'sub_categories_id' => $sodas->id, 'url_photo' => null],
            ['name' => 'Coca-Cola (1.5L)', 'amount' => 50, 'price' => '12.00', 'sub_categories_id' => $sodas->id, 'url_photo' => null],
            ['name' => 'Sprite (350ml)', 'amount' => 80, 'price' => '5.00', 'sub_categories_id' => $sodas->id, 'url_photo' => null],
            ['name' => 'Fanta Naranja (350ml)', 'amount' => 70, 'price' => '5.00', 'sub_categories_id' => $sodas->id, 'url_photo' => null],
            ['name' => 'Pepsi (350ml)', 'amount' => 90, 'price' => '5.00', 'sub_categories_id' => $sodas->id, 'url_photo' => null],
            ['name' => 'Manaos (Guaraná)', 'amount' => 60, 'price' => '4.50', 'sub_categories_id' => $sodas->id, 'url_photo' => null],

            // Bebidas - Cervezas (marcas bolivianas)
            ['name' => 'Cerveza Paceña (355ml)', 'amount' => 150, 'price' => '8.00', 'sub_categories_id' => $cervezas->id, 'url_photo' => null],
            ['name' => 'Cerveza Huari (355ml)', 'amount' => 120, 'price' => '8.00', 'sub_categories_id' => $cervezas->id, 'url_photo' => null],
            ['name' => 'Cerveza Potosina (355ml)', 'amount' => 80, 'price' => '7.00', 'sub_categories_id' => $cervezas->id, 'url_photo' => null],
            ['name' => 'Cerveza IPA (artesanal)', 'amount' => 40, 'price' => '12.00', 'sub_categories_id' => $cervezas->id, 'url_photo' => null],

            // Bebidas - Vinos
            ['name' => 'Vino Tinto (botella 750ml)', 'amount' => 25, 'price' => '45.00', 'sub_categories_id' => $vinos->id, 'url_photo' => null],
            ['name' => 'Vino Blanco (botella 750ml)', 'amount' => 20, 'price' => '45.00', 'sub_categories_id' => $vinos->id, 'url_photo' => null],

            // Bebidas - Licores
            ['name' => 'Whisky Johnnie Walker (500ml)', 'amount' => 15, 'price' => '120.00', 'sub_categories_id' => $licores->id, 'url_photo' => null],
            ['name' => 'Singani (botella 750ml)', 'amount' => 30, 'price' => '80.00', 'sub_categories_id' => $licores->id, 'url_photo' => null],

            // Bebidas - Jugos naturales
            ['name' => 'Jugo de Naranja (500ml)', 'amount' => 40, 'price' => '10.00', 'sub_categories_id' => $jugos->id, 'url_photo' => null],
            ['name' => 'Jugo de Mango (500ml)', 'amount' => 35, 'price' => '10.00', 'sub_categories_id' => $jugos->id, 'url_photo' => null],
            ['name' => 'Jugo de Papaya (500ml)', 'amount' => 30, 'price' => '10.00', 'sub_categories_id' => $jugos->id, 'url_photo' => null],

            // Bebidas - Aguas frescas
            ['name' => 'Agua de Horchata (500ml)', 'amount' => 50, 'price' => '6.00', 'sub_categories_id' => $aguasFrescas->id, 'url_photo' => null],
            ['name' => 'Agua de Jamaica (500ml)', 'amount' => 45, 'price' => '6.00', 'sub_categories_id' => $aguasFrescas->id, 'url_photo' => null],
            ['name' => 'Agua de Tamarindo (500ml)', 'amount' => 40, 'price' => '6.00', 'sub_categories_id' => $aguasFrescas->id, 'url_photo' => null],

            // ------------------------------
            // POSTRES
            // ------------------------------
            ['name' => 'Gelatina de Frutas', 'amount' => 30, 'price' => '7.00', 'sub_categories_id' => $gelatina->id, 'url_photo' => null],
            ['name' => 'Helado de Vainilla (2 bolas)', 'amount' => 25, 'price' => '9.00', 'sub_categories_id' => $helado->id, 'url_photo' => null],
            ['name' => 'Helado de Chocolate (2 bolas)', 'amount' => 25, 'price' => '9.00', 'sub_categories_id' => $helado->id, 'url_photo' => null],
            ['name' => 'Flan Casero', 'amount' => 20, 'price' => '10.00', 'sub_categories_id' => $flan->id, 'url_photo' => null],
            ['name' => 'Torta de Chocolate (rebanada)', 'amount' => 15, 'price' => '12.00', 'sub_categories_id' => $torta->id, 'url_photo' => null],
            ['name' => 'Dulce de Leche con Frutos', 'amount' => 18, 'price' => '10.00', 'sub_categories_id' => $dulceLeche->id, 'url_photo' => null],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(
                [
                    'name' => $product['name'],
                    'sub_categories_id' => $product['sub_categories_id'],
                ],
                [
                    'amount' => $product['amount'],
                    'price' => $product['price'],
                    'url_photo' => $product['url_photo'],
                ]
            );
        }
    }
}
