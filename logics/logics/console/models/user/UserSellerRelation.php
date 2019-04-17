<?php
namespace console\models\user;

use console\models\BaseModel;

class UserSellerRelation extends BaseModel {

    const TABLE_NAME = "user_seller_relation";

    public function primaryKey() {
        return ['seller_id' => 'key', 'userId' => 'key', 'id' => 'auto'];
    }

    protected function resolveFields($v = array(), $default_arr = array()) {

        $subset = array(
            "id",
            "sections",
            "postId",
            "posts",
            "roles",
            "superd",
            "defaulte",
            "deleted",
            "seller_id",
            "userId",
            "nowTime"          
        );

        $info_arr = parent::key_values_intersect($v, $subset, $default_arr);
        return $info_arr;
    }
    
    public static function setAddData($event, $sellerId, $superd, &$sections, &$roles, $nowTime) {
        
        $event->user_seller_relation_data = array(       
            "sections" => &$sections,
            "roles" => &$roles,
            "superd" => $superd,
            "defaulte" => 0,
            "deleted" => 0,
            "seller_id" => $sellerId,
            "userId" => &$event->userId,
            "nowTime" => $nowTime,
            "operate" => 1
        );        
        
    } 
    
    public static function setEditData($event, $id, $deleted, $nowTime) {
        
        $event->user_seller_relation_data = array(       
            "id" => $id,
            "deleted" => $deleted,
            "nowTime" => $nowTime,
            "operate" => 0
        );        
        
    }    

    public function addModify($event){
        
        $data = $event->user_seller_relation_data;
        if (!empty($data)) {

            $operate = $data["operate"];
            
            if ($operate) { 
                $this->add($event);
            } else {
                $this->delete($event);              
            }
        }           
    }

}
