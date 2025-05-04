<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;
class Product extends Model
{
    use HasFactory;

    protected $table = 'products';
    protected $primaryKey = 'product_id';

    protected $fillable = [
        'name',
        'description',
        'image',
        'image_detail1',
        'image_detail2',
        'image_detail3',
        'stock_quantity',
        'price',
        'price_sale',
        'gender',
        'color',
        'category_id',
        'promotion_id'
    ];

    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function promotion()
    {
        return $this->belongsTo(Promotion::class, 'promotion_id');
    }

    public function sizes()
    {
        return $this->belongsToMany(Size::class, 'size_products', 'product_id', 'size_id');
    }
    public function sizeProducts()
    {
        return $this->hasMany(Size_Product::class, 'product_id', 'product_id');
    }


    public function carts()
    {
        return $this->hasMany(Cart::class, 'product_id', 'product_id');
    }







    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'product_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'product_id');
    }



    public static function topSellingProducts()
    {
        $products = DB::table('order_details as od')
            ->join('size_products as sp', 'od.size_product_id', '=', 'sp.size_product_id')
            ->join('products as p', 'sp.product_id', '=', 'p.product_id')
            ->select(
                'p.product_id',
                'p.name',
                'p.image',
                'p.price',
                'p.price_sale',
                DB::raw('SUM(od.quantity) as total_sold')
            )
            ->groupBy('p.product_id', 'p.name', 'p.image', 'p.price', 'p.price_sale') // group by đầy đủ
            ->orderByDesc('total_sold')
            ->limit(10)
            ->get();

        return response()->json($products);
    }

    public static function topSellingWManProducts()
    {
        $products = DB::table('order_details as od')
            ->join('size_products as sp', 'od.size_product_id', '=', 'sp.size_product_id')
            ->join('products as p', 'sp.product_id', '=', 'p.product_id')
            ->select(
                'p.product_id',
                'p.name',
                'p.image',
                'p.price',
                'p.price_sale',
                DB::raw('SUM(od.quantity) as total_sold')
            )
            ->where('p.gender', 'nam') // lọc sản phẩm dành cho nữ
            ->groupBy('p.product_id', 'p.name', 'p.image', 'p.price', 'p.price_sale')
            ->orderByDesc('total_sold')
            ->limit(10)
            ->get();

        return response()->json($products);
    }


    public static function topSellingWonmenProducts()
    {
        $products = DB::table('order_details as od')
            ->join('size_products as sp', 'od.size_product_id', '=', 'sp.size_product_id')
            ->join('products as p', 'sp.product_id', '=', 'p.product_id')
            ->select(
                'p.product_id',
                'p.name',
                'p.image',
                'p.price',
                'p.price_sale',
                DB::raw('SUM(od.quantity) as total_sold')
            )
            ->where('p.gender', 'nu') // lọc sản phẩm dành cho nữ
            ->groupBy('p.product_id', 'p.name', 'p.image', 'p.price', 'p.price_sale')
            ->orderByDesc('total_sold')
            ->limit(10)
            ->get();

        return response()->json($products);
    }

    public static function saleProducts()
    {
        $products = Product::whereNotNull('price_sale')
            ->whereColumn('price_sale', '<', 'price')
            ->select(
                'product_id',
                'name',
                'image',
                'price',
                'price_sale',
                'description',
                DB::raw('(price - price_sale) AS discount_amount'),
                DB::raw('ROUND(((price - price_sale) / price) * 100, 2) AS discount_percent')
            )
            ->orderByDesc('discount_percent')
            ->limit(10)
            ->get();

        return response()->json($products);
    }


    public static function getColors()
    {
        return self::query()
            ->select('color')
            ->distinct()
            ->pluck('color');
    }

    public static function maleProducts()
{
    return self::where('gender', 'nam')->get();
}



}