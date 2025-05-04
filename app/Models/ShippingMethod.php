<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingMethod extends Model
{
    protected $table = 'shipping_methods';
    protected $primaryKey = 'shipping_method_id';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'cost',
        'min_order_value',
    ];
    public function orders()
    {
        return $this->hasMany(Order::class, 'shipping_method_id', 'id');
    }
}