<?php
namespace console\controllers\user;

use console\behaviors\user\SellerBehavior;
use console\events\user\SellerEvent;
use console\controllers\BaseController;
use console\behaviors\BaseBehavior;

class SellerController extends BaseController {

    public function init() {
        parent::init();
        //给方法添加行为
        $this->behavior = new SellerBehavior();
        $this->attachBehavior("seller_behavior", $this->behavior);
        //整理Web服务器传入的参数
        $this->event = new SellerEvent();
    }

    public function actionFetchsiteinfo($data) {
        //整理传入的数据
        $this->event->set($data, BaseBehavior::FETCH_ALL_ACTION);
        parent::fetch_all($this->getModels_FetchSiteInfo(), $this->event);
        $this->event->Display();
    }
   

}
