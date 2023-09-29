<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\PeopleModel;
use App\Models\UsersProfilesModel;
use App\Models\UsersBarberModel;
use App\Models\UsersCompanyModel;
use App\Models\UsersLocationsModel;
use Exception;

class UsersModel extends Model{
    public $table         = "users";
    public $identificator = "user_id";
    public $creator       = "user_created_by";
    public $modifier      = "user_modified_by";
    public $modifiedAt    = "user_modified_at";
    public $active        = "user_active";
    public $delete        = "user_delete";

    public $modelPeople;
    public $modelUsersProfiles;
    public $modelUsersBarbers;
    public $modelUsersLocation;
    public $modelUsersCompany;

    public function __construct(){
        $this->modelPeople        = new PeopleModel();
        $this->modelUsersProfiles = new UsersProfilesModel();
        $this->modelUsersLocation = new UsersLocationsModel();
        $this->modelUsersCompany  = new UsersCompanyModel();
        $this->modelUsersBarbers  = new UsersBarberModel();
    }


    public function get($user, $id = null){
        if($id == null){
            $sql = "SELECT
                        us.*,
                        us.user_created_at AS created_at,
                        us.user_modified_at AS modified_at,
                        COALESCE(
                            pec.person_firstname || ' '||
                            pec.person_secondname||' ' ||
                            pec.person_surname||' '||
                            pec.person_secondsurname,pec.person_firstname||' '||
                            pec.person_surname
                        ) AS people_name,
                        COALESCE(
                            pec.person_firstname || ' '||
                            pec.person_secondname||' ' ||
                            pec.person_surname||' '||
                            pec.person_secondsurname,pec.person_firstname||' '||
                            pec.person_surname
                        ) AS created_by,
                        COALESCE(
                            pem.person_firstname || ' '||
                            pem.person_secondname||' ' ||
                            pem.person_surname||' '||
                            pem.person_secondsurname,pem.person_firstname||' '||
                            pem.person_surname
                        )  AS modified_by,
                        co.company_name,
                        pe.person_firstname,
                        pe.person_secondname,
                        pe.person_surname,
                        pe.person_secondsurname,
                        pe.person_email,
                        pe.person_phone,
                        pe.person_birthdate,
                        pe.person_active,
                        pro.profile_id,
                        pro.profile_name,
                        uco.usercompany_id,
                        ub.userbarber_id,
                        ul.userlocation_id
                    FROM users us
                    INNER JOIN people pe
                        ON pe.person_id = us.person_id
                        AND pe.person_active = 1
                    LEFT JOIN companies co
                        ON co.company_id = us.company_id
                        AND co.company_active = 1
                    LEFT JOIN users_companies uco
					    ON uco.user_id = us.user_id
                    LEFT JOIN users_barbers ub
                        ON ub.user_id = us.user_id
                    LEFT JOIN users_locations ul
                        ON ul.user_id = us.user_id
                    INNER JOIN users_profiles up
                        ON up.user_id = us.user_id
                    INNER JOIN profiles pro
                        ON pro.profile_id = up.profile_id
                    LEFT JOIN users usc
                        ON usc.user_id = us.user_created_by
                    LEFT JOIN people pec
                        ON pec.person_id = usc.person_id
                    LEFT JOIN users usm
                        ON usm.user_id = us.user_modified_by
                    LEFT JOIN people pem
                        ON pem.person_id = usm.person_id
                    WHERE us.user_delete = 0";
            $result = DB::select($sql);
        }else {
            $sql = "SELECT
                        us.*,
                        us.user_created_at AS created_at,
                        us.user_modified_at AS modified_at,
                        COALESCE(
                            pec.person_firstname || ' '||
                            pec.person_secondname||' ' ||
                            pec.person_surname||' '||
                            pec.person_secondsurname,pec.person_firstname||' '||
                            pec.person_surname
                        ) AS people_name,
                        COALESCE(
                            pec.person_firstname || ' '||
                            pec.person_secondname||' ' ||
                            pec.person_surname||' '||
                            pec.person_secondsurname,pec.person_firstname||' '||
                            pec.person_surname
                        ) AS created_by,
                        COALESCE(
                            pem.person_firstname || ' '||
                            pem.person_secondname||' ' ||
                            pem.person_surname||' '||
                            pem.person_secondsurname,pem.person_firstname||' '||
                            pem.person_surname
                        )  AS modified_by,
                        co.company_name,
                        pe.person_firstname,
                        pe.person_secondname,
                        pe.person_surname,
                        pe.person_secondsurname,
                        pe.person_email,
                        pe.person_phone,
                        pe.person_birthdate,
                        pe.person_active,
                        pro.profile_id,
                        pro.profile_name,
                        uco.usercompany_id,
                        ub.userbarber_id,
                        ul.userlocation_id
                    FROM users us
                    INNER JOIN people pe
                        ON pe.person_id = us.person_id
                        AND pe.person_active = 1
                    LEFT JOIN companies co
                        ON co.company_id = us.company_id
                        AND co.company_active = 1
                    LEFT JOIN users_companies uco
					    ON uco.user_id = us.user_id
                    LEFT JOIN users_barbers ub
                        ON ub.user_id = us.user_id
                    LEFT JOIN users_locations ul
                        ON ul.user_id = us.user_id
                    INNER JOIN users_profiles up
                        ON up.user_id = us.user_id
                    INNER JOIN profiles pro
                        ON pro.profile_id = up.profile_id
                    LEFT JOIN users usc
                        ON usc.user_id = us.user_created_by
                    LEFT JOIN people pec
                        ON pec.person_id = usc.person_id
                    LEFT JOIN users usm
                        ON usm.user_id = us.user_modified_by
                    LEFT JOIN people pem
                        ON pem.person_id = usm.person_id
                    WHERE us.user_delete = 0 AND  us.user_id = ?";
            $result = DB::select($sql, array($id));
        }
        return $result;
    }

