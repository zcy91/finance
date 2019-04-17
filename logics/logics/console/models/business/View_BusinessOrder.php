<?php
namespace console\models\business;

use console\models\BaseModel;

class View_BusinessOrder extends BaseModel {
    
    public function getOne($event, $orderId, $sellerId){
        
        $sql = "SELECT bo.id,
                       /*bo.nos,*/
                       bo.customerUserName,
                       bo.applyName,
                       bo.applyMobile,
                       bo.applyCard,
                       /*bo.productNos,*/
                       bo.productNames,
                       bo.productImage,
                       bo.applyAmount,
                       bo.algorithm,
                       bo.resultAmount,
                       bo.commission,
                       bo.onePriced,
                       bo.actualAmount,
                       bo.receiveAmount,
                       bo.dstatus,
                       bo.seller_id,
                       bo.productId,
                       bo.customerUserId,
                       bo.dstatus,
                       bo.nowTime,
                       bos.relatedUserId AS saleId,
                       bos.relatedUserName AS saleName,
                       bocq.applyDays,
                       bocq.resultDays
                FROM business_order AS bo LEFT OUTER JOIN
                     business_order_status AS bos ON bo.seller_id = bos.seller_id AND bo.id = bos.orderId LEFT OUTER JOIN
                     business_order_commission_quot AS bocq ON bo.seller_id = bocq.seller_id AND bo.id = bocq.orderId
                WHERE bo.seller_id = :sellerId AND bo.id = :orderId ";
        
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
    
    public static function orderAttachStr($strType){
        
        switch ($strType) {
            case 1:
                $result = array(
                    "fromStr" => "business_order AS bo INNER JOIN
                     business_order_status AS bos ON bo.seller_id = bos.seller_id AND bo.id = bos.orderId AND bos.dstatus = :dstatus",
                    
                    "whereStr" => "WHERE bo.seller_id = :sellerId "
                );                                                   
                break;
            case 2:                         
                $result = array(                     
                    "fromStr" => "(SELECT DISTINCT
                            rsb.seller_id,
                            rsb.id AS sectionId
                     FROM right_post_user AS rpu INNER JOIN 
                     right_post_section AS rps ON rpu.seller_id = rps.seller_id AND rpu.postId = rps.postId INNER JOIN
                     right_section AS rsa ON rps.seller_id = rsa.seller_id AND rps.sectionId = rsa.id INNER JOIN
                          right_section AS rsb ON rsa.seller_id = rsb.seller_id AND rsb.parentsPath LIKE CONCAT(rsa.parentsPath, '%')
                     WHERE rpu.seller_id = :sellerId AND rpu.userId = :userId 
                    ) AS os INNER JOIN
                     business_order_status AS bos ON os.seller_id = bos.seller_id AND os.sectionId= bos.sectionId AND 
                      bos.dstatus = :dstatus INNER JOIN 
                     business_order AS bo ON bos.seller_id = bo.seller_id AND bos.orderId = bo.id ",   
                    
                    "whereStr" => "WHERE bos.seller_id = :sellerId "
                );
                break;
            case 3:
                $result = array(
                    "fromStr" => "business_order_status AS bos INNER JOIN
                     business_order AS bo ON bos.seller_id = bo.seller_id AND bos.orderId = bo.id",
                    
                    "whereStr" => "WHERE bos.seller_id = :sellerId AND bos.relatedUserId = :userId AND bos.dstatus = :dstatus",
                );
        }
        
        return $result;
        
    }
    
