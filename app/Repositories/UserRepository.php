<?php
namespace App\Repositories;

use App\Models\User;

class UserRepository
{

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