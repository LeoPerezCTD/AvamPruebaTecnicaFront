<?php

namespace App\Http\Controllers\util;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

// excel
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ReadAndWriteExcel extends Controller{

    public $spreadsheet;

    function __construct($file, $extension){
        if($file != null){
            $this->spreadsheet = IOFactory::load($file);
        }
    }

    function readFile($start,$end){
        $sheet  = $this->spreadsheet->getActiveSheet();

        $data   = $sheet->rangeToArray(
            $start.':'.$end,     // The worksheet range that we want to retrieve
            '',        // Value that should be returned for empty cells
            TRUE,        // Should formulas be calculated (the equivalent of getCalculatedValue() for each cell)
            TRUE,        // Should values be formatted (the equivalent of getFormattedValue() for each cell)
            TRUE         // Should the array be indexed by cell row and cell column
        );
        return $data;
    }

    function writeFile($filename,$data, $inicio){
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray(
            $data,  // The data to set
            NULL,        // Array values with this value will not be set
            $inicio//'C3'         // Top left coordinate of the worksheet range where
                         //    we want to set these values (default is A1)
        );
        // ob_clean();
        header("Pragma: public");
        header("Expires: 0");
        header('Content-type: application/vnd.ms-excel');
        header("Content-Disposition: attachment; filename=fichero.xls");
        header("Pragma: no-cache");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        $writer = new Xls($spreadsheet);
        $writer->save('php://output');
        exit;
    }




    /**
    * @internal 		Funcion Entrega los indices de columnas de un archivo excel. partiendo del numero de columnas.
    * @param 			Int 	$columnas numero de columnas del archivo.
    * @return 			Array 	$letras - contiene la nomenclatura de los indices de las columnas hasta donde se especifico por paramentro.
    *
    * @author 			Daniel Bolivar - dbolivar@processoft.com.co - daniel.bolivar.freelance@gmail.com
    * @version 			1.0.0
    * @since 			19-06-2019
    */
    function get_columnas($columnas){
        $letras = array();
        $index_vocabulary = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        if($columnas > 26){
            // $mod = $columnas%26; // si el mod es cero quiere decir que se esta pasando a otra combinacion de 2, 3, 4... n combinaciones.
            $combinaciones = intval($columnas / 26); 	// numero de letras combinadas.
            $estado_combinaciones = 0; 					// comienza siempre en 1 por que estamos en posiciones de columnas mayor a 26. 
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

}