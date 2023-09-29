<?php

namespace App\Http\Controllers\util;

use Aws\Ses\SesClient; 
use Aws\Exception\AwsException;
use App\Http\Controllers\MyController;
// use Illuminate\Http\Request;
use Exception;

class BoldSESController extends MyController{
    private $sesClient;

    public function __construct(){
        $this->sesClient = new SesClient([
            'version' => 'latest',
            'profile'   => env('AWS_PROFILE'),
            'region'    => env('AWS_REGION')
        ]); 
    }

    private function configEmail($to,$subject,$body){
        return [
            'Destination' => [
                'ToAddresses' => [$to],
            ],
            'Message' => [
                'Body' => [
                    'Html' => [
                        'Charset' => 'UTF-8',
                        'Data' => $body,
                    ],
                ],
                'Subject' => [
                    'Charset' => 'UTF-8',
                    'Data' => $subject,
                ],
            ],
            'Source' => env('FROM_EMAIL')
        ];
    }

    public function sendEmail($to, $subject, $body){
        $params = $this->configEmail($to,$subject, $body);
        try {
            $result = $this->sesClient->sendEmail($params);
            return $result['MessageId'];
        } catch (AwsException $e) {
            $this->showException($e);
            // throw new Exception("Error al enviar el correo electrónico: " . $e->getMessage());
        }
    }

    public function sendEmailGet(){
        $from = 'development@barberlytics.com';
        $to = 'daniel.bolivar.freelance@gmail.com';
        $subject = 'Estado de suscripción';
        $body = 'Status: In process';
        return $this->sendEmail($from,$to,$subject,$body);
    }
}