<?php
namespace console\events\right;

use console\events\BaseEvent;

class PostEvent extends BaseEvent {

    public $post_id;

    public $right_post_data;
    public $right_post_module_data;
    public $right_post_base_data;
    public $right_post_section_data;
    public $right_post_operate_data;
    
    public function  set_post_id($seq_no){
        $this->post_id = $seq_no;
    }

}

?>