<?php
namespace console\controllers\right;

use console\behaviors\right\PostBehavior;
use console\events\right\PostEvent;
use console\controllers\BaseController;
use console\behaviors\BaseBehavior;

class PostController extends BaseController {

    public function init() {
        parent::init();
  
        $this->behavior = new PostBehavior();
        $this->attachBehavior("Postbehavior", $this->behavior);
        $this->event = new PostEvent();
    }

    public function actionPostadd($data) {
        $this->event->set($data, BaseBehavior::ADD_ACTION);
        parent::add($this->getModels_PostAdd(), $this->event);
        $this->event->Display();
        return 0;
    }

    public function actionPostedit($data) {
        $this->event->set($data, BaseBehavior::ADD_ACTION);
        parent::modify($this->getModels_PostEdit(), $this->event);
        $this->event->Display();
        return 0;
    }      
    
    public function actionPostdelete($data) {
        $this->event->set($data, BaseBehavior::ADD_ACTION);
        parent::delete($this->getModels_PostDelete(), $this->event);
        $this->event->Display();
        return 0;
    } 

    public function actionPostlist($data) {
        $this->event->set($data, BaseBehavior::FETCH_ALL_ACTION);
        parent::fetch_all($this->getModels_PostList(), $this->event, false);
        $this->event->Display();
        return 0;
    }
    
    public function actionPostsingle($data) {
        $this->event->set($data, BaseBehavior::FETCH_ALL_ACTION);
        parent::fetch_all($this->getModels_PostSingle(), $this->event, false);
        $this->event->Display();
        return 0;
    }

}
