<?php

namespace console\models\user;

use console\models\BaseModel;

class View_UserProfile extends BaseModel {

    public function staffList($event, $ispage, $condition, $params, $limit = null, $fromStr = "") {

        $sql = "SELECT " . ($ispage ? " sql_calc_found_rows " : "") . "
                       ul.id,
                       up.id AS staffId,
                       up.pic AS staffImage,
                       ul.dnames AS staffAccount,
                       up.dnames AS staffName,
                       up.sex,
                       usr.sections,
                       usr.postId,
                       usr.posts,
                       up.mobile,
                       up.dstatus
                FROM user_seller_relation AS usr INNER JOIN
                     user_login AS ul ON usr.userId = ul.id INNER JOIN
                     user_profile AS up ON usr.userId = up.userId
                     $fromStr
                WHERE usr.seller_id = :sellerId $condition 
                ORDER BY usr.nowTime DESC";

        $result = $this->query_SQL($sql, $event, $limit, $params);

        return $result;
    }

    public function getOrderLogUser($event ,$sellerId, $userId) {

        $sql = "SELECT ul.id AS customerId,
                       ul.dnames AS customerAccount,
                       up.dnames AS customerName,
                       IF(ur.roleId = 1, rsr.roleId, ur.roleId) AS roleId
                FROM user_login AS ul INNER JOIN
                     user_profile AS up ON ul.id = up.userId INNER JOIN
                     user_role AS ur ON up.userId = ur.useId LEFT OUTER JOIN
                     user_customer AS uc ON uc.seller_id = :sellerId AND up.userId = uc.customerId LEFT OUTER JOIN
                     user_medium AS um ON um.seller_id = :sellerId AND up.userId = um.mediumId LEFT OUTER JOIN
                     user_seller_relation AS usr ON usr.seller_id = :sellerId AND up.userId = usr.userId LEFT OUTER JOIN
                     right_staff_role AS rsr ON rsr.seller_id = :sellerId AND up.userId = rsr.userId
                WHERE up.userId = :userId AND IF(ur.roleId = 1, IF(IFNULL(usr.superd,0) = 1, 4, rsr.roleId), ur.roleId) IN (2,3,4) ";
        
        $params = array(
            ":sellerId" => $sellerId,
            ":userId" => $userId
        );       
        
        $result = $this->query_SQL($sql, $event, null, $params);
        
        if (!empty($result)) {
            $result = $result[0];
        }
        
        return $result;
    }
    
    public function getStaffInfo($event ,$sellerId, $userId) {

        $sql = "SELECT usr.seller_id,
                       usr.userId,
                       usr.superd,
                       ul.dnames AS userAccount,
                       up.dnames AS userName
                FROM user_seller_relation AS usr INNER JOIN 
                     user_login AS ul ON usr.userId = ul.id INNER JOIN 
                     user_profile AS up ON usr.userId = up.userId
                WHERE usr.seller_id = :sellerId AND usr.userId = :userId";
        
        $params = array(
            ":sellerId" => $sellerId,
            ":userId" => $userId
        );       
        
        $result = $this->query_SQL($sql, $event, null, $params);
        
        if (!empty($result)) {
            $result = $result[0];
        }
        
        return $result;
    }    

}
