<?php

namespace App\Http\Middleware;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Http\Controllers\MyController;
use Closure;
use Exception;

class Authentication extends MyController{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next){
        $header = apache_request_headers();
        try{
            if(isset($header['Authorization'])){
                $jwt = $header['Authorization'];
            }else{
                $jwt = $header['authorization'];
            }
            $hash = "HS256";
            $key = env('KEY_ACCESS');
            $decode = JWT::decode($jwt,new Key($key,$hash));
            // $decode = JWT::decode($jwt,$key,array('HS256'));
            if(is_object($decode)){
                $expire  = $this->desencrypt($decode->expire);
                if($expire <= time()){
                    $objData = array(
                        'success'   => false,
                        'message'   => 'Su sesión ha caducado',
                        'action'    => 'closeSession'
                    );
                    echo json_encode($objData);
                    exit();
                }else{
                    return $next($request);
                }
            }else{
                $objData = array(
                    'success'   => false,
                    'message'   => 'Su sesión ha caducado',
                    'action'    => 'closeSession'
                );
                echo json_encode($objData);
                exit();
            }
        }catch(Exception $e){
            $objData = array(
                'success'   => false,
                'message'   => 'Su sesión ha caducado',
                'action'    => 'closeSession'
            );
            echo json_encode($objData);
            exit();
        }
    }
}