    public function insertData($form, $user){
        try {
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
            return DB::select($sql, $sqlValues);
        } catch (Exception $e) {
            if(env("APP_ENV") == "local"){
                print_r($e->getMessage());
            }
            return false;
        }
    }

    public function insertDataReceptionistManager($form, $user){
        try {
            DB::beginTransaction();
            
            $person = new \stdClass();
            $person->person_firstname       = $form->person_firstname;
            $person->person_secondname      = $form->person_secondname;
            $person->person_surname         = $form->person_surname;
            $person->person_secondsurname   = $form->person_secondsurname;
            $person->person_phone           = $form->user_username;
            $person->person_email           = $form->person_email;
            $person->person_birthdate       = $form->person_birthdate;
            
            $person = $this->modelPeople->insertData($person, $user);
            if($person == false){
                return false;
            }
            
            $person = $person[0]->person_id;
            
            $users = new \stdClass();
            $users->user_username    = $form->user_username;
            $users->person_id        = $person;
            
            $users = $this->insertData($users, $user);
            if($users == false){
                return false;
            }

            $users = $users[0]->user_id;

            $profile = new \stdClass();
            $profile->user_id    = $users;
            $profile->profile_id = $form->profile_id;

            $profile = $this->modelUsersProfiles->insertData($profile, $user);
            if($profile == false){
                return false;
            }

            $userLocation = new \stdClass();
            $userLocation->user_id     = $users;
            $userLocation->location_id = $form->location_id;

            $userLocation = $this->modelUsersLocation->insertData($userLocation, $user);
            if($userLocation == false){
                return false;
            }

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            return false;
        }
    }

    public function insertDataUsers($form, $user){
        try {
            DB::beginTransaction();

            $person = new \stdClass();
            $person->person_firstname       = $form->person_firstname;
            $person->person_secondname      = $form->person_secondname;
            $person->person_surname         = $form->person_surname;
            $person->person_secondsurname   = $form->person_secondsurname;
            $person->person_phone           = $form->user_username;
            $person->person_email           = $form->person_email;
            $person->person_birthdate       = $form->person_birthdate;
            
            $person = $this->modelPeople->insertData($person, $user);
            if($person == false){
                return false;
            }
            
            $person = $person[0]->person_id;
            
            $users = new \stdClass();
            $users->user_username    = $form->user_username;
            $users->person_id        = $person;
            
            $users = $this->insertData($users, $user);
            if($users == false){
                return false;
            }

            $users = $users[0]->user_id;

            $profile = new \stdClass();
            $profile->user_id    = $users;
            $profile->profile_id = $form->profile_id;

            $profile = $this->modelUsersProfiles->insertData($profile, $user);
            if($profile == false){
                return false;
            }

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            return false;
        }
    }

