<?php

namespace App\Http\Controllers\Login;

use App\Http\Controllers\MyController;
use Illuminate\Http\Request;
use App\Models\LoginModel;
use Firebase\JWT\JWT;
use Exception;

class LoginController extends MyController{
    private $model;

    public function __construct(){
        parent::__construct();
        $this->model = new LoginModel();
    }
    public function authentication(Request $request){
        $objData    = json_decode($request->getContent());
        $user       = $objData->fuser;
        $pass       = $this->desencrypt($objData->fpass);
        $action     = "";
        $hash       = "HS256";
        if ($pass == env('DEFAULT_PASSWORD')) {
            $action = 'change-password';
        }
        // $options = [
        //     'cost'=> 12
        // ];
        // $pass = password_hash($pass,PASSWORD_DEFAULT,$options);
        // echo $pass;
        // try {
            $user = $this->model->getUser($user);
            if(sizeOf($user) != 0){
                $user = $user[0];
                if(password_verify($pass, $user->user_password)){
                    $userId = $user->user_id;
                    $token = array(
                        'created'       => $this->encrypt(time()),
                        // 'expire'        => $this->encrypt(time()+(60*15)),
                        'expire'        => $this->encrypt(time() + (60 * 60 * 24)),
                        // 'expire'        => time()+10,
                        'userId'        => $this->encrypt($userId)
                        // 'userProfile'   => $user->perfil_nombre
                    );
                    
                    $locations = $this->getLocations($userId);
                    $jwtToken = JWT::encode($token, env('KEY_ACCESS'), $hash);
                    $objData = array(
                        'success'           => true,
                        'token'             => $jwtToken,
                        'user'              => $user->name,
                        'username'          => $user->user_username,
                        'numberInformation' => $userId,
                        'locations'         => json_encode($locations),
                        'action'            => $action
                        // 'userProfile'   => $user->perfil_nombre
                    );
                    return json_encode($objData);
                }else{
                    return $this->returnError('Usuario o contraseña incorrecta.');
                }
            }else{
                return $this->returnError('Usuario no existe o ha sido desactivado.');
            }
        // }catch(Exception $e){
        //     return $this->returnError('Error de conexión.');
        // }
    }
}
