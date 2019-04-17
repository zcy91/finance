<?php
namespace console\events\base;

use console\events\BaseEvent;

class ProductEvent extends BaseEvent {

    public $productId;

    public $base_product_data;
    public $base_product_attr_data;
    public $base_product_attr_item_data;
    public $base_product_operate_data;
    public $base_product_commission_quot_data;
    public $base_product_commission_percentage_data;      

    
    public $base_product_attr_del;
    public $base_product_attr_item_del;    
    public $base_product_commission_quot_del;
    public $base_product_commission_percentage_del;        
    
    public function  set_product_id($seq_no){
        $this->productId = $seq_no;
    }

}

?>