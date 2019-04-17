<?php
namespace console\events\base;

use console\events\BaseEvent;

class AttrEvent extends BaseEvent {

    public $attrId;

    public $base_attr_data;
    public $base_attr_item_data;
    public $base_attr_operate_data;
    
    public $base_attr_item_add;
    public $base_attr_item_del;
    
    public function  set_attr_id($seq_no){
        $this->attrId = $seq_no;
    }

}

?>