<?php
namespace console\events\business;

use console\events\BaseEvent;

class OrderEvent extends BaseEvent {

    public $orderId;
    //public $orderNo;
    
    public $applyDays;
    public $resultDays = 0;
    public $resultAmount = 0;

    public $business_order_data;
    public $business_order_attr_data;
    public $business_order_attr_item_data;
    public $business_order_attr_value_data;
    public $business_order_commission_quot_data;
    public $business_order_commission_percentage_data;
    public $business_order_merchandiser_data;
    public $business_order_salesman_data;
    public $business_order_status_data;
    public $business_order_reason_data;
    public $business_order_delay_data;
    public $business_order_operate_data;
    
    public $business_order_attr_delete;
    public $business_order_attr_item_delete;
    public $business_order_attr_value_delete;
    public $business_order_commission_quot_delete;
    public $business_order_commission_percentage_delete;
    public $business_order_salesman_delete;


    public function  set_order_id($seq_no){
        $this->orderId = $seq_no;
    }

}

?>