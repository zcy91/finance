<?php
namespace console\models\user;

use console\models\BaseModel;

class UserRole extends BaseModel {

    const TABLE_NAME = "user_role";

    public function primaryKey() {
        return ['id' => 'auto'];
    }

    protected function resolveFields($v = array(), $default_arr = array()) {

        $subset = array( 
            "id",
            "default",
            "useId",
            "roleId"   
        );

        $info_arr = parent::key_values_intersect($v, $subset, $default_arr);
        return $info_arr;
    }
    
    public static function setAddData($event, $roleId) {
        
        $event->user_role_data = array(    
            "defaulte" => 0,
            "useId" => &$event->userId,
            "roleId" => $roleId,
        );  
        
    }

    public function deleteAdd($event) {
        
        $data = $event->user_role_data;
        if (!empty($data)) {

            $operate = $data["operate"];
            
            if ($operate) { 
                $this->add($event);
            } else {
                $sql = "DELETE FROM user_role WHERE useId = :useId AND roleId = :roleId";
                
                $params = array(
                    ":useId" => $data["useId"],
                    ":roleId" => $data["roleId"]
                );    

                $this->update_sql($sql, $event, $params);                
            }
        }                    
    }    

}
