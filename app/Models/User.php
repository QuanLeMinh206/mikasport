<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $primaryKey = 'user_id'; // Khóa chính là user_id
    public $incrementing = true; // Khóa chính tự động tăng
    protected $keyType = 'int'; // Kiểu dữ liệu của khóa chính

    protected $fillable = [
        'user_id',
        'full_name',  // Bảng của bạn có full_name, nên cần thêm vào
        'user_name',
        'email',
        'password',
        'phone',  // Cột phone cũng có trong bảng
        'role',
        'img',
        'address',  // Cột address có trong bảng
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Nếu bạn muốn xác thực email, có thể thêm:
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
?>