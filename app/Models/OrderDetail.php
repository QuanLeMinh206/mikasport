<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    use HasFactory;

    // Tên bảng (nếu bảng không theo chuẩn của Laravel)
    protected $table = 'order_details';
    public $timestamps = false;

    // Cột không thể gán đại trà (mass assignment)
    protected $fillable = [
        'quantity',
        'sub_price',
        'size_product_id',
        'order_id',
    ];

    // Định nghĩa quan hệ với bảng Order
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }

    // Định nghĩa quan hệ với bảng SizeProduct
    public function sizeProduct()
    {
        return $this->belongsTo(Size_product::class, 'size_product_id', 'size_product_id');
    }
}