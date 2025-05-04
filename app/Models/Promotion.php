<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    protected $table = 'promotions';
    protected $primaryKey = 'promotion_id';
    public $timestamps = false;

    protected $fillable = [
        'title',
        'desc',
        'discount_type',
        'discount_value',
        'start_date',
        'end_date',
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'promotion_id');
    }
}