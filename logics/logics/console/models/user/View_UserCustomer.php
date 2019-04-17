<?php

namespace console\models\user;

use console\models\BaseModel;

class View_UserCustomer extends BaseModel {
    
    public function getOneSeller($event, $customerId, $sellerId){
        
        $sql = "SELECT id,
                       seller_id
                       customerId,
                       createUserId,
                       deleted
                FROM user_customer AS uc
                WHERE uc.seller_id = :sellerId AND uc.customerId = :customerId    ";
        
        $params = array(
            ":customerId" => $customerId, 
            ":sellerId" => $sellerId, 
        );
        
        $result = $this->query_SQL($sql, $event, null, $params);  
        
        if (!empty($result)) {
            $result = $result[0];
        }
        
        return $result;        
        
    }  
    
    public function customerDesc($event, $sellerId, $customerId){
        
        $sql = "SELECT uc.seller_id,
                       ul.id,
                       ul.dnames AS account,
                       ul.mobile,
                       ul.email,
                       up.pic,
                       up.dnames,
                       up.officePhone,
                       up.idCard,
                       up.bank,
                       up.bankNo,
                       up.wxNo,
                       up.zfbNo
                FROM user_customer AS uc INNER JOIN
                     user_login AS ul ON uc.customerId = ul.id INNER JOIN
                     user_profile AS up ON uc.customerId = up.userId
                WHERE uc.seller_id = :sellerId AND uc.customerId = :customerId";
        
        $params = array(
            ":sellerId" => $sellerId,
            ":customerId" => $customerId
        );
     
        $result = $this->query_SQL($sql, $event, null, $params);
        
        if (!empty($result)) {
            $result = $result[0];
        }
        
        return $result;
    }    

    public function customerList($event, $ispage, $condition, $params, $limit) {

        $sql = "SELECT " . ($ispage ? " sql_calc_found_rows " : "") . "
                       uc.seller_id,
                       uc.customerId,
                       ul.dnames AS customerAccount,
                       up.dnames AS customerName,
                       up.sex,
                       up.mobile,
                       up.idCard,
                       up.bank,
                       up.bankNo,
                       up.wxNo,
                       up.zfbNo
                FROM user_customer AS uc INNER JOIN
                     user_login AS ul ON uc.customerId = ul.id INNER JOIN
                     user_profile AS up ON uc.customerId = up.userId
                WHERE uc.seller_id = :sellerId $condition
                ORDER BY uc.createtime DESC";

        $result = $this->query_SQL($sql, $event, $limit, $params);

        return $result;
    }

    public function customerSumm($event, $summType, $condition, $params) {
        
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
        
        $sql = "SELECT DATE_FORMAT(uc.createtime,'" . $summFORMAT . "') AS " . $summName . ",
                       COUNT(*) AS customerCount
                FROM user_customer AS uc
                WHERE uc.seller_id = :sellerId AND uc.deleted = 0
                      $condition
                GROUP BY $summName";
        
        $result = $this->query_SQL($sql, $event, null, $params);
        
        return $result;
    }

}
