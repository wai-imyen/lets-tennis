<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'line_user_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * @param string $name
     * @param string $line_user_id
     * 
     * @return void
     */
    public function addLineUser($name, $line_user_id)
    {
        // 檢查用戶是否存在，若無則建立
        $query = User::query()->where('line_user_id','=',$line_user_id);
        if( ! $query->first()){
            User::create(
                array(
                    'name' => $name,
                    'line_user_id' => $line_user_id,
                )
            );
        }
    }
}
