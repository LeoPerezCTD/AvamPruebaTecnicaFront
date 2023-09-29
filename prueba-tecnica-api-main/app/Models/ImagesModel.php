<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Exception;

class ImagesModel extends Model{
    protected $table              = "images";
    protected $identificador      = "image_id";
    protected $actualizador       = "image_modified_by";
    protected $factualizacion     = "image_modified_at";
    protected $creador            = "image_created_by";

    protected $tableGroup          = "images_group";
    protected $identificadorGroup  = "imagegroup_id";
    protected $actualizadorGroup   = "imagegroup_modified_by";
    protected $factualizacionGroup = "imagegroup_modified_at";
    protected $creadorGroup        = "imagegroup_created_by";

    public $timestamps = false;
    
    
    public function getImagesByGroup($idGroup){
        try{
            $sql = "SELECT
                *
            from images im
            where im.imagegroup_id = ?
            ";
            return DB::select($sql,[$idGroup]);
        }catch(Exception $e){
            if(env("APP_ENV") == "local"){
                print_r($e->getMessage());
            }
            return false;
        }
    }

    public function insertData_($form, $user){


        $imageGroup[$this->creadorGroup] = $user;
        
        foreach ($imageGroup as $key => $value) {
            if ($value != '') {
                $sqlInsert[]    = $key;
                $sqlBind[]      = '?';
                $sqlValues[]    = $value;
            }
        }
        $sqlInsert = implode(',', $sqlInsert);
        $sqlBind = implode(',', $sqlBind);
        $sql = "INSERT INTO $this->tableGroup ($sqlInsert) values($sqlBind) returning imagegroup_id";
        
        $imgaeGroupId = DB::select($sql, $sqlValues);
        $imageGroupId = $imgaeGroupId[0]->imagegroup_id;

        foreach ($form as $key => $value) {
            $form[$key]['imagegroup_id'] = $imageGroupId;
            $form[$key][$this->creador] =  $user;
        }

        DB::table('images')->insert($form);

        return $imageGroupId;
    }

    public function insertData($form, $user){
        try{
            $form->{$this->creador} = $user;
      
            foreach ($form as $key => $value) {
                if ($value != '') {
                    $sqlInsert[]    = $key;
                    $sqlBind[]      = '?';
                    $sqlValues[]    = $value;
                }
            }
            $sqlInsert = implode(',', $sqlInsert);
            $sqlBind = implode(',', $sqlBind);
            $sql = "INSERT INTO $this->table ($sqlInsert) values($sqlBind) returning *";
    
            return DB::select($sql, $sqlValues);
        }catch(Exception $e){
            if(env("APP_ENV") == "local"){
                print_r($e->getMessage());
            }
            return false;
        }
        
    }

    public function updateData($form, $id, $user){
        $form->{$this->actualizador} = $user;
        $form->{$this->factualizacion} = 'now()';

        $sql = "UPDATE $this->table set ";
        $sqlSets = [];
        $sqlValues = [];

        foreach ($form as $key => $value) {
            $sqlSets[] = " $key = ? ";
            $sqlValues[] = $value;
        }
        $sqlSets = implode(',', $sqlSets);
        $sql .= $sqlSets . " where $this->identificador = ?";

        $sqlValues[] = $id;
        return DB::update($sql, $sqlValues);
    }

    public function deleteData($id,$user){
        try{
            $sql = "DELETE from $this->table WHERE $this->identificador = ?";
            return DB::delete($sql, array($id));
        }catch(Exception $e){
            if(env("APP_ENV") == "local"){
                print_r($e->getMessage());
            }
            return false;
        }
    }
}
