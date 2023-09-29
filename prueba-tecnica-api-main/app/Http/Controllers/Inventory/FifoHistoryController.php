<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\MyController;
use App\Http\Controllers\util\ReadAndWriteExcel;
use App\Models\Inventory\FifoHistoryModel;
use Illuminate\Http\Request;
use Exception;

class FifoHistoryController extends MyController{
    private $model;
    private $endpoint = 'inventory/fifo_history';
   
    public function __construct(){
        parent::__construct();
        $this->model = new FifoHistoryModel();
    }

    public function index(Request $request){
        $user       = $this->getUser();
        $permission = $this->checkPermission($user, $this->endpoint,__FUNCTION__);
        if ($permission){
            $action = $request->input('action');
            switch ($action) {
                case 'getParamsUpdate':
                    try {
                        $company = $this->getCompany($user);
                        $result = $this->model->getParamsUpdate($user,$company);
                        if($result=== false){
                            return $this->returnError("0015");
                        }
                        return $this->returnData($result);
                    } catch(Exception $e) {
                        if(env("APP_ENV") == "local"){
                            print_r($e->getMessage());
                        }
                        return $this->returnError('0003');
                    }
                break;
                case 'getStates':
                    try {
                        $country   = $request->input('country');
                        return  $this->getState($country);
                    } catch (Exception $e) {
                        if(env("APP_ENV") == "local"){
                            print_r($e->getMessage());
                        }
                        return $this->returnError("0003");
                    }
                break;
                case 'getCities':
                    try {
                        $country = $request->input('country');
                        $state   = $request->input('state');
                        return $this->getCity($country, $state);
                    }catch (Exception $e){
                        if(env("APP_ENV") == "local"){
                            print_r($e->getMessage());
                        }
                        return $this->returnError("0013");
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
                case 'generateReport':
                    try{
                        $data = $this->model->get($user);
                        
                        $resultData = [];
                        
                        $headers = ['id', 'Lote', 'Pallet', 'Zona', 'Cantidad', 'Nota', 'Fecha', 'User', 'Creado por', 'Fecha de creacion', 'Modificado por', 'Fecha de modificacion'];
                        $resultData[] = $headers;

                        foreach($data as $key=>$value){
                            $resultData[] = [
                                $value->fifohistory_id,
                                $value->fifohistory_lote_code,
                                $value->fifohistory_pallet_code,
                                $value->fifohistory_zone,
                                $value->fifohistory_quantity,
                                $value->fifohistory_note,
                                $value->fifohistory_datetime,
                                $value->user_id,
                                $value->fifohistory_created_by,
                                $value->fifohistory_created_at,
                                $value->fifohistory_modified_by,
                                $value->fifohistory_modified_at
                            ];
                        }
                        $excel = new ReadAndWriteExcel(null,null);
                        return $excel->writeFile("FIFO",$resultData,"A3");
                    }catch(Exception $e){
                        return $this->returnError("0003"); // se produjo un error
                    }
                break;
                default:
                    try{
                        $result = $this->model->get($user);
                        if($result === false){
                            return $this->returnError("0018"); // error al consultar registro
                        }
                    }catch(Exception $e){
                        return $this->returnError("0003"); // se produjo un error
                    }
                break;
            }
            return $this->returnData($result);
        } else {
            return $this->notPermission();
        }
    }

    public function store(Request $request){
        $user = $this->getUser();
        $permission = $this->checkPermission($user,$this->endpoint,__FUNCTION__);
        if($permission){
            try{
                $objData = json_decode($request->getContent());
                $result = $this->model->insertData($objData, $user);
                if($result === false){
                    return $this->returnError("0019");// Se produjo un error al intentar crear el registro
                }
                return $this->returnCreated($result, "0010");//Creado con éxito
            }catch(Exception $e){
                if(env("APP_ENV") == "local"){
                    print_r($e->getMessage());
                }
                return $this->returnError('0003'); // Se produjo un error
            }
        }else{
            return $this->notPermission();
        }
    }

    public function show($id){
        $user = $this->getUser();
        $permission = $this->checkPermission($user,$this->endpoint,__FUNCTION__);
        if($permission){
            // try{
                $result = $this->model->get($user, $id);
                if($result === false){
                    return $this->returnError('0018'); // Error al consultar registro
                }
                return $this->returnData($result, "0008"); //Datos no encontrados
            // }catch(Exception $e){
            //     if(env("APP_ENV") == "local"){
            //         print_r($e->getMessage());
            //     }
            //     return $this->returnError('0003'); // se produjo un error
            // }
        }else{
            return $this->notPermission();
        }
    }

    public function update(Request $request, $id){
        $user = $this->getUser();
        $action = $request->input('action');
        $permission = $this->checkPermission($user,$this->endpoint,__FUNCTION__);
        if($permission){
            switch($action){
                case 'updateStatus':
                    try{
                        $objData = json_decode($request->getContent());
                        $status = $objData->status;
                        if($id == 'null'){
                            $codigos = $objData->codigos;
                        }else{
                            $codigos = null;
                        }
                        $result = $this->model->updateStatus($status,$id,$user,$codigos);
                        if($result === false){
                            return $this->returnError("0017"); //Se produjo un error al actualizar datos
                        }
                        return $this->returnOk('0009'); // Actualizado exitosamente
                    }catch(Exception $e){
                        if(env("APP_ENV") == "local"){
                            print_r($e->getMessage());
                        }
                        return $this->returnError('0003'); // se produjo un error
                    }
                break;
                default:
                    try{
                        $objData = json_decode($request->getContent());
                        $result = $this->model->updateData($objData, $id, $user);
                        if($result === false){
                            return $this->returnError("0017"); //Se produjo un error al actualizar datos
                        }
                        return $this->returnOk("0009");// Actualizado exitosamente
                    }catch(Exception $e){
                        if(env("APP_ENV") == "local"){
                            print_r($e->getMessage());
                        }
                        return $this->returnError('0003'); // se produjo un error
                    }
            }
        }else{
            return $this->notPermission();
        }
    }

    public function destroy($id){
        $user = $this->getUser();
        $permission = $this->checkPermission($user,$this->endpoint,__FUNCTION__);
        if($permission){
            try{
                $result = $this->model->deleteById($id, $user);
                if($result === false){
                    $this->returnError("0020");
                }
                return $this->returnOk("0011");
            }catch(Exception $e){
                if(env("APP_ENV") == "local"){
                    print_r($e->getMessage());
                }
                return $this->returnError('0003');
            }
        }else{
            return $this->notPermission();
        }
    }
}
