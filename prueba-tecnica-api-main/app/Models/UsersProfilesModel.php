<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UsersProfilesModel extends Model{

    public $table         = "users_profiles";
    public $identificator = "userprofile_id";
    public $creator       = "userprofile_created_by";
    public $modifier      = "userprofile_modified_by";
    public $modifiedAt    = "userprofile_modified_at";
    public $active        = "userprofile_active";
    public $userId        = "user_id";

   
    public function insertData($form, $user){
        try{
            $form->{$this->creator} = $user;

            foreach ($form as $key => $value) {
                if ($value != '') {
                    $sqlInsert[]    = $key;
                    $sqlBind[]      = '?';
                    $sqlValues[]    = $value;
                }
            }
            $sqlInsert = implode(',', $sqlInsert);
            $sqlBind = implode(',', $sqlBind);
            $sql = "INSERT INTO $this->table ($sqlInsert) values($sqlBind) returning $this->identificator";
            return DB::select($sql, $sqlValues);
        }catch(Exception $e){
            if(env("APP_ENV") == "local"){
                print_r($e->getMessage());
            }
            return false;
        }
    }

    public function updateData($form, $id, $user){
        try{
            $form->{$this->modifier}= $user;
            $form->{$this->modifiedAt} = 'now()';
    
            $sql = "UPDATE $this->table set ";
            $sqlSets = [];
            $sqlValues = [];
            foreach ($form as $key => $value) {
                $sqlSets[] = " $key = ? ";
                $sqlValues[] = $value;
            }
            $sqlSets = implode(',', $sqlSets);
            $sql .= $sqlSets . " where $this->userId = ?";
            $sqlValues[] = $id;
            return DB::update($sql, $sqlValues);
        }catch(Exception $e){
            if(env("APP_ENV") == "local"){
                print_r($e->getMessage());
            }
            return false;
        }
    }

    public function inactive($id, $user){
        $sql = "UPDATE $this->table set $this->active = 0, $this->actualizador = $user WHERE $this->identificador = ?";
        return DB::update($sql, array($id));
    }

    public function deleteData($id){
        try {
            $sql = "DELETE FROM $this->table WHERE user_id = ?";
            return DB::delete($sql, array($id));

        } catch (Exception $e) {
            if(env("APP_ENV") == "local"){
                print_r($e->getMessage());
            }
            return false;
        }
    }

  


   

}
