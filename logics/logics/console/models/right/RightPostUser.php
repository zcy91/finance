<?php
namespace console\models\right;

use console\models\BaseModel;

class RightPostUser extends BaseModel {

    const TABLE_NAME = "right_post_user";

    public function primaryKey() {
        return ['id' => 'auto'];
    }

    protected function resolveFields($v = array(), $default_arr = array()) {

        $subset = array(
            "id",
            "seller_id",
            "postId",
            "userId"    
        );

        $info_arr = parent::key_values_intersect($v, $subset, $default_arr);
        return $info_arr;
    }
    
    public static function setEditData($event, $useId, $sellerId, $newData, $oldData){
        
        $add = array_diff($newData, $oldData);
        $del = array_diff($oldData, $newData);
        
        foreach ($add as $addItem) {
            $event->right_post_user_add[] = array(
                "defaulte" => 0,
                "seller_id" => $sellerId,
                "userId" => $useId,
                "postId" => $addItem                
            );
        }
        
        $event->right_post_user_del = array(
            "seller_id" => $sellerId,
            "userId" => $useId,
            "postIds" => $del
        );
        
    }     
    
    public static function creatCondition($sortTable, $ids) {
        
        $fromStr = "INNER JOIN
                     right_post_user AS rpu ON $sortTable.$ids[0] = rpu.seller_id AND $sortTable.$ids[1] = rpu.userId";
        
        return $fromStr;
        
    }       

    public function deleteAdd($event) {
        
        $delData = $event->right_post_user_del;
        
        if (!empty($delData) && !empty($delData["postIds"])) {
            $postIds = $delData["postIds"];
            if (is_array($postIds)) {
                $condition = " AND postId IN(" . implode(",", $postIds) . ")";
            } else {
                $condition = " AND postId = $postIds";
            }

            $params = array(
                ":sellerId" => $delData["seller_id"],
                ":userId" => $delData["userId"]
            );

            $sql = "DELETE FROM right_post_user WHERE seller_id = :sellerId AND userId = :userId $condition";

            $this->update_sql($sql, $event, $params);                
        }        

        if (!empty($event->right_post_user_add)) {
            $event->right_post_user_data = $event->right_post_user_add;
            $this->add($event);
        }   
        
    }      

}
