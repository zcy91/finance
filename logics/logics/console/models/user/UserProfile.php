<?php
namespace console\models\user;

use console\models\BaseModel;

class UserProfile extends BaseModel {

    const TABLE_NAME = "user_profile";

    public function primaryKey() {
        return ['id' => 'auto'];
    }

    protected function resolveFields($v = array(), $default_arr = array()) {

        $subset = array(
            "id",
            "pic",
            "dnames",
            "mobile",
            "entryTime",
            "salary",
            "sex",
            "officePhone",
            "idCard",
            "bank",
            "bankNo",
            "wxNo",
            "zfbNo",
            "qq",
            "qqPasswd",
            "birthDay",
            "ethnic",
            "homeAddress",
            "nowAddress",
            "educational",
            "graduateSchool",
            "graduateTime",
            "profession",
            "speciality",
            "dstatus",
            "userId",
        );

        $info_arr = parent::key_values_intersect($v, $subset, $default_arr);
        return $info_arr;
    }
    
    public static function setAddData($event, $data, $nowTime) {
        
        $event->user_profile_data = array(
            "pic" => (isset($data["pic"]) && !empty($data["pic"]) && is_string($data["pic"])) ? $data["pic"] : "",
            "dnames" =>  $data["dnames"],
            "mobile" => $data["mobile"],
            "entryTime" => (isset($data["entryTime"]) && !empty($data["entryTime"]) && is_string($data["entryTime"])) ? $data["entryTime"] : "",
            "salary" => (isset($data["salary"]) && !empty($data["salary"]) && is_numeric($data["salary"])) ? $data["salary"] : 0,
            "sex" => (isset($data["sex"]) && !empty($data["sex"]) && is_numeric($data["sex"])) ? $data["sex"] : 0,
            "officePhone" => (isset($data["officePhone"]) && !empty($data["officePhone"]) && is_string($data["officePhone"])) ? $data["officePhone"] : "",
            "idCard" => (isset($data["idCard"]) && !empty($data["idCard"]) && is_string($data["idCard"])) ? $data["idCard"] : "",
            "bank" => (isset($data["bank"]) && !empty($data["bank"]) && is_string($data["bank"])) ? $data["bank"] : "",
            "bankNo" => (isset($data["bankNo"]) && !empty($data["bankNo"]) && is_string($data["bankNo"])) ? $data["bankNo"] : "",
            "wxNo" => (isset($data["wxNo"]) && !empty($data["wxNo"]) && is_string($data["wxNo"])) ? $data["wxNo"] : "",
            "zfbNo" => (isset($data["zfbNo"]) && !empty($data["zfbNo"]) && is_string($data["zfbNo"])) ? $data["zfbNo"] : "",
            "qq" => (isset($data["qq"]) && !empty($data["qq"]) && is_numeric($data["qq"])) ? $data["qq"] : 0,
            "qqPasswd" => (isset($data["qqPasswd"]) && !empty($data["qqPasswd"]) && is_string($data["qqPasswd"])) ? $data["qqPasswd"] : "",
            "birthDay" => (isset($data["birthDay"]) && !empty($data["birthDay"]) && is_string($data["birthDay"])) ? $data["birthDay"] : "",
            "ethnic" => (isset($data["ethnic"]) && !empty($data["ethnic"]) && is_string($data["ethnic"])) ? $data["ethnic"] : "",
            "homeAddress" => (isset($data["homeAddress"]) && !empty($data["homeAddress"]) && is_string($data["homeAddress"])) ? $data["homeAddress"] : "",
            "nowAddress" => (isset($data["nowAddress"]) && !empty($data["nowAddress"]) && is_string($data["nowAddress"])) ? $data["nowAddress"] : "",
            "educational" => (isset($data["educational"]) && !empty($data["educational"]) && is_string($data["educational"])) ? $data["educational"] : "",
            "graduateSchool" => (isset($data["graduateSchool"]) && !empty($data["graduateSchool"]) && is_string($data["graduateSchool"])) ? $data["graduateSchool"] : "",
            "graduateTime" => (isset($data["graduateTime"]) && !empty($data["graduateTime"]) && is_string($data["graduateTime"])) ? $data["graduateTime"] : "",
            "profession" => (isset($data["profession"]) && !empty($data["profession"]) && is_string($data["profession"])) ? $data["profession"] : "",
            "speciality" => (isset($data["speciality"]) && !empty($data["speciality"]) && is_string($data["speciality"])) ? $data["speciality"] : "",
            "status" => (isset($data["status"]) && !empty($data["status"]) && is_string($data["status"])) ? $data["status"] : "",
            "userId" => &$event->userId      
        );
        
    }
    
