<?php
namespace console\behaviors\user;

use console\behaviors\BaseBehavior;

class UserBehavior extends BaseBehavior {

    public function getModels_Login() {
        return array(
            'console\models\user\List_User' => 'userLogin',
            'console\models\user\UserLogin' => 'modify'
        );
    }
    
    public function getModels_GetUserNode() {
        return array(
            'console\models\user\View_GetUserNode'
        );
    }
    
    public function getModels_GetAllNode() {
        return array(
            'console\models\user\View_GetUserNode'=>'getAllNode'
        );
    }
    
    public function getModels_StaffAdd() {
        return array(
            'console\models\user\InitData_User' => 'staffAdd',
            'console\models\user\UserLogin' => 'add',
            'console\models\user\UserProfile' => 'add',
            'console\models\user\UserSellerRelation' => 'add',
            'console\models\right\RightSectionUser' => 'add',
            'console\models\right\RightStaffRole' => 'add',
            'console\models\user\UserRole' => 'add',
            'console\models\user\UserOperate' => 'add'
        );
    }   
    
    public function getModels_StaffEdit() {
        return array(
            'console\models\user\InitData_User' => 'StaffEdit',
            'console\models\user\UserLogin' => 'modify',
            'console\models\user\UserProfile' => 'modify',
            'console\models\user\UserSellerRelation' => 'modify',
            'console\models\right\RightSectionUser' => 'deleteAdd',
            'console\models\right\RightStaffRole' => 'deleteAdd',
            'console\models\user\UserOperate' => 'add'            
        );
    }  
    
    public function getModels_StaffEnable() {
        return array(
            'console\models\user\InitData_User' => 'staffEnable',
            'console\models\user\UserLogin' => 'modify',
            'console\models\user\UserProfile' => 'modify',
            'console\models\user\UserSellerRelation' => 'addModify',
            'console\models\user\UserRole' => 'deleteAdd',
            'console\models\user\UserOperate' => 'add'    
        );
    }   
    
    public function getModels_StaffSetSection() {
        return array(
            'console\models\user\InitData_User' => 'setSection',
            'console\models\right\RightSectionUser' => 'deleteAdd',
        );
    }   
    
    public function getModels_StaffSetPost() {
        return array(
            'console\models\user\InitData_User' => 'setPost',
            'console\models\right\RightPostUser' => 'deleteAdd',
            'console\models\user\UserSellerRelation' => 'modify',
            'console\models\user\UserOperate' => 'add'  
            
        );
    } 

    public function getModels_StaffList() {
        return array(
            'console\models\user\List_User' => 'staffList'
        );
    }

    public function getModels_StaffDescEd() {
        return array(
            'console\models\user\List_User' => 'staffDescEd'
        );
    }

    public function getModels_StaffDesc() {
        return array(
            'console\models\user\List_User' => 'staffDesc'
        );
    }   
    
    public function getModels_BindAccount() {
        return array(
            'console\models\user\InitData_User' => 'bindAccount',
            'console\models\user\UserLogin',
        );
    }        

    public function getModels_RoleStaffList() {
        return array(
            'console\models\user\List_User' => 'roleStaffList'
        );
    }     

}