    public function insertDataBarberBusinnes($form, $user){
        try {
            DB::beginTransaction();
            
            $person = new \stdClass();
            $person->person_firstname       = $form->person_firstname;
            $person->person_secondname      = $form->person_secondname;
            $person->person_surname         = $form->person_surname;
            $person->person_secondsurname   = $form->person_secondsurname;
            $person->person_phone           = $form->user_username;
            $person->person_email           = $form->person_email;
            $person->person_birthdate       = $form->person_birthdate;
            
            $person = $this->modelPeople->insertData($person, $user);
            if($person == false){
                return false;
            }
            
            $person = $person[0]->person_id;
            
            $users = new \stdClass();
            $users->user_username    = $form->user_username;
            $users->company_id       = $form->company_id;
            $users->person_id        = $person;
            
            $users = $this->insertData($users, $user);
            if($users == false){
                return false;
            }

            $users = $users[0]->user_id;

            $profile = new \stdClass();
            $profile->user_id    = $users;
            $profile->profile_id = $form->profile_id;

            $profile = $this->modelUsersProfiles->insertData($profile, $user);
            if($profile == false){
                return false;
            }

            $userCompany = new \stdClass();
            $userCompany->user_id    = $users;
            $userCompany->company_id = $form->company_id;

            $userCompany = $this->modelUsersCompany->insertData($userCompany, $user);
            if($userCompany == false){
                return false;
            }


            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            return false;
        }
    }

    public function insertDataBarberComission($form, $user){
        try {
            DB::beginTransaction();

            $person = new \stdClass();
            $person->person_firstname       = $form->person_firstname;
            $person->person_secondname      = $form->person_secondname;
            $person->person_surname         = $form->person_surname;
            $person->person_secondsurname   = $form->person_secondsurname;
            $person->person_phone           = $form->user_username;
            $person->person_email           = $form->person_email;
            $person->person_birthdate       = $form->person_birthdate;
            
            $person = $this->modelPeople->insertData($person, $user);
            if($person == false){
                return false;
            }
            
            $person = $person[0]->person_id;
            
            $users = new \stdClass();
            $users->user_username    = $form->user_username;
            $users->person_id        = $person;
            
            $users = $this->insertData($users, $user);
            if($user == false){
                return false;
            }

            $users = $users[0]->user_id;

            $profile = new \stdClass();
            $profile->user_id    = $users;
            $profile->profile_id = $form->profile_id;

            $profile = $this->modelUsersProfiles->insertData($profile, $user);
            if($profile == false){
                return false;
            }

            $userBarber = new \stdClass();
            $userBarber->user_id                       = $users;
            $userBarber->location_id                   = $form->location_id;
            $userBarber->barberlevel_id                = $form->barberlevel_id;
            $userBarber->userbarber_start_date         = $form->userbarber_start_date;
            $userBarber->userbarber_end_date           = $form->userbarber_end_date;
            $userBarber->userbarber_type               = "Commission";
            $userBarber->userbarber_post_booking       = $form->userbarber_post_booking;
            $userBarber->userbarber_tip                = $form->userbarber_tip;
            $userBarber->userbarber_interval_schedule  = $form->userbarber_interval_schedule;
            $userBarber->userbarber_walkin_restriction = $form->userbarber_walkin_restriction;

            $userBarber = $this->modelUsersBarbers->insertData($userBarber, $user);
            if($userBarber == false){
                return false;
            }

            $userLocation = new \stdClass();
            $userLocation->user_id    = $users;
            $userLocation->location_id = $form->location_id;

            $userLocation = $this->modelUsersLocation->insertData($userLocation, $user);
            if($userLocation == false){
                return false;
            }

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            return false;
        }
    }

    public function updateData($form,$id,$user){
        try{
            $form->{$this->modifier} = $user;
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

            $sqlValues[] = $id;
            return DB::update($sql,$sqlValues);
        }catch(Exception $e){
            if(env("APP_ENV") == "local"){
                print_r($e->getMessage());
            }
            return false;
        }
    }

