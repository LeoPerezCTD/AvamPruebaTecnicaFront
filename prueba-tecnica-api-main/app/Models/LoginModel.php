<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LoginModel extends Model{
    public function getUser($user){
        $sql = "SELECT 
                    us.user_id,
                    us.user_username,
                    us.user_password,
                    pe.person_firstname || ' ' || pe.person_surname as name,
                    up.profile_id
                FROM 
                    users us
                inner join people pe
                    on us.person_id = pe.person_id
                inner join users_profiles up
                    on up.user_id = us.user_id
                where 
                    us.user_username = ?
                    and us.user_active = 1";
        return DB::select($sql,array($user));
    }
}
