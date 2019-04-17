<?php
namespace console\models\right;

use console\models\BaseModel;

class View_RightPostUser extends BaseModel {

    public function getOneSeller($event, $userId, $sellerId){
        
        $sql = "SELECT id,
                       postId
                FROM right_post_user AS rpu
                WHERE rpu.seller_id = :sellerId AND rpu.userId = :userId  ";
        
        $params = array(
            ":userId" => $userId, 
            ":sellerId" => $sellerId, 
        );
        
        $result = $this->query_SQL($sql, $event, null, $params);  
        
        return $result;         
    }    

    public function getStaffPost($event, $userId, $sellerId){
        
        $sql = "SELECT rpu.seller_id,
                       rpu.userId,
                       rpu.postId,
                       rp.dnames
                FROM right_post_user AS rpu INNER JOIN
                     right_post AS rp ON rpu.seller_id = rp.seller_id AND rpu.postId = rp.id
                WHERE rpu.seller_id = :sellerId AND rpu.userId = :userId";
        
        $params = array(
            ":userId" => $userId, 
            ":sellerId" => $sellerId, 
        );
        
        $result = $this->query_SQL($sql, $event, null, $params);  
        
        return $result;         
    }  

    

}
