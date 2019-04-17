<?php
namespace console\behaviors\base;

use console\behaviors\BaseBehavior;

class CateBehavior extends BaseBehavior {

    public function getModels_CateAdd() {
        return array(
            'console\models\base\InitData_BaseCate' => 'cateAdd',
            'console\models\base\BaseProductCategory'
        );
    }
    
    public function getModels_CateEdit() {
        return array(
            'console\models\base\InitData_BaseCate' => 'cateEdit',
            'console\models\base\BaseProductCategory'
        );
    }    

    public function getModels_CateDelete() {
        return array(
            'console\models\base\InitData_BaseCate' => 'cateDelete',
            'console\models\base\BaseProductCategory',
        );
    }

    public function getModels_CateList() {
        return array(
            'console\models\base\BaseProductCategory'
        );
    }   

}
