<?php
namespace console\models\user;

use console\models\BaseModel;

class UserCustomer extends BaseModel {

    const TABLE_NAME = "user_customer";

    public function primaryKey() {
        return ['id' => 'auto'];
    }

    protected function resolveFields($v = array(), $default_arr = array()) {

        $subset = array( 
            "id",
            "seller_id",
            "customerId",
            "createUserId",
            "createtime"   
        );

        $info_arr = parent::key_values_intersect($v, $subset, $default_arr);
        return $info_arr;
    }
    
    public static function setAddData($event, $sellerId, $createUserId, $deleted, $nowTime) {
        
        $event->user_customer_data = array(    
            "seller_id" => $sellerId,
            "customerId" => &$event->userId,
            "deleted" => $deleted,
            "createUserId" => $createUserId,
            "createtime" => $nowTime,
            "operate" => 1
        );  
        
    }
    
    public static function setEditData($event, $id, $deleted) {
        
        $event->user_customer_data = array(       
            "id" => $id,
            "deleted" => $deleted,
            "operate" => 0
        );        
       
    }    
    
    public function deleteAdd($event) {
        
        $data = $event->user_customer_data;
        if (!empty($data)) {

            $operate = $data["operate"];
            
            if ($operate) { 
                $this->add($event);
            } else {
                $sql = "DELETE FROM user_customer WHERE customerId = :customerId AND seller_id = :sellerId";
                
                $params = array(
                    ":customerId" => $data["customerId"],
                    ":sellerId" => $data["sellerId"]
                );    

                $this->update_sql($sql, $event, $params);                
            }
        }                    
    }    

}
