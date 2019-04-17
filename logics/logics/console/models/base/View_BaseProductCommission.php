<?php
namespace console\models\base;

use console\models\BaseModel;

class View_BaseProductCommission extends BaseModel {
    
    public function getOneCommissionPercentage($event, $sellerId, $productId){
        
        $sql = "SELECT bpcp.seller_id,
                       bpcp.productId,
                       bpcp.commission,
                       bpcp.mediumCommission,
                       bpcp.salesmanCommission
                FROM base_product_commission_percentage AS bpcp
                WHERE bpcp.seller_id = :sellerId AND bpcp.productId = :productId ";
        
        $params = array(
            ":sellerId" => $sellerId,
            ":productId" => $productId
        );
        
        $result = $this->query_SQL($sql, $event, null, $params);  
        
        if (!empty($result)) {
            $result = $result[0];
        }
        
        return $result;        
        
    } 
    
    public function getOneCommissionQuot($event, $sellerId, $productId){
        
        $sql = "SELECT bpcq.seller_id,
                       bpcq.productId,
                       bpcq.minAmount,
                       bpcq.minDays,                       
                       bpcq.commission,
                       bpcq.mediumCommission,
                       bpcq.salesmanCommission
                FROM base_product_commission_quot AS bpcq
                WHERE bpcq.seller_id = :sellerId AND bpcq.productId = :productId ";
        
        $params = array(
            ":sellerId" => $sellerId,
            ":productId" => $productId
        );
        
        $result = $this->query_SQL($sql, $event, null, $params);  
        
        if (!empty($result)) {
            $result = $result[0];
        }
        
        return $result;        
        
    }     

}
