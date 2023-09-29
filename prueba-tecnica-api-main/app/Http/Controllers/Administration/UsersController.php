<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\MyController;
use Illuminate\Http\Request;
use App\Models\AppModel;
use App\Models\UsersModel;
use App\Models\PeopleModel;
use App\Models\LocationsModel;
use App\Models\UsersLocationsModel;
use App\Models\UsersCompanyModel;
use App\Models\UsersProfilesModel;

use Exception;
use stdClass;

class UsersController extends MyController{
    private $model;
    private $endpoint = 'administration/users';
   
    public function __construct(){
        parent::__construct();
        $this->model = new UsersModel();
        $this->locationModel = new LocationsModel();
        $this->peopleModel = new PeopleModel();
        $this->modelCompany = new UsersCompanyModel();
        $this->modelLocation = new UsersLocationsModel();
    }

    public function index(Request $request){
        $user       = $this->getUser();
        $permission = $this->checkPermission($user, $this->endpoint,__FUNCTION__);
        
        if ($permission){
            $action = $request->input('action');
            switch ($action) {
                case 'getParamsUpdate':
                    try {
                        $result = $this->model->getParamsUpdate($user);
                        return $this->returnData($result, '0008');
                    } catch(Exception $e) {
                        return $this->returnError('0003');
                    }
                break;
                case 'getLocation':
                    try {
                        $company   = $request->input('company');
                       
                        $result = $this->model->getParamsLocations($company);
                        return $this->returnData($result, '0008');
                    } catch(Exception $e) {
                        return $this->returnError('0003');
                    }
                break;
                case 'getDataTypeUser':
                    try {
                        
                        $profile   = $this->getProfileId($user);
                        $result = $this->model->getDataTypeUser($profile);
                        return $this->returnData($result, '0008');
                    } catch(Exception $e) {
                        return $this->returnError('0003');
                    }
                break;
                case 'filter':
                    try{
                        $filters = $request->query();
                        unset($filters['action']);
                        $result = $this->model->getByFilters($user,$filters);
                        if($result=== false){
                            return $this->returnError("0016");
                        }
                    }catch(Exception $e){
                        return $this->returnError("0003");
                    }
                break;
                default:
                    $result = $this->model->get($user);
                    return $this->returnData($result, '0008');
                break;
            }
        } else {
            return $this->notPermission();
        }
    }

    public function store(Request $request){
        $user       = $this->getUser();
        $permission = $this->checkPermission($user, $this->endpoint,__FUNCTION__);
        if ($permission) {
            try {
                $objData    = json_decode($request->getContent());

                if($objData->profile_id == 3){
                    $result = $this->model->insertDataBarberBusinnes($objData, $user);
                }elseif($objData->profile_id == 5){
                    $result = $this->model->insertDataBarberComission($objData, $user);
                }elseif($objData->profile_id == 6){
                    $result = $this->model->insertDataBarberRent($objData, $user);
                }elseif($objData->profile_id == 7 || $objData->profile_id == 4){
                    $result = $this->model->insertDataReceptionistManager($objData, $user);
                }elseif($objData->profile_id == 1 || $objData->profile_id == 2) {
                    $result = $this->model->insertDataUsers($objData, $user);
                }else{
                    $result = $this->model->insertData($objData, $user);
                }

                if($result === false){
                    return $this->returnError('0019');
                }

                return $this->returnCreated($result, "0010");//Creado con éxito
            } catch (Exception $e){
              return $this->returnError('0003');
            }
        } else {
            return $this->notPermission();
        }

    }

    public function show($id){
        $user       = $this->getUser();

        $permission = $this->checkPermission($user,$this->endpoint,__FUNCTION__);

        if ($permission) {
            try {
                $result = $this->model->get($user, $id);
                return $this->returnData($result, '0008');
            } catch (Exception $e) {
                return $this->returnError('0003');
            }
        } else {
            return $this->notPermission();
        }
    }

    public function update(Request $request, $id){
        $action     = $request->input('action');
        $user       = $this->getUser();
        $permission = $this->checkPermission($user,$this->endpoint,__FUNCTION__);
        if ($permission){
            switch ($action) {
                case 'updateStatus':
                    try {
                        $objData = json_decode($request->getContent());
                        $status = $objData->status;
                        if ($id == 'null'){
                            $codigos = $objData->codigos;
                        } else {
                            $codigos = null;
                        }
                        $this->model->updateStatus($status, $id, $user, $codigos);
                        
                        return $this->returnOk('0009');
                    } catch (Exception $e){
                        return $this->returnError('0003');
                    }
                break;
                default:
                    try {
                        $objData     = json_decode($request->getContent());

                        if($objData->profile_id == 3){
                            $result = $this->model->updateDataBarberBusinnes($objData, $id, $user);
                        }elseif($objData->profile_id == 5){
                            $result = $this->model->updateDataBarberComission($objData, $id, $user);
                        }elseif($objData->profile_id == 6){
                            $result = $this->model->updateDataBarberRent($objData, $id, $user);
                        }elseif($objData->profile_id == 7 || $objData->profile_id == 4){
                            $result = $this->model->updateDataReceptionistManager($objData, $id, $user);
                        }else{
                            $result = $this->model->updateDataUsers($objData, $id, $user);
                        }

                        if($result === false){
                            return $this->returnError('0003');
                        }
        
                        return $this->returnOk("0009");
                        
                    } catch (Exception $e){
                        return $this->returnError('Se produjo un error al intentar actualizar');
                    }
                break;
            }
        }else{
            return $this->notPermission();
        }
    }

    public function destroy($id){
        $user       = $this->getUser();
        $permission = $this->checkPermission($user,$this->endpoint,__FUNCTION__);
        if($permission){
            try{
                $this->model->deleteById($user, $id);
                return $this->returnOk('0011');
            }catch(Exception $e){
                return $this->returnError('0003');
            }
        }else{
            return $this->notPermission();
        }
    }


    public function setUserFromUserToApprove($status,$id,$codigos,$user){
        try{
            if($codigos == null){
                $codigos = array($id);
            }
            $users = $this->model->getUsuariosToApprove($user,$codigos);
            if(sizeof($users)>0){
                if($status == 1){
                    return $this->model->createUsersFromUserToApprove($users,$user);
                }else{
                    return $this->model->inactivateUsersToApprove($users,$user);
                }
            }
        }catch(Exception $e){
            return $this->returnError('Se produjo un error al intentar asociar usuario y propiedad');
        }
    }
}
