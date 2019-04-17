<?php
namespace console\models\user;

use console\models\BaseModel;

class UserOperate extends BaseModel {

    const TABLE_NAME = "user_operate";

    public function primaryKey() {
        return ['id' => 'auto'];
    }

    protected function resolveFields($v = array(), $default_arr = array()) {

        $subset = array(
            "id",
            "seller_id",
            "userId",
            "operate",
            "sectionId",
            "operateUid",
            "operateTime"
        );

        $info_arr = parent::key_values_intersect($v, $subset, $default_arr);
        return $info_arr;
    }
    
    public static function setAddData($event, $operate, $sellerId, $sectionId, $userId, $nowTime) {

        $event->user_operate_data = array(
            "seller_id" => $sellerId,
            "userId" => &$event->userId,
            "operate" => $operate,
            "sectionId" => $sectionId,
            "operateUid" => $userId,
            "operateTime" => $nowTime              
        );
        
    }


}
