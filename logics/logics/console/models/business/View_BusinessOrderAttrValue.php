<?php
namespace console\models\business;

use console\models\BaseModel;

class View_BusinessOrderAttrValue extends BaseModel {
    
    public function getOrderAttrValue($event, $sellerId, $orderId) { 

        $sql = "SELECT boav.attrId,
                       boav.itemId,
                       boav.itemName
                FROM business_order_attr_value AS boav
                WHERE boav.seller_id = :sellerId AND boav.orderId = :orderId";
        
        $params = array(
            ":sellerId" => $sellerId,
            ":orderId" => $orderId
        );        
        
        $result = $this->query_SQL($sql, $event, null, $params);

        return $result;        
    }      
    
}
