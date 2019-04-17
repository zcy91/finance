<?php
namespace console\controllers\business;

use console\behaviors\business\OrderBehavior;
use console\events\business\OrderEvent;
use console\controllers\BaseController;
use console\behaviors\BaseBehavior;

class OrderController extends BaseController {

    public function init() {
        parent::init();
  
        $this->behavior = new OrderBehavior();
        $this->attachBehavior("Orderbehavior", $this->behavior);
        $this->event = new OrderEvent();
    }

    public function actionOrderadd($data) {
        $this->event->set($data, BaseBehavior::ADD_ACTION);
        parent::add($this->getModels_OrderAdd(), $this->event);
        $this->event->Display();
        return 0;
    }

    public function actionOrderedit($data) {
        $this->event->set($data, BaseBehavior::ADD_ACTION);
        parent::modify($this->getModels_OrderEdit(), $this->event);
        $this->event->Display();
        return 0;
    }      
    
    public function actionOrderdelete($data) {
        $this->event->set($data, BaseBehavior::ADD_ACTION);
        parent::delete($this->getModels_OrderDelete(), $this->event);
        $this->event->Display();
        return 0;
    } 

    public function actionOrdercommit($data) {
        $this->event->set($data, BaseBehavior::ADD_ACTION);
        parent::modify($this->getModels_OrderCommit(), $this->event);
        $this->event->Display();
        return 0;
    }       
        
    public function actionOrdercheck($data) {
        $this->event->set($data, BaseBehavior::ADD_ACTION);
        parent::modify($this->getModels_OrderCheck(), $this->event);
        $this->event->Display();
        return 0;
    }  
    
    public function actionOrdersign($data) {
        $this->event->set($data, BaseBehavior::ADD_ACTION);
        parent::modify($this->getModels_OrderSign(), $this->event);
        $this->event->Display();
        return 0;
    }     

    public function actionOrderreceive($data) {
        $this->event->set($data, BaseBehavior::ADD_ACTION);
        parent::modify($this->getModels_OrderReceive(), $this->event);
        $this->event->Display();
        return 0;
    }       

    public function actionOrderdelay($data) {
        $this->event->set($data, BaseBehavior::ADD_ACTION);
        parent::modify($this->getModels_OrderDelay(), $this->event);
        $this->event->Display();
        return 0;
    }     

    public function actionOrderlist($data) {
        $this->event->set($data, BaseBehavior::FETCH_ALL_ACTION);
        parent::fetch_all($this->getModels_OrderList(), $this->event, false);
        $this->event->Display();
        return 0;
    }

    public function actionOrderdesced($data) {
        $this->event->set($data, BaseBehavior::FETCH_ALL_ACTION);
        parent::fetch_all($this->getModels_OrderDescEd(), $this->event, false);
        $this->event->Display();
        return 0;
    }

    public function actionOrderdesc($data) {
        $this->event->set($data, BaseBehavior::FETCH_ALL_ACTION);
        parent::fetch_all($this->getModels_OrderDesc(), $this->event, false);
        $this->event->Display();
        return 0;
    }    

    public function actionOrdercountsumm($data) {
        $this->event->set($data, BaseBehavior::FETCH_ALL_ACTION);
        parent::fetch_all($this->getModels_OrderCountSumm(), $this->event, false);
        $this->event->Display();
        return 0;
    }

    public function actionOrderamountsumm($data) {
        $this->event->set($data, BaseBehavior::FETCH_ALL_ACTION);
        parent::fetch_all($this->getModels_OrderAmountSumm(), $this->event, false);
        $this->event->Display();
        return 0;
    }    

    public function actionOrderconsolesumm($data) {
        $this->event->set($data, BaseBehavior::FETCH_ALL_ACTION);
        parent::fetch_all($this->getModels_OrderConsoleSumm(), $this->event, false);
        $this->event->Display();
        return 0;
    }      

}
