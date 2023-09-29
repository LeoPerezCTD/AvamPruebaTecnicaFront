<?php

namespace App\Http\Controllers\util;

use App\Http\Controllers\Controller;
use Aws\Exception\AwsException;
use Aws\S3\S3Client;
use Exception;

class BoldUploadsFiles extends Controller {

    function saveFile($file, $route, $extensiones) {
        $filename   = $file['name'];
        $type       = $file['type'];
        $size       = $file['size'];

        $point      = strpos($filename, ".");
        $extension  = substr($filename, $point + 1);
        $filename   = substr($filename, 0, $point);

        $valid      = false;
        $date       = Date('Y_m_d_h_i_s');
        foreach ($extensiones as $val) {
            if ($extension == $val) {
                $valid = true;
            }
        }
        if ($valid == false) {
            // TODO mostrar error de carga, extension no compatible
            throw new Exception("Error: Extension de fichero no es aceptada", 1);
        }

        $filename = $date . "-" . $filename . "." . $extension;


        if ($size <= 102400) {
            if (!is_dir($route)) {
                mkdir($route);
            }
            move_uploaded_file($file['tmp_name'], $route . $filename);
        } else {
            return array(
                'success'   => false
            );
        }
        return array(
            'success'       => true,
            'filename'      => $filename,
            'extension'     => $extension
        );
    }


    /** 
     * @author Daniel Bolivar - debb94, github
     * @version v1.0.0
     * @since 27-01-2023
     * @internal Metodo para guardar ficheros.
     * @param $files Ficheros a guardar provenientes de $_FILES[]
     * @param $path Ruta donde se van a almacenar los ficheros
     * @return $uploads[] Array con las uri de los recursos guardados en el servidor.
     */
    public function uploadsFiles($files, $path) {
        $uploads = [];
        $quantity = count($files['tmp_name']);
        // chmod("./" . $path, 0777);
        for ($i = 0; $i < $quantity; $i++) {
            $infoFile = explode(".", $files['name'][$i]);
            $ext = $infoFile[$quantity - 1];
            $filename = sha1($infoFile[0] + round(microtime(true) * 1000));
            $uri = "./" . $path . $filename . "." . $ext;
            $temp = $files['tmp_name'][$i];
            if (move_uploaded_file($temp, $uri)) {
                $uploads[] = env("APP_URL").$uri;
            } else {
                return "error";
            }
        }
        return $uploads;
    }

    /**
     * @author Daniel Bolivar - debb94, github
     * @version v1.0.0
     * @since 27-01-2023
     * @internal Metodo para guardar ficheros de tipo base64.
     * @param $files Ficheros a guardar provenientes de un objeto con src base64
     * @param $path Ruta donde se van a almacenar los ficheros
     * @example objeto de entrada: [{name:..., src:(base64)}]
     * @return $uploads[] Array con las uri de los recursos guardados en el servidor.
     */
    public function uploadFilesBase64($files, $path) {
        $uploads = [];
        foreach ($files as $file) {
            $infoFile = explode(".", $file->name);
            $ext = $infoFile[count($infoFile) - 1];
            $filename = sha1($infoFile[0] . round(microtime(true) * 1000));
            $uri = $path . $filename . "." . $ext;

            $data = explode(";base64,", $file->src);
            $data = base64_decode($data[1]);
            file_put_contents($uri, $data);
            $uploads[] = env("APP_URL").$uri;
        }
        return $uploads;
    }

