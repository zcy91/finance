<?php

namespace console\controllers\base;

use console\behaviors\base\CateBehavior;
use console\events\base\CateEvent;
use console\controllers\BaseController;
use console\behaviors\BaseBehavior;

class CateController extends BaseController {
    
    public function init(){
        parent::init();
        $this->behavior = new CateBehavior();
        $this->attachBehavior('CateBehavior', $this->behavior);
        $this->event = new CateEvent();
    }
    
    public function actionCateadd($data) {
        $this->event->set($data, BaseBehavior::ADD_ACTION);
        parent::add($this->getModels_CateAdd(), $this->event);
        $this->event->Display();
    }
    
    public function actionCatesave($data) {
        $this->event->set($data, BaseBehavior::ADD_ACTION);
        parent::modify($this->getModels_CateEdit(), $this->event);
        $this->event->Display();
    }
    
    public function actionCatedelete($data){
        $this->event->set($data, BaseBehavior::DEL_ACTION);
        parent::delete($this->getModels_CateDelete(),$this->event);
        $this->event->Display();
        return 0;
    }
    public function actionFetchall($data){
        $this->event->set($data,BaseBehavior::FETCH_ALL_ACTION);
        //触发查询事件
        parent::fetch_all($this->getModels_CateList(),$this->event,false);
        $this->event->Display();
    }
}
