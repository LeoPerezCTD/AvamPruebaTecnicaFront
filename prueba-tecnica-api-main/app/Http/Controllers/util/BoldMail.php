<?php

namespace App\Http\Controllers\util;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// PHPmailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// PRODUCCION
define("CORREO", "info@app.com");
define("SMTP_AUTH", TRUE);
define("SMTP_SSL", "ssl");
define("SMTP_SERVER", "mail.app.com");
define("SMTP_PORT", 465);
define("SMTP_DEBUGGER",SMTP::DEBUG_OFF);
define("SMTP_USERNAME", "info@app.com");
define("SMTP_PASSWORD", "");
// Constantes gmail.
// define("CORREO", "info@gmail.com");
// define("SMTP_AUTH", TRUE);
// define("SMTP_SSL", "ssl");
// define("SMTP_SERVER", "smtp.gmail.com");
// define("SMTP_PORT", 465);
// define("SMTP_DEBUGGER",SMTP::DEBUG_OFF);
// define("SMTP_USERNAME", "info@gmail.com");
// define("SMTP_PASSWORD", ""); 

class BoldMail extends Controller{
    private $url_recovery;
    
    public function __construct(){
        if(env('APP_ENV')=='local'){
            $this->url_recovery = "localhost";
        }else{
            $this->url_recovery = env("APP_URL"); //"barberlytics.com";
        }
    }
    public function configMail($data){
        try{
            $mail = new PHPMailer(true);
            if(env('APP_ENV') == 'local'){
                $mail->isSMTP(); 
                $mail->SMTPDebug  = SMTP_DEBUGGER;
                $mail->Host       = SMTP_SERVER;
                $mail->SMTPAuth   = SMTP_AUTH;
                $mail->Username   = SMTP_USERNAME;
                $mail->Password   = SMTP_PASSWORD;
                $mail->SMTPSecure = SMTP_SSL;
                $mail->Port       = SMTP_PORT;
            }else{
                // godaddy
                $mail->isSMTP();                                    //Send using SMTP
                $mail->SMTPDebug  = SMTP_DEBUGGER;                  //Enable verbose debug output
                $mail->Host       = SMTP_SERVER;                    //Set the SMTP server to send through
                $mail->SMTPAuth   = SMTP_AUTH;                      //Enable SMTP authentication
                $mail->Username   = SMTP_USERNAME;                  //SMTP username
                $mail->Password   = SMTP_PASSWORD;                  //SMTP password      
                $mail->SMTPSecure = SMTP_SSL;                       //Enable implicit TLS encryption
                $mail->Port       = SMTP_PORT;
                $mail->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    )
                );
            }
            // CHARSET
            $mail->CharSet = 'UTF-8';
            // ENABLED HTML
            $mail->isHTML(true);
            // SUBJECT
            $subject        = utf8_decode($data['subject']);
            $mail->Subject  = utf8_encode($subject);

