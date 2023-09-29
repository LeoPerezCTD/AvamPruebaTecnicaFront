<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\MyController;
use App\Models\UsersLocationsModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DateInterval;
use DateTime;
use Exception;

class UsersLocationsController extends MyController{
    public $model;
    private $endpoint   = 'administration/users/locations';
    private $notFound = "Data not found";

    public function __construct(){
        parent::__construct();
        $this->model = new UsersLocationsModel();
    }

    public function index(Request $request){
        $user  = $this->getUser();
        $permission = $this->checkPermission($user, $this->endpoint, __FUNCTION__);

        if ($permission) {
            $action = $request->input('action');
            switch ($action) {
                case 'getParamsUpdate':
                    try {
                        $result = $this->model->getParamsUpdate();
                        return $this->returnData($result, $this->notFound);
                    } catch (Exception $e) {
                        return $this->returnError($this->notFound);
                    }
                    break;
                default:
                    try {
                        $result = $this->model->get($user);
                        return $this->returnData($result, $this->notFound);
                    } catch (Exception $e) {
                        return $this->returnError($this->notFound);
                    }
                    break;
            }
        } else {
            return $this->notPermission();
        }
    }

    public function store(Request $request){
        $user = $this->getUser();
        $permission = $this->checkPermission($user, $this->endpoint, __FUNCTION__);
        if ($permission) {
            try {
                $objData = json_decode($request->getContent(), true);
                $result = $this->model->insertData($objData, $user);
                return $this->returnOk('Successfully created');
            } catch (Exception $e) {
                return $this->returnError('Error creating company register');
            }
        }else {
            return $this->notPermission();
        }
        
    }

    public function show($id){
        $user  = $this->getUser();
        $permission = $this->checkPermission($user, $this->endpoint, __FUNCTION__);

        if ($permission) {
            try {
                $result = $this->model->get($user, $id);
                return $this->returnData($result, $this->notFound);
            } catch (Exception $e) {
                return $this->returnError($this->notFound);
            }
        } else {
            return $this->notPermission();
        }
    }

    public function update(Request $request, $id){
        $action     = $request->input('action');
        $user  = $this->getUser();
        $permission = $this->checkPermission($user, $this->endpoint, __FUNCTION__);

        if ($permission) {
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
                        
                        return $this->returnOk('Successfully updated');
                    } catch (Exception $e){
                        return $this->returnError('Error, Update failed.');
                    }
                break;
                default:
                try {
                    $objData = json_decode($request->getContent());
                    $this->model->updateData($objData, $id, $user);
                    return $this->returnOk("Successfully updated");
                } catch (Exception $e) {
                    return $this->returnError('Error updating the register');
                }
            }
        } else {
            return $this->notPermission();
        }
    }


}
