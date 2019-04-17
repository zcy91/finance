<?php
namespace console\controllers\user;

use console\behaviors\user\UserBehavior;
use console\events\user\UserEvent;
use console\controllers\BaseController;
use console\behaviors\BaseBehavior;
use console\models\user\List_User;

class UserController extends BaseController {

    public function init() {
        parent::init();
        //给方法添加行为
        $this->behavior = new UserBehavior();
        $this->attachBehavior("Userbehavior", $this->behavior);
        //整理Web服务器传入的参数
        $this->event = new UserEvent();
    }

    public function actionLogin($data) {
        //整理传入的数据
        $this->event->set($data, BaseBehavior::FETCH_ALL_ACTION);
        parent::fetch_all($this->getModels_Login(), $this->event);
        $this->event->Display();
        return 0;
    }  
    
    public function actionFetchusernode($data) {
        //整理传入的数据
        $this->event->set($data, BaseBehavior::FETCH_ALL_ACTION);
        parent::fetch_all($this->getModels_GetUserNode(), $this->event);
        $this->event->Display();
        return 0;
    }
    
    public function actionStaffadd($data) {
        $this->event->set($data, BaseBehavior::ADD_ACTION);
        parent::add($this->getModels_StaffAdd(), $this->event);
        $this->event->Display();
        return 0;
    }

    public function actionStaffedit($data) {
        $this->event->set($data, BaseBehavior::ADD_ACTION);
        parent::modify($this->getModels_StaffEdit(), $this->event);
        $this->event->Display();
        return 0;
    }   
    
    public function actionStaffenable($data) {
        $this->event->set($data, BaseBehavior::ADD_ACTION);
        parent::modify($this->getModels_StaffEnable(), $this->event);
        $this->event->Display();
        return 0;
    }  

    public function actionStaffsetsection($data) {
        $this->event->set($data, BaseBehavior::ADD_ACTION);
        parent::modify($this->getModels_StaffSetSection(), $this->event);
        $this->event->Display();
        return 0;
    } 

    public function actionStaffsetpost($data) {
        $this->event->set($data, BaseBehavior::ADD_ACTION);
        parent::modify($this->getModels_StaffSetPost(), $this->event);
        $this->event->Display();
        return 0;
    }      
    
    public function actionStafflist($data) {
        //整理传入的数据
        $this->event->set($data, BaseBehavior::FETCH_ALL_ACTION);
        parent::fetch_all($this->getModels_StaffList(), $this->event);
        $this->event->Display();
        return 0;
    }  

    public function actionStaffdesced($data) {
        //整理传入的数据
        $this->event->set($data, BaseBehavior::FETCH_ALL_ACTION);
        parent::fetch_all($this->getModels_StaffDescEd(), $this->event);
        $this->event->Display();
        return 0;
    }     
    
    public function actionStaffdesc($data) {
        //整理传入的数据
        $this->event->set($data, BaseBehavior::FETCH_ALL_ACTION);
        parent::fetch_all($this->getModels_StaffDesc(), $this->event);
        $this->event->Display();
        return 0;
    } 
    
    public function actionBindaccount($data) {
        $this->event->set($data, BaseBehavior::ADD_ACTION);
        parent::modify($this->getModels_BindAccount(), $this->event);
        if (!$this->event->error_code && $this->event->bindAccount) {
            $List_User = new List_User();
            $List_User->userLogin($this->event);
            unset($List_User);
        }
        $this->event->Display();
        return 0;
    }  
   
    public function actionRolestafflist($data) {
        //整理传入的数据
        $this->event->set($data, BaseBehavior::FETCH_ALL_ACTION);
        parent::fetch_all($this->getModels_RoleStaffList(), $this->event);
        $this->event->Display();
        return 0;
    }   
    
    public function actionGetallnode($data) {
        //整理传入的数据
        $this->event->set($data, BaseBehavior::FETCH_ALL_ACTION);
        parent::fetch_all($this->getModels_GetAllNode(), $this->event);
        $this->event->Display();
        return 0;
    }
   
}
