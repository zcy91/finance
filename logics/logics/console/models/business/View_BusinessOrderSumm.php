<?php
namespace console\models\business;

use console\models\BaseModel;

class View_BusinessOrderSumm extends BaseModel {
    
    public function orderCountSumm($event, $summType, $condition, $params) {
        
        $summFORMAT = $summName = "";
        switch ($summType) {
            case 1:
                $summFORMAT = "%Y-%m-%d";
                $summName = "dayNo";
                break;
            case 2:
                $summFORMAT = "%Y-%U";
                $summName = "weekNo";                
                break;
            default:
                $summFORMAT = "%Y-%m";
                $summName = "monthNo";                            
        }
        
        $sql = "SELECT DATE_FORMAT(bos.relatedTime,'" . $summFORMAT . "') AS " . $summName . ",
                       COUNT(*) AS orderCount
                FROM business_order AS bo INNER JOIN
                     business_order_status AS bos ON bo.seller_id = bos.seller_id AND
                      bo.id = bos.orderId
                WHERE bo.seller_id = :sellerId AND bos.dstatus = 2
                      $condition
                GROUP BY $summName";
        
        $result = $this->query_SQL($sql, $event, null, $params);
        
        return $result;
    }  
    
    public function orderAmountSumm($event, $summType, $condition, $params) {
        
        $summFORMAT = $summName = "";
        switch ($summType) {
            case 1:
                $summFORMAT = "%Y-%m-%d";
                $summName = "dayNo";
                break;
            case 2:
                $summFORMAT = "%Y-%U";
                $summName = "weekNo";                
                break;
            default:
                $summFORMAT = "%Y-%m";
                $summName = "monthNo";                            
        }
        
        $sql = "SELECT DATE_FORMAT(bos.relatedTime,'" . $summFORMAT . "') AS " . $summName . ",
                       SUM(bo.resultAmount) AS orderAmount
                FROM business_order AS bo INNER JOIN
                     business_order_status AS bos ON bo.seller_id = bos.seller_id AND
                      bo.id = bos.orderId
                WHERE bo.seller_id = :sellerId AND bos.dstatus = 6 AND bo.dstatus > 7
                      $condition
                GROUP BY $summName";
        
        $result = $this->query_SQL($sql, $event, null, $params);
        
        return $result;
    }      
    
}
