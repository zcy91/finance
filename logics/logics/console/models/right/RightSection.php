<?php
namespace console\models\right;

use console\models\BaseModel;

class RightSection extends BaseModel {

    const TABLE_NAME = "right_section";

    public function primaryKey() {
        return ['id' => 'auto'];
    }

    protected function resolveFields($v = array(), $default_arr = array()) {

        $subset = array(
            "id",
            "dnames",
            "deleted",
            "seller_id",
            "parentId",
            "parentsPath",
            "dLevel",
            "sort",
            "creatTime",
            "nowTime"
        );

        $info_arr = parent::key_values_intersect($v, $subset, $default_arr);
        return $info_arr;
    }
    
    public function get_seq_no($event, $data_arr, $array_dim) {

        $count = $array_dim == 1 ? 1 : count($data_arr);
        $seq_no = 0;
        $this->proc_call('getKeyValue', array(102, $count), $seq_no, $event);

        return $seq_no;
    }

    public function refer_to($event, $data_arr, $array_dim, $seq_no, $count) {
        $event->set_Section_id($seq_no);
    }
    
    public function delete($event) {
        
        if (!empty($event->right_section_data)) {
            
            $data = &$event->right_section_data;
            
            $condition = "id = :sectionId AND seller_id = :sellerId";
            $params = array(
                ":sellerId" => $data["sellerId"],
                ":sectionId" => $data["id"]
            );       

            $this->deleteAll(self::TABLE_NAME, $condition, $event, $params);            
        } 
    }

}
