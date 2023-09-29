<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\AppModel;
use Exception;

class MyController extends Controller{
    private $appModel;
    public $start;

    public function __construct(){
        $this->appModel = new AppModel();
    }
    
    public function returnData($data,$message=null,$addData=null,$startTime=null){
        if($startTime!==null){
            $this->start = $startTime;
        }
        $end = microtime(true);
        $time = $end - $this->start;
        if(sizeof($data)>0 || $addData != null){
            if($addData != null){
                if(sizeof($data)>0){
                    $objData = array(
                        'success'   => true,
                        'data'      => $data,
                        'duration'  => $time." seconds"
                    );
                }else{
                    $objData = array(
                        'success'   => true,
                        'data'      => null,
                        'duration'  => $time." seconds"
                    );
                }
                foreach($addData as $key => $val){
                    $objData[$key] = $val;
                }
                return json_encode($objData);
            }else{
                $objData = array(
                    'success'   => true,
                    'data'      => $data,
                    'duration'  => $time." seconds"
                );
            }
            return json_encode($objData);
        }else{
            $objData = array(
                'success'   => false,
                'data'      => null,
                'message'   => $message,
                'duration'  => $time." seconds"
            );
            return json_encode($objData);
        }
    }

    public function returnCreated($data,$msg){
        $end = microtime(true);
        $time = $end - $this->start;
        $objData = array(
            'success'   => true,
            'message'   => $msg,
            'data'      => $data[0],
            'duration'  => $time." seconds"
        );
        return response()->json($objData,201);
    }

    public function returnOk($message){
        $end = microtime(true);
        $time = $end - $this->start;
        $objData = array(
            'success'   => true,
            'message'   => $message,
            'duration'  => $time." seconds"
        );
        return json_encode($objData);
    }

    public function returnError($msg,$errorCode = '400'){
        $end = microtime(true);
        $time = $end - $this->start;
        $objData = array(
            'success'   =>false,
            'message'   =>$msg,
            'code'      => $errorCode,
            'duration'  => $time." seconds"
        );
        return response()->json($objData,$errorCode);
        // return response()->json($objData);
    }


    public function desencrypt($var){
        return base64_decode(base64_decode($var));
    }

    public function encrypt($var){
        return base64_encode(base64_encode($var));
    }

    public function getUser(){
        // obtengo cabecera
        $header = apache_request_headers();

        if(isset($header['Authorization']) || isset($header['authorization'])){
            $authorization = isset($header['Authorization']) ? $header['Authorization'] : $header['authorization'];
            try{
                $authorization = JWT::decode($authorization,new Key(env('KEY_ACCESS'),'HS256'));
                return $this->desencrypt($authorization->userId);
            }catch(Exception $e){
                echo $this->returnError('Acceso denegado');
                exit();
            }
        }else{
            // enviar a iniciar session.
            exit();
        }
    }
    public function getLocation($user){
        $location = $this->appModel->getLocation($user);
        return $location[0]->location_id;
    }
    public function getLocations($user){
        $locations = $this->appModel->getLocations($user);
        return $locations;
    }

    public function getCompany($user){
        $company = $this->appModel->getCompany($user);
        // TODO esta trayendo varias companias, solo espero recibir una
        return $company[0]->company_id;
    }

    public function getCompanyName($company){
        $company = $this->appModel->getCompanyName($company);
        return $company[0]->company_name;
    }

    public function getCompanyByLocation($locationId){
        $company = $this->appModel->getCompanyByLocation($locationId);
        return $company[0]->company_id;
    }

    public function checkPermission($user,$endpoint,$function){
        $this->start = microtime(true);
        $permissions = $this->appModel->checkPermission($user,$endpoint);
        if(sizeof($permissions) > 0){
            $permissions = explode('|',$permissions[0]->permissions);
            switch($function){
                case 'index':
                    return in_array('view',$permissions,true);
                break;
                case 'show':
                    return in_array('view',$permissions,true);
                break;
                case 'store':
                    return in_array('create',$permissions,true);
                break;
                case 'update':
                    return in_array('update',$permissions,true);
                break;
                case 'destroy':
                    return in_array('delete',$permissions,true);
                break;
                default:
                    return false;
                break;
            }
        }
        return false;
    }

    public function notPermission(){
        $end = microtime(true);
        $time = $end - $this->start;
        $objData = array(
            'success'   =>false,
            'code'      => '0001',
            'message'   => "No tiene, permisos para ejecutar esta acción",
            'duration'  => $time." seconds"
        );
        return json_encode($objData);
    }

