<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
// use Exception;

class AppModel extends Model{
    // use HasFactory;

    public function checkPermission($user,$endpoint){
        $sql = "SELECT 
                    pm.profilemenu_permissions permissions,
                    m.menu_name menu,
                    m.menu_action endpoint
                from 
                    users_profiles up
                    inner join profiles p
                        on p.profile_id = up.profile_id
                        and p.profile_active = 1
                        and up.user_id = ?
                    inner join profiles_menu pm
                        on pm.profile_id = up.profile_id
                        and pm.profilemenu_active = 1
                    inner join menus m
                        on m.menu_id = pm.menu_id
                        and (m.menu_parent is not null OR upper(m.menu_type) = 'ONLY_PERMISSIONS')
                        and m.menu_action = ?";
        return DB::select($sql,array($user,$endpoint));
    }

    function getLocation($user){
        $sql = "SELECT
                    location_id
                from
                    users_locations
                where 
                    user_id = ?
                    and userlocation_active = 1";
        return DB::select($sql,[$user]);
    }

    function getLocations($user){
        $sql = "SELECT -- superuser
                    lo.location_id,
                    lo.location_name
                from locations lo
                where 1 = (
                        select 
                            up.profile_id 
                        from users us
                        inner join users_profiles up
                            on up.user_id = us.user_id
                            and us.user_id = ?
                    )
                union
                -- Brava
                SELECT
                    lo.location_id,
                    lo.location_name
                from locations lo
                where 2 = (
                        select 
                            up.profile_id 
                        from users us
                        inner join users_profiles up
                            on up.user_id = us.user_id
                            and us.user_id = ?
                    )
                union
                -- company
                SELECT
                    lo.location_id,
                    lo.location_name
                from companies co
                inner join users_companies uc
                    on uc.company_id = co.company_id
                    and uc.user_id = ?
                left join locations lo
                    on lo.company_id = co.company_id
                where 3 = (
                        select 
                            up.profile_id 
                        from users us
                        inner join users_profiles up
                            on up.user_id = us.user_id
                            and us.user_id = ?
                    )
                union
                -- manager
                SELECT
                    lo.location_id,
                    lo.location_name
                from locations lo
                inner join users_locations ul
                    on ul.location_id = lo.location_id
                    and ul.user_id = ?
                where 4 = (
                        select 
                            up.profile_id 
                        from users us
                        inner join users_profiles up
                            on up.user_id = us.user_id
                            and us.user_id = ?
                    )
                union
                -- employee
                SELECT
                    lo.location_id,
                    lo.location_name
                from locations lo
                inner join users_locations ul
                    on ul.location_id = lo.location_id
                    and ul.user_id = ?
                where 5 = (
                        select 
                            up.profile_id 
                        from users us
                        inner join users_profiles up
                            on up.user_id = us.user_id
                            and us.user_id = ?
                    )";
        return DB::select($sql,[$user,$user,$user,$user,$user,$user,$user,$user]);
    }

    function getCompany($user){
        $sql = "SELECT -- superuser
                    co.company_id,
                    co.company_name
                from companies co
                where 1 = (
                    select 
                        up.profile_id 
                    from users us
                    inner join users_profiles up
                        on up.user_id = us.user_id
                        and us.user_id = ?
                )
                union
                -- Brava
                SELECT
                    co.company_id,
                    co.company_name
                from companies co
                where 2 = (
                    select 
                        up.profile_id 
                    from users us
                    inner join users_profiles up
                        on up.user_id = us.user_id
                        and us.user_id = ?
                )
                union
                -- company
                SELECT
                    co.company_id,
                    co.company_name
                from companies co
                inner join users_companies uc
                    on uc.company_id = co.company_id
                    and uc.user_id = ?
                where 3 = (
                    select 
                        up.profile_id 
                    from users us
                    inner join users_profiles up
                        on up.user_id = us.user_id
                        and us.user_id = ?
                )
                union
                -- manager
                SELECT
                    co.company_id,
                    co.company_name
                from companies co
                inner join locations lo
                    on lo.company_id = co.company_id
                inner join users_locations ul
                    on ul.location_id = lo.location_id
                    and ul.user_id = ?
                where 4 = (
                    select 
                        up.profile_id 
                    from users us
                    inner join users_profiles up
                        on up.user_id = us.user_id
                        and us.user_id = ?
                )
                union
                -- employee
                SELECT
                    co.company_id,
                    co.company_name
                from companies co
                inner join locations lo
                    on lo.company_id = co.company_id
                inner join users_locations ul
                    on ul.location_id = lo.location_id
                    and ul.user_id = ?
                where 5 = (
                    select 
                        up.profile_id 
                    from users us
                    inner join users_profiles up
                        on up.user_id = us.user_id
                        and us.user_id = ?
                )
        ";
        return DB::select($sql,[$user,$user,$user,$user,$user,$user,$user,$user]);
    }

