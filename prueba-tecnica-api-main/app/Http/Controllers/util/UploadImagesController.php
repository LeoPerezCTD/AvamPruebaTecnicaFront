<?php

namespace App\Http\Controllers\util;

use App\Http\Controllers\Controller;
use Aws\S3\S3Client;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;
// use Exception;

class UploadImagesController extends Controller {

    public function chargeImages(Request $request) {
        $objData = json_decode($request->getContent());
        $images = $objData->images;
        // print_r($images);
        $boldUploads = new BoldUploadsFiles();
        $result = $boldUploads->uploadsFilesBase64S3($images, "images/products/");
        // $result = $boldUploads->uploadFilesBase64($images, "images/products/");
        print_r($result);


        /* // carga imagenes con solicitudes PUT o POST. desde $_FILES
        if (isset($_FILES['images'])) {
            $images = $_FILES['images'];
            $boldUploads = new BoldUploadsFiles();


            $result = $boldUploads->uploadsFiles($images, "/images/products/");

            // obtengo resultado, que las imagenes fueron guardadas.
            // creo un grupo de imagenes. db.
            // cargo imagenes en db.

            print_r($result);
        } */
    }

    public function listObjects(Request $request) {
        $bucket_name = env("AWS_BUCKET");
        $s3client = new S3Client([
            'region' => env("AWS_DEFAULT_REGION"),
            'version' => 'latest',
            'profile' => env('AWS_PROFILE')

        ]);
        $versions = $s3client->listObjectVersions(['Bucket' => $bucket_name]);
        echo "<pre>";
        foreach($versions['Versions'] as $object){
            print_r($object);
        }
    }
}
