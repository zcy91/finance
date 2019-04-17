<?php
namespace console\models\business;

use console\models\BaseModel;

class BusinessOrderDelay extends BaseModel {

    const TABLE_NAME = "business_order_delay";

    public function primaryKey() {
        return ['id' => 'auto'];
    }

    protected function resolveFields($v = array(), $default_arr = array()) {

        $subset = array( 
            "id",
            "seller_id",
            "orderId",
            "actualDays",
            "delayDays",
            "actualTime",
            "delayTime"
        );

        $info_arr = parent::key_values_intersect($v, $subset, $default_arr);
        return $info_arr;
    }

}
