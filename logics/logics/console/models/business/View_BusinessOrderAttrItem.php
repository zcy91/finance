<?php
namespace console\models\business;

use console\models\BaseModel;

class View_BusinessOrderAttrItem extends BaseModel {
    
    public function getOrderAttrItem($event, $sellerId, $orderId) { 

        $sql = "SELECT boai.attrId,
                       boai.itemId,
                       boai.itemName
                FROM business_order_attr_item AS boai
                WHERE boai.seller_id = :sellerId AND boai.orderId = :orderId";
        
        $params = array(
            ":sellerId" => $sellerId,
            ":orderId" => $orderId
        );        
        
        $result = $this->query_SQL($sql, $event, null, $params);

        return $result;        
    }  
    
}
