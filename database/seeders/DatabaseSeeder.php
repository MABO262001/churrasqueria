<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            ThemeSeeder::class,
            UsersSeeder::class,
            ProfileSeeder::class,
            CategorieSeeder::class,
            SubCategorieSeeder::class,
            ProductSeeder::class,
            UnitSeeder::class,
            InsumoSeeder::class,
            TableSeeder::class,
            ReservationSeeder::class,
            DetailsReservationSeeder::class,
            SalesNoteSeeder::class,
        ]);
    }
}
