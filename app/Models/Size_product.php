<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Size_product extends Model
{
    use HasFactory;

    protected $primaryKey = 'size_product_id';
    protected $fillable = ['size_id', 'product_id'];
}