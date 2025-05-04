<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $table = 'carts';
    protected $primaryKey = 'cart_id';
    public $timestamps = false;

    protected $fillable = ['user_id', 'session_id', 'size_product_id', 'quantity', 'sub_price'];

    protected $casts = [
        'quantity' => 'integer',
        'sub_price' => 'float',
        'session_id' => 'string',
    ];

    // Quan hệ với SizeProduct
    public function sizeProduct()
{
    return $this->belongsTo(Size_Product::class, 'size_product_id'); // Đảm bảo là 'size_product_id'
}

    // Quan hệ với User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    // Quan hệ với Product (sửa lại để liên kết với SizeProduct)

public function size_Product()
{
    return $this->belongsTo(Size_Product::class, 'size_product_id');
}

public function product()
{
    return $this->sizeProduct->product(); // Lấy thông tin sản phẩm từ size_product
}
}
