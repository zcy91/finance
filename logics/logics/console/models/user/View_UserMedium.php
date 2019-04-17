<?php

namespace console\models\user;

use console\models\BaseModel;

class View_UserMedium extends BaseModel {
    
    
    public function getOneSeller($event, $mediumId, $sellerId){
        
        $sql = "SELECT id,
                       seller_id
                       mediumId,
                       createUserId,
                       deleted
                FROM user_medium AS um
                WHERE um.seller_id = :sellerId AND um.mediumId = :mediumId    ";
        
        $params = array(
            ":mediumId" => $mediumId, 
            ":sellerId" => $sellerId, 
        );
        
        $result = $this->query_SQL($sql, $event, null, $params);  
        
        if (!empty($result)) {
            $result = $result[0];
        }
        
        return $result;        
        
    }  

    public function mediumDesc($event, $sellerId, $mediumId){
        
        $sql = "SELECT um.seller_id,
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
                FROM user_medium AS um INNER JOIN
                     user_login AS ul ON um.userId = ul.id INNER JOIN
                     user_profile AS up ON um.userId = up.userId
                WHERE um.seller_id = :sellerId AND um.customerId = :customerId";
        
        $params = array(
            ":sellerId" => $sellerId,
            ":mediumId" => $mediumId
        );
     
        $result = $this->query_SQL($sql, $event, null, $params);
        
        if (!empty($result)) {
            $result = $result[0];
        }
        
        return $result;
    }        

    public function mediumList($event, $ispage, $condition, $params, $limit) {

        $sql = "SELECT " . ($ispage ? " sql_calc_found_rows " : "") . "
                       um.seller_id,
                       um.mediumId,
                       ul.dnames AS mediumAccount,
                       up.dnames AS mediumName,
                       up.sex,
                       up.mobile,
                       up.idCard,
                       up.bank,
                       up.bankNo,
                       up.wxNo,
                       up.zfbNo,
                       up.homeAddress
                FROM user_medium AS um INNER JOIN
                     user_login AS ul ON um.mediumId = ul.id INNER JOIN
                     user_profile AS up ON um.mediumId = up.userId
                WHERE um.seller_id = :sellerId $condition";

        $result = $this->query_SQL($sql, $event, $limit, $params);

        return $result;
    }

}