    function getCompanyByLocation($locationId){
        $sql = "SELECT
                    co.company_id
                from
                    companies co
                inner join locations lo
                    on lo.company_id = co.company_id
                where 
                    lo.location_id = ?
                    and location_active = 1";
        return DB::select($sql,[$locationId]);
    }

    function getProfile($user){
        $sql = "SELECT
                    pr.profile_name
                FROM
                    users_profiles up
                inner join profiles pr
                    on pr.profile_id = up.profile_id
                    and up.userprofile_active = 1
                where up.user_id = ?";
        return DB::select($sql,array($user));
    }

    function getProfileId($user){
        $sql = "SELECT
                    pr.profile_id
                FROM
                    users_profiles up
                inner join profiles pr
                    on pr.profile_id = up.profile_id
                    and up.userprofile_active = 1
                where up.user_id = ?";
        return DB::select($sql,array($user));
    }


    /* public function getUser($user){
        $sql = "SELECT 
                    pe.*
                from 
                    usuarios us
                    inner join personas pe
                    on pe.persona_id = us.persona_id
                where usuario_id = ?";
        return DB::select($sql,array($user));
    } */

    // paises, ciudades, estados
    public function getCountries(){
        $sql = "SELECT 
                (select 
                    coalesce(json_agg(row_to_json(tmp.*)),'[]')
                from(
                    select 
                        pa.pais_codigo as codigo,
                        pa.pais_nombre as nombre
                    from 
                        paises pa
                    where pa.pais_estado = 1
                ) as tmp) as countries";
        return DB::select($sql);
    }
    public function getStates($country){
        $sql = "SELECT 
                    (select 
                        coalesce(json_agg(row_to_json(tmp.*)),'[]')
                    from(
                        select 
                            es.estado_codigo as codigo,
                            es.estado_nombre as nombre
                        from estados es
                        where es.pais_codigo = ?
                            and es.estado_estado = 1
                        order by es.estado_nombre
                    ) as tmp) as states
                ";
        return DB::select($sql,array($country));
    }
    public function getCities($country,$state){
        $sql = "SELECT 
                    (select 
                        coalesce(json_agg(row_to_json(tmp.*)),'[]')
                    from(
                        SELECT 
                            ci.ciudad_codigo as codigo,
                            ci.ciudad_nombre as nombre
                        from ciudades ci
                        where ci.pais_codigo = ? 
                            and ci.estado_codigo = ?
                            and ci.ciudad_estado = 1
                        order by ci.ciudad_nombre
                    ) as tmp) as cities";
        return DB::select($sql,array($country,$state));
    }
    public function getCityById($id){
        $sql = "SELECT ciudad_nombre from ciudades where ciudad_codigo = ?";
        return DB::select($sql,array($id));
    }

    public function getEstate($country){
        try{
			$sql = "SELECT
                        es.state_code as code,
                        es.state_name as name
                    from states es
                    where es.country_code = ?
                        and es.state_active = 1
                    order by es.state_name";
            $result = DB::select($sql, array($country));
            return $result;
		}catch(Exception $e){
			if(env("APP_ENV") == "local"){
				print_r($e->getMessage());
			}
			return false;
		}
        
    }

    public function getCity($country, $state){
        try{
            $sql = "SELECT
                        ci.city_code as code,
                        ci.city_name as name
                    from cities ci
                    where ci.country_code = ?
                        and ci.state_code = ?
                        and ci.city_active = 1
                    order by ci.city_name";
            $result = DB::select($sql, array($country, $state));
            return $result;
		}catch(Exception $e){
			if(env("APP_ENV") == "local"){
				print_r($e->getMessage());
			}
			return false;
		}
       
    }

    public function getCompanyName($id){
        $sql = "SELECT
                    company_name
                FROM
                    companies
                WHERE company_id = ?";
        $result = DB::select($sql, array($id));
        return $result;
    }
}