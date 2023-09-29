<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\MyController;
use Illuminate\Http\Request;
use App\Models\UsersConfigurationModel;
use Exception;

class UsersController extends MyController{
    private $model;
    private $modelPerson;
    private $endpoint = "configuration/users";

    function __construct(){
        $this->model = new UsersConfigurationModel();
    }

    public function index(Request $request){
        $user       = $this->getUser();
        $result     = $this->model->get($user);

        return $this->returnData($result);

    }

    public function store(Request $request){
        //
    }

    public function show($id){
        $user = $this->getUser();
        $permission = $this->checkPermission($user,$this->endpoint,__FUNCTION__);
        if($permission){
            try{
                $result     = $this->model->get($user, $id);
                return $this->returnData($result);
            }catch(Exception $e){
                return $this->returnError('Error getting the information'); 
            }
        }else{
            return $this->notPermission();
        }
    }

    public function update(Request $request, $id){
        $user = $this->getUser();
        $permission = $this->checkPermission($user,$this->endpoint,__FUNCTION__);
        if($permission){
            try{
                $objData = json_decode($request->getContent());
                $this->model->updateData($objData, $id, $user);
                return $this->returnOk("Successfully updated");
            }catch(Exception $e){
                return $this->returnError('Error updating the information'); 
            }
        }else{
            return $this->notPermission();
        }
    }

    public function destroy($id){
        //
    }
}
