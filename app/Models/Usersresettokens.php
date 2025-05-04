<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Usersresettokens extends Model
{
    use HasFactory;

    protected $table = 'users_reset_tokens';
    protected $primaryKey = 'email';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'email',
        'token',
    ];

    public $timestamps = false;

    public function user()
    {
        return $this->hasOne(User::class, 'email', 'email');
    }

    // Đổi tên đúng chuẩn Laravel
    public function scopeCheckToken($query, $token)
    {
        return $query->where('token', $token)->firstOrFail();
    }
}

?>
