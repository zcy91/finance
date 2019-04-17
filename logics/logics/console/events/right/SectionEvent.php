<?php
namespace console\events\right;

use console\events\BaseEvent;

class SectionEvent extends BaseEvent {

    public $sectionId;
    public $parentsPath;
    public $selfParentsPath;

    public $right_section_data;
    public $right_section_operate_data;
    
    public function  set_section_id($seq_no){
        $this->sectionId = $seq_no;
        $this->selfParentsPath = $this->parentsPath . $seq_no . "|";
    }

}

?>