            // FROM
            if(isset($data['from'])){
                $mail->setFrom($data['from'],$data['from']);
            }else{
                $mail->setFrom(CORREO,'Barberlytics');
            }
            // DESTINATARIOS
            if(env('APP_ENV') == 'local'){
                $mail->AddAddress("daniel.bolivar.freelance@gmail.com");
            }else{
                if(is_array($data['recipient'])){
                    $recipients = $data['recipient'];
                    $recipients = array_unique($recipients);
                }else{
                    $recipients = explode(",", $data['recipient']);
                    $recipients = array_unique($recipients);
                }
                foreach($recipients as $recipient){
                    $mail->AddAddress(trim($recipient));
                }
            }
            return $mail;
        }catch(Exception $e){
            return false;
        }
    }

    /**
     * @param       $asunto_correo asunto
     * @param       $mensaje    mensaje HTML del correo
     * @param       $from       from desde donde se envia el mensaje
     * @param       $destino_correo array de correos destinatarios
     * @param       $tipo_correo    tipo de correo a enviar se buscan desde la base de dato o metodo que lo genera.
     * @param       $data       informacion a ser reemplazada en la plantilla de tipo de correo.
     */
    // public function sendMail($asunto_correo,$mensaje = null,$from="",$destino_correo,$tipo_correo,$data){
    public function sendMail($asunto_correo,$mensaje = null,$from="",$destino_correo){
        try{
            $asunto_correo  = utf8_decode($asunto_correo);
            $mensaje        = utf8_encode($mensaje);
            $data['subject']    = $asunto_correo;
            $data['']           = $mensaje;
            if($from!="" && $from != null){
                $data['from']   = $from;
            }else{
                $data['from']   = env('APP_NAME');
            }
            $data['recipient']  = $destino_correo;

            $mail = $this->configMail($data);

            //CARGUE DE LOGO
            // $logo_app       = dirname(__FILE__).'/../../../../public/img/indicar-color.svg';
            // $mail->AddEmbeddedImage($logo_app, 'logo_app', $logo_app, 'base64', 'image/png');

            //AGREGAR ADJUNTOS SOLO DIGITAR LA URL DEL ARCHIVO
            //$mail->AddAttachment("ruta-del-archivo/archivo.zip");

            // CORREOS BAJAS
            // $bajas          = array();
            //$bajas = $this->filtrarBaja($destino_correo);  // filtro aquellas personas que decidieron irse de baja

            // MENSAJE
            $mail->Body    = $mensaje;
            // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
            $mail->send();
            return true;
        }catch(Exception $e){
            echo "Mensaje no enviado. {$mail->ErrorInfo}";
            return false;
        }
    }

    /**
     * @internal Envio de correos a partir de un template.
     * @param   template string indica la plantilla que se va a enviar, posteriormente consultar de base de dato.
     * @param   data contiene la informacion necesaria para complementar la plantilla y datos de envio.
     */
    public function sendTemplate($template,$data){
        $mail = $this->configMail($data);
        if($mail != false){ //configuracion correcta
            $message = $this->returnMsgByTemplate($template,$data);
            if($message != false){
                // mensaje
                $mail->Body = $message;
                $mail->send();
                return true;
            }else{ // mensaje sin contenido
                return false;
            }
        }else{
            // echo "Mensaje no enviado. {$mail->ErrorInfo}";
            return false;
        }
    }

    public function returnMsgByTemplate($template,$data){
        try{
            switch($template){
                case 'WELCOME':
                    $msg = $this->header_msg().'
                    <tbody>
                        <tr>
                            <td colspan="6" style="padding: 0 30px;">
                                <h3>Welcome to Barberlytics</h3>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="12" style="padding: 0 30px;">
                                <p>Hi, '.$data["firstname"].'</p>
                                <p>We are glad that you start with us the path to the growth of your business, Barberlytics has a huge and passionate community, ready to see and celebrate all your growth, and we will accompany you at every step.</p>
                                <p>We invite you to start learning all the great features we have for you, you will realize that growing, maximizing and optimizing your business is very easy with Barberlytics.</p>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="6" style="padding: 20px 30px 60px 30px;">
                                <a href="#" style=" background: #F0B669; border-radius: 10px; padding: 10px 40px; border: none; width: 150px; font-weight: 600; text-decoration: none; color: inherit; margin: 30px 0">
                                    <img src="https://barberlytics-dev.s3.amazonaws.com/uploads/plus.png" width="10px"/>
                                    Start
                                </a>
                            </td>
                        </tr>
                    </tbody>
                    '.$this->footer_msg();
                break;
                case 'FORGOT-NUMBER':
                    $msg = $this->header_msg().'
                    <tbody>
                        <tr>
                            <td colspan="6" style="padding: 0 30px;">
                                <h3>Trouble logging in?</h3>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="12" style="padding: 0 30px;">
                                <p>Hi, '.$data["firstname"].'</p>
                                <p>You\'ve requested your contact number to enter the application.</p>
                                <p>At Barberlytics we\'ve found this number associated with your email:</p>
                                <h2>'.$data["phone"].'</h2>
                                <p>Continue growing, maximizing and optimizing your business is very easy with Barberlytics.</p>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="6" style="padding: 20px 30px 60px 30px;">
                                <a href="#" style="background: #F0B669; border-radius: 10px; padding: 10px 40px; border: none; width: 150px;font-weight: 600;text-decoration: none;color: inherit;margin: 30px 0">
                                    <img src="https://barberlytics-dev.s3.amazonaws.com/uploads/plus.png" width="10px"/>
                                    Login
                                </a>
                            </td>
                        </tr>
                    </tbody>
                    '.$this->footer_msg();
                break;
                case 'PASSWORD-RECOVERY':
                    $msg = $this->header_msg().'
                                            <br>
                                            <tr align="center">
                                                <td>
                                                    <h2 style="text-transform: capitalize;font-family:Open Sans, sans-serif;">Hola '.$data["client"].'</h2>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td align="center">
                                                    <br>
                                                    <b>Para completar el proceso de recuperación de tu contraseña te invitamos a que ingreses al siguiente link</b>
                                                    <br>
                                                    <a href="https://'.$this->url_recovery.'/login?reset-password='.$data["keyRecovery"].'" target="_blank" class="btn-green" style="text-decoration:none;font-weight:500;background:#007bff; color: white;padding: 5px 10px; display:inline-block;border-radius: 8px;">Recuperar Contraseña</a>
                                                </td>
                                            </tr>
                                            <tr width="100%">
                                                <td width="100%">
                                                    '.$this->footer_msg().'
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td width="15%">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </body>
                    </html>';
                break;
                case 'PAGO-EXTERNO':
                    $msg = $this->header_msg().'
                                            <br>
                                            <tr align="center">
                                                <td>
                                                    <h2 style="text-transform: capitalize;font-family:Open Sans, sans-serif;">Hola '.$data["client"].'</h2>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td align="center">
                                                    <br>
                                                    <h2 style="text-transform: capitalize;font-family:Open Sans, sans-serif;">Pago Recibido</h2>
                                                    <b>Hemos recibido tu pago, con el siguiente numero de comprobante: '.$data['comprobante'].'</b>
                                                    <br>
                                                    <b>Fecha de pago: '.$data['fecha'].'</b>
                                                    <a href="https://localhost/login?reset-password='.$data["keyRecovery"].'" target="_blank" class="btn-green" style="text-decoration:none;font-weight:500;background:#007bff; color: white;padding: 5px 10px; display:inline-block;border-radius: 8px;">Recuperar Contraseña</a>
                                                </td>
                                            </tr>
                                            <tr width="100%">
                                                <td width="100%">
                                                    '.$this->footer_msg().'
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td width="15%">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </body>
                    </html>';
                break;
                case 'APROBADO':
                    $msg = $this->header_msg().'
                                            <br>
                                            <tr align="center">
                                                <td>
                                                    <h2 style="text-transform: capitalize;font-family:Open Sans, sans-serif;">Estamos muy felices de ser tu aliado en la publicación de clasificados.</h2>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td align="center">
                                                    <br>
                                                    <b>Los compradores ya pueden ver tu clasificado. </b>
                                                    <br>
                                                </td>
                                            </tr>
                                            <tr>
                                                <p>Recuerda que publicar en INDICAR es gratis y que puedes publicar cuantos clasificados quieras.</p><br>
                                                <span><b>Publicación: #'.$data['codigo'].'.</b> Para ver, haz clic <a href="https://indicar.com.co/clasificado/detalle/'.$data['codigo_encriptado'].'" target"_blank">aquí</a></span>
                                            </tr>
                                            <tr width="100%">
                                                <td width="100%">
                                                    '.$this->footer_msg($data).'
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td width="15%">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </body>
                    </html>';
                break;
                case 'SUPPORT-MESSAGE':
                    $msg = $this->header_msg().'
                                            <br>
                                            <tr align="center">
                                                <td>
                                                    <h2 style="text-transform: capitalize;font-family:Open Sans, sans-serif;">Hola '.$data["name"].'</h2>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td align="center">
                                                    <br>
                                                    <h2 style="text-transform: capitalize;font-family:Open Sans, sans-serif;">Soporte</h2>
                                                    <b>Hemos recibido tu solicitud, en breve nos comunicaremos contigo a traves de los metodos de contacto proporcionados</b>
                                                    <br>
                                                </td>
                                            </tr>
                                            <tr width="100%">
                                                <td width="100%">
                                                    '.$this->footer_msg().'
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td width="15%">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </body>
                    </html>';
                break;
                case 'SUPPORT-MESSAGE-ADMIN':
                    $msg = $this->header_msg().'
                                            <br>
                                            <tr align="center">
                                                <td>
                                                    <h2 style="text-transform: capitalize;font-family:Open Sans, sans-serif;">Caso de soporte</h2>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td align="center">
                                                    <br>
                                                    <h2 style="text-transform: capitalize;font-family:Open Sans, sans-serif;">Contacto</h2>
                                                    <br>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <b>Nombre:</b> '.$data["name"].'<br>
                                                    <b>Teléfono:</b> '.$data["phone"].'<br>
                                                    <b>Email:</b> '.$data["mail"].'<br><br>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                <h3 style="text-transform: capitalize;font-family:Open Sans, sans-serif;">Mensaje</h3>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>'.$data["message"].'</td>
                                            </tr>
                                            <tr width="100%">
                                                <td width="100%">
                                                    '.$this->footer_msg().'
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td width="15%">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </body>
                    </html>';
                break;
            }
            return $msg;
        }catch(Exception $e){
            return false;
        }
    }


    /**
    *   @internal   CONSTRUYE EL HEADER DE LOS CORREOS.
    *   @author     Daniel Bolivar - debb94 github - dbolivar@processoft.com.co
    *   @version    1.0.0
    *   @since      20-03-2020
    */
    function header_msg(){
        /* $header = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
                    <html xmlns="http://www.w3.org/1999/xhtml"> */
        $header = '<!DOCTYPE html">
                    <html lang="en">
                        <head>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">
                            <style>
                                *{font-family: "Open Sans", sans-serif;}
                            </style>
                        </head>
                        <body style="font-family: Roboo, Arial, Helvetica, sans-serif; line-height: 1.5; font-size: 15px; text-align: justify; background:#fcfcfc;">
                            <div style="width: 500px; margin:auto">
                                <table role = "presentation"
                                    style="width:100%;
                                    border-collapse:collapse;
                                    border:0;
                                    border-spacing:0;
                                    background:#ffffff;">
                                    <thead >
                                        <tr>
                                            <td colspan="6" style="padding:30px 30px 0 30px;">
                                                <img src="https://barberlytics-dev.s3.amazonaws.com/uploads/Logo.png" alt="logo barberlytics"/>
                                            </td>
                                        </tr>
                                    </thead>       
                            ';
        return $header;
    }
    /**
    *   @internal   CONSTRUYE EL FOOTER DE LOS CORREOS.
    *   @author     Daniel Bolivar - debb94 github - dbolivar@processoft.com.co
    *   @version    1.0.0
    *   @since      20-03-2020
    */
    function footer_msg(){
        $footer = '<tfoot style="background: #F7F8FA;">
                        <tr >
                            <td colspan="12" style="padding: 20px 30px 5px 30px">
                                <h5>Connect with us</h5>
                            </td>
                        </tr>
                        <tr style="padding: 0 30px;">
                            <td style="padding-left: 30px;" colspan="1" align="center" >
                                <a href="#" target="_blank">
                                    <img style="height: 27px;" src="https://barberlytics-dev.s3.amazonaws.com/uploads/facebook.png" alt="facebook" /></a>
                                </a>
                            </td>
                            <td colspan="2" align="center">
                                <a href="#" target="_blank">
                                    <img src="https://barberlytics-dev.s3.amazonaws.com/uploads/instagram.png" alt="instagram" height="28px"/>
                                </a>
                            </td>
                            <td colspan="2" align="center" style="width: 120px;">
                                <a href="https://www.apple.com/co/app-store/" target="_blank">
                                    <img style="width: 110px;" src="https://barberlytics-dev.s3.amazonaws.com/uploads/appstore.png" alt="appstore" />
                                </a>
                            </td>
                            <td style="padding-right: 30px; width: 120px;"  colspan="2">
                                <a href="https://play.google.com/store/games" target="_blank">
                                    <img style="width: 120px;" src="https://barberlytics-dev.s3.amazonaws.com/uploads/googlestore.jpg" alt="googlestore"/>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="12" style="padding: 30px 30px;">
                                <a href="www.barbelytics.com" target="_blank"
                                    style="font-size: 14px; 
                                    font-weight: 600; 
                                    text-decoration: none; 
                                    color:#3165CC">
                                    www.barbelytics.com
                                </a>
                            </td>
                        </tr>
                    </tfoot>
                </table>
                </div>
            </body>
        </html>';
        return $footer;
    }

    public function return_msg_by_type($type, $data){
        switch($type){
            case 'RECHAZADO':
                $msg = $this->header_msg($type).'
                                        <br>
                                        <tr align="center">
                                            <td>
                                                <h2 style="text-transform: capitalize;font-family:Open Sans, sans-serif;">Hola '.$data["cliente"].'</h2>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="center">
                                                <br>
                                                <b>¡Gracias por elegirnos para tu publicación!</b>
                                                <br>
                                            </td>
                                        </tr>
                                        <tr>
                                            <p>Nos falta poco para publicar tu '.$data['linea'].', necesitamos tu ayuda corrigiendo lo sugerido a continuación</p><br>
                                            <b>Observaciones:</b><br>
                                            '.$data['motivos'].'
                                        </tr>
                                        <tr>
                                            <span>Para realizar los cambios, te invitamos a que leas <a href="https://www.indicar.com.co/server_indicar/servicios/libs/INSTRUCTIVO-EDITAR-UN-CLASIFICADO-EN-INDICAR.pdf" target="_blank">aquí</a></span><br>
                                            <span>Una vez hayas realizado las correcciones, INDICAR se encargará de la aprobación.</span>  
                                        </tr>
                                        <tr width="100%">
                                            <td width="100%">
                                                '.$this->footer_msg($data).'
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                                <td width="15%">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </body>
                </html>';
            break;
            case 'APROBADO':
                $msg = $this->header_msg($type).'
                                        <br>
                                        <tr align="center">
                                            <td>
                                                <h2 style="text-transform: capitalize;font-family:Open Sans, sans-serif;">Estamos muy felices de ser tu aliado en la publicación de clasificados.</h2>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="center">
                                                <br>
                                                <b>Los compradores ya pueden ver tu clasificado. </b>
                                                <br>
                                            </td>
                                        </tr>
                                        <tr>
                                            <p>Recuerda que publicar en INDICAR es gratis y que puedes publicar cuantos clasificados quieras.</p><br>
                                            <span><b>Publicación: #'.$data['codigo'].'.</b> Para ver, haz clic <a href="https://indicar.com.co/clasificado/detalle/'.$data['codigo_encriptado'].'" target"_blank">aquí</a></span>
                                        </tr>
                                        <tr width="100%">
                                            <td width="100%">
                                                '.$this->footer_msg($data).'
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                                <td width="15%">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </body>
                </html>';
            break;
        }
        return $msg;
    }

    private function filtrarBaja($destinoCorreo){
        // $bajas = array();
        $correosAux = explode(",", $destinoCorreo);       // convierto en array
        $correosAux = array_unique($correosAux);          // almaceno solo arreglos diferentes
        $correosAux = implode("','", $correosAux);        // convierto en string separados por '
        $correosAux = "'".$correosAux."'";                // agrego ' al principio y final.
        try{
            return DB::select("select * from frontend.ic_clientes_baja where cliente_correo in ({$correosAux})");
        }catch(Exception $e){
            $e->getMessage();
        }
    }
}