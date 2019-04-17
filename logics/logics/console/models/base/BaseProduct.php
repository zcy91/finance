<?php
namespace console\models\base;

use console\models\BaseModel;

class BaseProduct extends BaseModel {

    const TABLE_NAME = "base_product";

    public function primaryKey() {
        return ['id' => 'auto'];
    }

    protected function resolveFields($v = array(), $default_arr = array()) {

        $subset = array(       
            "id",
            "dnos",
            "dnames",
            "image",
            "algorithm",
            "isSetAlgorithm",
            "display",
            "descr",
            "seller_id",
            "categoryId",
            "creatTime",
            "nowTime"          
        );

        $info_arr = parent::key_values_intersect($v, $subset, $default_arr);
        return $info_arr;
    }
    
    public function get_seq_no($event, $data_arr, $array_dim) {

        $count = $array_dim == 1 ? 1 : count($data_arr);
        $seq_no = 0;
        $this->proc_call('getKeyValue', array(503, $count), $seq_no, $event);

        return $seq_no;
    }

    public function refer_to($event, $data_arr, $array_dim, $seq_no, $count) {
        $event->set_product_id($seq_no);
    }
    
    public static function setEditData($event, $id, $nowTime, $newData, $oldData){
        
        if (isset($newData["dnos"]) && !empty($newData["dnos"]) && $newData["dnos"] != $oldData["dnos"]) {
            $event->base_product_data["dnos"] = $newData["dnos"];
        } 
        
        if (isset($newData["dnames"]) && !empty($newData["dnames"]) && $newData["dnames"] != $oldData["dnames"]) {
            $event->base_product_data["dnames"] = $newData["dnames"];
        }  

        if (isset($newData["image"]) && !empty($newData["image"]) && $newData["image"] != $oldData["image"]) {
            $event->base_product_data["image"] = $newData["image"];
        }  
        
        if (isset($newData["algorithm"]) && !empty($newData["algorithm"]) && $newData["algorithm"] != $oldData["algorithm"]) {
            $event->base_product_data["algorithm"] = $newData["algorithm"];
        }  
        
        if (isset($newData["display"]) && !empty($newData["display"]) && $newData["display"] != $oldData["display"]) {
            $event->base_product_data["display"] = $newData["display"];
        } 
        
        if (isset($newData["sort"]) && !empty($newData["sort"]) && $newData["sort"] != $oldData["sort"]) {
            $event->base_product_data["sort"] = $newData["sort"];
        } 

        if (isset($newData["descr"]) && !empty($newData["descr"]) && $newData["descr"] != $oldData["descr"]) {
            $event->base_product_data["descr"] = $newData["descr"];
        }         
        
        if (isset($newData["categoryId"]) && !empty($newData["categoryId"]) && $newData["categoryId"] != $oldData["categoryId"]) {
            $event->base_product_data["categoryId"] = $newData["categoryId"];
        }         
        
        if (!empty($event->base_product_data)) {
            $event->base_product_data["id"] = $id;
            $event->base_product_data["nowTime"] = $nowTime;
        }          
    } 

    public function delete($event) {
        
            $data = $event->base_product_data;
            
        if (!empty($data)) {
            
            $sql = "DELETE FROM base_product WHERE seller_id = :sellerId AND id = :productId";
            $params = array(
                ":sellerId" => $data["sellerId"],
                ":productId" => $data["id"],
            );       

            $this->update_sql($sql, $event, $params);
            
        } 
    }     

}
