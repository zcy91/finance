<?php
namespace console\models\user;

use console\models\BaseModel;

class UserMedium extends BaseModel {

    const TABLE_NAME = "user_medium";

    public function primaryKey() {
        return ['id' => 'auto'];
    }

    protected function resolveFields($v = array(), $default_arr = array()) {

        $subset = array( 
            "id",
            "seller_id",
            "mediumId",
            "createUserId",
            "deleted",
            "createtime"   
        );

        $info_arr = parent::key_values_intersect($v, $subset, $default_arr);
        return $info_arr;
    }
    
    public static function setAddData($event, $sellerId, $createUserId, $deleted, $nowTime) {
        
        $event->user_medium_data = array(    
            "seller_id" => $sellerId,
            "mediumId" => &$event->userId,
            "createUserId" => $createUserId,
            "deleted" => $deleted,
            "createtime" => $nowTime,
            "operate" => 1
        );  
        
    }
    
    public static function setEditData($event, $id, $deleted) {
        
        $event->user_medium_data = array(       
            "id" => $id,
            "deleted" => $deleted,
            "operate" => 0
        );        
       
    }     
    
    public function deleteAdd($event) {
        
        $data = $event->user_medium_data;
        if (!empty($data)) {

            $operate = $data["operate"];
            
            if ($operate) { 
                $this->add($event);
            } else {
                $sql = "DELETE FROM user_medium WHERE mediumId = :mediumId AND seller_id = :sellerId";
                
                $params = array(
                    ":mediumId" => $data["mediumId"],
                    ":sellerId" => $data["sellerId"]
                );    

                $this->update_sql($sql, $event, $params);                
            }
        }                    
    }  
    
    public function addModify($event){
        
        $data = $event->user_medium_data;
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