    public function insertDataBarberRent($form, $user){
        try {
            DB::beginTransaction();

            $person = new \stdClass();
            $person->person_firstname       = $form->person_firstname;
            $person->person_secondname      = $form->person_secondname;
            $person->person_surname         = $form->person_surname;
            $person->person_secondsurname   = $form->person_secondsurname;
            $person->person_phone           = $form->user_username;
            $person->person_email           = $form->person_email;
            $person->person_birthdate       = $form->person_birthdate;
            
            $person = $this->modelPeople->insertData($person, $user);
            if($person == false){
                return false;
            }
            
            $person = $person[0]->person_id;
            
            $users = new \stdClass();
            $users->user_username    = $form->user_username;
            $users->person_id        = $person;
            
            $users = $this->insertData($users, $user);
            $users = $users[0]->user_id;

            $profile = new \stdClass();
            $profile->user_id    = $users;
            $profile->profile_id = $form->profile_id;

            $profile = $this->modelUsersProfiles->insertData($profile, $user);
            if($profile == false){
                return false;
            }

            $userBarberd = new \stdClass();
            $userBarberd->user_id                       = $users;
            $userBarberd->location_id                   = $form->location_id;
            $userBarberd->barberlevel_id                = $form->barberlevel_id;
            $userBarberd->userbarber_start_date         = $form->userbarber_start_date;
            $userBarberd->userbarber_end_date           = $form->userbarber_end_date;
            $userBarberd->userbarber_type               = "Rent";
            $userBarberd->userbarber_post_booking       = $form->userbarber_post_booking;
            $userBarberd->userbarber_tip                = $form->userbarber_tip;
            $userBarberd->userbarber_interval_schedule  = $form->userbarber_interval_schedule;
            $userBarberd->userbarber_walkin_restriction = $form->userbarber_walkin_restriction;

            $userBarber = $this->modelUsersBarbers->insertData($userBarberd, $user);
            if($userBarber == false){
                return false;
            }

            $userLocation = new \stdClass();
            $userLocation->user_id     = $users;
            $userLocation->location_id = $form->location_id;

            $userLocation = $this->modelUsersLocation->insertData($userLocation, $user);
            if($userLocation == false){
                return false;
            }

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            return false;
        }
    }

    public function updateDataBarberBusinnes($form, $id, $user){
        try {
            DB::beginTransaction();

            $userData = $this->get($user, $id);

            $personId      = $userData[0]->person_id;
            $profileId     = $userData[0]->profile_id;
            $userCompanyId = $userData[0]->usercompany_id;

            $person = new \stdClass();
            $person->person_firstname       = $form->person_firstname;
            $person->person_secondname      = $form->person_secondname;
            $person->person_surname         = $form->person_surname;
            $person->person_secondsurname   = $form->person_secondsurname;
            $person->person_phone           = $form->user_username;
            $person->person_email           = $form->person_email;
            $person->person_birthdate       = $form->person_birthdate;
            
            $person = $this->modelPeople->updateData($person, $personId, $user);

            if($person == false){
                return false;
            }
            
            $users = new \stdClass();
            $users->user_username    = $form->user_username;
            $users->company_id       = $form->company_id;
            
            $users = $this->updateData($users, $id, $user);
           
            if($users == false){
                return false;
            }
           
            if($form->profile_id != $profileId){
                $this->getDeleteUsersRelations($profileId, $id, $user);

                $profile = new \stdClass();
                $profile->user_id    = $id;
                $profile->profile_id = $form->profile_id;
    
                $profile = $this->modelUsersProfiles->insertData($profile, $user);
                if($profile == false){
                    return false;
                }
    
                $userCompany = new \stdClass();
                $userCompany->user_id    = $id;
                $userCompany->company_id = $form->company_id;
    
                $userCompany = $this->modelUsersCompany->insertData($userCompany, $user);
                if($userCompany == false){
                    return false;
                }
            }else {
                $userCompany = new \stdClass();
                $userCompany->company_id = $form->company_id;

                $userCompany = $this->modelUsersCompany->updateData($userCompany, $userCompanyId, $user);
                
                if($userCompany == false){
                    return false;
                }
            }
            
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            return false;
        }
    }

