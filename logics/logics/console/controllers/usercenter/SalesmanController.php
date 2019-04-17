<?php
namespace console\controllers\usercenter;

use console\behaviors\usercenter\SalesmanBehavior;
use console\events\usercenter\SalesmanEvent;
use console\controllers\BaseController;
use console\behaviors\BaseBehavior;

class SalesmanController extends BaseController {

    public function init() {
        parent::init();
        //给方法添加行为
        $this->behavior = new SalesmanBehavior();
        $this->attachBehavior("Salesmanhavior", $this->behavior);
        //整理Web服务器传入的参数
        $this->event = new SalesmanEvent();
    }
    
    public function actionOrderlist($data) {
        $this->event->set($data, BaseBehavior::FETCH_ALL_ACTION);
        parent::fetch_all($this->getModels_Orderlist(), $this->event, false);
        $this->event->Display();
        return 0;
    }      

}
