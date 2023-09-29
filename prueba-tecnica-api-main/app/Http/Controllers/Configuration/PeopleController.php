<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\MyController;
use Illuminate\Http\Request;
use App\Models\PeopleModel;
use Exception;

class PeopleController extends MyController{
    private $model;
    private $endpoint = "configuration/profile";

    function __construct(){
        parent::__construct();
        $this->model = new PeopleModel();
    }

    public function index(Request $request){
        $user = $this->getUser();
        $permission = $this->checkPermission($user,$this->endpoint,__FUNCTION__);
        if($permission){
            try{
                $action = $request->input('action');
                switch($action){
                    case 'getParamsUpdate':
                        try{
                            $result = $this->model->getParamsUpdate();
                            return $this->returnData($result);
                        }catch(Exception $e){
                            return $this->returnError('Error trying to get parameters'); 
                        }      
                    break;
                    default: 
                        try{
                            $result = $this->model->get($user);
                        }catch(Exception $e){
                            return $this->returnError('Error trying to get companies'); 
                        }                    
                    break;
                }
                return $this->returnData($result);
            }catch(Exception $e){
                return $this->returnError('Not controlled error'); 
            }
        }else{
            return $this->notPermission();
        }

    }

    public function store(Request $request){
        //
    }

    public function show($id){
        $user       = $this->getUser();
        $permission = $this->checkPermission($user,$this->endpoint,__FUNCTION__);

        if($permission && $user === $id){
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
        if($permission && $user === $id){
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
