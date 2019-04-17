<?php
namespace console\models\right;

use console\models\BaseModel;

class RightSectionUser extends BaseModel {

    const TABLE_NAME = "right_section_user";

    public function primaryKey() {
        return ['seller_id' => 'key', 'userId' => 'key', 'id' => 'auto'];
    }

    protected function resolveFields($v = array(), $default_arr = array()) {

        $subset = array(
            "id",
            "seller_id",
            "sectionId",
            "userId"           
        );

        $info_arr = parent::key_values_intersect($v, $subset, $default_arr);
        return $info_arr;
    }
    
    public static function setAddData($event, $sellerId, $Sections){

        foreach ($Sections as $addItem) {
            $event->right_section_user_data[] = array(
                "defaulte" => 0,
                "seller_id" => $sellerId,
                "userId" => &$event->userId,
                "sectionId" => $addItem                
            );
        }

    }     
    
    public static function setEditData($event, $useId, $sellerId, $newData, $oldData){
        
        $add = array_diff($newData, $oldData);
        $del = array_diff($oldData, $newData);
        
        foreach ($add as $addItem) {
            $event->right_section_user_add[] = array(
                "defaulte" => 0,
                "seller_id" => $sellerId,
                "userId" => $useId,
                "sectionId" => $addItem                
            );
        }
        
        $event->right_section_user_del = array(
            "seller_id" => $sellerId,
            "userId" => $useId,
            "sectionIds" => $del
        );
        
    }  
    
    public static function creatCondition($sortTable, $ids) {
        
        $fromStr = "INNER JOIN
                     right_section_user AS rsu ON $sortTable.$ids[0] = rsu.seller_id AND $sortTable.$ids[1] = rsu.userId";
        
        return $fromStr;
        
    }   
    
    public function deleteAdd($event) {
        
        $delData = $event->right_section_user_del;
        
        if (!empty($delData) && !empty($delData["sectionIds"])) {
            $sectionIds = $delData["sectionIds"];
            if (is_array($sectionIds)) {
                $condition = " AND sectionId IN(" . implode(",", $sectionIds) . ")";
            } else {
                $condition = " AND sectionId = $sectionIds";
            }
            
            $params = array(
                ":sellerId" => $delData["seller_id"],
                ":userId" => $delData["userId"]
            );
            
            $sql = "DELETE FROM right_section_user WHERE seller_id = :sellerId AND userId = :userId $condition";
            
            $this->update_sql($sql, $event, $params);
            
        }        

        if (!empty($event->right_section_user_add)) {
            $event->right_section_user_data = $event->right_section_user_add;
            $this->add($event);
        }   
        
    }    

}
