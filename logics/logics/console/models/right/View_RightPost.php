<?php
namespace console\models\right;

use console\models\BaseModel;
use console\models\user\View_UserLogin;

class View_RightPost extends BaseModel {
    
    public function getOne($event, $postId, $sellerId){
        
        $one = 0;
        $isArray = is_array($postId) ? 1 : 0;
        if ((is_array($postId) && count($postId) == 1) || is_numeric($postId)) {
            $one = 1;
            $condition = " AND rp.id = :postId ";
            $postId = is_array($postId) ? $postId[0] : $postId;
        } else {
            $postId = implode(",", $postId);
            $condition = " AND rp.id IN ($postId)";
        }
        
        $sql = "SELECT rp.id,
                       rp.dnames,
                       rp.seller_id
                FROM right_post AS rp
                WHERE rp.seller_id = :sellerId $condition ";

        $params = array(
            ":sellerId" => $sellerId
        );
        
        if ($one) {
            $params[":postId"] = $postId;
        }        
        
        $result = $this->query_SQL($sql, $event, null, $params);  
        
        if (!$isArray) {
            if (!empty($result)) {
                $result = $result[0];
            }            
        }

        return $result;        
        
    }      

    public function getShowInfo($event, $userId, $sellerId) {
        
        $showId = 0;
        
        $sql = "SELECT rpu.seller_id, 
                       rpu.userId, 
                       rpb.baseId AS showId
                FROM right_post_user AS rpu INNER JOIN
                     right_post_base AS rpb ON rpu.seller_id = rpb.seller_id  AND rpu.postId = rpb.postId
                WHERE rpu.seller_id = :sellerId AND rpu.userId = :userId AND rpb.baseId IN (40,41,42,43)
                GROUP BY rpu.seller_id, rpu.userId";
        
        $params = array(
            ":userId" => $userId,
            ":sellerId" => $sellerId,
        );
        
        $data = $this->query_SQL($sql, $event, null, $params);
        
        $result = array(
            "showId" => 0,
            "showArr" => []
        );
        if (!empty($data)) {
            $showArr = array_column($data, "showId");
            $showId = min($showArr);
            $result["showArr"] = $showArr;
            $result["showId"] = $showId;
        }
        
        return $showId;
    }

}
