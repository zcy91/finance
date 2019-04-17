<?php
namespace console\models\base;

use console\models\BaseModel;

class View_BaseProductcategory extends BaseModel {
    
    public function getOne($event, $categoryId, $sellerId){
        
        $sql = "SELECT bpc.id,
                       bpc.dnames,
                       bpc.seller_id,
                       bpc.pid,
                       bpc.algorithm,
                       bpc.parentsPath
                FROM base_product_category AS bpc
                WHERE bpc.seller_id = :sellerId AND bpc.id = :categoryId ";
        
        $params = array(
            ":sellerId" => $sellerId,
            ":categoryId" => $categoryId
        );
        
        $result = $this->query_SQL($sql, $event, null, $params);  
        
        if (!empty($result)) {
            $result = $result[0];
        }
        
        return $result;        
        
    }      

}
