<?php
namespace console\behaviors\usercenter;

use console\behaviors\BaseBehavior;

class CustomerBehavior extends BaseBehavior {

    public function getModels_CustomerRegister() {
        return array(
            'console\models\user\InitData_Customer' => 'customerAdd',
            'console\models\user\UserLogin' => 'add',
            'console\models\user\UserProfile' => 'add',
            'console\models\user\UserCustomer' => 'add',
            'console\models\user\UserRole' => 'add',
            'console\models\user\UserOperate' => 'add'
        );
    }   
    
    public function getModels_CustomerEdit() {
        return array(
            'console\models\user\InitData_Customer' => 'customerEdit',
            'console\models\user\UserLogin' => 'modify',
            'console\models\user\UserProfile' => 'modify',
            'console\models\user\UserOperate' => 'add'            
        );
    }  
    
    public function getModels_CustomerEnable() {
        return array(
            'console\models\user\InitData_Customer' => 'customerEnable',
            'console\models\user\UserLogin' => 'modify',
            'console\models\user\UserProfile' => 'modify',
            'console\models\user\UserCustomer' => 'addModify',
            'console\models\user\UserRole' => 'deleteAdd',
            'console\models\user\UserOperate' => 'add'                           
        );
    }     

    public function getModels_CustomerList() {
        return array(
            'console\models\user\List_Customer' => 'customerList'
        );
    }
    
    public function getModels_CustomerDesc() {
        return array(
            'console\models\user\List_Customer' => 'customerDesc'
        );
    }     

    public function getModels_OrderList() {
        return array(
            'console\models\business\List_BusinessOrder' => "orderOwnList"
        );
    }     

    public function getModels_customerSumm() {
        return array(
            'console\models\user\List_Customer' => 'customerSumm'
        );
    }    

}
