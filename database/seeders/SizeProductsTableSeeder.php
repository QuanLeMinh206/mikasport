<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Size;
use App\Models\Size_product;
use Illuminate\Support\Facades\DB;

class SizeProductsTableSeeder extends Seeder
{
    public function run()
    {   
 
    
    
        
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
       Size_product::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        // Lấy tất cả sản phẩm và size
        $products = Product::all();
        $sizes = Size::all();

        foreach ($products as $product) {
            foreach ($sizes as $size) {
                DB::table('size_products')->insert([
                    'size_id' => $size->size_id,
                    'product_id' => $product->product_id,
                ]);
            }
        }
    }
}