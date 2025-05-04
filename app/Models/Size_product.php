<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Size_product extends Model
{
    use HasFactory;

    protected $table = 'size_products'; // Định nghĩa tên bảng nếu không theo chuẩn Laravel
    protected $primaryKey = 'size_product_id'; // Khóa chính của bảng
    public $timestamps = false; // Nếu bảng không có cột timestamps (`created_at`, `updated_at`)

    protected $fillable = ['size_id', 'product_id','stock'];

    // Quan hệ với bảng Sizes
    public function size()
    {
        return $this->belongsTo(Size::class, 'size_id', 'size_id');
    }

    // Quan hệ với bảng Products
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
