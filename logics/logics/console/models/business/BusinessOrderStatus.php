<?php
namespace console\models\business;

use console\models\BaseModel;

class BusinessOrderStatus extends BaseModel {

    const TABLE_NAME = "business_order_status";

    public function primaryKey() {
        return ['seller_id' => 'key','orderId' => 'key','dstatus' => 'key','id' => 'auto'];
    }

    protected function resolveFields($v = array(), $default_arr = array()) {

        $subset = array(          
            "id",
            "seller_id",
            "orderId",
            "dstatus",
            "sectionId",
            "relatedTime",  
            "relatedUserId",
            "relatedUserName",
            "relatedUserAccount" 
        );

        $info_arr = parent::key_values_intersect($v, $subset, $default_arr);
        return $info_arr;
    }
    
    public function delete($event) {
        
        $data = $event->business_order_status_data;
        
        if (!empty($data)) {
            
            $sql = "DELETE FROM business_order_status WHERE seller_id = :sellerId AND orderId = :orderId";
            $params = array(
                ":sellerId" => $data["sellerId"],
                ":orderId" => $data["orderId"],
            );
            
            $this->update_sql($sql, $event, $params);
            
        }
    }      

    public function orderCommit($event) {
        
        $data = $event->business_order_status_data;
        
        if (!empty($data)) {
            $sql = "UPDATE business_order_status
                    SET dstatus = 2, relatedTime = :relatedTime
                    WHERE seller_id= :sellerId AND orderId = :orderId AND dstatus = 1";
            $params = array(
                ":sellerId" => $data["sellerId"],
                ":orderId" => $data["orderId"],
                ":relatedTime" => $data["nowTime"],
            );
            $this->update_sql($sql, $event, $params);
        }
    }   

}