    public static function orderStatuStr($statuType){
        
        switch ($statuType) {
            //新订单
            case 1:
                $result = array(
                    "selectStr" => "                        
                       bos.relatedUserId AS saleId,
                       bos.relatedUserName AS saleName,
                       bos.relatedUserAccount AS saleAccount,",

                    "joinStr" => "",
                    "orderStr" => "ORDER BY bos.relatedTime Desc"
                );
                break;
            //审核订单
            case 2:
                $result = array(
                    "selectStr" => "                                       
                        bos.relatedUserId AS saleId,
                        bos.relatedUserName AS saleName,
                       bos.relatedUserAccount AS saleAccount,
                        bosa.relatedUserId AS checkId,
                       bosa.relatedUserName AS checkName,
                       bosa.relatedUserAccount AS checkAccount,
                       bosa.relatedTime AS checkTime,",
                    
                    "joinStr" => " LEFT OUTER JOIN
                     business_order_status AS bosa ON bo.seller_id = bosa.seller_id AND bo.id = bosa.orderId  AND bosa.dstatus = 6 ",
                    "orderStr" => "ORDER BY bos.relatedTime Desc"
                );
                break;
            //签约订单
            case 3:
                $result = array(
                    "selectStr" => "                      
                       bosa.relatedUserId AS saleId,
                        bosa.relatedUserName AS saleName,
                       bosa.relatedUserAccount AS saleAccount,
                        bos.relatedUserId AS checkId,
                        bos.relatedUserName AS checkName,                        
                       bos.relatedUserAccount AS checkAccount,
                       bos.relatedTime AS checkTime,
                        bosb.relatedUserId AS signId,
                       bosb.relatedUserName AS signName,
                       bosb.relatedUserAccount AS signAccount,
                       bosb.relatedTime AS signTime,
                       bocq.actualDays,",
                    
                    "joinStr" => " LEFT OUTER JOIN
                     business_order_status AS bosa ON bo.seller_id = bosa.seller_id AND bo.id = bosa.orderId AND bosa.dstatus = 2 LEFT OUTER JOIN
                     business_order_status AS bosb ON bo.seller_id = bosb.seller_id AND bo.id = bosb.orderId AND bosb.dstatus = 10 LEFT OUTER JOIN
                     business_order_commission_quot AS bocq ON bo.seller_id = bocq.seller_id AND bo.id = bocq.orderId",
                    "orderStr" => "ORDER BY bos.relatedTime Desc"
                );
                break;
            //回款订单
            case 4:
                $result = array(
                    "selectStr" => "                                         
                       bosa.relatedUserId AS saleId,
                                                           bosa.relatedUserName AS saleName,
                       bosa.relatedUserAccount AS saleAccount,
                                                           bosb.relatedUserId AS checkId,
                                                           bosb.relatedUserName AS checkName,                        
                       bosb.relatedUserAccount AS checkAccount,
                       bosb.relatedTime AS checkTime,                       
                                                           bos.relatedUserId AS signId,
                                                           bos.relatedUserName AS signName,  
                       bos.relatedUserAccount AS signAccount, 
                       bos.relatedTime AS signTime,  
                       bosc.relatedUserId AS receiveId,
                       bosc.relatedUserName AS receiveName,
                       bosc.relatedUserAccount AS receiveAccount,
                       bosc.relatedTime AS receiveTime, 
                       bocq.actualDays,
                       bocq.receiveDays,",
                    
                    "joinStr" => " LEFT OUTER JOIN
                     business_order_status AS bosa ON bo.seller_id = bosa.seller_id AND  bo.id = bosa.orderId AND bosa.dstatus = 2 LEFT OUTER JOIN
                     business_order_status AS bosb ON bo.seller_id = bosb.seller_id AND bo.id = bosb.orderId AND bosb.dstatus = 6 LEFT OUTER JOIN
                     business_order_status AS bosc ON bo.seller_id = bosc.seller_id AND bo.id = bosc.orderId AND bosc.dstatus = 15 LEFT OUTER JOIN
                     business_order_commission_quot AS bocq ON bo.seller_id = bocq.seller_id AND bo.id = bocq.orderId",
                    "orderStr" => "ORDER BY bos.relatedTime Desc"
                );                  
        }
        
        return $result;
        
    }    
    
