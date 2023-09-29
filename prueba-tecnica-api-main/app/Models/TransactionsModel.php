<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TransactionsModel extends Model{
    protected $table              = "transactions";
    protected $identificador      = "tx_id";
    protected $creador            = "tx_created_by";

    public $timestamps = false;

    public function getTransactions($id = null){
        if ($id == null) {
            $sql = "SELECT 
                        tr.*,
                        o.*,
                        pc.person_email AS email_created,
                        concat(pc.person_firstname,' ',pc.person_secondname,' ',pc.person_surname,' ',pc.person_secondsurname) AS name_created,
                        pm.person_email AS email_modified,
                        concat(pm.person_firstname,' ',pm.person_secondname,' ',pm.person_surname,' ',pm.person_secondsurname) AS name_modified
                    FROM transactions tr
                    LEFT JOIN orders o ON o.order_id = tr.order_id
                    LEFT JOIN users uc ON uc.user_id = o.order_created_by
                    LEFT JOIN people pc ON pc.person_id = uc.person_id
                    LEFT JOIN users um ON um.user_id = o.order_modified_by
                    LEFT JOIN people pm ON pm.person_id = um.person_id";
            $result = DB::select($sql);
        } else {
            $sql = "SELECT
                        tr.*,
                        o.*,
                        pc.person_email AS email_created,
                        concat(pc.person_firstname,' ',pc.person_secondname,' ',pc.person_surname,' ',pc.person_secondsurname) AS name_created,
                        pm.person_email AS email_modified,
                        concat(pm.person_firstname,' ',pm.person_secondname,' ',pm.person_surname,' ',pm.person_secondsurname) AS name_modified
                    FROM transactions tr
                    LEFT JOIN orders o ON o.order_id = tr.order_id
                    LEFT JOIN users uc ON uc.user_id = o.order_created_by
                    LEFT JOIN people pc ON pc.person_id = uc.person_id
                    LEFT JOIN users um ON um.user_id = o.order_modified_by
                    LEFT JOIN people pm ON pm.person_id = um.person_id
                    WHERE tr.tx_id = ?";
            $result = DB::select($sql, array($id));
        }
        return $result;
    }

    public function insertData($form, $user){
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
    }

    public function updateData($form, $id, $user, $identifier, $table, $act, $modify){
        $this->actualizador = $act;
        $this->factualizacion = $modify;
        // usuario actualizacion
        $form[$this->actualizador] = $user;
        // fecha actualizacion
        $form[$this->factualizacion] = 'now()';

        $this->identificador = $identifier;
        $this->table = $table;

        $sql = "UPDATE $this->table set ";
        $sqlSets = [];
        $sqlValues = [];
        foreach ($form as $key => $value) {
            $sqlSets[] = " $key = ? ";
            $sqlValues[] = $value;
        }
        $sqlSets = implode(',', $sqlSets);
        $sql .= $sqlSets . " where $this->identificador = ?";

        // id actualizacion
        $sqlValues[] = $id;
        return DB::update($sql, $sqlValues);
    }
}
