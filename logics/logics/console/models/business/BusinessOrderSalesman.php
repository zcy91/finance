<?php
namespace console\models\business;

use console\models\BaseModel;

class BusinessOrderSalesman extends BaseModel {

    const TABLE_NAME = "business_order_salesman";

    public function primaryKey() {
        return ['id' => 'auto'];
    }

    protected function resolveFields($v = array(), $default_arr = array()) {

        $subset = array(          
            "id",
            "seller_id",
            "orderId",
            "sectionId",
            "salesmanId",
            "salesmanName",
            "salesmanAccount",
            "relatedTime"       
        );

        $info_arr = parent::key_values_intersect($v, $subset, $default_arr);
        return $info_arr;
    }
    
    public function delete($event) {
        
        $data = $event->business_order_salesman_delete;
        
        if (!empty($data)) {
            
            $sql = "DELETE FROM business_order_salesman WHERE seller_id = :sellerId AND orderId = :orderId";
            $params = array(
                ":sellerId" => $data["sellerId"],
                ":orderId" => $data["orderId"],
            );
            
            $this->update_sql($sql, $event, $params);
            
        }
    }    

}