    public function checkOrderSection($event, $userId, $sellerId, $orderId, $status){

        $params = array(
            ":userId" => $userId,
            ":sellerId" => $sellerId
        );

        $sql = "SELECT usr.seller_id,
                       usr.userId,
                       usr.superd,
                       MAX(IFNULL(rps.sectionId,0)) AS sectionId
                FROM user_seller_relation AS usr LEFT OUTER JOIN
                     right_post_user AS rpu ON usr.seller_id = rpu.seller_id AND usr.userId = rpu.userId INNER JOIN
                     right_post_section AS rps ON rpu.seller_id = rps.seller_id AND rpu.postId = rps.postId
                WHERE usr.seller_id = :sellerId AND usr.userId = :userId";
        
        $result = $this->query_SQL($sql, $event, null, $params);
        
        if (!empty($result)) {
            $result = $result[0];
            if ($result["superd"]) {
                return 1;
            } elseif ($result["sectionId"]) {
                $sql = "SELECT rpu.seller_id,
                               rpu.userId,
                               bo.id AS orderId,
                               bo.dstatus
                        FROM right_post_user AS rpu INNER JOIN
                             right_post_section AS rps ON rpu.seller_id = rps.seller_id AND rpu.postId = rps.postId INNER JOIN
                             right_section AS rsa ON rps.seller_id = rsa.seller_id AND rps.sectionId = rsa.id INNER JOIN
                             right_section AS rsb ON rsa.seller_id = rsb.seller_id AND 
                              rsb.parentsPath LIKE CONCAT(rsa.parentsPath, '%') INNER JOIN
                             business_order_status AS bos ON rsb.seller_id = bos.seller_id AND rsb.id = bos.sectionId AND 
                              bos.orderId = :orderId AND bos.dstatus = :dstatus INNER JOIN
                             business_order AS bo ON bos.seller_id = bo.seller_id AND 
                              bos.orderId = bo.id AND bo.id = :orderId
                        WHERE rpu.seller_id = :sellerId AND rpu.userId = :userId";

                $params[":orderId"] = $orderId;
                $params[":dstatus"] = $status;
                $result = $this->query_SQL($sql, $event, null, $params);
            
                if (!empty($result)) {
                    return 1;
            } else {
                    return 0;
                }
            } else {
                return 0;
            } 
        }

        return 0;
      
    }    
    
    public function getAllOrder($event, $ispage, $condition, $params, $limit, $attach){
        
        $selectStr = $attach["selectStr"];
        $fromStr = $attach["fromStr"];
        $joinStr = $attach["joinStr"];
        $orderStr = $attach["orderStr"];
        
        $sql = "SELECT " . ($ispage ? " sql_calc_found_rows " : "") . "
                       bo.seller_id,
                       bo.id,
                       /*bo.nos,*/
                       bo.applyName,
                       bo.applyMobile,
                       bo.applyCard,
                       bo.productId,
                       /*bo.productNos,*/
                       bo.productNames,
                       bo.creatTime AS applyTime,
                       bo.dstatus,
                       bo.applyAmount,
                       bo.resultAmount,
                       $selectStr
                       bo.customerUserId,
                       bo.customerUserName,
                       bo.customerUserAccount
                FROM $fromStr $joinStr
                $condition
                $orderStr";
        
        $result = $this->query_SQL($sql, $event, $limit, $params);
        
        return $result;
        
    } 

