<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Exception;

class PeopleModel extends Model{
    public $table           = "people";
    public $identificator   = "person_id";
    public $creator         = "person_created_by";
    public $modifier        = "person_modified_by";
    public $modifiedAt      = "person_modified_at";
    public $active          = "person_active";

    public function get($user, $id = null){
        if ($id == null){
            $sql = "SELECT
                        us.user_id,
                        us.user_username,
                        (select
                            coalesce(pe1.person_firstname||' '||pe1.person_surname,pe1.person_firstname)
                            from people pe1
                            inner join users us1
                                on pe1.person_id = us1.person_id
                            where us1.user_id = us.user_created_by
                        ) as user_created_by,
                        us.user_created_at,
                        coalesce(pe.person_firstname||' '||pe.person_surname,pe.person_firstname) as person_fullname,
                        pe.*
                    from
                        users us
                    inner join people pe
                        on pe.person_id = us.person_id
                    order by us.user_created_at, person_fullname";
            $result = DB::select($sql);
        } else {
            $sql = "SELECT
                        us.user_id,
                        us.user_username,
                        us.user_active,
                        (select
                            coalesce(pe1.person_firstname||' '||pe1.person_surname,pe1.person_firstname)
                            from people pe1
                            inner join users us1
                                on pe1.person_id = us1.person_id
                            where us1.user_id = us.user_created_by
                        ) as user_created_by,
                        us.user_created_at,
                        coalesce(pe.person_firstname||' '||pe.person_surname,pe.person_firstname) as person_fullname,
                        pe.*
                    from
                        users us
                    inner join people pe
                        on pe.person_id = us.person_id
                    where us.user_id = ?
                    order by us.user_created_at,person_fullname";
            $result = DB::select($sql,array($id));
        }
        return $result;
    }

    public function getNameByPhoneNumber($phone){
        $sql = "SELECT person_firstname, person_email from people pe inner join users us on us.person_id = pe.person_id where us.user_username = ?";
        return DB::select($sql,[$phone]);
    }

    // actualizacion
    public function updateData($form,$id,$user){
        try{
            // usuario actualizacion
            $form->{$this->modifier} = $user;
            // fecha actualizacion
            $form->{$this->modifiedAt} = 'now()';

            $sql = "UPDATE $this->table set ";
            $sqlSets = [];
            $sqlValues = [];
            foreach ($form as $key => $value){
                $sqlSets[] = " $key = ? ";
                $sqlValues[] = $value;
            }
            $sqlSets = implode(',',$sqlSets);
            $sql .= $sqlSets . " where $this->identificator = ?";

            // id actualizacion
            $sqlValues[] = $id;
            return DB::update($sql,$sqlValues);
        }catch(Exception $e){
            if(env("APP_ENV") == "local"){
                print_r($e->getMessage());
            }
            return false;
        }
    }

    // actualizacion estatus
    public function updateStatus($status,$id,$user,$codigos = null){
        if ($id == 'null'){
            $codigos = implode(',',$codigos);
            $sql ="UPDATE $this->table set
                $this->sqlEstado = ?,
                $this->modifier = ?,
                $this->modifiedAt = now()
                where $this->identificator in ($codigos)";
            $result = DB::update($sql,array($status,$user));
        } else {
            $sql ="UPDATE $this->table set
                $this->sqlEstado = ?,
                $this->modifier = ?,
                $this->modifiedAt = now()
                where $this->identificator = ?";
                $result = DB::update($sql,array($status,$user,$id));
        }
        return $result;
    }

    // insercion
    public function insertData($form,$user){
        try{
            $form->{$this->creator} = $user;
            foreach($form as $key=>$value){
                if($value != ''){
                    $sqlInsert[]    = $key;
                    $sqlBind[]      = '?';
                    $sqlValues[]    = $value;
                }
            }
            $sqlInsert = implode(',', $sqlInsert);
            $sqlBind = implode(',', $sqlBind);
            $sql = "INSERT INTO $this->table ($sqlInsert) values($sqlBind) returning $this->identificator";
            $result = DB::select($sql,$sqlValues);
            return $result;
        }catch(Exception $e){
            if(env("APP_ENV") == "local"){
                print_r($e->getMessage());
            }
            return false;
        }
    }

    public function inactive($id, $user){
        $sql = "UPDATE $this->table set $this->active = 0, $this->modifier = $user WHERE $this->identificator = ?";
        return DB::update($sql, array($id));
    }

    public function deletePersona($id){
        $sql = "DELETE from $this->table where $this->identificator = ?";
        return DB::delete($sql,array($id));
    }

    public function getParamsUpdate(){
        $sql="SELECT
                (SELECT
                COALESCE(json_agg(tmp.*),'[]')::text
                FROM(
                    SELECT 
                        setting_name as name, 
                        setting_value as code
                    FROM settings
                    WHERE setting_type = 'SEX'
                    ORDER BY setting_order ASC
                ) as tmp) as Sex,
                (
                SELECT 
                COALESCE (json_agg(tmp.*),'[]')::text
                FROM(
                    SELECT 
                        setting_short_name as name, 
                        setting_value as code
                    FROM settings
                    WHERE setting_type = 'MARITAL_STATUS'
                    ORDER BY setting_order ASC
                ) as tmp) as MaritalStatus
        ";
        $result = DB::select($sql);
        return $result;
    }

}
