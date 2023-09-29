<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RegisterModel extends Model{

    public function register($user){
        $sql = "SELECT 
                    us.usuario_id,
                    us.usuario_username,
                    us.usuario_password,
                    pe.persona_nombre1 || ' ' || pe.persona_apellido1 as nombre
                FROM 
                    usuarios us
                inner join personas pe
                    on us.persona_id = pe.persona_id
                where 
                    us.usuario_username = ?
                    and us.usuario_estado = 1";
        return DB::select($sql,array($user));
    }

    public function insertRegister($form){
        $options = ['cost'=> 12];
        $pass = password_hash($form->usuario_password,PASSWORD_DEFAULT,$options);
        $form->usuario_password = $pass;

        foreach($form as $key=>$value){
            if($value !== ''){
                $sqlInsert[]    = $key;
                $sqlBind[]      = '?';
                $sqlValues[]    = $value;
            }
        }
        $sqlInsert  = implode(',',$sqlInsert);
        $sqlBind    = implode(',',$sqlBind);
        $sql        = "INSERT INTO aprobar_usuarios ($sqlInsert) values($sqlBind)";
        return DB::select($sql,$sqlValues);
    }

    public function checkEmail($username){
        $sql = "SELECT usuario_username from usuarios where usuario_username = ?";
        return DB::select($sql,array($username));
    }

}