    /**
     * @author Daniel Bolivar - debb94, github
     * @version v1.0.0
     * @since 04-06-2023
     * @internal Metodo para guardar ficheros de tipo base64 en bucket S3.
     * @param $files Ficheros a guardar provenientes de un objeto con src base64
     * @param $path Ruta donde se van a almacenar los ficheros
     * @example objeto de entrada: [{name:..., src:(base64)}]
     * @return $uploads[] Array con las uri de los recursos guardados en el servidor.
     */
    public function uploadsFilesBase64S3($files, $path){
        $bucket = env("AWS_BUCKET");
        $s3client = new S3Client([
            'region' => env("AWS_DEFAULT_REGION"),
            'version' => 'latest',
            'profile' => env('AWS_PROFILE')
        ]);
       
        $uploads = [];
        foreach ($files as $file) {
            // url
            if(substr($file->src,0,4)== 'http'){
                $uploads[] = $file->src;
            }else{ // file
                $infoFile = explode(".", $file->name);
                $ext = $infoFile[count($infoFile) - 1];
                $filename = sha1($infoFile[0] . round(microtime(true) * 1000));
                $uri = $path . $filename . "." . $ext;
                $data = explode(";base64,", $file->src);
                $data = base64_decode($data[1]);
                
                file_put_contents($uri, $data);
                
                try {
                    $s3client->putObject([
                        'Bucket' => $bucket,
                        'Key' => $uri,
                        'SourceFile' => $uri
                    ]);
                    unlink($uri);
                } catch (Exception $exception) {
                    unlink($uri);
                    echo "Failed to upload $filename with error: " . $exception->getMessage();
                    exit("Please fix error with file upload before continuing.");
                }
                $uploads[] = env("AWS_PATH_BUCKET").$uri;
            }
        }
        return $uploads;
    }


    /**
     * @author Daniel Bolivar - debb94, github
     * @version v1.0.0
     * @since 24-08-2023
     * @internal Metodo para eliminar ficheros en bucket S3.
     * @param $keys clave que identifica el fichero en el bucket.
     * @return null
     */
    public function deleteFilesS3($keys){
        $bucket   = env("AWS_BUCKET");

        $s3client = new S3Client([
            'region' => env("AWS_DEFAULT_REGION"),
            'version' => 'latest',
            'profile' => env('AWS_PROFILE')
        ]);
       
        foreach($keys as $key){
            try{
                $img = str_replace(env('AWS_PATH_BUCKET'),'',$key->image_url);
                $result = $s3client->deleteObject([
                    'Bucket' => $bucket,
                    'Key' => $img
                ]);
            }catch(AwsException $e){
                // TODO: Enviar a log centralizado
                echo "Error al eliminar la imagen: ".$e->getMessage();
            }
        }
    }

    /**
     * @author Daniel Bolivar - debb94, github
     * @version v1.0.0
     * @since 24-08-2023
     * @internal Metodo para validar imagenes, las que deseo crear y las que existen.
     * @param $images Ficheros o imagenes que deseo cargar
     * @param $uploadedImages Ficheros o imagenes existentes en base de dato
     * @return Array con tres arreglos internos que hacen referencia a: imagesToDelete, imagesToUpdate, imagesToCreate.
     */
    public function validateImages($images,$uploadedImages){
        $imagesToCreate = [];
        $imagesToUpdate = [];
        $imagesToDelete = [];

        // si grupo de imagenes esta vacio solo debo crear.
        if($uploadedImages == null || sizeof($uploadedImages) == 0){
            // crear todas las imagenes.
            foreach($images as $key => $image){
                $imagesToCreate[] = $image;
            }
        }else{
            $existImageId = [];
            foreach($images as $key => $image){
                $exist = false; // si la imagen no existe.
                foreach($uploadedImages as $key2 => $uploadedImage){
                    // si la imagen cambia de posicion.
                    if($image->src == $uploadedImage->image_url){
                        if($key != $key2){
                            $img = new \stdClass();
                            $img->image_id      = $uploadedImage->image_id;
                            $img->image_order   = $key+1;
                            $imagesToUpdate[] = $img;
                        }
                        $exist = true;
                        // guardo posiciones de imagenes para eliminar.
                        $existImageId[] = $uploadedImage->image_id;
                    }
                }
                // creo las imagenes que no existen
                if(!$exist){
                    $imagesToCreate[] = $image;
                }
            }
            foreach($uploadedImages as $key => $image){
                if(!in_array($image->image_id,$existImageId,true)){
                    $imagesToDelete[] = $image;
                }
            }
        }

        return [
            "imagesToDelete" => $imagesToDelete,
            "imagesToUpdate" => $imagesToUpdate,
            "imagesToCreate" => $imagesToCreate
        ];
    }
}