    public static function orderStatuDescStr($statuType){
        
        //$status 1:新订单，2:审核订单，3:签约订单，4:回款订单 5:(中介/客户)
        switch ($statuType) {
            //新订单
            case 1:
                $result = array(
                    "selectStr" => "   
                       bo.commission AS applyCommission,
                       bocq.applyDays,
                       bocq.resultDays,
                       bos.salesmanId AS saleId,
                       bos.salesmanName AS saleName,
                       bos.salesmanAccount AS saleAccount,",
                    
                    "joinStr" => "LEFT OUTER JOIN
                     business_order_commission_quot AS bocq ON bo.seller_id = bocq.seller_id AND bo.id = bocq.orderId LEFT OUTER JOIN
                     business_order_salesman AS bos ON bo.seller_id = bos.seller_id AND bo.id = bos.orderId ",
                );
                break;
            //审核订单
            case 2:
                $result = array(
                    "selectStr" => "    
                     /*bos.salesmanId AS saleId,
                       bos.salesmanName AS saleName,*/
                       bom.merchandiserId,
                       bom.merchandiserName,                       
                       bosa.relatedUserId AS checkId,
                       bosa.relatedUserName AS checkName,
                       bosa.relatedUserAccount AS checkAccount,
                       bosa.relatedTime AS checkTime,
                       bor.reason,
                       bocq.applyDays,",
                    
                    "joinStr" => "LEFT OUTER JOIN
                     business_order_salesman AS bos ON bo.seller_id = bos.seller_id AND bo.id = bos.orderId LEFT OUTER JOIN
                     business_order_merchandiser AS bom ON bo.seller_id = bom.seller_id AND bo.id = bom.orderId LEFT OUTER JOIN
                     business_order_status AS bosa ON bo.seller_id = bosa.seller_id AND bo.id = bosa.orderId AND bosa.dstatus = 6 LEFT OUTER JOIN
                     business_order_reason AS bor ON bo.seller_id = bor.seller_id AND bo.id = bor.orderId AND bosa.dstatus = 6 LEFT OUTER JOIN
                     business_order_commission_quot as bocq on bo.seller_id = bocq.seller_id AND bo.id = bocq.orderId",
                );
                break;
            //签约订单
            case 3:
                $result = array(
                    "selectStr" => "    
                       bo.commission AS applyCommission,
                       bo.customerUserId,
                       bo.customerUserName,                       
                       bo.customerUserAccount, 
                       bo.onePriced,
                       bocq.minDays,
                       bocq.applyDays,
                       bocq.resultDays,                        
                       bocq.actualDays,
                       bo.actualAmount,
                       bos.salesmanId AS saleId,
                       bos.salesmanName AS saleName,
                       bos.salesmanAccount AS saleAccount,
                       bom.merchandiserId,
                       bom.merchandiserName,                         
                       bom.merchandiserAccount, 
                       bosa.relatedUserId AS checkId,
                       bosa.relatedUserName AS checkName,                        
                       bosa.relatedUserAccount AS checkAccount, 
                       bosa.relatedTime AS checkTime,  
                       bosb.relatedUserId AS signId,
                       bosb.relatedUserName AS signName,
                       bosb.relatedUserAccount AS signAccount,
                       bosb.relatedTime AS signTime,",
                    
                    "joinStr" => "LEFT OUTER JOIN
                     business_order_commission_quot AS bocq ON bo.seller_id = bocq.seller_id AND bo.id = bocq.orderId LEFT OUTER JOIN
                     business_order_salesman AS bos ON bo.seller_id = bos.seller_id AND bo.id = bos.orderId LEFT OUTER JOIN
                     business_order_status AS bosa ON bo.seller_id = bosa.seller_id AND bo.id = bosa.orderId AND bosa.dstatus = 6 LEFT OUTER JOIN
                     business_order_merchandiser AS bom ON bo.seller_id = bom.seller_id AND bo.id = bom.orderId LEFT OUTER JOIN
                     business_order_status AS bosb ON bo.seller_id = bosb.seller_id AND bo.id = bosb.orderId AND bosb.dstatus = 10",
                );
                break;
            //回款订单
            case 4:
            //(中介/客户)    
            case 5:
                $result = array(
                    "selectStr" => "  
                       bo.commission AS applyCommission,
                       bo.customerUserId,
                       bo.customerUserName,
                       bo.customerUserAccount,
                       bocq.applyDays,
                       bocq.resultDays,                        
                       bocq.resultDays,          
                       bocq.actualDays,
                       bo.actualAmount,
                       bo.receiveAmount,                       
                       bosa.relatedUserId AS saleId,
                       bosa.relatedUserName AS saleName,
                       bosa.relatedUserAccount AS saleNameAccount,
                       bosb.relatedUserId AS checkId,
                       bosb.relatedUserName AS checkName,                        
                       bosb.relatedUserAccount AS checkAccount,  
                       bosb.relatedTime AS checkTime,   
                       bos.relatedUserId AS signId,
                       bos.relatedUserName AS signName,  
                       bos.relatedUserAccount AS signAccount,
                       bosb.relatedTime AS signTime
                       bosc.relatedUserId AS receiveId,
                       bosc.relatedUserName AS receiveName,
                       bosc.relatedUserAccount AS receiveAccount,
                       bosc.relatedTime AS receiveTime,",
                    
                    "joinStr" => " INNER JOIN
                     business_order_commission_quot AS bocq ON bo.seller_id = bocq.seller_id AND bo.id = bocq.orderId LEFT OUTER JOIN
                     business_order_status AS bos ON bo.seller_id = bos.seller_id AND bo.id = bos.orderId  AND bosa.dstatus = 0 LEFT OUTER JOIN
                     business_order_status AS bosa ON bo.seller_id = bosa.seller_id AND bo.id = bosa.orderId AND bosa.dstatus = 6 LEFT OUTER JOIN
                     business_order_merchandiser AS bom ON bo.seller_id = bom.seller_id AND bo.id = bom.orderId LEFT OUTER JOIN
                     business_order_status AS bosb ON bo.seller_id = bosb.seller_id AND bo.id = bosb.orderId AND bosb.dstatus = 10 LEFT OUTER JOIN
                     business_order_status AS bosc ON bo.seller_id = bosc.seller_id AND bo.id = bosc.orderId AND bosc.dstatus = 15",
                ); 
                
        }
        
        return $result;
        
    }    
    
