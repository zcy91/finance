<?php
namespace console\models\base;

use console\models\BaseModel;

class BaseProductOperate extends BaseModel {

    const TABLE_NAME = "base_product_operate";

    public function primaryKey() {
        return ['id' => 'auto'];
    }

    protected function resolveFields($v = array(), $default_arr = array()) {

        $subset = array(   
            "id",
            "seller_id",
            "productId",
            "operate",
            "sectionId",
            "operateUid",
            "operateTime"    
        );

        $info_arr = parent::key_values_intersect($v, $subset, $default_arr);
        return $info_arr;
    }
    
    public static function setAddData($event, $operate, $sellerId, $sectionId, $userId, $nowTime) {

        $event->base_product_operate_data = array(
            "seller_id" => $sellerId,
            "productId" => &$event->productId,
            "operate" => $operate,
            "sectionId" => $sectionId,
            "operateUid" => $userId,
            "operateTime" => $nowTime              
        );
        
    }      

}