    public function updateDataBarberComission($form, $id, $user){
        try {
            DB::beginTransaction();

            $userData = $this->get($user, $id);

            $personId       = $userData[0]->person_id;
            $profileId      = $userData[0]->profile_id;
            $userBarberId   = $userData[0]->userbarber_id;
            $userLocationId = $userData[0]->userlocation_id;

            $person = new \stdClass();
            $person->person_firstname       = $form->person_firstname;
            $person->person_secondname      = $form->person_secondname;
            $person->person_surname         = $form->person_surname;
            $person->person_secondsurname   = $form->person_secondsurname;
            $person->person_phone           = $form->user_username;
            $person->person_email           = $form->person_email;
            $person->person_birthdate       = $form->person_birthdate;
            
            $person = $this->modelPeople->updateData($person, $personId, $user);

            if($person == false){
                return false;
            }
            
            $users = new \stdClass();
            $users->user_username    = $form->user_username;
            
            $users = $this->updateData($users, $id, $user);
           
            if($users == false){
                return false;
            }
           
            if($form->profile_id != $profileId){
                $this->getDeleteUsersRelations($profileId, $id, $user);

                $profile = new \stdClass();
                $profile->user_id    = $id;
                $profile->profile_id = $form->profile_id;
    
                $profile = $this->modelUsersProfiles->insertData($profile, $user);
                if($profile == false){
                    return false;
                }

                $userBarber = new \stdClass();
                $userBarber->user_id                       = $id;
                $userBarber->location_id                   = $form->location_id;
                $userBarber->barberlevel_id                = $form->barberlevel_id;
                $userBarber->userbarber_start_date         = $form->userbarber_start_date;
                $userBarber->userbarber_end_date           = $form->userbarber_end_date;
                $userBarber->userbarber_type               = "Commission";
                $userBarber->userbarber_post_booking       = $form->userbarber_post_booking;
                $userBarber->userbarber_tip                = $form->userbarber_tip;
                $userBarber->userbarber_interval_schedule  = $form->userbarber_interval_schedule;
                $userBarber->userbarber_walkin_restriction = $form->userbarber_walkin_restriction;

                $userBarber = $this->modelUsersBarbers->insertData($userBarber, $user);
                if($userBarber == false){
                    return false;
                }

                $userLocation = new \stdClass();
                $userLocation->user_id     = $id;
                $userLocation->location_id = $form->location_id;

                $userLocation = $this->modelUsersLocation->insertData($userLocation, $user);
                if($userLocation == false){
                    return false;
                }
            }else {
                $userBarber = new \stdClass();
                $userBarber->location_id                   = $form->location_id;
                $userBarber->barberlevel_id                = $form->barberlevel_id;
                $userBarber->userbarber_start_date         = $form->userbarber_start_date;
                $userBarber->userbarber_end_date           = $form->userbarber_end_date;
                $userBarber->userbarber_type               = "Commission";
                $userBarber->userbarber_post_booking       = $form->userbarber_post_booking;
                $userBarber->userbarber_tip                = $form->userbarber_tip;
                $userBarber->userbarber_interval_schedule  = $form->userbarber_interval_schedule;
                $userBarber->userbarber_walkin_restriction = $form->userbarber_walkin_restriction;

                $userBarber = $this->modelUsersBarbers->updateData($userBarber, $userBarberId, $user);
                
                if($userBarber == false){
                    return false;
                }

                $userLocation = new \stdClass();
                $userLocation->location_id = $form->location_id;

                $userLocation = $this->modelUsersLocation->updateData($userLocation, $userLocationId, $user);
                
                if($userLocation == false){
                    return false;
                }
            }
            
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            return false;
        }
    }

