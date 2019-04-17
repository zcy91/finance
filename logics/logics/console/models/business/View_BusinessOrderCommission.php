<?php
namespace console\models\business;

use console\models\BaseModel;

class View_BusinessOrderCommission extends BaseModel {
    
    public function getOneCommissionQuot($event, $sellerId, $orderId){
        
        $sql = "SELECT bocq.id,
                       bocq.seller_id,
                       bocq.orderId,
                       bocq.minAmount,
                       bocq.minDays,
                       bocq.commission,
                       bocq.mediumCommission,
                       bocq.salesmanCommission,
                       bocq.applyDays,
                       bocq.resultDays,
                       bocq.actualDays,
                       bocq.receiveDays
                FROM business_order_commission_quot AS bocq
                WHERE bocq.seller_id = :sellerId AND bocq.orderId = :orderId ";
        
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
    
    public function getOneCommissionPercentage($event, $sellerId, $orderId){
        
        $sql = "SELECT bocp.seller_id,
                       bocp.orderId,
                       bocp.commission,
                       bocp.mediumCommission,
                       bocp.salesmanCommission
                FROM business_order_commission_percentage AS bocp
                WHERE bocp.seller_id = :sellerId AND bocp.orderId = :orderId ";
        
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
