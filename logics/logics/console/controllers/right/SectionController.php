<?php
namespace console\controllers\right;

use console\behaviors\right\SectionBehavior;
use console\events\right\SectionEvent;
use console\controllers\BaseController;
use console\behaviors\BaseBehavior;

class SectionController extends BaseController {

    public function init() {
        parent::init();
  
        $this->behavior = new SectionBehavior();
        $this->attachBehavior("Sectionbehavior", $this->behavior);
        $this->event = new SectionEvent();
    }

    public function actionSectionadd($data) {
        $this->event->set($data, BaseBehavior::ADD_ACTION);
        parent::add($this->getModels_SectionAdd(), $this->event);
        $this->event->Display();
        return 0;
    }

    public function actionSectionedit($data) {
        $this->event->set($data, BaseBehavior::ADD_ACTION);
        parent::modify($this->getModels_SectionEdit(), $this->event);
        $this->event->Display();
        return 0;
    }      
    
    public function actionSectiondelete($data) {
        $this->event->set($data, BaseBehavior::ADD_ACTION);
        parent::delete($this->getModels_SectionDelete(), $this->event);
        $this->event->Display();
        return 0;
    } 

    public function actionSectionlist($data) {
        $this->event->set($data, BaseBehavior::FETCH_ALL_ACTION);
        parent::fetch_all($this->getModels_SectionList(), $this->event, false);
        $this->event->Display();
        return 0;
    }

}
