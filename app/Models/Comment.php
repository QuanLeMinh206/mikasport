<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $table = 'comments'; // Tên bảng
    protected $primaryKey = 'comment_id'; // Khóa chính
    public $timestamps = false; // Không sử dụng timestamps mặc định

    protected $fillable = [
        'comment_id',
        'title',
        'img',
        'video',
        'message',
        'rating',
        'timestamp',
        'product_id',
        'user_id',
    ];

    // Định nghĩa quan hệ với User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id'); // Sửa 'id' thành 'user_id' nếu cần
    }

    // Định nghĩa quan hệ với Product
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id'); // Sửa 'id' thành 'product_id' nếu cần
    }
}

?>
