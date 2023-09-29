<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BrandsModel extends Model{

    protected $table         = "brands";
    protected $identificator = "brand_id";
    protected $modifier      = "brand_modified_by";
    protected $modifiedAt    = "brand_modified_at";
    protected $creator       = "brand_created_by";
    protected $active        = "brand_active";
    protected $delete        = "brand_delete";


    public function get($user, $id = null) {
        if ($id == null) {
            try{
                $sql = "SELECT
                            br.*,
                            br.brand_created_at AS created_at,
                            br.brand_modified_at AS modified_at,
                            co.company_name,
                            COALESCE(
                                pec.person_firstname || ' ' ||
                                pec.person_secondname || ' ' ||
                                pec.person_surname || ' ' ||
                                pec.person_secondsurname, pec.person_firstname || ' ' ||
                                pec.person_surname
                            ) as created_by,
                            COALESCE(
                                pem.person_firstname || ' ' ||
                                pem.person_secondname || ' ' ||
                                pem.person_surname || ' ' ||
                                pem.person_secondsurname, pem.person_firstname || ' ' ||
                                pem.person_surname
                            ) as modified_by
                        FROM brands br
                        INNER JOIN companies co
                            ON co.company_id = br.company_id
                        INNER JOIN users as us
                            ON us.user_id = br.brand_created_by
                        INNER JOIN people as pec
                            ON pec.person_id = us.person_id
                        LEFT JOIN users as usm
                            ON usm.user_id = br.brand_modified_by
                        LEFT JOIN people as pem
                            ON pem.person_id = usm.person_id
                        WHERE br.brand_delete = 0";
                    $result =  DB::select($sql);
            }catch(Exception $e){
                if(env("APP_ENV") == "local"){
                    print_r($e->getMessage());
                }
                return false;
            }
            
        }else {

            try{
                $sql = "SELECT
                            br.*,
                            br.brand_created_at AS created_at,
                            br.brand_modified_at AS modified_at,
                            co.company_name,
                            COALESCE(
                                pec.person_firstname || ' ' ||
                                pec.person_secondname || ' ' ||
                                pec.person_surname || ' ' ||
                                pec.person_secondsurname, pec.person_firstname || ' ' ||
                                pec.person_surname
                            ) as created_by,
                            COALESCE(
                                pem.person_firstname || ' ' ||
                                pem.person_secondname || ' ' ||
                                pem.person_surname || ' ' ||
                                pem.person_secondsurname, pem.person_firstname || ' ' ||
                                pem.person_surname
                            ) as modified_by
                        FROM brands br
                        INNER JOIN companies co
                            ON co.company_id = br.company_id
                        INNER JOIN users as us
                            ON us.user_id = br.brand_created_by
                        INNER JOIN people as pec
                            ON pec.person_id = us.person_id
                        LEFT JOIN users as usm
                            ON usm.user_id = br.brand_modified_by
                        LEFT JOIN people as pem
                            ON pem.person_id = usm.person_id
                        WHERE br.brand_delete = 0 AND br.brand_id = ?";
                $result = DB::select($sql, array($id));
            }catch(Exception $e){
                if(env("APP_ENV") == "local"){
                    print_r($e->getMessage());
                }
                return false;
            }
        }
        return $result;
    }

    public function insertData($form, $user){
        try {
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
        try {
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
            $sql .= $sqlSets . " where $this->identificator = ? AND $this->delete = 0";
    
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
                    $this->modifier = ?,
                    $this->modifiedAt = now()
                    where $this->identificator in ($codigos)";
                $result = DB::update($sql,array($status,$user));
            } else {
                $sql ="UPDATE $this->table set
                    $this->active = ?,
                    $this->modifier = ?,
                    $this->modifiedAt = now()
                    where $this->identificator = ?";
                    $result = DB::update($sql, array($status,$user,$id));
            }
            return $result;
        }catch(Exception $e){
			if(env("APP_ENV") == "local"){
				print_r($e->getMessage());
			}
			return false;
		}
    }


    public function deleteById($user, $id){
        try {
            $sql ="UPDATE $this->table set
                $this->delete = 1,
                $this->modifier = ?,
                $this->modifiedAt = now()
                where $this->identificator = ?";
        
            return DB::update($sql, array($user, $id));
        }catch(Exception $e){
            if(env("APP_ENV") == "local"){
                print_r($e->getMessage());
            }
            return false;
        }
    }

    public function getByFilters($user,$filters){
		try{
			$arrWhere = array();
			$where = " $this->delete = 0 ";
			foreach($filters as $field => $filter){
				$where .= " and $field = ?";
				$arrWhere[] = $filter;
			}
			$sql = "SELECT
						st.*,
						case
							when storage_type = 'LOCATION' then
								(select location_name from locations where location_id = st.location_id)
							else
								null
						end as location_name,
						case
							when storage_type = 'BARBER' then
								(   select
										coalesce(pe2.person_firstname||' '||pe2.person_surname, pe2.person_firstname)
									from users us2
									inner join people pe2
										on pe2.person_id = us2.person_id
									where
										st.user_id = us2.user_id
								)
							else
								null
						end as barber_name,
						coalesce(pe.person_firstname||' '||pe.person_surname, pe.person_firstname) as created_by,
						coalesce(pe1.person_firstname||' '||pe1.person_surname, pe1.person_firstname) as modified_by
					from
						storages st
					-- created_by
					inner join users us
						on st.storage_created_by = us.user_id
					inner join people pe
						on pe.person_id = us.person_id
					-- modified_by
					left join users us1
						on st.storage_modified_by = us1.user_id
					left join people pe1
						on pe1.person_id = us1.person_id
					where
						$where
					";
			$result = DB::select($sql,$arrWhere);
			return $result;
		}catch(Exception $e){
			if(env("APP_ENV") == "local"){
				print_r($e->getMessage());
			}
			return false;
		}
	}
}
