<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Product extends Model
{
    use HasFactory;

    protected $primaryKey = 'product_id';
    protected $fillable = [
        'name', 'description', 'image', 'image_detail1', 'image_detail2',
        'image_detail3', 'stock_quantity', 'price', 'price_sale', 'gender',
        'color', 'category_id', 'promotion_id'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function sizes()
    {
        return $this->belongsToMany(Size::class, 'size_products', 'product_id', 'size_id');
    }
}