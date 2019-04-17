<?php
namespace console\models\right;

use console\models\BaseModel;

class View_RightStaffRole extends BaseModel {
    
    public function getDefaultRole($event, $userId, $sellerId) {    
 
        $sql = "SELECT rsr.roleId,
                       sr.dnames AS roleNames,
                       rsr.default
                FROM right_staff_role AS rsr INNER JOIN
                     system_role AS sr ON rsr.roleId = sr.id
                WHERE seller_id = :sellerId  AND userId = :userId
                ORDER BY rsr.default DESC";
        
        $params = array(
            ":userId" => $userId,
            ":sellerId" => $sellerId
        );
      
        $result = $this->query_SQL($sql, $event, null, $params);      
        
        return $result;
    }
    
    public function getOneSeller($event, $userId, $sellerId){
        
        $sql = "SELECT id,
                       defaulte,
                       roleId
                FROM right_staff_role AS rsr
                WHERE rsr.seller_id = :sellerId AND rsr.userId = :userId  ";
        
        $params = array(
            ":userId" => $userId, 
            ":sellerId" => $sellerId, 
        );
        
        $result = $this->query_SQL($sql, $event, null, $params);  
        
        return $result;         
    }    

    public function getStaffRole($event, $userId, $sellerId){
        
        $sql = "SELECT rsr.seller_id,
                       rsr.userId,
                       rsr.roleId,
                       sr.dnames
                FROM right_staff_role AS rsr INNER JOIN
                     system_role AS sr ON rsr.roleId = sr.id
                WHERE rsr.seller_id = :sellerId  AND rsr.userId = :userId";
        
        $params = array(
            ":userId" => $userId, 
            ":sellerId" => $sellerId, 
        );
        
        $result = $this->query_SQL($sql, $event, null, $params);  
        
        return $result;         
    }      

}