    public function updateDataBarberRent($form, $id, $user){
        try {
            DB::beginTransaction();

            $userData = $this->get($user, $id);

            $personId       = $userData[0]->person_id;
            $profileId      = $userData[0]->profile_id;
            $userBarberId   = $userData[0]->userbarber_id;
            $userLocationId = $userData[0]->userlocation_id;

            $person = new \stdClass();
            $person->person_firstname       = $form->person_firstname;
            $person->person_secondname      = $form->person_secondname;
            $person->person_surname         = $form->person_surname;
            $person->person_secondsurname   = $form->person_secondsurname;
            $person->person_phone           = $form->user_username;
            $person->person_email           = $form->person_email;
            $person->person_birthdate       = $form->person_birthdate;
            
            $person = $this->modelPeople->updateData($person, $personId, $user);

            if($person == false){
                return false;
            }
            
            $users = new \stdClass();
            $users->user_username    = $form->user_username;
            
            $users = $this->updateData($users, $id, $user);
           
            if($users == false){
                return false;
            }
           
            if($form->profile_id != $profileId){
                $this->getDeleteUsersRelations($profileId, $id, $user);

                $profile = new \stdClass();
                $profile->user_id    = $id;
                $profile->profile_id = $form->profile_id;
    
                $profile = $this->modelUsersProfiles->insertData($profile, $user);
                if($profile == false){
                    return false;
                }

                $userBarber = new \stdClass();
                $userBarber->user_id                       = $id;
                $userBarber->location_id                   = $form->location_id;
                $userBarber->barberlevel_id                = $form->barberlevel_id;
                $userBarber->userbarber_start_date         = $form->userbarber_start_date;
                $userBarber->userbarber_end_date           = $form->userbarber_end_date;
                $userBarber->userbarber_type               = "Rent";
                $userBarber->userbarber_post_booking       = $form->userbarber_post_booking;
                $userBarber->userbarber_tip                = $form->userbarber_tip;
                $userBarber->userbarber_interval_schedule  = $form->userbarber_interval_schedule;
                $userBarber->userbarber_walkin_restriction = $form->userbarber_walkin_restriction;

                $userBarber = $this->modelUsersBarbers->insertData($userBarber, $user);
                if($userBarber == false){
                    return false;
                }

                $userLocation = new \stdClass();
                $userLocation->user_id     = $id;
                $userLocation->location_id = $form->location_id;

                $userLocation = $this->modelUsersLocation->insertData($userLocation, $user);
                if($userLocation == false){
                    return false;
                }
            }else {
                $userBarber = new \stdClass();
                $userBarber->location_id                   = $form->location_id;
                $userBarber->barberlevel_id                = $form->barberlevel_id;
                $userBarber->userbarber_start_date         = $form->userbarber_start_date;
                $userBarber->userbarber_end_date           = $form->userbarber_end_date;
                $userBarber->userbarber_type               = "Rent";
                $userBarber->userbarber_post_booking       = $form->userbarber_post_booking;
                $userBarber->userbarber_tip                = $form->userbarber_tip;
                $userBarber->userbarber_interval_schedule  = $form->userbarber_interval_schedule;
                $userBarber->userbarber_walkin_restriction = $form->userbarber_walkin_restriction;

                $userBarber = $this->modelUsersBarbers->updateData($userBarber, $userBarberId, $user);
                
                if($userBarber == false){
                    return false;
                }

                $userLocation = new \stdClass();
                $userLocation->location_id = $form->location_id;

                $userLocation = $this->modelUsersLocation->updateData($userLocation, $userLocationId, $user);
                
                if($userLocation == false){
                    return false;
                }
            }
            
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            return false;
        }
    }

    public function updateDataReceptionistManager($form, $id, $user){
        try {
            DB::beginTransaction();

            $userData = $this->get($user, $id);

            $personId      = $userData[0]->person_id;
            $profileId     = $userData[0]->profile_id;
            $userLocationId = $userData[0]->userlocation_id;

            $person = new \stdClass();
            $person->person_firstname       = $form->person_firstname;
            $person->person_secondname      = $form->person_secondname;
            $person->person_surname         = $form->person_surname;
            $person->person_secondsurname   = $form->person_secondsurname;
            $person->person_phone           = $form->user_username;
            $person->person_email           = $form->person_email;
            $person->person_birthdate       = $form->person_birthdate;
            
            $person = $this->modelPeople->updateData($person, $personId, $user);

            if($person == false){
                return false;
            }
            
            $users = new \stdClass();
            $users->user_username    = $form->user_username;
            $users->company_id       = $form->company_id;
            
            $users = $this->updateData($users, $id, $user);
           
            if($users == false){
                return false;
            }
           
            if($form->profile_id != $profileId){
                $this->getDeleteUsersRelations($profileId, $id, $user);

                $profile = new \stdClass();
                $profile->user_id    = $id;
                $profile->profile_id = $form->profile_id;
    
                $profile = $this->modelUsersProfiles->insertData($profile, $user);
                if($profile == false){
                    return false;
                }
    
                $userLocation = new \stdClass();
                $userLocation->user_id     = $id;
                $userLocation->location_id = $form->location_id;

                $userLocation = $this->modelUsersLocation->insertData($userLocation, $user);
                if($userLocation == false){
                    return false;
                }
            }else {
                $userLocation = new \stdClass();
                $userLocation->location_id = $form->location_id;

                $userLocation = $this->modelUsersLocation->updateData($userLocation, $userLocationId, $user);
                
                if($userLocation == false){
                    return false;
                }
            }
            
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            return false;
        }
    }

