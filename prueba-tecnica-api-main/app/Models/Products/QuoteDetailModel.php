<?php

namespace App\Models\Products;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Exception;

class QuoteDetailModel extends Model{
    protected $table           = "quote_detail";
    protected $identificator   = "quotedetail_id";
    protected $creator         = "quotedetail_created_by";
    protected $modifier        = "quotedetail_modified_by";
    protected $modifiedAt      = "quotedetail_modified_at";
    protected $active          = "quotedetail_active";
    protected $delete          = "quotedetail_delete";


    public function get($user, $id = null){
        if ($id == null){
			try{
				$sql = "SELECT
                            qu.*,
                            coalesce(pe.person_firstname||' '||pe.person_surname, pe.person_firstname) as created_by,
                            coalesce(pe1.person_firstname||' '||pe1.person_surname, pe1.person_firstname) as modified_by
                        from quote_detail qu
                        -- created_by
                        inner join users us
                            on qu.quote_created_by = us.user_id
                            and us.user_delete = 0
                        inner join people pe
                            on pe.person_id = us.person_id
                            and pe.person_delete = 0
                        -- modified_by
                        left join users us1
                            on qu.quote_modified_by = us1.user_id
                            and us1.user_delete = 0
                        left join people pe1
                            on pe1.person_id = us1.person_id
                            and pe1.person_delete = 0
						";
				$result = DB::select($sql);
			}catch(Exception $e){
				if(env("APP_ENV") == "local"){
					print_r($e->getMessage());
				}
				return false;
			}
        } else {
			try{
				$sql = "SELECT
							qu.*,
							coalesce(pe.person_firstname||' '||pe.person_surname, pe.person_firstname) as created_by,
							coalesce(pe1.person_firstname||' '||pe1.person_surname, pe1.person_firstname) as modified_by
						from quote_detail qu
						-- created_by
						inner join users us
							on qu.quote_created_by = us.user_id
							and us.user_delete = 0
						inner join people pe
							on pe.person_id = us.person_id
							and pe.person_delete = 0
						-- modified_by
						left join users us1
							on qu.quote_modified_by = us1.user_id
							and us1.user_delete = 0
						left join people pe1
							on pe1.person_id = us1.person_id
							and pe1.person_delete = 0
                        where qu.quote_id = ?
						";
				$result = DB::select($sql,array($id));
			}catch(Exception $e){
				if(env("APP_ENV") == "local"){
					print_r($e->getMessage());
				}
				return false;
			}
        }
        return $result;
    }

	public function getByFilters($user,$filters){
		try{
			$arrWhere = array();
			$where = " 1 = 1 ";
			foreach($filters as $field => $filter){
				$where .= " and $field = ?";
				$arrWhere[] = $filter;
			}
			$sql = "SELECT
						qu.*,
						coalesce(pe.person_firstname||' '||pe.person_surname, pe.person_firstname) as created_by,
						coalesce(pe1.person_firstname||' '||pe1.person_surname, pe1.person_firstname) as modified_by
					from quotes qu
					-- created_by
					inner join users us
						on qu.quote_created_by = us.user_id
						and us.user_delete = 0
					inner join people pe
						on pe.person_id = us.person_id
						and pe.person_delete = 0
					-- modified_by
					left join users us1
						on qu.quote_modified_by = us1.user_id
						and us1.user_delete = 0
					left join people pe1
						on pe1.person_id = us1.person_id
						and pe1.person_delete = 0
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
		try{
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
					$result = DB::update($sql,array($status,$user,$id));
			}
			return $result;
		}catch(Exception $e){
			if(env("APP_ENV") == "local"){
				print_r($e->getMessage());
			}
			return false;
		}
        
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
            $sql = "INSERT INTO $this->table ($sqlInsert) values($sqlBind) returning *";
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

	public function deleteById($id, $user){
		try{
			$sql = "UPDATE $this->table set $this->delete = 1, $this->modifier = $user, $this->modifiedAt= now() WHERE $this->identificator = ?";
        	return DB::update($sql, array($id));
		}catch(Exception $e){
			if(env("APP_ENV") == "local"){
				print_r($e->getMessage());
			}
			return false;
		}
    }

    public function getParamsUpdate($user,$companyId){
		try{
			$sql="SELECT
					(select
						coalesce(json_agg(tmp.*),'[]')
						from(
							select
								co.country_code as code,
								co.country_name as name
							from
								countries co
							where
								co.country_active = 1
							order by co.country_name
						) as tmp) as countries,
					(SELECT
						COALESCE (json_agg(tmp.*),'[]')::text
					FROM(
						SELECT
							setting_name as name,
							setting_value as code
						FROM settings
						WHERE setting_type = 'STORAGE_TYPE'
						ORDER BY setting_order ASC
					) as tmp) as storage_type,
					(SELECT
						COALESCE (json_agg(tmp.*),'[]')::text
					from (
							select
								lo.location_id as code,
								lo.location_name as name
							from
								locations lo
							inner join companies co
								on co.company_id = lo.company_id
								and co.company_active = 1
							where
								lo.location_active = 1
								and lo.location_delete = 0
								and co.company_id = ?
							order by lo.location_name asc
						) as tmp
					) as locations,
					(
						SELECT
							COALESCE (json_agg(tmp.*),'[]')::text
						from (
							SELECT
								us.user_id as code,
								coalesce(
									pe.person_firstname ||' '|| pe.person_secondname ||' '|| pe.person_surname,
									pe.person_firstname ||' '|| pe.person_surname
								) as name
							from
								users_barbers ub
							inner join users us
								on us.user_id = ub.user_id
								and ub.userbarber_active = 1
								and ub.userbarber_delete = 0
							inner join people pe
								on us.person_id = pe.person_id
							where us.user_active = 1
								and us.user_delete = 0
							order by name asc
						) as tmp
					) as barbers
			";
			$result = DB::select($sql,[$companyId,]);
			return $result;
		}catch(Exception $e){
			if(env("APP_ENV") == "local"){
				print_r($e->getMessage());
			}
			return false;
		}


    }
}