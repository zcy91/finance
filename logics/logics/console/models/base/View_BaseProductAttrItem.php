<?php
namespace console\models\base;

use console\models\BaseModel;

class View_BaseProductAttrItem extends BaseModel {
    
    public function getProductAttrItem($event, $sellerId, $productId) { 

        $sql = "SELECT bpai.attrId,
                       bpai.attrItemId,
                       bpai.attrItemName
                FROM base_product_attr_item AS bpai
                WHERE bpai.seller_id = :sellerId AND bpai.productId = :productId";
        
        $params = array(
            ":sellerId" => $sellerId,
            ":productId" => $productId
        );        
        
        $result = $this->query_SQL($sql, $event, null, $params);

        return $result;        
    }  
    
}
