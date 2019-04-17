<?php
namespace console\events\base;

use console\events\BaseEvent;

class CateEvent extends BaseEvent {

    public $cate_id;
    public $parentPath;
    public $selfParentPath;

    public $base_product_category_data;
    public function  set_cate_id($seq_no){
        $this->cate_id = $seq_no;
        $this->selfParentPath = $this->parentPath . $seq_no . "|";
    }
  
}

?>