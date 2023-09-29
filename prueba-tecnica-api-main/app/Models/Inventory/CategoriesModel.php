<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CategoriesModel extends Model{

    protected $table              = "categories";
    protected $identificator      = "category_id";
    protected $creator            = "category_created_by";
    protected $modifier           = "category_modified_by";
    protected $modifiedAt         = "category_modified_at";
    protected $active             = "category_active";
    protected $delete             = "category_delete";


    public function get($user, $id=null){
        if($id == null){
            $sql = "SELECT
                    ca.*,
                    COALESCE((SELECT json_agg(tmp.*)
                            FROM (
                                SELECT -1 AS subcategory_id,
                                        'All' AS subcategory_name,
                                        'default_img.jpg' AS subcategory_image
                                UNION ALL
                                SELECT
                                        sub.subcategory_id,
                                        sub.subcategory_name,
                                        sub.subcategory_image
                                FROM subcategories sub
                                where sub.category_id = ca.category_id
                                
                            ) tmp)
                            ,'[]') as subcategories
                    FROM 
                        categories ca
                    WHERE
                        ca.category_delete = 0";
            return DB::select($sql);
        }else{
            $sql = "SELECT 
                        ca.* ,
                        COALESCE(pe.person_firstname || ' ' || pe.person_secondname || ' ' || pe.person_surname || ' ' || pe.person_secondsurname, pe.person_firstname || ' ' || pe.person_surname ) as created_by,
                        COALESCE(pem.person_firstname || ' ' || pem.person_secondname || ' ' || pem.person_surname || ' ' || pem.person_secondsurname, pem.person_firstname || ' ' || pem.person_surname ) as modified_by
                    FROM 
                        categories ca
                    INNER JOIN users as us
                        ON us.user_id = ca.category_created_by
                    INNER JOIN people as pe 
                        ON pe.person_id = us.person_id
                    LEFT JOIN users as usm
                        ON usm.user_id = ca.category_modified_by
                    LEFT JOIN people as pem 
                        ON pem.person_id = usm.person_id
                    WHERE 
                        category_id=? 
                    AND 
                        category_delete = 0";
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
        // print_r($sqlValues);
        return DB::select($sql, $sqlValues);
    }

    public function updateData($form, $id, $user){
        // usuario actualizacion
        $form->{$this->modifier} = $user;
        // fecha actualizacion
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

        // id actualizacion
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

    public function deleteById($id, $user){
        $sql = "UPDATE $this->table set $this->delete = 1, $this->modifier = $user, $this->modifiedAt= now() WHERE $this->identificator = ?";
        return DB::update($sql, array($id));
    }

    public function getSubcategories($subcategory){
        $sql = "SELECT ca.category_id, sub.*
        FROM categories as ca
        INNER JOIN subcategories as sub ON ca.category_id = sub.category_id
        WHERE ca.category_active=1 AND sub.subcategory_active=1 AND ca.category_id= ? ";

        return DB::select($sql, [$subcategory]);
    }
}
