<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ImageGroupModel extends Model{

    protected $table              = "images_group";
    protected $modifier           = "imagegroup_modified_by";
    protected $modifiedAt         = "imagegroup_modified_at";
    protected $identificator      = "imagegroup_id";
    protected $creator            = "imagegroup_created_by";
    protected $active             = "imagegroup_active";
    // protected $delete             = "imagegroup_delete"; no posee


    public function get($user, $id=null){
        if($id == null){
            $sql = "SELECT
                        ig.*,
                        (
                            select
                            json_agg(tmp.*)
                            from (
                                select 
                                    im.* 
                                from images im
                                where im.imagegroup_id = ig.imagegroup_id
                            ) tmp
                        ) as images,
                        COALESCE(pe.person_firstname || ' ' || pe.person_secondname || ' ' || pe.person_surname || ' ' || pe.person_secondsurname, pe.person_firstname || ' ' || pe.person_surname ) as created_by,
                        COALESCE(pem.person_firstname || ' ' || pem.person_secondname || ' ' || pem.person_surname || ' ' || pem.person_secondsurname, pem.person_firstname || ' ' || pem.person_surname ) as modified_by
                    FROM images_group ig
                    INNER JOIN users as us
                        ON us.user_id = ig.imagegroup_created_by
                    INNER JOIN people as pe
                        ON pe.person_id = us.person_id
                    LEFT JOIN users as usm
                        ON usm.user_id = ig.imagegroup_modified_by
                    LEFT JOIN people as pem
                        ON pem.person_id = usm.person_id";
            return DB::select($sql);
        } else {
           $sql = "SELECT
                        ig.*,
                        (
                            select
                            json_agg(tmp.*)
                            from (
                                select 
                                    im.* 
                                from images im
                                where im.imagegroup_id = ig.imagegroup_id
                            ) tmp
                        ) as images,
                        COALESCE(pe.person_firstname || ' ' || pe.person_secondname || ' ' || pe.person_surname || ' ' || pe.person_secondsurname, pe.person_firstname || ' ' || pe.person_surname ) as created_by,
                        COALESCE(pem.person_firstname || ' ' || pem.person_secondname || ' ' || pem.person_surname || ' ' || pem.person_secondsurname, pem.person_firstname || ' ' || pem.person_surname ) as modified_by
                    FROM images_group ig
                    INNER JOIN users as us
                        ON us.user_id = ig.imagegroup_created_by
                    INNER JOIN people as pe
                        ON pe.person_id = us.person_id
                    LEFT JOIN users as usm
                        ON usm.user_id = ig.imagegroup_modified_by
                    LEFT JOIN people as pem
                        ON pem.person_id = usm.person_id
                    WHERE ig.imagegroup_id = ?";
            return DB::select($sql, array($id));
        }
    }

    public function insertData($form, $user){
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

    public function updateStatus($status,$id,$user,$codigos = null){
        if($id == 'null'){
            $codigos = implode(',',$codigos);
            $sql ="UPDATE $this->table set
                $this->active = ?,
                $this->modifier = ?,
                $this->modifiedAt = now()
                where $this->identificator in ($codigos)";
            $result = DB::update($sql,array($status,$user));
        }else{
            $sql ="UPDATE $this->table set
                $this->active = ?,
                $this->modifier = ?,
                $this->modifiedAt = now()
                where $this->identificator = ?";
                $result = DB::update($sql,array($status,$user,$id));
        }
        return $result;
    }

    /*public function deleteById($id, $user){
        $sql = "UPDATE $this->table set $this->delete = 1, $this->modifier = $user, $this->modifiedAt= now() WHERE $this->identificator = ?";
        return DB::update($sql, array($id));
    }*/
    
}