    public function updateDataUsers($form, $id, $user){
        try {
            DB::beginTransaction();

            $userData = $this->get($user, $id);

            $personId      = $userData[0]->person_id;
            $profileId     = $userData[0]->profile_id;

            $person = new \stdClass();
            $person->person_firstname       = $form->person_firstname;
            $person->person_secondname      = $form->person_secondname;
            $person->person_surname         = $form->person_surname;
            $person->person_secondsurname   = $form->person_secondsurname;
            $person->person_phone           = $form->user_username;
            $person->person_email           = $form->person_email;
            $person->person_birthdate       = $form->person_birthdate;
            
            $person = $this->modelPeople->updateData($person, $personId, $user);

            if($person == false){
                return false;
            }
            
            $users = new \stdClass();
            $users->user_username    = $form->user_username;
            $users->company_id       = $form->company_id;
            
            $users = $this->updateData($users, $id, $user);
           
            if($users == false){
                return false;
            }
           
            if($form->profile_id != $profileId){
                $this->getDeleteUsersRelations($profileId, $id, $user);

                $profile = new \stdClass();
                $profile->user_id    = $id;
                $profile->profile_id = $form->profile_id;
    
                $profile = $this->modelUsersProfiles->insertData($profile, $user);
                if($profile == false){
                    return false;
                }
            }
            
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            return false;
        }
    }

