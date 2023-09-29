<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Exception;


class UsersConfigurationModel extends Model{
    public $table           = "users";
    public $identificador   = "user_id";
    public $creador         = "user_created_by";
    public $actualizador    = "user_modified_by";
    public $factualizacion  = "user_modified_at";
    public $active          = "user_active";

    public function get($user, $id=NULL){
        if($id==null){
            $sql ="SELECT 
                        us.user_id, 
                        us.user_username,
                        COALESCE(pe.person_firstname || ' ' || pe.person_secondname || ' ' || pe.person_surname || ' ' || pe.person_secondsurname, pe.person_firstname || ' ' || pe.person_surname) as user_name,
                        pe.*,
                        co.company_name,
                        co.company_id,
                        l.location_id,
                        l.location_name,
                        pr.profile_id,
                        pr.profile_name
                    FROM users us
                    INNER JOIN people as pe
                        ON pe.person_id = us.person_id
                        AND pe.person_active = 1
                    INNER JOIN users_profiles as up
                        ON us.user_id = up.user_id
                        AND up.userprofile_active = 1
                    INNER JOIN profiles as pr
                        ON pr.profile_id = up.profile_id
                        AND pr.profile_active = 1
                    INNER JOIN companies as co
                        ON co.company_id = us.company_id
                        AND co.company_active = 1
                    LEFT JOIN users_locations as ul
                        ON ul.user_id = us.user_id
                        AND ul.userlocation_active = 1
                    LEFT JOIN locations as l
                        ON l.location_id = ul.location_id
                        AND l.location_active = 1
                "; 
            return DB::select($sql);
            

        } else{
            $sql ="SELECT 
                        us.user_id, 
                        us.user_username,
                        COALESCE(pe.person_firstname || ' ' || pe.person_secondname || ' ' || pe.person_surname || ' ' || pe.person_secondsurname, pe.person_firstname || ' ' || pe.person_surname) as user_name,
                        pe.*,
                        co.company_name,
                        co.company_id,
                        l.location_id,
                        l.location_name,
                        pr.profile_id,
                        pr.profile_name
                    FROM users us
                    INNER JOIN people as pe
                        ON pe.person_id = us.person_id
                        AND pe.person_active = 1
                    INNER JOIN users_profiles as up
                        ON us.user_id = up.user_id
                        AND up.userprofile_active = 1
                    INNER JOIN profiles as pr
                        ON pr.profile_id = up.profile_id
                        AND pr.profile_active = 1
                    INNER JOIN companies as co
                        ON co.company_id = us.company_id
                        AND co.company_active = 1
                    LEFT JOIN users_locations as ul
                        ON ul.user_id = us.user_id
                        AND ul.userlocation_active = 1
                    LEFT JOIN locations as l
                        ON l.location_id = ul.location_id
                        AND l.location_active = 1
                    WHERE us.user_id = ?
                ";
            return DB::select($sql, array($id));
        
        }  
    }

    public function updateData($form, $id, $user){
        // usuario actualizacion
        $form->{$this->actualizador} = $user;
        // fecha actualizacion
        $form->{$this->factualizacion} = 'now()';

        $sql = "UPDATE $this->table set ";
        $sqlSets = [];
        $sqlValues = [];
        foreach ($form as $key => $value) {
            $sqlSets[] = " $key = ? ";
            $sqlValues[] = $value;
        }
        $sqlSets = implode(',', $sqlSets);
        $sql .= $sqlSets . " where $this->identificador = ?";

        // id actualizacion
        $sqlValues[] = $id;
        return DB::update($sql, $sqlValues);
    }

    
}
