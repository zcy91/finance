<?php
namespace console\models\base;

use console\models\BaseModel;

class View_BaseProductAttr extends BaseModel {
    
    public function getProductAttr($event, $sellerId, $productId) { 

        $sql = "SELECT bpa.attrId,
                       bpa.attrName,
                       bpa.genre,
                       bpa.required,
                       bpa.imageCount,
                       bpa.sort
                FROM base_product_attr AS bpa
                WHERE bpa.seller_id = :sellerId AND bpa.productId = :productId
                ORDER BY bpa.sort";
        
        $params = array(
            ":sellerId" => $sellerId,
            ":productId" => $productId
        );
        
        $result = $this->query_SQL($sql, $event, null, $params);

        return $result;        
    }  
    
}
