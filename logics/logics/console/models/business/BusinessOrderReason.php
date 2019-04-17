<?php
namespace console\models\business;

use console\models\BaseModel;

class BusinessOrderReason extends BaseModel {

    const TABLE_NAME = "business_order_reason";

    public function primaryKey() {
        return ['id' => 'auto'];
    }

    protected function resolveFields($v = array(), $default_arr = array()) {

        $subset = array(          
            "id",
            "seller_id",
            "orderId",
            "dstatus",
            "reason"     
        );

        $info_arr = parent::key_values_intersect($v, $subset, $default_arr);
        return $info_arr;
    }

}
