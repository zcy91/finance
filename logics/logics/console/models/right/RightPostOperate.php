<?php
namespace console\models\right;

use console\models\BaseModel;

class RightPostOperate extends BaseModel {

    const TABLE_NAME = "right_post_operate";

    public function primaryKey() {
        return ['id' => 'auto'];
    }

    protected function resolveFields($v = array(), $default_arr = array()) {

        $subset = array(             
            "id",
            "seller_id",
            "postId",
            "operate",
            "sectionId",
            "operateUid",
            "operateTime"     
        );

        $info_arr = parent::key_values_intersect($v, $subset, $default_arr);
        return $info_arr;
    }

}
