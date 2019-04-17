<?php
namespace console\controllers\base;

use console\behaviors\base\ProductBehavior;
use console\events\base\ProductEvent;
use console\controllers\BaseController;
use console\behaviors\BaseBehavior;

class ProductController extends BaseController {

    public function init() {
        parent::init();
  
        $this->behavior = new ProductBehavior();
        $this->attachBehavior("Productbehavior", $this->behavior);
        $this->event = new ProductEvent();
    }

    public function actionProductadd($data) {
        $this->event->set($data, BaseBehavior::ADD_ACTION);
        parent::add($this->getModels_ProductAdd(), $this->event);
        $this->event->Display();
        return 0;
    }

    public function actionProductredit($data) {
        $this->event->set($data, BaseBehavior::ADD_ACTION);
        parent::modify($this->getModels_ProductEdit(), $this->event);
        $this->event->Display();
        return 0;
    }      
    
    public function actionProductdisplay($data) {
        $this->event->set($data, BaseBehavior::ADD_ACTION);
        parent::modify($this->getModels_ProductDisplay(), $this->event);
        $this->event->Display();
        return 0;
    } 
    
    public function actionProductdelete($data) {
        $this->event->set($data, BaseBehavior::ADD_ACTION);
        parent::delete($this->getModels_ProductDelete(), $this->event);
        $this->event->Display();
        return 0;
    } 
    
    public function actionProductcommission($data) {
        $this->event->set($data, BaseBehavior::ADD_ACTION);
        parent::modify($this->getModels_ProductCommission(), $this->event);
        $this->event->Display();
        return 0;
    } 

    public function actionProductlist($data) {
        $this->event->set($data, BaseBehavior::FETCH_ALL_ACTION);
        parent::fetch_all($this->getModels_ProductList(), $this->event, false);
        $this->event->Display();
        return 0;
    }

    public function actionProductdesc($data) {
        $this->event->set($data, BaseBehavior::FETCH_ALL_ACTION);
        parent::fetch_all($this->getModels_ProductDesc(), $this->event, false);
        $this->event->Display();
        return 0;
    }

    public function actionProductcommissiondesc($data) {
        $this->event->set($data, BaseBehavior::FETCH_ALL_ACTION);
        parent::fetch_all($this->getModels_ProductCommissionDesc(), $this->event, false);
        $this->event->Display();
        return 0;
    }       

}
