<?php
namespace console\behaviors\base;

use console\behaviors\BaseBehavior;

class ProductBehavior extends BaseBehavior {

    public function getModels_ProductAdd() {
        return array(
            'console\models\base\InitData_BaseProduct' => 'productAdd',
            'console\models\base\BaseProduct' => 'add',
            'console\models\base\BaseProductAttr' => 'add',
            'console\models\base\BaseProductAttrItem' => 'add',
            'console\models\base\BaseProductOperate' => 'add',
        );
    }
    
    public function getModels_ProductEdit() {
        return array(
            'console\models\base\InitData_BaseProduct' => 'productEdit',
            'console\models\base\BaseProduct' => 'modify',
            'console\models\base\BaseProductAttr' => 'deleteAdd',
            'console\models\base\BaseProductAttrItem' => 'deleteAdd',
            'console\models\base\BaseProductOperate' => 'add',            
        );
    }   
            
    public function getModels_ProductDisplay() {
        return array(
            'console\models\base\InitData_BaseProduct' => 'productDisplay',
            'console\models\base\BaseProduct' => 'modify',
            'console\models\base\BaseProductOperate' => 'add',            
        );
    }    

    public function getModels_ProductDelete() {
        return array(
            'console\models\base\InitData_BaseProduct' => 'productDelete',
            'console\models\base\BaseProduct' => 'delete',
            'console\models\base\BaseProductAttr' => 'delete',
            'console\models\base\BaseProductAttrItem' => 'delete',
            'console\models\base\BaseProductCommissionQuot' => 'delete',
            'console\models\base\BaseProductCommissionPercentage' => 'delete',            
            'console\models\base\BaseProductOperate' => 'add', 
        );
    }
    
    public function getModels_ProductCommission() {
        return array(
            'console\models\base\InitData_BaseProduct' => 'productCommission',
            'console\models\base\BaseProduct' => 'modify',
            'console\models\base\BaseProductCommissionQuot' => 'handle',
            'console\models\base\BaseProductCommissionPercentage' => 'handle',
            'console\models\base\BaseProductOperate' => 'add', 
        );
    }

    public function getModels_ProductList() {
        return array(
            'console\models\base\List_BaseProduct' => "productlist"
        );
    }   
    
    public function getModels_ProductDesc() {
        return array(
            'console\models\base\List_BaseProduct' => "productDesc"
        );
    }   

    public function getModels_ProductCommissionDesc() {
        return array(
            'console\models\base\List_BaseProduct' => "productCommissionDesc"
        );
    }      

}
