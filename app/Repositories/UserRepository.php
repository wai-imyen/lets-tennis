<?php
namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use App\Models\User;

class UserRepository
{

    /**
     * 建立用戶
     * 
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

    /**
     * 取得用戶ID
     * 
     * @param string $line_user_id
     * 
     * @return int|null
     */
    public function getUserIdByLineId($line_user_id)
    {
        $query = User::query()->where('line_user_id','=',$line_user_id)->first('id');
        return $query ? $query->id : null;
    }

    /**
     * 取得收藏清單
     * 
     * @param string $line_user_id
     * 
     * @return array
     */
    public function getWishlist($line_user_id)
    {
        return DB::select('select c.* from competitor_to_user as ctu 
        left join users as u on ctu.user_id = u.id 
        left join competitors as c on ctu.urn_competitor = c.urn_competitor 
        where u.line_user_id = ?', [$line_user_id]);
    }

    /**
     * 加入收藏清單
     * 
     * @param string $line_user_id
     * @param string $urn_competitor
     * 
     * @return bool
     */
    public function addWishlist(string $line_user_id, string $urn_competitor)
    {
        
        $user_id = $this->getUserIdByLineId($line_user_id);
        $user = User::find($user_id);
        if($user){
            $user->wishlist()->detach($urn_competitor);
            $user->wishlist()->attach($urn_competitor);
            return true;
        }

        return false;
    }

    /**
     * 刪除收藏清單
     * 
     * @param string $line_user_id
     * @param string $urn_competitor
     * 
     * @return bool
     */
    public function removeWishlist(string $line_user_id, string $urn_competitor)
    {
        
        $user_id = $this->getUserIdByLineId($line_user_id);
        $user = User::find($user_id);
        if($user){
            $user->wishlist()->detach($urn_competitor);
            return true;
        }

        return false;
    }
}