    public static function setEditData($event, $id, $newData, $oldData){
        
        if (isset($newData["pic"]) && !empty($newData["pic"]) && $newData["pic"] != $oldData["pic"]) {
            $event->user_profile_data["pic"] = $newData["pic"];
        }         
        
        if (isset($newData["dnames"]) && !empty($newData["dnames"]) && $newData["dnames"] != $oldData["dnames"]) {
            $event->user_profile_data["dnames"] = $newData["dnames"];
        } 
        
        if (isset($newData["mobile"]) && !empty($newData["mobile"]) && $newData["mobile"] != $oldData["mobile"]) {
            $event->user_profile_data["mobile"] = $newData["mobile"];
        }  

        if (isset($newData["entryTime"]) && !empty($newData["entryTime"]) && $newData["entryTime"] != $oldData["entryTime"]) {
            $event->user_profile_data["entryTime"] = $newData["entryTime"];
        }  
        
        if (isset($newData["salary"]) && !empty($newData["salary"]) && $newData["salary"] != $oldData["salary"]) {
            $event->user_profile_data["salary"] = $newData["salary"];
        } 

        if (isset($newData["sex"]) && !empty($newData["sex"]) && $newData["sex"] != $oldData["sex"]) {
            $event->user_profile_data["sex"] = $newData["sex"];
        }  
        
        if (isset($newData["officePhone"]) && !empty($newData["officePhone"]) && $newData["officePhone"] != $oldData["officePhone"]) {
            $event->user_profile_data["officePhone"] = $newData["officePhone"];
        } 

        if (isset($newData["bank"]) && !empty($newData["bank"]) && $newData["bank"] != $oldData["bank"]) {
            $event->user_profile_data["bank"] = $newData["bank"];
        } 

        if (isset($newData["bankNo"]) && !empty($newData["bankNo"]) && $newData["bankNo"] != $oldData["bankNo"]) {
            $event->user_profile_data["bankNo"] = $newData["bankNo"];
        } 

        if (isset($newData["wxNo"]) && !empty($newData["wxNo"]) && $newData["wxNo"] != $oldData["wxNo"]) {
            $event->user_profile_data["wxNo"] = $newData["wxNo"];
        } 

        if (isset($newData["zfbNo"]) && !empty($newData["zfbNo"]) && $newData["zfbNo"] != $oldData["zfbNo"]) {
            $event->user_profile_data["zfbNo"] = $newData["zfbNo"];
        }         

        if (isset($newData["idCard"]) && !empty($newData["idCard"]) && $newData["idCard"] != $oldData["idCard"]) {
            $event->user_profile_data["idCard"] = $newData["idCard"];
        }          
        
        if (isset($newData["entryTime"]) && !empty($newData["entryTime"]) && $newData["entryTime"] != $oldData["entryTime"]) {
            $event->user_profile_data["entryTime"] = $newData["entryTime"];
        }  
        
        if (isset($newData["qq"]) && !empty($newData["qq"]) && $newData["qq"] != $oldData["qq"]) {
            $event->user_profile_data["qq"] = $newData["qq"];
        } 

        if (isset($newData["qqPasswd"]) && !empty($newData["qqPasswd"]) && $newData["qqPasswd"] != $oldData["qqPasswd"]) {
            $event->user_profile_data["qqPasswd"] = $newData["qqPasswd"];
        }  
        
        if (isset($newData["birthDay"]) && !empty($newData["birthDay"]) && $newData["birthDay"] != $oldData["birthDay"]) {
            $event->user_profile_data["birthDay"] = $newData["birthDay"];
        } 

        if (isset($newData["ethnic"]) && !empty($newData["ethnic"]) && $newData["ethnic"] != $oldData["ethnic"]) {
            $event->user_profile_data["ethnic"] = $newData["ethnic"];
        }  

        if (isset($newData["homeAddress"]) && !empty($newData["homeAddress"]) && $newData["homeAddress"] != $oldData["homeAddress"]) {
            $event->user_profile_data["homeAddress"] = $newData["homeAddress"];
        } 

        if (isset($newData["nowAddress"]) && !empty($newData["nowAddress"]) && $newData["nowAddress"] != $oldData["nowAddress"]) {
            $event->user_profile_data["nowAddress"] = $newData["nowAddress"];
        }  
        
        if (isset($newData["dnames"]) && !empty($newData["educational"]) && $newData["educational"] != $oldData["educational"]) {
            $event->user_profile_data["educational"] = $newData["educational"];
        } 

        if (isset($newData["graduateSchool"]) && !empty($newData["graduateSchool"]) && $newData["graduateSchool"] != $oldData["graduateSchool"]) {
            $event->user_profile_data["graduateSchool"] = $newData["graduateSchool"];
        }
        
        if (isset($newData["graduateTime"]) && !empty($newData["graduateTime"]) && $newData["graduateTime"] != $oldData["graduateTime"]) {
            $event->user_profile_data["graduateTime"] = $newData["graduateTime"];
        }  
        
        if (isset($newData["profession"]) && !empty($newData["profession"]) && $newData["profession"] != $oldData["profession"]) {
            $event->user_profile_data["profession"] = $newData["profession"];
        } 

        if (isset($newData["speciality"]) && !empty($newData["speciality"]) && $newData["speciality"] != $oldData["speciality"]) {
            $event->user_profile_data["speciality"] = $newData["speciality"];
        }
        
        if (isset($newData["dstatus"]) && !empty($newData["dstatus"]) && $newData["dstatus"] != $oldData["dstatus"]) {
            $event->user_profile_data["dstatus"] = $newData["dstatus"];
        }  
        
        if (!empty($event->user_profile_data)) {
            $event->user_profile_data["id"] = $id;
        }
        
    }    


}
