<?php
namespace console\models\base;

use console\models\BaseModel;

class View_BusinessOrderStatus extends BaseModel {
    
    public function getOne($event, $orderId, $sellerId, $status){
        
        $sql = "SELECT ba.id,
                       ba.dnames,
                       ba.genre,
                       ba.sort,
                       ba.values,
                       ba.seller_id
                FROM base_attr AS ba
                WHERE ba.seller_id = :sellerId AND ba.id = :sectionId ";
        
        $params = array(
            ":sellerId" => $sellerId,
            ":sectionId" => $sectionId
        );
        
        $result = $this->query_SQL($sql, $event, null, $params);  
        
        if (!empty($result)) {
            $result = $result[0];
        }
        
        return $result;        
        
    }      
}
