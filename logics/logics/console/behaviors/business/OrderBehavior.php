<?php
namespace console\behaviors\business;

use console\behaviors\BaseBehavior;

class OrderBehavior extends BaseBehavior {

    public function getModels_OrderAdd() {
        return array(
            'console\models\business\InitData_BusinessOrder' => 'orderAdd',
            'console\models\business\BusinessOrder' => 'add',
            'console\models\business\BusinessOrderAttr' => 'add',
            'console\models\business\BusinessOrderAttrItem' => 'add',
            'console\models\business\BusinessOrderAttrValue' => 'add',
            'console\models\business\BusinessOrderCommissionQuot' => 'add',
            'console\models\business\BusinessOrderCommissionPercentage' => 'add',
            'console\models\business\BusinessOrderSalesman' => 'add',  
            'console\models\business\BusinessOrderStatus' => 'add', 
            'console\models\business\BusinessOrderOperate' => 'add' 
        );
    }
    
    public function getModels_OrderEdit() {
        return array(
            'console\models\business\InitData_BusinessOrder' => 'orderEdit',
            'console\models\business\BusinessOrder' => 'modify',
            'console\models\business\BusinessOrderAttr' => 'deleteAdd',
            'console\models\business\BusinessOrderAttrItem' => 'deleteAdd',
            'console\models\business\BusinessOrderAttrValue' => 'deleteAdd',
            'console\models\business\BusinessOrderCommissionQuot' => 'handleAll',
            'console\models\business\BusinessOrderCommissionPercentage' => 'deleteAdd',
            'console\models\business\BusinessOrderSalesman' => 'modify',  
            'console\models\business\BusinessOrderStatus' => 'modify', 
            'console\models\business\BusinessOrderOperate' => 'add' 
        );
    }    

    public function getModels_OrderDelete() {
        return array(
            'console\models\business\InitData_BusinessOrder' => 'orderDelete',
            'console\models\business\BusinessOrder' => 'delete',
            'console\models\business\BusinessOrderAttr' => 'delete',
            'console\models\business\BusinessOrderAttrItem' => 'delete',
            'console\models\business\BusinessOrderAttrValue' => 'delete',
            'console\models\business\BusinessOrderCommissionQuot' => 'delete',
            'console\models\business\BusinessOrderCommissionPercentage' => 'delete',
            'console\models\business\BusinessOrderSalesman' => 'delete',  
            'console\models\business\BusinessOrderStatus' => 'delete', 
            'console\models\business\BusinessOrderOperate' => 'add'
        );
    }

    public function getModels_OrderCommit() {
        return array(
            'console\models\business\InitData_BusinessOrder' => 'orderCommit',
            'console\models\business\BusinessOrder' => 'modify', 
            'console\models\business\BusinessOrderStatus' => 'orderCommit', 
            'console\models\business\BusinessOrderOperate' => 'add' 
        );
    }      

    public function getModels_OrderCheck() {
        return array(
            'console\models\business\InitData_BusinessOrder' => 'orderCheck',
            'console\models\business\BusinessOrder' => 'modify', 
            'console\models\business\BusinessOrderReason' => 'add', 
            'console\models\business\BusinessOrderMerchandiser' => 'add', 
            'console\models\business\BusinessOrderStatus' => 'add', 
            'console\models\business\BusinessOrderOperate' => 'add' 
        );
    }      

    public function getModels_OrderSign() {
        return array(
            'console\models\business\InitData_BusinessOrder' => 'orderSign',
            'console\models\business\BusinessOrder' => 'modify', 
            'console\models\business\BusinessOrderCommissionQuot' => 'modify', 
            'console\models\business\BusinessOrderStatus' => 'add', 
            'console\models\business\BusinessOrderReason' => 'add', 
            'console\models\business\BusinessOrderOperate' => 'add' 
        );
    }     

    public function getModels_OrderReceive() {
        return array(
            'console\models\business\InitData_BusinessOrder' => 'orderReceive',
            'console\models\business\BusinessOrder' => 'modify', 
            'console\models\business\BusinessOrderCommissionQuot' => 'modify', 
            'console\models\business\BusinessOrderStatus' => 'add',  
            'console\models\business\BusinessOrderOperate' => 'add' 
        );
    } 

    public function getModels_OrderDelay() {
        return array(
            'console\models\business\InitData_BusinessOrder' => 'orderDelay',
            'console\models\business\BusinessOrder' => 'modify', 
            'console\models\business\BusinessOrderCommissionQuot' => 'modify',
            'console\models\business\BusinessOrderDelay' => 'add', 
            'console\models\business\BusinessOrderOperate' => 'add' 
        );
    }     

    public function getModels_OrderList() {
        return array(
            'console\models\business\List_BusinessOrder' => "orderList"
        );
    }   

    public function getModels_OrderDescEd() {
        return array(
            'console\models\business\List_BusinessOrder' => "orderDescEd"
        );
    }   

    public function getModels_OrderDesc() {
        return array(
            'console\models\business\List_BusinessOrder' => "orderDesc"
        );
    }       

    public function getModels_OrderCountSumm() {
        return array(
            'console\models\business\List_BusinessOrder' => "orderCountSumm"
        );
    }   

    public function getModels_OrderAmountSumm() {
        return array(
            'console\models\business\List_BusinessOrder' => "orderAmountSumm"
        );
    }       

    public function getModels_OrderConsoleSumm() {
        return array(
            'console\models\business\List_BusinessOrder' => "orderConsoleSumm"
        );
    }     

}
