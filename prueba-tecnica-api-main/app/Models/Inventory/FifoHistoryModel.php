<?php

namespace App\Models\Inventory;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FifoHistoryModel extends Model{

    protected $table         = "fifo_history";
    protected $identificator = "fifohistory_id";
    protected $modifier      = "fifohistory_modified_by";
    protected $modifiedAt    = "fifohistory_modified_at";
    protected $creator       = "fifohistory_created_by";
    // protected $active        = "fifohistory_active";
    // protected $delete        = "fifohistory_delete";


    public function get($user, $id = null) {
        if ($id == null) {
            try{
                $sql = "SELECT 
                            fh.*,
                            coalesce(
								pec.person_firstname ||' '|| pec.person_secondname ||' '|| pec.person_surname,
								pec.person_firstname ||' '|| pec.person_surname
							) AS created_by,
							coalesce(
								pem.person_firstname ||' '|| pem.person_secondname ||' '|| pem.person_surname,
								pem.person_firstname ||' '|| pem.person_surname
							) AS modified_by,
                            fh.fifohistory_created_at AS created_at,
                            fh.fifohistory_modified_at AS modified_at
                        from fifo_history fh
                        -- created_by
						inner join users us
							on fh.fifohistory_created_by = us.user_id
							-- and us.user_delete = 0
						inner join people pec
							on pec.person_id = us.person_id
							-- and pe.person_delete = 0
						-- modified_by
						left join users us1
							on fh.fifohistory_modified_by = us1.user_id
							-- and us1.user_delete = 0
						left join people pem
							on pem.person_id = us1.person_id
							-- and pe1.person_delete = 0
                        ";
                $result = DB::select($sql);
            }catch(Exception $e){
                if(env("APP_ENV") == "local"){
                    print_r($e->getMessage());
                }
                return false;
            }
        }else{
            try{
                $sql = "SELECT 
                            fh.*,
                            coalesce(
								pec.person_firstname ||' '|| pec.person_secondname ||' '|| pec.person_surname,
								pec.person_firstname ||' '|| pec.person_surname
							) AS created_by,
							coalesce(
								pem.person_firstname ||' '|| pem.person_secondname ||' '|| pem.person_surname,
								pem.person_firstname ||' '|| pem.person_surname
							) AS modified_by,
                            fh.fifohistory_created_at AS created_at,
                            fh.fifohistory_modified_at AS modified_at
                        from fifo_history fh
                        -- created_by
						inner join users us
							on fh.fifohistory_created_by = us.user_id
							-- and us.user_delete = 0
						inner join people pec
							on pec.person_id = us.person_id
							-- and pe.person_delete = 0
						-- modified_by
						left join users us1
							on fh.fifohistory_modified_by = us1.user_id
							-- and us1.user_delete = 0
						left join people pem
							on pem.person_id = us1.person_id
							-- and pe1.person_delete = 0
                        where fifohistory_id = ?";
                $result = DB::select($sql,[$id]);
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
