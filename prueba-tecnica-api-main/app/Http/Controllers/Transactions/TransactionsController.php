<?php

namespace App\Http\Controllers\Transactions;


use Illuminate\Http\Request;
use App\Http\Controllers\MyController;
use App\Models\TransactionsModel;
use Exception;

class TransactionsController extends MyController{
    public $model;
    public $orderModel;
    private $endpoint = 'transactions';

    public function __construct(){
        parent::__construct();
        $this->model = new TransactionsModel();
    }

    public function index(){
        $user = $this->getUser();
        $permission = $this->checkPermission($user,$this->endpoint,__FUNCTION__);
        if($permission){
            try{
                $result = $this->model->getTransactions();
                return $this->returnData($result, "No results");
            }catch(Exception $e){
                return $this->returnError('Error getting transactions');
            }
        }else{
            return $this->notPermission(); 
        }
    }

    public function store(Request $request){
        $user = $this->getUser();
        $permission = $this->checkPermission($user,$this->endpoint,__FUNCTION__);
        if($permission){
            try {
                $user = $this->getUser();
                $objData = json_decode($request->getContent());
    
                $objData->tx_transaction_response = json_encode($objData->tx_transaction_response);
                $objData->tx_token_card = json_encode($objData->tx_token_card);
                $objData->tx_card = json_encode($objData->tx_card);
    
                $transaction = $this->model->insertData($objData, $user);
                
                return $this->returnCreated($transaction, "Successfully");
            } catch (Exception $e) {
                return $this->returnError('Error, Error creating the product.');
            }
        }else{
            return $this->notPermission(); 
        }
        
    }

    public function show($id){
        $user = $this->getUser();
        $permission = $this->checkPermission($user,$this->endpoint,__FUNCTION__);
        if($permission){
            try{
                $response = $this->model->getTransactions($id);
                return $this->returnData($response);
            }catch(Exception $e){
                return $this->returnError('Error getting this transaction');
            }
        }else{
            return $this->notPermission(); 
        }
        
    }


    public function update(Request $request, $id){
        //TODO LLAMADO A PROCEDIMIENTO EN BD
    }

    public function destroy($id){
        // TODO PENDIENTE DE ACTIVAR
    }

}
