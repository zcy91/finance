<?php
namespace console\models\business;

use console\models\BaseModel;

class View_BusinessOrderAttr extends BaseModel {
    
    public function getOrderAttr($event, $sellerId, $orderId) { 

        $sql = "SELECT boa.attrId,
                       boa.attrName,
                       boa.genre,
                       boa.required,
                       boa.imageCount,
                       boa.attrItemId,
                       IF(boa.attrValue = '', NULL, boa.attrValue) AS attrValue
                FROM business_order_attr AS boa
                WHERE boa.seller_id = :sellerId AND boa.orderId = :orderId";
        
        $params = array(
            ":sellerId" => $sellerId,
            ":orderId" => $orderId
        );
        
        $result = $this->query_SQL($sql, $event, null, $params);

        return $result;        
    }  
    
}
