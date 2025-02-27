<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\DB;


class ProductsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
       Product::truncate();
       DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $products = [
            ['name' => 'Áo bóng đá Nike', 'category' => 'Football', 'price' => 500000, 'color' => 'Trắng', 'gender' => 'Nam'],
            ['name' => 'Giày đá bóng Adidas', 'category' => 'Football', 'price' => 1200000, 'color' => 'Đen', 'gender' => 'Unisex'],
            ['name' => 'Áo bóng rổ Jordan', 'category' => 'Basketball', 'price' => 750000, 'color' => 'Đỏ', 'gender' => 'Nam'],
            ['name' => 'Quần tập gym Under Armour', 'category' => 'Workout', 'price' => 450000, 'color' => 'Xám', 'gender' => 'Unisex'],
            ['name' => 'Giày chạy bộ Asics', 'category' => 'Running', 'price' => 1600000, 'color' => 'Xanh', 'gender' => 'Nữ'],
            ['name' => 'Váy tennis Nike', 'category' => 'Tennis', 'price' => 800000, 'color' => 'Hồng', 'gender' => 'Nữ'],
            ['name' => 'Nike Adizero 4', 'category' => 'Running', 'price' => 800000, 'color' => 'Trắng', 'gender' => 'Nam'],
        ];

        foreach ($products as $product) {
            Product::create([
                'name' => $product['name'],
                'description' => 'Mô tả sản phẩm ' . $product['name'],
                'image' => 'default.jpg',
                'stock_quantity' => rand(10, 100),
                'price' => $product['price'],
                'price_sale' => $product['price'] - 100000,
                'gender' => $product['gender'],
                'color' => $product['color'],
                'category_id' => Category::where('name', $product['category'])->first()->category_id,
                'promotion_id' => null,
            ]);
        }
    }
}