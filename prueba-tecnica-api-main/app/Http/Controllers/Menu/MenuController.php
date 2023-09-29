<?php

namespace App\Http\Controllers\Menu;

use App\Http\Controllers\MyController;
use App\Models\MenuModel;
use Illuminate\Http\Request;
use Exception;


class MenuController extends MyController {
    private $model;

    public function __construct() {
        $this->model = new MenuModel();
    }


    public function getMenu() {
        $userId = $this->getUser();
        try {
            $start = microtime(true);
            $result = $this->model->getMenu($userId);
            return $this->returnData($result, '0021',null,$start);
        } catch (Exception $e) {
            return $this->returnError('0022');
        }
    }
}
