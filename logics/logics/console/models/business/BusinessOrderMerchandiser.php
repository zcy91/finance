<?php
namespace console\models\business;

use console\models\BaseModel;

class BusinessOrderMerchandiser extends BaseModel {

    const TABLE_NAME = "business_order_merchandiser";

    public function primaryKey() {
        return ['id' => 'auto'];
    }

    protected function resolveFields($v = array(), $default_arr = array()) {

        $subset = array(          
            "id",
            "seller_id",
            "orderId",
            "sectionId",
            "merchandiserId",
            "merchandiserName",
            "merchandiserAccount",
            "relatedTime"      
        );

        $info_arr = parent::key_values_intersect($v, $subset, $default_arr);
        return $info_arr;
    }

}
