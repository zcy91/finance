<?php
namespace console\controllers\usercenter;

use console\behaviors\usercenter\MerchandiserBehavior;
use console\events\usercenter\MerchandiserEvent;
use console\controllers\BaseController;
use console\behaviors\BaseBehavior;

class MerchandiserController extends BaseController {

    public function init() {
        parent::init();
        //给方法添加行为
        $this->behavior = new MerchandiserBehavior();
        $this->attachBehavior("Merchandiserhavior", $this->behavior);
        //整理Web服务器传入的参数
        $this->event = new MerchandiserEvent();
    }
    
    public function actionOrderlist($data) {
        $this->event->set($data, BaseBehavior::FETCH_ALL_ACTION);
        parent::fetch_all($this->getModels_Orderlist(), $this->event, false);
        $this->event->Display();
        return 0;
    }      

}
