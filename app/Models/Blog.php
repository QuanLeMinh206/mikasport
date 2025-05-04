<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    protected $table = 'blogs';
    protected $primaryKey = 'blog_id';
    public $timestamps = false;

    protected $fillable = [
        'title',
        'img',
        'video',
        'message',
        'timestamp',
        'product_id',
        'user_id',

    ];

}
