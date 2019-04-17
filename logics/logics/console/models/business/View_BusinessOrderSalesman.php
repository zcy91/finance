<?php
namespace console\models\business;

use console\models\BaseModel;

class View_BusinessOrderSalesman extends BaseModel {
    
    public function getOneOrderSalesman($event, $orderId, $sellerId){
        
        $sql = "SELECT bos.seller_id,
                       bos.orderId,
                       bos.sectionId,
                       bos.salesmanId
                FROM business_order_salesman AS bos
                WHERE bos.seller_id = :sellerId AND bos.orderId = :orderId ";
        
        $params = array(
            ":sellerId" => $sellerId,
            ":orderId" => $orderId
        );
        
        $result = $this->query_SQL($sql, $event, null, $params);  
        
        if (!empty($result)) {
            $result = $result[0];
        }
        
        return $result;        
        
    }   

}
