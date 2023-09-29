<?php

namespace App\Http\Controllers\util;

use Aws\Sns\SnsClient; 
use Aws\Exception\AwsException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BoldSMSController extends Controller{

    public function sendOTP($phone,$otp, $msg = null){
        $client = new SnsClient([
            'profile'   => env('AWS_PROFILE'),
            'region'    => env('AWS_REGION'), // Reemplaza con la región correcta
            'version'   => 'latest',
        ]);

        if($msg == null){
            $msg = "Barberlytics ha generado el siguiente codigo de acceso: ".$otp;
        }
        try {
            $result = $client->SetSMSAttributes([
                'attributes' => [
                    'DefaultSMSType' => 'Transactional',
                ],
            ]);
            $result = $client->publish([
                'Message'       => $msg,
                'PhoneNumber'   => $phone,
                'MessageAttributes' => [
                    'AWS.SNS.SMS.SMSType' => [
                        'DataType' => 'String',
                        'StringValue' => 'Transactional'
                    ]
                ]
            ]);

            return true;
            // TODO: enviar a un log centralizado el resultado del envio de mensaje o error
            //echo 'Mensaje de texto enviado correctamente. ID de mensaje: ' . $result['MessageId'];
        } catch (AwsException $e) {
            return false;
            // echo 'Error al enviar el mensaje de texto: ' . $e->getMessage();
        }
    }
    
    public function index(){
        $client = new SnsClient([
            'profile' => 'barberlytics',
            // 'credentials' => [
            //     'key' => 'AKIAR75SZIYDQJUO24F3',
            //     'secret'=> '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'
            // ],
            'region' => 'us-east-1', // Reemplaza con la región correcta
            'version' => 'latest',
        ]);
        
        $message = 'Hola, este es un mensaje de prueba de Barberlytics.'; // Mensaje de texto a enviar
        $phoneNumber = 'numero'; // Número de teléfono de destino
        
        try {
            $result = $client->SetSMSAttributes([
                'attributes' => [
                    'DefaultSMSType' => 'Transactional',
                ],
            ]);
            $result = $client->publish([
                'Message' => $message,
                'PhoneNumber' => $phoneNumber,
                'MessageAttributes' => [
                    'AWS.SNS.SMS.SMSType' => [
                        'DataType' => 'String',
                        'StringValue' => 'Transactional'
                    ]
                ]
            ]);
        
            echo 'Mensaje de texto enviado correctamente. ID de mensaje: ' . $result['MessageId'];
        } catch (AwsException $e) {
            echo 'Error al enviar el mensaje de texto: ' . $e->getMessage();
        }
    }
}