    public function getIp(){
        if (isset($_SERVER["HTTP_CLIENT_IP"])){
            return $_SERVER["HTTP_CLIENT_IP"];
        }elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"])){
            return $_SERVER["HTTP_X_FORWARDED_FOR"];
        }elseif (isset($_SERVER["HTTP_X_FORWARDED"])){
            return $_SERVER["HTTP_X_FORWARDED"];
        }elseif (isset($_SERVER["HTTP_FORWARDED_FOR"])){
            return $_SERVER["HTTP_FORWARDED_FOR"];
        }elseif (isset($_SERVER["HTTP_FORWARDED"])){
            return $_SERVER["HTTP_FORWARDED"];
        }else{
            return $_SERVER["REMOTE_ADDR"];
        }
    }



    public function getState($country){
        try{
            $result = $this->appModel->getEstate($country);
            if($result=== false){
                return $this->returnError("0012");
            }
            return $this->returnData($result, '0008');
        }catch(Exception $e){
            return $this->returnError('0003');
        }
    }

    public function getCity($country, $state){
        try{
            $result = $this->appModel->getCity($country, $state);
            if($result=== false){
                return $this->returnError("0013");
            }
            return $this->returnData($result, '0008');
        }catch(Exception $e){
            return $this->returnError('0003');
        }
    }

    public function getUsuarios(){
        try{
            $result = $this->appModel->getUsuarios();
            return $this->returnData($result);
        }catch(Exception $e){
            return $this->returnError('Error al consultar Edificios');
        }
    }
    
    
    
    public function getProfile($user){
        try{
            $result = $this->appModel->getProfile($user);
            return $result[0]->profile_name;
        }catch(Exception $e){
            return $this->returnError('Error when consulting user profile');
        }
    }

    public function getProfileId($user){
        try{
            $result = $this->appModel->getProfileId($user);
            return $result[0]->profile_id;
        }catch(Exception $e){
            return $this->returnError('Error when consulting user profile');
        }
    }

    // GENERALES
    /**
	* @internal 		Funcion Entrega los indices de columnas de un archivo excel. partiendo del numero de columnas.
	* @param 			Int 	$columnas numero de columnas del archivo.
	* @return 			Array 	$letras - contiene la nomenclatura de los indices de las columnas hasta donde se especifico por paramentro.
	*
	* @author 			Daniel Bolivar - dbolivar@processoft.com.co - daniel.bolivar.freelance@gmail.com
	* @version 			1.0.0
	* @since 			19-06-2019
	*/
	public function getColumns($columnas){
		$letras = array();
		$index_vocabulary = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		if($columnas > 26){
			// $mod = $columnas%26; // si el mod es cero quiere decir que se esta pasando a otra combinacion de 2, 3, 4... n combinaciones.
			$combinaciones = intval($columnas / 26); 	// numero de letras combinadas.
			$estado_combinaciones = 0; // comienza siempre en 1 por que estamos en posiciones de columnas mayor a 26.
			$posicion = 0;
			while($posicion <= $columnas){
				//$iterador_array = 26 * $estado_combinaciones - $columnas[posicion];
				if($posicion <26){
					$letras[] = substr($index_vocabulary,$posicion, 1);
					if($posicion == 25){
						$estado_combinaciones++;
					}
					$posicion++;
				}else{
					//$iterador_array = intval($columnas/26);
					for ($iterador=0; $iterador < $combinaciones ; $iterador++) { 
						// recorro 26 veces 
						// menos cuando ya se excede el numero de la posicion
						for ($i=0; $i < 26 ; $i++) { 
							$pos = $posicion - 26 * $estado_combinaciones;
							$letras[] = $letras[$iterador].substr($index_vocabulary,$pos,1);
							$posicion++;
						}
						$estado_combinaciones++;
					}
				}
			}
		}else{
			for($i=0; $i < $columnas; $i++) { 
				$letras[]=substr($index_vocabulary, $i,1);
			}
		}
		return $letras;
	}

    /**
     * @internal    Genera una llave aleatoria alfanumerica.
     * @param       length largo de la llave a generar.
     */
    public function generateKey($length){
        $permittedChars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $key = "";
        $strlength = strlen($permittedChars);
        for($i = 0; $i < $length; $i++){
            $key.= $permittedChars[mt_rand(0,$strlength-1)];
        }
        return $key;
    }


    public function getDependencies($dependencies,$result){
        $data = [];
        foreach($dependencies as $value){
            switch($value){
                case 'estados':
                    $response = $this->appModel->getEstate($result->pais_codigo);
                    $data['estados'] = $response;
                break;
                case 'ciudades':
                    $response = $this->appModel->getCity($result->pais_codigo,$result->estado_codigo);
                    $data['ciudades'] = $response;
                break;
                default:
                break;
            }
        }
        return $data;
    }
    public function showException($e){
        if(env("APP_ENV") == "local"){
            print_r($e->getMessage());
        }
    }


}

