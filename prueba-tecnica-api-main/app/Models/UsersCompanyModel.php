<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UsersCompanyModel extends Model{
    protected $table          = "users_companies";
    protected $identificator  = "usercompany_id";
    protected $creator        = "usercompany_created_by";
    protected $modifier       = "usercompany_modified_by";
    protected $modifiedAt     = "usercompany_modified_at";
    protected $active         = "usercompany_active";
    protected $delete         = "usercompany_delete";

    public function get($user, $id=null){
        if ($id == null) {
            try{
                $sql = "SELECT
                            uc.*,
                            uc.usercompany_created_at AS created_at,
                            uc.usercompany_modified_at AS modified_at,
                            co.company_name,
                            usc.user_username,
                            peco.person_phone,
                            peco.person_email,
                            peco.person_birthdate,
                            peco.person_firstname,
                            peco.person_secondname,
                            peco.person_surname,
                            peco.person_secondsurname,
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
                        FROM users_companies uc
                            INNER JOIN companies co
                                ON co.company_id = uc.company_id
                            INNER JOIN users as usc
                                ON usc.user_id = uc.user_id
                            INNER JOIN people as peco
                                ON peco.person_id = usc.person_id
                            INNER JOIN users as us
                                ON us.user_id = uc.usercompany_created_by
                            INNER JOIN people as pec
                                ON pec.person_id = us.person_id
                            LEFT JOIN users as usm
                                ON usm.user_id = uc.usercompany_modified_by
                            LEFT JOIN people as pem
                                ON pem.person_id = usm.person_id
                        WHERE uc.usercompany_delete = 0";
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
                            uc.*,
                            uc.usercompany_created_at AS created_at,
                            uc.usercompany_modified_at AS modified_at,
                            co.company_name,
                            usc.user_username,
                            peco.person_phone,
                            peco.person_email,
                            peco.person_birthdate,
                            peco.person_firstname,
                            peco.person_secondname,
                            peco.person_surname,
                            peco.person_secondsurname,
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
                        FROM users_companies uc
                            INNER JOIN companies co
                                ON co.company_id = uc.company_id
                            INNER JOIN users as usc
                                ON usc.user_id = uc.user_id
                            INNER JOIN people as peco
                                ON peco.person_id = usc.person_id
                            INNER JOIN users as us
                                ON us.user_id = uc.usercompany_created_by
                            INNER JOIN people as pec
                                ON pec.person_id = us.person_id
                            LEFT JOIN users as usm
                                ON usm.user_id = uc.usercompany_modified_by
                            LEFT JOIN people as pem
                                ON pem.person_id = usm.person_id
                        WHERE uc.usercompany_delete = 0 AND uc.usercompany_id = ?";
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
        } catch (Exception $e) {
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
            $sql .= $sqlSets . " where $this->identificator = ?";
            $sqlValues[] = $id;
            return DB::update($sql, $sqlValues);
        } catch (Exception $e) {
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
                $sql = "UPDATE
                            $this->table
                        SET
                            $this->active = ?,
                            $this->modifier = ?,
                            $this->modifiedAt = now()
                        WHERE $this->identificator in ($codigos)";
                $result = DB::update($sql,array($status,$user));
            } else {
                $sql = "UPDATE
                            $this->table
                        SET
                            $this->active = ?,
                            $this->modifier = ?,
                            $this->modifiedAt = now()
                        WHERE $this->identificator = ?";
                $result = DB::update($sql, array($status,$user,$id));
            }
            return $result;
        } catch (Exception $e) {
            if(env("APP_ENV") == "local"){
                print_r($e->getMessage());
            }
            return false;
        }
        
    }

    public function deleteById($user, $id){
        try {
            $sql = "UPDATE
                        $this->table
                    SET
                        $this->delete = 1,
                        $this->modifier = ?,
                        $this->modifiedAt = now()
                    WHERE $this->identificator = ?";
    
            return DB::update($sql, array($user, $id));
        } catch (Exception $e) {
            if(env("APP_ENV") == "local"){
                print_r($e->getMessage());
            }
            return false;
        }
    }

    public function getParamsUpdate(){
        $sql = "SELECT
                    (
                        SELECT
                            COALESCE (json_agg (tmp.*), '[]')
                        FROM(
                                SELECT
                                    us.user_id as code,
                                    COALESCE(
                                        us.user_username || ' - ' ||
                                        pe.person_firstname || ' ' ||
                                        pe.person_secondname|| ' ' ||
                                        pe.person_surname || ' ' ||
                                        pe.person_secondsurname, us.user_username || ' - ' ||
                                        pe.person_firstname || ' ' ||
                                        pe.person_surname
                                    ) as name
                                FROM users us
                                INNER JOIN people pe
                                    ON pe.person_id = us.person_id
                                ORDER BY us.user_username ASC
                            ) as  tmp
                        ) as users,
                    (
                        SELECT
                            COALESCE (json_agg (tmp.*), '[]')
                            FROM (
                                    SELECT
                                        co.company_id as code,
                                        co.company_name as name
                                        FROM companies co
                                        ORDER BY co.company_name ASC
                                ) as  tmp
                    ) as companies
        ";
        return DB::select($sql);
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