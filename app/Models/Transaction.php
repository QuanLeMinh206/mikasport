<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    // Nếu tên bảng không phải là dạng số nhiều của model (transactions)
    // protected $table = 'transactions';

    // Các trường có thể gán giá trị (mass assignable)
    protected $fillable = [
        'order_id',
        'transaction_no',
        'amount',
        'payment_method',
        'status'
    ];

    // Các trường không thể gán giá trị (mass assignable)
    // protected $guarded = ['id'];

    // Thiết lập quan hệ với bảng orders
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}