    public function getOrderDesc($event, $orderId, $sellerId, $attach){
      
        $selectStr = $attach["selectStr"];
        $joinStr = $attach["joinStr"];        

        $sql = "SELECT bo.id,
                     /*bo.nos,
                       bo.customerUserName,*/
                       bo.applyName,
                       bo.applyMobile,
                       bo.applyCard,
                       /*bo.productNos,*/
                       bo.productNames,
                       bo.productImage,
                       bo.applyAmount,
                       bo.algorithm,
                       bo.resultAmount,
                       bo.dstatus,
                       bo.seller_id,
                       $selectStr
                       bo.productId
                     /*bo.customerUserId*/
                FROM business_order AS bo $joinStr
                WHERE bo.seller_id = :sellerId AND bo.id = :orderId ";
        
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
    
    public function orderOwnList($event, $ownType, $ispage, $condition, $params, $limit) {
        
        $selectStr = "SELECT " . ($ispage ? " sql_calc_found_rows " : "") . "
                            bo.id,
                             /*bo.nos,*/
                             bo.customerUserName,
                             bo.applyName,
                             bo.applyMobile,
                             bo.applyCard,
                             /*bo.productNos,*/
                             bo.productNames,
                             bo.productImage,
                             bo.applyAmount,
                             bo.commission,
                             bo.algorithm,
                             bo.resultAmount,
                             bo.onePriced,
                             bo.actualAmount,
                             bo.receiveAmount,
                             bo.dstatus,
                             bo.seller_id,
                             bo.productId,
                             bo.customerUserId";
        //$ownType 1:(客户/中介) 2:业务员 3:跟单员
        switch ($ownType) {
            case 2:
            case 3:
                $fromStr = "
                  FROM business_order AS bo                     
                    ";
                $whereStr = "
                  WHERE bo.seller_id = :sellerId AND bo.customerUserId = :usrId $condition";                 
                break;
            case 4:
                $fromStr = "
                  FROM business_order_salesman AS bos INNER JOIN
                       business_order AS bo on bos.seller_id = bo.seller_id AND bos.orderId = bo.id                     
                    ";  
                $whereStr = "
                  WHERE bos.seller_id = :sellerId AND bos.salesmanId = :usrId $condition";  
                break;
            case 5:
                $fromStr = "
                FROM business_order_merchandiser AS bom INNER JOIN
                     business_order AS bo on bom.seller_id = bo.seller_id AND bom.orderId = bo.id                     
                    ";    
                $whereStr = "
                  WHERE bom.seller_id = :sellerId AND bom.merchandiserId = :usrId $condition";                  
        }  
        
        $sql = $selectStr . $fromStr . $whereStr;
        
        $result = $this->query_SQL($sql, $event, $limit, $params);
        
        return $result;
        
    }    

    public function getConsoleSumm($event, $condition, $params, $attach){
        
        $selectStr = $attach["selectStr"];
        $fromStr = $attach["fromStr"];
        $joinStr = $attach["joinStr"];
        
        $sql = "SELECT $selectStr
                FROM $fromStr $joinStr
                $condition ";
        
        $result = $this->query_SQL($sql, $event, null, $params);
        
        if (!empty($result)) {
            $result = $result[0];
        }
        
        return $result;
        
    }             

}
