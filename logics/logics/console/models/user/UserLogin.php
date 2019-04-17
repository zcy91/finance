<?php
namespace console\models\user;

use console\models\BaseModel;

class UserLogin extends BaseModel {

    const TABLE_NAME = "user_login";

    public function primaryKey() {
        return ['id' => 'auto'];
    }

    protected function resolveFields($v = array(), $default_arr = array()) {

        $subset = array(       
            "id",
            "dnames",
            "mobile",
            "email",
            "password",
            "salt",
            "loginTime",
            "loginIp",
            "loginTimes",
            "locked",
            "lockedTime",
            "openId",
            "deleted",
            "createtime"          
        );

        $info_arr = parent::key_values_intersect($v, $subset, $default_arr);
        return $info_arr;
    }
    
    public function get_seq_no($event, $data_arr, $array_dim) {

        $count = $array_dim == 1 ? 1 : count($data_arr);
        $seq_no = 0;
        $this->proc_call('getKeyValue', array(201, $count), $seq_no, $event);

        return $seq_no;
    }

    public function refer_to($event, $data_arr, $array_dim, $seq_no, $count) {
        $event->set_user_id($seq_no);
    }

    public static function setAddData($event, $data, $nowTime){
        
        $event->user_login_data = array(       
            "id" => &$event->userId,
            "dnames" => $data["account"],
            "mobile" => $data["mobile"],
            "email" => $data["email"],
            "password" =>(isset($data["password"]) && !empty($data["password"]) && is_string($data["password"])) ? $data["password"] : "",
            "salt" => (isset($data["salt"]) && !empty($data["salt"]) && is_string($data["salt"])) ? $data["salt"] : "",
            "locked" => 0,
            "openId" => (isset($data["openId"]) && !empty($data["openId"]) && is_string($data["openId"])) ? $data["openId"] : "",
            "deleted" => 0,
            "createtime" => $nowTime 
        );        
        
    }
    
    public static function setEditData($event, $id, $newData, $oldData){
        
        if (isset($newData["account"]) && !empty($newData["account"]) && $newData["account"] != $oldData["dnames"]) {
            $event->user_login_data["dnames"] = $newData["account"];
        } 
        
        if (isset($newData["mobile"]) && !empty($newData["mobile"]) && $newData["mobile"] != $oldData["mobile"]) {
            $event->user_login_data["mobile"] = $newData["mobile"];
        }  

        if (isset($newData["email"]) && !empty($newData["email"]) && $newData["email"] != $oldData["email"]) {
            $event->user_login_data["email"] = $newData["email"];
        }  
        
        if (isset($newData["password"]) && !empty($newData["password"]) && $newData["password"] != $oldData["password"]) {
            $event->user_login_data["password"] = $newData["password"];
        } 

        if (isset($newData["salt"]) && !empty($newData["salt"]) && $newData["salt"] != $oldData["salt"]) {
            $event->user_login_data["salt"] = $newData["salt"];
        }  
        
        if (isset($newData["locked"]) && !empty($newData["locked"]) && $newData["locked"] != $oldData["locked"]) {
            $event->user_login_data["locked"] = $newData["locked"];
        } 

        if (isset($newData["lockedTime"]) && !empty($newData["lockedTime"]) && $newData["lockedTime"] != $oldData["lockedTime"]) {
            $event->user_login_data["lockedTime"] = $newData["lockedTime"];
        }
        
        if (!empty($event->user_login_data)) {
            $event->user_login_data["id"] = $id;
        }        
        
    }    

}
