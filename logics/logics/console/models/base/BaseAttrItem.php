<?php
namespace console\models\base;

use console\models\BaseModel;

class BaseAttrItem extends BaseModel {

    const TABLE_NAME = "base_attr_item";

    public function primaryKey() {
        return ['seller_id' => 'auto','attrId' => 'auto','id' => 'auto'];
    }

    protected function resolveFields($v = array(), $default_arr = array()) {

        $subset = array(     
            "id",
            "dnames",
            "sort",
            "seller_id",
            "attrId"
        );

        $info_arr = parent::key_values_intersect($v, $subset, $default_arr);
        return $info_arr;
    }
    
    public function delete($event) {
        
        if (!empty($event->base_attr_item_data)) {
            
            $data = $event->base_attr_item_data;
            
            $condition = "attrId = :attrId AND seller_id = :sellerId";
            $params = array(
                ":sellerId" => $data["seller_id"],
                ":attrId" => $data["attrId"]
            );       

            $this->deleteAll(self::TABLE_NAME, $condition, $event, $params);              
        } 
    }     
    
    public function deleteAdd($event) {
        
        if (!empty($event->base_attr_item_del)) {
            $event->base_attr_item_data = $event->base_attr_item_del;
            $this->delete($event);
        }
        
        if (!empty($event->base_attr_item_add)) {
            $event->base_attr_item_data = $event->base_attr_item_add;
            $this->add($event);
        }
        
    }


}
