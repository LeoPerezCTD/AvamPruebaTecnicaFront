<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UsersLocationsModel extends Model{

    protected $table         = "users_locations";
    protected $modifier      = "userlocation_modified_by";
    protected $modifiedAt    = "userlocation_modified_at";
    protected $identificator = "userlocation_id";
    protected $creator       = "userlocation_created_by";
    protected $active        = "userlocation_active";

    public function get($user, $id=null){
        
        if ($id == null){
            $sql = "SELECT
                        ul.userlocation_id,
                        ul.location_id,
                        ul.user_id,
                        ul.userlocation_position,
                        ul.userlocation_active,
                        ul.userlocation_created_at,
                        ul.userlocation_modified_at,
                        COALESCE(
                            pc.person_firstname || ' '||
                            pc.person_secondname||' ' ||
                            pc.person_surname||' '||
                            pc.person_secondsurname,pc.person_firstname||' '||
                            pc.person_surname
                        ) as userlocation_created_by,
                        COALESCE(
                            pm.person_firstname || ' '||
                            pm.person_secondname||' ' ||
                            pm.person_surname||' '||
                            pm.person_secondsurname,pm.person_firstname||' '||
                            pm.person_surname
                        )  AS userlocation_modified_by,
                        COALESCE(
                        pe.person_firstname || ' '||
                        pe.person_secondname||' ' ||
                        pe.person_surname||' '||
                        pe.person_secondsurname,pe.person_firstname||' '||
                        pe.person_surname
                    ) as user_name,
                    co.company_name
                    FROM users_locations ul
                    INNER JOIN locations lo
                        ON lo.location_id = ul.location_id
                    INNER JOIN companies co
                        ON co.company_id = lo.company_id
                    INNER JOIN users us
                        ON us.user_id = ul.user_id
                    INNER JOIN people pe
                        ON pe.person_id = us.person_id
                    INNER JOIN users uc
                        ON uc.user_id = ul.userlocation_created_by
                    INNER JOIN people pc
                        ON pc.person_id = uc.person_id
                    LEFT JOIN users um
                        ON um.user_id = ul.userlocation_modified_by
                    LEFT JOIN people pm
                        ON pm.person_id = um.person_id";
                 return DB::select($sql);
        } else {
            $sql = "SELECT
                        ul.userlocation_id,
                        ul.location_id,
                        ul.user_id,
                        ul.userlocation_position,
                        ul.userlocation_active,
                        ul.userlocation_created_at,
                        ul.userlocation_modified_at,
                        COALESCE(
                            pc.person_firstname || ' '||
                            pc.person_secondname||' ' ||
                            pc.person_surname||' '||
                            pc.person_secondsurname,pc.person_firstname||' '||
                            pc.person_surname
                        ) as userlocation_created_by,
                        COALESCE(
                            pm.person_firstname || ' '||
                            pm.person_secondname||' ' ||
                            pm.person_surname||' '||
                            pm.person_secondsurname,pm.person_firstname||' '||
                            pm.person_surname
                        )  AS userlocation_modified_by,
                        COALESCE(
                        pe.person_firstname || ' '||
                        pe.person_secondname||' ' ||
                        pe.person_surname||' '||
                        pe.person_secondsurname,pe.person_firstname||' '||
                        pe.person_surname
                    ) as user_name,
                    co.company_name
                    FROM users_locations ul
                    INNER JOIN locations lo
                        ON lo.location_id = ul.location_id
                    INNER JOIN companies co
                        ON co.company_id = lo.company_id
                    INNER JOIN users us
                        ON us.user_id = ul.user_id
                    INNER JOIN people pe
                        ON pe.person_id = us.person_id
                    INNER JOIN users uc
                        ON uc.user_id = ul.userlocation_created_by
                    INNER JOIN people pc
                        ON pc.person_id = uc.person_id
                    LEFT JOIN users um
                        ON um.user_id = ul.userlocation_modified_by
                    LEFT JOIN people pm
                        ON pm.person_id = um.person_id
                    WHERE ul.userlocation_id = ?";
            return DB::select($sql, array($id));
        }
    }

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
        
        $form->{$this->modifier} = $user;
        $form->{$this->modifiedAt} = 'now()';

        $sql = "UPDATE $this->table set ";
        $sqlSets = [];
        $sqlValues = [];
        foreach ($form as $key => $value) {
            $sqlSets[] = " $key = ? ";
            $sqlValues[] = $value;
        }
        $sqlSets = implode(',', $sqlSets);
        $sql .= $sqlSets . " where $this->identificator = ?";
        $sqlValues[] = $id;
        return DB::update($sql, $sqlValues);
    }

    public function updateStatus($status, $id, $user, $codigos = null){
        if ($id == 'null'){
            $codigos = implode(',',$codigos);
            $sql ="UPDATE $this->table set
                $this->active = ?,
                $this->modifier = ?,
                $this->modifiedAt = now()
                where $this->identificator in ($codigos)";
            $result = DB::update($sql, array($status, $user));
        } else {
            $sql ="UPDATE $this->table set
                $this->active = ?,
                $this->modifier = ?,
                $this->modifiedAt = now()
                where $this->identificator = ?";
                $result = DB::update($sql, array($status, $user, $id));
        }
        return $result;
    }

    public function inactive($id, $user){
        $sql = "UPDATE $this->table set $this->active = 0, $this->actualizador = $user WHERE $this->identificador = ?";
        return DB::update($sql, array($id));
    }

    public function getParamsUpdate(){
        $sql = "SELECT
                    (select
                        coalesce(json_agg(tmp.*),'[]')::text
                            from(
                                select
                                    us.user_id as code,
                                    COALESCE(
                                        pe.person_firstname || ' '||
                                        pe.person_secondname||' ' ||
                                        pe.person_surname||' '||
                                        pe.person_secondsurname,pe.person_firstname||' '||
                                        pe.person_surname
                                    ) as name
                                from users us
                                inner join people pe
                                    on pe.person_id = us.person_id
                                where
                                    us.user_active = 1
                                order by name
                                ) as tmp) as users,
                    (select
                        coalesce(json_agg(tmp.*),'[]')::text
                            from(
                                select
                                    lo.location_id as code,
                                    lo.location_name as name
                                from locations lo
                                where
                                    lo.location_active = 1
                                order by name
                                ) as tmp) as locations";
          return DB::select($sql);
    }

    public function getParamslocations(){
        $sql = "SELECT
                    us.user_id,
                    COALESCE(
                        pe.person_firstname || ' '||
                        pe.person_secondname||' ' ||
                        pe.person_surname||' '||
                        pe.person_secondsurname,pe.person_firstname||' '||
                        pe.person_surname
                    ) as user_name
                FROM users us
                INNER JOIN people pe
                    ON pe.person_id = us.person_id";
          return DB::select($sql);
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
