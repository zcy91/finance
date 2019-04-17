<?php
namespace console\controllers\usercenter;

use console\behaviors\usercenter\MediumBehavior;
use console\events\usercenter\MediumEvent;
use console\controllers\BaseController;
use console\behaviors\BaseBehavior;

class MediumController extends BaseController {

    public function init() {
        parent::init();
        //给方法添加行为
        $this->behavior = new MediumBehavior();
        $this->attachBehavior("MediumBehavior", $this->behavior);
        //整理Web服务器传入的参数
        $this->event = new MediumEvent();
    }

    public function actionMediumadd($data) {
        $this->event->set($data, BaseBehavior::ADD_ACTION);
        parent::add($this->getModels_MediumAdd(), $this->event);
        $this->event->Display();
        return 0;
    }

    public function actionMediumedit($data) {
        $this->event->set($data, BaseBehavior::ADD_ACTION);
        parent::modify($this->getModels_MediumEdit(), $this->event);
        $this->event->Display();
        return 0;
    }      
    
    public function actionMediumenable($data) {
        $this->event->set($data, BaseBehavior::ADD_ACTION);
        parent::delete($this->getModels_MediumEnable(), $this->event);
        $this->event->Display();
        return 0;
    } 
    
    public function actionMediumlist($data) {
        $this->event->set($data, BaseBehavior::FETCH_ALL_ACTION);
        parent::fetch_all($this->getModels_MediumList(), $this->event, false);
        $this->event->Display();
        return 0;
    }   
    
    public function actionOrderlist($data) {
        $this->event->set($data, BaseBehavior::FETCH_ALL_ACTION);
        parent::fetch_all($this->getModels_Orderlist(), $this->event, false);
        $this->event->Display();
        return 0;
    }     

}
