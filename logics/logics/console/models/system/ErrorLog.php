<?php
namespace console\models\system;

use console\models\BaseModel;

class ErrorLog extends BaseModel {

    const TABLE_NAME = "error_log";
    
    public function primaryKey() {
        return ['error_code' => 'auto'];
    }    

    protected function resolveFields($v = array(), $default_arr = array()) {

        $subset = array(
            "error_code",
            "error_type",
            "message",
            "createtime"
        );

        $info_arr = parent::key_values_intersect($v, $subset, $default_arr);
        return $info_arr;
    }
    
    public function errAdd($event){     
        $this->insert(self::TABLE_NAME, $event->error_log_data, $event);        
    }    

}
