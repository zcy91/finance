<?php
namespace console\controllers\base;

use console\behaviors\base\AttrBehavior;
use console\events\base\AttrEvent;
use console\controllers\BaseController;
use console\behaviors\BaseBehavior;

class AttrController extends BaseController {

    public function init() {
        parent::init();
  
        $this->behavior = new AttrBehavior();
        $this->attachBehavior("Attrbehavior", $this->behavior);
        $this->event = new AttrEvent();
    }

    public function actionAttradd($data) {
        $this->event->set($data, BaseBehavior::ADD_ACTION);
        parent::add($this->getModels_AttrAdd(), $this->event);
        $this->event->Display();
        return 0;
    }

    public function actionAttrsave($data) {
        $this->event->set($data, BaseBehavior::ADD_ACTION);
        parent::modify($this->getModels_AttrEdit(), $this->event);
        $this->event->Display();
        return 0;
    }      
    
    public function actionAttrdelete($data) {
        $this->event->set($data, BaseBehavior::ADD_ACTION);
        parent::delete($this->getModels_AttrDelete(), $this->event);
        $this->event->Display();
        return 0;
    } 

    public function actionAttrlist($data) {
        $this->event->set($data, BaseBehavior::FETCH_ALL_ACTION);
        parent::fetch_all($this->getModels_AttrList(), $this->event, false);
        $this->event->Display();
        return 0;
    }

    public function actionAttrdesc($data) {
        $this->event->set($data, BaseBehavior::FETCH_ALL_ACTION);
        parent::fetch_all($this->getModels_AttrDesc(), $this->event, false);
        $this->event->Display();
        return 0;
    }    

}
