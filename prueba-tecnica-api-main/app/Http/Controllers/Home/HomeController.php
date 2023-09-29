<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\MyController;
use Illuminate\Http\Request;
use App\Models\HomeModel;
use App\Models\AppModel;
use Exception;

class HomeController extends MyController{
    private $model;
    public  $appModel;
    
    public function __construct(){
        parent::__construct();
        $this->model    = new HomeModel();
        $this->appModel = new AppModel();
    }

    public function index(){
        try{
            $user       = $this->getUser();
            $profile    = $this->getProfile($user);
            $locations  = $this->getLocations($user);
            // $chart = null;
            // TODO pendiente configurar metricas iniciales del dashboard
            return "1";
            switch($profile){
                case 'Superuser':
                    // $result = $this->model->getMainDataAdminSuperusuario();
                    // $chart  = $this->model->getChartAdminSuperusuario();
                    // $perfil= 'admin';
                break;
                case 'Empresa administración':
                    $result = $this->model->getMainDataAdminEmpresa($user);
                    $chart  = $this->model->getChartAdminEmpresa($user);
                    $perfil= 'admin';
                break;
                case 'Administrador':
                case 'Junta':
                case 'Titular':
                    $result = $this->model->getMainData($user);
                    $perfil = 'comun';
                break;
                default:
                break;
            }
            $extra = [
                'locations' => $locations,
                // 'perfil'    => $perfil,
                // 'chart'     => $chart
            ];
            // return $this->returnData($result,null,$extra);
        }catch(Exception $e){
            return $this->returnError('Se produjo un error al consultar pantalla inicial');
        }
    }
}
