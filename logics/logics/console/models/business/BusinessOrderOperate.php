<?php
namespace console\models\business;

use console\models\BaseModel;

class BusinessOrderOperate extends BaseModel {

    const TABLE_NAME = "business_order_operate";

    public function primaryKey() {
        return ['id' => 'auto'];
    }

    protected function resolveFields($v = array(), $default_arr = array()) {

        $subset = array(             
            "id",
            "seller_id",
            "orderId",
            "operate",
            "sectionId",
            "operateUid",
            "operateName",
            "operateTime"    
        );

        $info_arr = parent::key_values_intersect($v, $subset, $default_arr);
        return $info_arr;
    }
    
    public static function setAddData($event, $operate, $sellerId, $sectionId, $userId, $nowTime) {

        $event->business_order_operate_data = array(
            "seller_id" => $sellerId,
            "orderId" => &$event->orderId,
            "operate" => $operate,
            "sectionId" => $sectionId,
            "operateUid" => $userId,
            "operateTime" => $nowTime              
        );
        
    }     

}
