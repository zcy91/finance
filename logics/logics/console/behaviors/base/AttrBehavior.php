<?php
namespace console\behaviors\base;

use console\behaviors\BaseBehavior;

class AttrBehavior extends BaseBehavior {

    public function getModels_AttrAdd() {
        return array(
            'console\models\base\InitData_BaseAttr' => 'attrAdd',
            'console\models\base\BaseAttr' => 'add',
            'console\models\base\BaseAttrItem' => 'add',
            'console\models\base\BaseAttrOperate' => 'add',
        );
    }
    
    public function getModels_AttrEdit() {
        return array(
            'console\models\base\InitData_BaseAttr' => 'attrEdit',
            'console\models\base\BaseAttr' => 'modify',
            'console\models\base\BaseAttrItem' => 'deleteAdd',
            'console\models\base\BaseAttrOperate' => 'add',
        );
    }    

    public function getModels_AttrDelete() {
        return array(
            'console\models\base\InitData_BaseAttr' => 'attrDelete',
            'console\models\base\BaseAttr' => "delete",
            'console\models\base\BaseAttrItem' => "delete",
            'console\models\base\BaseAttrOperate' => 'add',
        );
    }

    public function getModels_AttrList() {
        return array(
            'console\models\base\List_BaseAttr' => "attrList"
        );
    }  
    
    public function getModels_AttrItemList() {
        return array(
            'console\models\base\List_BaseAttr' => "attrItemList"
        );
    }   

    public function getModels_AttrDesc() {
        return array(
            'console\models\base\List_BaseAttr' => "attrDesc"
        );
    }      

}
