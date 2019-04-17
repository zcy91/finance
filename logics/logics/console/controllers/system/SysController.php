<?php
namespace console\controllers\system;

use console\behaviors\system\SysBehavior;
use console\events\system\SysEvent;
use console\controllers\BaseController;
use console\behaviors\BaseBehavior;

class SysController extends BaseController {

    public function init() {
        parent::init();
  
        $this->behavior = new SysBehavior();
        $this->attachBehavior("Sysbehavior", $this->behavior);
        $this->behavior->changeDB(\Yii::$app->db_logger);
        $this->event = new SysEvent();
    }

    public function actionSyslogadd($data) {
        $this->event->set($data, BaseBehavior::ADD_ACTION);
        parent::add($this->getModels_SysLogAdd(), $this->event, false);
        $this->event->Display();
        return 0;
    }
    
    public function actionSyserradd($data) {
        $this->event->set($data, BaseBehavior::ADD_ACTION);
        parent::add($this->getModels_SysErrAdd(), $this->event);
        $this->event->Display();
        return 0;
    }  
    
    public function actionFetchmoduleinfo($data) {
        $this->event->set($data, BaseBehavior::ADD_ACTION);
        parent::add($this->getModels_SysModuleInfo(), $this->event);
        $this->event->Display();
        return 0;
    } 
    
    public function actionFetcherrorinfo($data) {
        $this->event->set($data, BaseBehavior::ADD_ACTION);
        parent::add($this->getModels_SysErrorInfo(), $this->event);
        $this->event->Display();
        return 0;
    }      

    public function actionFetchsysinfo($data) {
        $this->event->set($data, BaseBehavior::FETCH_ALL_ACTION);
        parent::fetch_all($this->getModels_FetchSysInfo(), $this->event, false);
        $this->event->Display();
        return 0;
    }

}
