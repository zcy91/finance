<?php
namespace console\models\base;

use console\models\BaseModel;

class BaseAttr extends BaseModel {

    const TABLE_NAME = "base_attr";

    public function primaryKey() {
        return ['id' => 'auto'];
    }

    protected function resolveFields($v = array(), $default_arr = array()) {

        $subset = array( 
            "id",
            "dnames",
            "genre",
            "required",
            "imageCount",
            "sort",
            "values",
            "seller_id",
            "creatTime",
            "nowTime"
        );

        $info_arr = parent::key_values_intersect($v, $subset, $default_arr);
        return $info_arr;
    }
    
    public function get_seq_no($event, $data_arr, $array_dim) {

        $count = $array_dim == 1 ? 1 : count($data_arr);
        $seq_no = 0;
        $this->proc_call('getKeyValue', array(502, $count), $seq_no, $event);

        return $seq_no;
    }

    public function refer_to($event, $data_arr, $array_dim, $seq_no, $count) {
        $event->set_attr_id($seq_no);
    }
    
    public static function setEditData($event, $id, $nowTime, $newData, $oldData){
        
        if (isset($newData["dnames"]) && !empty($newData["dnames"]) && $newData["dnames"] != $oldData["dnames"]) {
            $event->base_attr_data["dnames"] = $newData["dnames"];
        } 
        
        if (isset($newData["genre"]) && !empty($newData["genre"]) && $newData["genre"] != $oldData["genre"]) {
            $event->base_attr_data["genre"] = $newData["genre"];
        }  

        if (isset($data["required"]) && !empty($data["required"]) && $data["required"] != $oldAttr["required"]) {
            $event->base_attr_data["required"] = $newData["required"];
        }   
        
        if (isset($data["imageCount"]) && !empty($data["imageCount"]) && $data["imageCount"] != $oldAttr["imageCount"]) {
            $event->base_attr_data["imageCount"] = $newData["imageCount"];
        }         

        if (isset($newData["sort"]) && !empty($newData["sort"]) && $newData["sort"] != $oldData["sort"]) {
            $event->base_attr_data["sort"] = $newData["sort"];
        }  
        
        if (isset($newData["values"]) && !empty($newData["values"]) && $newData["values"] != $oldData["values"]) {
            $event->base_attr_data["values"] = $newData["values"];
        } 
        
        if (!empty($event->base_attr_data)) {
            $event->base_attr_data["id"] = $id;
            $event->base_attr_data["nowTime"] = $nowTime;
        }        
        
    }   
    
    public function delete($event) {
        
        if (!empty($event->base_attr_data)) {
            
            $data = &$event->base_attr_data;
            
            $condition = "id = :attrId AND seller_id = :sellerId";
            $params = array(
                ":sellerId" => $data["seller_id"],
                ":attrId" => $data["id"]
            );       

            $this->deleteAll(self::TABLE_NAME, $condition, $event, $params);   
            
            $condition = "attrId = :attrId AND seller_id = :sellerId";
            $params = array(
                ":sellerId" => $data["seller_id"],
                ":attrId" => $data["id"]
            );       

            $this->deleteAll("base_attr_item", $condition, $event, $params);              
        } 
    }    


}
