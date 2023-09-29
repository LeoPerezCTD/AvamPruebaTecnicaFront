<?php

namespace App\Http\Controllers\util;

use App\Http\Controllers\Controller;
use App\Models\ServciesCategoriesModel;

use Exception;


class SlugGenerator extends Controller{

    private $model;

    public function __construct() {
        $this->model = new ServciesCategoriesModel();
    }

    public function generateSlug($name, $companyName) {
        $companyName = preg_replace('/[^a-zA-Z0-9]/', '', $companyName);
        $name = preg_replace('/[^a-zA-Z0-9]/', '', $name);

        $result = $this->model->slug($companyName.'_'.$name);

        if (empty($result)) {
            $slugGenerate = $companyName.'_'.$name;
        } else {
            $slugGenerate = $companyName.'_'.$name.'_1';
        }

        return $slugGenerate;
    }

}

?>