    public function updateStatus($status, $id, $user, $codigos = null){
        try{
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
        }catch(Exception $e){
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

    public function getParamsUpdate($user){
        try{
            $sql = "SELECT
                        (select
                            coalesce(json_agg(tmp.*),'[]')::text
                                from(
                                    select
                                        co.company_id as code,
                                        co.company_name as name
                                    from companies co
                                    where
                                        co.company_active = 1
                                    order by name
                                ) as tmp
                        ) as companies,
                        (select
                            coalesce(json_agg(tmp.*),'[]')::text
                                from(
                                    select
                                        pro.profile_name as name,
                                        pro.profile_id as code
                                    from
                                            profiles pro
                                    where
                                        pro.profile_active = 1
                                        and 1 = (select up.profile_id from users_profiles up where up.user_id = ?)
                                    UNION
                                    select
                                        pro.profile_name as name,
                                        pro.profile_id as code
                                    from
                                            profiles pro
                                    where
                                        pro.profile_active = 1
                                        and pro.profile_id > 2
                                        and 2 = (select up.profile_id from users_profiles up where up.user_id = ?)

                                    UNION
                                    select
                                        pro.profile_name as name,
                                        pro.profile_id as code
                                    from
                                        profiles pro
                                    where
                                        pro.profile_active = 1
                                        and pro.profile_id > 3
                                        and 3 = (select up.profile_id from users_profiles up where up.user_id = ?)
                                    order by code
                            ) as tmp) as profiles";

            return DB::select($sql, array($user,$user,$user));
        }catch(Exception $e){
            if(env("APP_ENV") == "local"){
                print_r($e->getMessage());
            }
            return false;
        }
    }

    public function getDeleteUsersRelations($profile, $id){
        if($profile == 3){
            $result = $this->modelUsersCompany->deleteData($id);
            $result = $this->modelUsersProfiles->deleteData($id);
        }elseif($profile == 5 || $profile == 6){
            $result = $this->modelUsersProfiles->deleteData($id);
            $result = $this->modelUsersLocation->deleteData($id);
            $result = $this->modelUsersBarbers->deleteData($id);
        }elseif($profile == 7 || $profile == 4){
            $result = $this->modelUsersLocation->deleteData($id);
            $result = $this->modelUsersProfiles->deleteData($id);
        }elseif($profile == 1 || $profile == 2){
            $result = $this->modelUsersProfiles->deleteData($id);
        }
    }

    public function getDataTypeUser($profile){
       
            $sql = "SELECT
                        us.*,
                        us.user_created_at AS created_at,
                        us.user_modified_at AS modified_at,
                        COALESCE(
                            pec.person_firstname || ' '||
                            pec.person_secondname||' ' ||
                            pec.person_surname||' '||
                            pec.person_secondsurname,pec.person_firstname||' '||
                            pec.person_surname
                        ) AS created_by,
                        COALESCE(
                            pem.person_firstname || ' '||
                            pem.person_secondname||' ' ||
                            pem.person_surname||' '||
                            pem.person_secondsurname,pem.person_firstname||' '||
                            pem.person_surname
                        )  AS modified_by,
                        co.company_name,
                        pe.person_firstname,
                        pe.person_secondname,
                        pe.person_surname,
                        pe.person_secondsurname,
                        pe.person_email,
                        pe.person_phone,
                        pe.person_birthdate,
                        pe.person_active,
                        pro.profile_id,
                        pro.profile_name,
                        uco.usercompany_id,
                        ub.userbarber_id,
                        ul.userlocation_id
                    FROM users us
                    INNER JOIN people pe
                        ON pe.person_id = us.person_id
                        AND pe.person_active = 1
                    LEFT JOIN companies co
                        ON co.company_id = us.company_id
                        AND co.company_active = 1
                    LEFT JOIN users_companies uco
					    ON uco.user_id = us.user_id
                    LEFT JOIN users_barbers ub
                        ON ub.user_id = us.user_id
                    LEFT JOIN users_locations ul
                        ON ul.user_id = us.user_id
                    INNER JOIN users_profiles up
                        ON up.user_id = us.user_id
                    INNER JOIN profiles pro
                        ON pro.profile_id = up.profile_id
                    LEFT JOIN users usc
                        ON usc.user_id = us.user_created_by
                    LEFT JOIN people pec
                        ON pec.person_id = usc.person_id
                    LEFT JOIN users usm
                        ON usm.user_id = us.user_modified_by
                    LEFT JOIN people pem
                        ON pem.person_id = usm.person_id
                    WHERE us.user_delete = 0 AND up.profile_id > $profile";
            $result = DB::select($sql);
      
        return $result;
    }

    public function getParamsLocations($company){
        try{
            $sql = "SELECT
                    lo.location_id as code,
                    lo.location_name as name
                FROM locations lo
                WHERE  lo.location_active = 1 and lo.location_delete = 0 and lo.company_id = ?
                ORDER BY name";

            return DB::select($sql, array($company));
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
                        us.*,
                        us.user_created_at AS created_at,
                        us.user_modified_at AS modified_at,
                        COALESCE(
                            pec.person_firstname || ' '||
                            pec.person_secondname||' ' ||
                            pec.person_surname||' '||
                            pec.person_secondsurname,pec.person_firstname||' '||
                            pec.person_surname
                        ) AS people_name,
                        COALESCE(
                            pec.person_firstname || ' '||
                            pec.person_secondname||' ' ||
                            pec.person_surname||' '||
                            pec.person_secondsurname,pec.person_firstname||' '||
                            pec.person_surname
                        ) AS created_by,
                        COALESCE(
                            pem.person_firstname || ' '||
                            pem.person_secondname||' ' ||
                            pem.person_surname||' '||
                            pem.person_secondsurname,pem.person_firstname||' '||
                            pem.person_surname
                        )  AS modified_by,
                        co.company_name,
                        pe.person_firstname,
                        pe.person_secondname,
                        pe.person_surname,
                        pe.person_secondsurname,
                        pe.person_email,
                        pe.person_phone,
                        pe.person_birthdate,
                        pe.person_active,
                        pro.profile_id,
                        pro.profile_name,
                        uco.usercompany_id,
                        ub.userbarber_id,
                        ul.userlocation_id
                    FROM users us
                    INNER JOIN people pe
                        ON pe.person_id = us.person_id
                        AND pe.person_active = 1
                    LEFT JOIN companies co
                        ON co.company_id = us.company_id
                        AND co.company_active = 1
                    LEFT JOIN users_companies uco
                        ON uco.user_id = us.user_id
                    LEFT JOIN users_barbers ub
                        ON ub.user_id = us.user_id
                    LEFT JOIN users_locations ul
                        ON ul.user_id = us.user_id
                    INNER JOIN users_profiles up
                        ON up.user_id = us.user_id
                    INNER JOIN profiles pro
                        ON pro.profile_id = up.profile_id
                    LEFT JOIN users usc
                        ON usc.user_id = us.user_created_by
                    LEFT JOIN people pec
                        ON pec.person_id = usc.person_id
                    LEFT JOIN users usm
                        ON usm.user_id = us.user_modified_by
                    LEFT JOIN people pem
                        ON pem.person_id = usm.person_id
					where $where
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
