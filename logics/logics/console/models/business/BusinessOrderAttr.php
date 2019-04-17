<?php
namespace console\models\business;

use console\models\BaseModel;

class BusinessOrderAttr extends BaseModel {

    const TABLE_NAME = "business_order_attr";

    public function primaryKey() {
        return ['id' => 'auto'];
    }

    protected function resolveFields($v = array(), $default_arr = array()) {

        $subset = array(          
            "id",
            "genre",
            "required",
            "imageCount",
            "attrName",
            "attrValue",
            "seller_id",
            "orderId",
            "attrId",
            "attrItemId"          
        );

        $info_arr = parent::key_values_intersect($v, $subset, $default_arr);
        return $info_arr;
    }
    
    public function delete($event) {
        
        $data = $event->business_order_attr_delete;
        
        if (!empty($data)) {
            
            $sql = "DELETE FROM business_order_attr WHERE seller_id = :sellerId AND orderId = :orderId";
            $params = array(
                ":sellerId" => $data["sellerId"],
                ":orderId" => $data["orderId"],
            );
            
            $this->update_sql($sql, $event, $params);
            
        }
    }
    
    public function deleteAdd($event){
        
        $this->delete($event);
        
        $this->add($event);
    }

}
