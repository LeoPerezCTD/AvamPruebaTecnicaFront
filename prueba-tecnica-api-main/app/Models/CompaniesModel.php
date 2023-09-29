<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CompaniesModel extends Model{
    protected $table              = "companies";
    protected $modifier           = "company_modified_by";
    protected $modifiedAt         = "company_modified_at";
    protected $identificator      = "company_id";
    protected $creator            = "company_created_by";
    protected $active             = "company_active";
    protected $delete             = "company_delete";

    public function get($user, $id= null){
        if ($id==null) {
            try {
                $sql = "SELECT
                            com.*
                        FROM companies com
                        WHERE com.company_delete = 0";
                return DB::select($sql);
            }catch(Exception $e){
                if(env("APP_ENV") == "local"){
                    print_r($e->getMessage());
                }
                return false;
            }
        } else {
            try {
                $sql = "SELECT
                            com.*,
                            com.company_created_at AS created_at,
                            com.company_modified_at AS modified_at,
                            COALESCE(
                                pe.person_firstname || ' ' ||
                                pe.person_secondname || ' ' ||
                                pe.person_surname || ' ' ||
                                pe.person_secondsurname, pe.person_firstname || ' ' ||
                                pe.person_surname
                            ) AS created_by,
                            COALESCE(
                                pem.person_firstname || ' ' ||
                                pem.person_secondname || ' ' ||
                                pem.person_surname || ' ' ||
                                pem.person_secondsurname, pem.person_firstname || ' ' ||
                                pem.person_surname
                            ) AS modified_by
                        FROM companies com
                        INNER JOIN users as us
                            ON us.user_id = com.company_created_by
                        INNER JOIN people as pe
                            ON pe.person_id = us.person_id
                        LEFT JOIN users as usm
                            ON usm.user_id = com.company_modified_by
                        LEFT JOIN people as pem
                            ON pem.person_id = usm.person_id
                        WHERE com.company_id = ? and company_delete = 0";
    
                return DB::select($sql, array($id));
            }catch(Exception $e){
                if(env("APP_ENV") == "local"){
                    print_r($e->getMessage());
                }
                return false;
            }
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
            $sql = "INSERT INTO $this->table ($sqlInsert) values($sqlBind) returning *";
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
        }catch(Exception $e){
            if(env("APP_ENV") == "local"){
                print_r($e->getMessage());
            }
            return false;
        }
    }
  
    public function updateStatus($status, $id, $user, $codigos = null){
        try {
            if ($id == 'null'){
                $codigos = implode(',',$codigos);
                $sql ="UPDATE $this->table set
                    $this->active = ?,
                    $this->modified_by = ?,
                    $this->modified_at = now()
                    where $this->identificator in ($codigos)";
                $result = DB::update($sql, array($status, $user));
            } else {
                $sql ="UPDATE $this->table set
                    $this->active = ?,
                    $this->modified_by = ?,
                    $this->modified_at = now()
                    where $this->identificator = ?";
                    $result = DB::update($sql, array($status, $user, $id));
            }
            return $result;
        }catch(Exception $e){
			if(env("APP_ENV") == "local"){
				print_r($e->getMessage());
			}
			return false;
		}
        
    }
        

    public function getParamsUpdate(){
        try {
            $sql = "SELECT
                    (select
                    coalesce(json_agg(tmp.*),'[]')
                    from(
                        SELECT
                            setting_short_name as name,
                            setting_value as code
                        FROM settings
                        WHERE setting_type = 'TYPE_DNI'
                        ORDER BY setting_order ASC
                    ) as tmp) as company_identification_type;
                ";
            $result = DB::select($sql);
            return $result;
        }catch(Exception $e){
			if(env("APP_ENV") == "local"){
				print_r($e->getMessage());
			}
			return false;
		}
        
    }

    public function deleteById($id, $user){
        try {
            $sql ="UPDATE $this->table set
                $this->delete = 1,
                $this->modified_by = ?,
                $this->modified_at = now()
                where $this->identificator = ?";
        
            return DB::update($sql, array($user, $id));
        }catch(Exception $e){
			if(env("APP_ENV") == "local"){
				print_r($e->getMessage());
			}
			return false;
		}
        
    }

}
