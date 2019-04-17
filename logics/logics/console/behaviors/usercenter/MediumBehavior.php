<?php
namespace console\behaviors\usercenter;

use console\behaviors\BaseBehavior;

class MediumBehavior extends BaseBehavior {

    public function getModels_MediumAdd() {
        return array(
            'console\models\user\InitData_Medium' => 'MediumAdd',
            'console\models\user\UserLogin' => 'add',
            'console\models\user\UserProfile' => 'add',
            'console\models\user\UserMedium' => 'add',
            'console\models\user\UserRole' => 'add',
            'console\models\user\UserOperate' => 'add'
        );
    }   
    
    public function getModels_MediumEdit() {
        return array(
            'console\models\user\InitData_Medium' => 'MediumEdit',
            'console\models\user\UserLogin' => 'modify',
            'console\models\user\UserProfile' => 'modify',
            'console\models\user\UserOperate' => 'add'            
        );
    }  
    
    public function getModels_MediumEnable() {
        return array(
            'console\models\user\InitData_Medium' => 'staffEnable',
            'console\models\user\UserLogin' => 'modify',
            'console\models\user\UserProfile' => 'modify',
            'console\models\user\UserMedium' => 'addModify',
            'console\models\user\UserRole' => 'deleteAdd',
            'console\models\user\UserOperate' => 'add'    
            
        );
    }   

    public function getModels_MediumList() {
        return array(
            'console\models\user\List_Medium' => 'mediumList'
        );
    }  
    
    public function getModels_CustomerDesc() {
        return array(
            'console\models\user\List_Medium' => 'mediumDesc'
        );
    }   

    public function getModels_OrderList() {
        return array(
            'console\models\business\List_BusinessOrder' => "orderOwnList"
        );
    }      

}
