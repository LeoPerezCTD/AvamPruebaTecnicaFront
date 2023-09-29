<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PictureModel extends Model{

    protected $table              = "pictures";
    protected $modifier           = "picture_modified_by";
    protected $modifiedAt         = "picture_modified_at";
    protected $identificator      = "picture_id";
    protected $creator            = "picture_created_by";
    protected $active             = "picture_active";


    public function get($user, $id=null){
        if($id == null){
            $sql = "SELECT
                        bl.*,
                        co.company_name,
                        COALESCE(pe.person_firstname || ' ' || pe.person_secondname || ' ' || pe.person_surname || ' ' || pe.person_secondsurname, pe.person_firstname || ' ' || pe.person_surname ) as created_by,
                        COALESCE(pem.person_firstname || ' ' || pem.person_secondname || ' ' || pem.person_surname || ' ' || pem.person_secondsurname, pem.person_firstname || ' ' || pem.person_surname ) as modified_by
                    FROM barber_level bl
                    INNER JOIN companies co
                        ON bl.company_id = co.company_id
                    INNER JOIN users as us
                        ON us.user_id = bl.barberlevel_created_by
                    INNER JOIN people as pe
                        ON pe.person_id = us.person_id
                    LEFT JOIN users as usm
                        ON usm.user_id = bl.barberlevel_modified_by
                    LEFT JOIN people as pem
                        ON pem.person_id = usm.person_id
                    WHERE
                        bl.barberlevel_delete = 0";
            return DB::select($sql);
        } else {
           $sql = "SELECT
                        bl.*,
                        co.company_name,
                        COALESCE(pe.person_firstname || ' ' || pe.person_secondname || ' ' || pe.person_surname || ' ' || pe.person_secondsurname, pe.person_firstname || ' ' || pe.person_surname ) as created_by,
                        COALESCE(pem.person_firstname || ' ' || pem.person_secondname || ' ' || pem.person_surname || ' ' || pem.person_secondsurname, pem.person_firstname || ' ' || pem.person_surname ) as modified_by
                    FROM barber_level bl
                    INNER JOIN companies co
                        ON bl.company_id = co.company_id
                    INNER JOIN users as us
                        ON us.user_id = bl.barberlevel_created_by
                    INNER JOIN people as pe
                        ON pe.person_id = us.person_id
                    LEFT JOIN users as usm
                        ON usm.user_id = bl.barberlevel_modified_by
                    LEFT JOIN people as pem
                        ON pem.person_id = usm.person_id
                    WHERE
                        bl.barberlevel_delete = 0
                    AND
                        bl.barberlevel_id = ?";
            return DB::select($sql, array($id));

        }
    }

    public function insertData($form, $user){
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
        $sql = "INSERT INTO $this->table ($sqlInsert) values($sqlBind) returning *";
        return DB::select($sql, $sqlValues);
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
   

}
