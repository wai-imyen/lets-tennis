<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Competitor extends Model
{
    use HasFactory;

    protected $table = 'competitors';
	protected $guarded = ['id'];

    /**
     * 取得用戶
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'id');
    }

    /**
     * 取得收藏
     */
    public function wishlist()
    {
        return $this->belongsToMany('App\Models\User', 'competitor_to_user', 'urn_competitor', 'user_id')->withTimestamps();
    }
}
