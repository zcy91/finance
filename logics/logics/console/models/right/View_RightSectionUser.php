<?php
namespace console\models\right;

use console\models\BaseModel;

class View_RightSectionUser extends BaseModel {

    public function getDefaultSection($event, $userId, $sellerId) {    
 
        $sql = "SELECT rsu.sectionId,
                       rs.dnames AS sectionName
                FROM right_section_user AS rsu INNER JOIN
                     right_section AS rs ON rsu.sectionId = rs.id
                WHERE rsu.seller_id = :seller_id AND rsu.userId = :userId
                ORDER BY rsu.default DESC";
        
        $params = array(
            ":userId" => $userId,
            ":seller_id" => $sellerId
        );
      
        $result = $this->query_SQL($sql, $event, null, $params);      
        
        return $result;
    }
    
    public function getOneSeller($event, $userId, $sellerId){
        
        $sql = "SELECT id,
                      `default`,
                       sectionId
                FROM right_section_user AS rsu
                WHERE rsu.seller_id = :sellerId AND rsu.userId = :userId  ";
        
        $params = array(
            ":userId" => $userId, 
            ":sellerId" => $sellerId, 
        );
        
        $result = $this->query_SQL($sql, $event, null, $params);  
        
        return $result;         
    } 
    
    public function getStaffSection($event, $userId, $sellerId){
        
        $sql = "SELECT rsu.seller_id,
                       rsu.userId,
                       rsu.sectionId,
                       rs.dnames
                FROM right_section_user AS rsu INNER JOIN
                     right_section AS rs ON rsu.seller_id = rs.seller_id AND rsu.sectionId = rs.id
                WHERE rsu.seller_id = :sellerId AND rsu.userId = :userId";
        
        $params = array(
            ":userId" => $userId, 
            ":sellerId" => $sellerId, 
        );
        
        $result = $this->query_SQL($sql, $event, null, $params);  
        
        return $result;         
    }     
    
}
