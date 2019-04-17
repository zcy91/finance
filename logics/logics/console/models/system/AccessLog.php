<?php
namespace console\models\system;

use console\models\BaseModel;

class AccessLog extends BaseModel {

    const TABLE_NAME = "access_log";

    public function primaryKey() {
        return ['logger_id' => 'auto'];
    }

    protected function resolveFields($v = array(), $default_arr = array()) {

        $subset = array(
            "logger_id",
            "customer_id",
            "site_id",
            "dis_lang_id",
            "user_id",
            "nick_name",
            "module_name",
            "controller_name",
            "action_name",
            "args",
            "src_ip",
            "webserver_ip",
            "webserver_url",
            "createtime"
        );

        $info_arr = parent::key_values_intersect($v, $subset, $default_arr);
        return $info_arr;
    }
    
    public function logAdd($event){
        
        $id = $this->insert(self::TABLE_NAME, $event->access_log_data, $event);
        $event->Postback($id);
        
    }

}
