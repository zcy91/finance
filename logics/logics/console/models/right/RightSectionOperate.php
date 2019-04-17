<?php
namespace console\models\right;

use console\models\BaseModel;

class RightSectionOperate extends BaseModel {

    const TABLE_NAME = "right_section_operate";

    public function primaryKey() {
        return ['id' => 'auto'];
    }

    protected function resolveFields($v = array(), $default_arr = array()) {

        $subset = array( 
            "id",
            "seller_id",
            "section",
            "operate",
            "sectionId",
            "operateUid",
            "operateTime"
        );

        $info_arr = parent::key_values_intersect($v, $subset, $default_arr);
        return $info_arr;
    }
    
    public static function setAddData($event, $operate, $sellerId, $sectionId, $userId, $nowTime) {

        $event->right_section_operate_data = array(
            "seller_id" => $sellerId,
            "section" => &$event->sectionId,
            "operate" => $operate,
            "sectionId" => $sectionId,
            "operateUid" => $userId,
            "operateTime" => $nowTime              
        );
        
    }    

}
