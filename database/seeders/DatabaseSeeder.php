<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Size;
use App\Models\Category;
use App\Models\Product;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    {
        $this->call([
            CategoriesTableSeeder::class,
            SizesTableSeeder::class,
            ProductsTableSeeder::class,
            SizeProductsTableSeeder::class,
        ]);
    }
 
}