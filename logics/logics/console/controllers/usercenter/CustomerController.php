<?php
namespace console\controllers\usercenter;

use console\behaviors\usercenter\CustomerBehavior;
use console\events\usercenter\CustomerEvent;
use console\controllers\BaseController;
use console\behaviors\BaseBehavior;
use console\models\user\List_User;

class CustomerController extends BaseController {

    public function init() {
        parent::init();
        //给方法添加行为
        $this->behavior = new CustomerBehavior();
        $this->attachBehavior("Mediumbehavior", $this->behavior);
        //整理Web服务器传入的参数
        $this->event = new CustomerEvent();
    }

    public function actionCustomerregister($data) {
        $this->event->set($data, BaseBehavior::ADD_ACTION);
        parent::add($this->getModels_CustomerRegister(), $this->event);
        if (!$this->event->error_code && $this->event->bindAccount) {
            $List_User = new List_User();
            $List_User->userLogin($this->event);
            unset($List_User);
        }
        $this->event->Display();
        return 0;
    }

    public function actionCustomeredit($data) {
        $this->event->set($data, BaseBehavior::ADD_ACTION);
        parent::modify($this->getModels_CustomerEdit(), $this->event);
        $this->event->Display();
        return 0;
    }    
    
    public function actionCustomeenable($data) {
        $this->event->set($data, BaseBehavior::ADD_ACTION);
        parent::modify($this->getModels_CustomerEnable(), $this->event);
        $this->event->Display();
        return 0;
    }        

    public function actionCustomerlist($data) {
        $this->event->set($data, BaseBehavior::FETCH_ALL_ACTION);
        parent::fetch_all($this->getModels_CustomerList(), $this->event, false);
        $this->event->Display();
        return 0;
    }
    
    public function actionCustomerdesc($data) {
        $this->event->set($data, BaseBehavior::FETCH_ALL_ACTION);
        parent::fetch_all($this->getModels_CustomerDesc(), $this->event, false);
        $this->event->Display();
        return 0;
    }    

    public function actionOrderlist($data) {
        $this->event->set($data, BaseBehavior::FETCH_ALL_ACTION);
        parent::fetch_all($this->getModels_Orderlist(), $this->event, false);
        $this->event->Display();
        return 0;
    }      

    public function actionCustomersumm($data) {
        $this->event->set($data, BaseBehavior::FETCH_ALL_ACTION);
        parent::fetch_all($this->getModels_customerSumm(), $this->event, false);
        $this->event->Display();
        return 0;
    }    

}
