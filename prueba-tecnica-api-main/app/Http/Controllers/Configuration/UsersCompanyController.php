<?php

namespace App\Http\Controllers\Configuration;

use Illuminate\Http\Request;
use App\Http\Controllers\MyController;
use App\Models\UsersCompanyModel;
use Exception;

class UsersCompanyController extends MyController{
    private $model;
    private $endpoint = "configuration/userscompanies";
    private $user;

    public function __construct(){
        parent::__construct();
        $this->model = new UsersCompanyModel();
        $this->user  = $this->getUser();

    }

    public function index(Request $request){
        $user = $this->getUser();
        $permission = $this->checkPermission($user,$this->endpoint,__FUNCTION__);
        if($permission){
            $action = $request->input('action');
            switch($action){
                case 'getParamsUpdate':
                    try{
                        $result = $this->model->getParamsUpdate();
                        if($result=== false){
                            return $this->returnError("0015");
                        }
                        return $this->returnData($result);
                    }catch(Exception $e){
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
                    try{
                        $result = $this->model->get($user);
                        if($result === false){
                            return $this->returnError("0018"); // error al consultar registro
                        }
                        return $this->returnData($result);
                    }catch(Exception $e){
                        return $this->returnError('0003');
                    }
                break;
            }
           
        }else{
            return $this->notPermission();
        }
    }

    public function store(Request $request){
        $user = $this->getUser();
        $permission = $this->checkPermission($user,$this->endpoint,__FUNCTION__);
        if($permission){
            try{
                $objData = json_decode($request->getContent());
                $result = $this->model->insertData($objData, $this->user);
                if($result === false){
                    return $this->returnError("0019");// Se produjo un error al intentar crear el registro
                }
                return $this->returnCreated($result, "0010");//Creado con éxito
            }catch(Exception $e){
                return $this->returnError('0003');
            }
        }else{
            return $this->notPermission();
        }
    }

    public function show($id){
        $user = $this->getUser();
        $permission = $this->checkPermission($user,$this->endpoint,__FUNCTION__);
        if($permission){
            try{
                $result = $this->model->get($user, $id);
                if($result === false){
                    return $this->returnError('0018'); // Error al consultar registro
                }
                return $this->returnData($result, "0008"); //Datos no encontrados
            }catch(Exception $e){
                return $this->returnError('0003');
            }
        }else{
            return $this->notPermission();
        }
    }

    public function update(Request $request, $id) {
        $user       = $this->getUser();
        $action     = $request->input('action');
        $permission = $this->checkPermission($user,$this->endpoint,__FUNCTION__);
        if ($permission){
            switch($action){
                case 'updateStatus':
                    try {
                        $objData = json_decode($request->getContent());
                        $status = $objData->status;
                        if ($id == 'null'){
                            $codigos = $objData->codigos;
                        } else {
                            $codigos = null;
                        }
                        $result = $this->model->updateStatus($status,$id,$user,$codigos);
                        if($result === false){
                            return $this->returnError("0017"); //Se produjo un error al actualizar datos
                        }
                        return $this->returnOk('0009'); // Actualizado exitosamente
                    } catch (Exception $e) {
                        return $this->returnError('0003');
                    }
                break;
                default:
                    try {
                        $objData = json_decode($request->getContent());
                        $result = $this->model->updateData($objData, $id, $user);
                        if($result === false){
                            return $this->returnError("0017"); //Se produjo un error al actualizar datos
                        }
                        return $this->returnOk("0009");// Actualizado exitosamente
                    } catch (Exception $e) {
                        return $this->returnError('0003');
                    }
                break;
            }
        }else {
            return $this->notPermission();
        }
    }

    public function destroy($id){
        $user = $this->getUser();
        $permission = $this->checkPermission($user, $this->endpoint,__FUNCTION__);
        if ($permission) {
            try {
                $result = $this->model->deleteById($user, $id);
                if($result === false){
                    $this->returnError("0020");
                }
                return $this->returnOk("0011");
            } catch (Exception $e){
                return $this->returnError('0003');
            }
        } else {
            return $this->notPermission();
        }
    }
}
