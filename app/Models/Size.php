<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Size extends Model
{
    use HasFactory;

    protected $primaryKey = 'size_id';
    protected $fillable = ['name'];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'size_products', 'size_id', 'product_id');
    }
}