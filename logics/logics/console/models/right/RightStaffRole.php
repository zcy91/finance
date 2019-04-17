<?php
namespace console\models\right;

use console\models\BaseModel;

class RightStaffRole extends BaseModel {

    const TABLE_NAME = "right_staff_role";

    public function primaryKey() {
        return ['seller_id' => 'key', 'userId' => 'key', 'id' => 'auto'];
    }

    protected function resolveFields($v = array(), $default_arr = array()) {

        $subset = array(
            "id",
            "default",
            "seller_id",
            "userId",
            "roleId"           
        );

        $info_arr = parent::key_values_intersect($v, $subset, $default_arr);
        return $info_arr;
    }
    
    public static function setAddData($event, $sellerId, $data) {
        
        if (is_array($data)) {
            foreach ($data as $id) {
                $event->right_staff_role_data[] = array(
                    "defaulte" => 0,
                    "seller_id" => $sellerId,
                    "userId" => &$event->userId,
                    "roleId" => $id
                );
            }
        } elseif (is_numeric($data)) {
            $event->right_staff_role_data = array(
                "defaulte" => 0,
                "seller_id" => $sellerId,
                "userId" => &$event->userId,
                "roleId" => $data
            );            
        } else {
            return 1;
        }
        
        return 0;
        
    }
    
    public static function setEditData($event, $useId, $sellerId, $newData, $oldData){
        
        $add = array_diff($newData, $oldData);
        $del = array_diff($oldData, $newData);
        
        foreach ($add as $addItem) {
            $event->right_staff_role_add[] = array(
                "defaulte" => 0,
                "seller_id" => $sellerId,
                "userId" => $useId,
                "roleId" => $addItem                
            );
        }
        
        $event->right_staff_role_del = array(
            "seller_id" => $sellerId,
            "userId" => $useId,
            "roleIds" => $del
        );
        
    }
    
    public static function creatCondition($sortTable, $ids) {
        
        $fromStr = "INNER JOIN
                     right_staff_role AS rsr ON $sortTable.$ids[0] = rsr.seller_id AND $sortTable.$ids[0] = rsr.userId";
        
        return $fromStr;
        
    }       
    
    public function deleteAdd($event) {
        
        $delData = $event->right_staff_role_del;
        
        if (!empty($delData) && !empty($delData["roleIds"])) {
            $roleIds = $delData["roleIds"];
            if (is_array($roleIds)) {
                $condition = " AND roleId IN(" . implode(",", $roleIds) . ")";
            } else {
                $condition = " AND roleId = $roleIds";
            }
            
            $params = array(
                ":sellerId" => $delData["seller_id"],
                ":userId" => $delData["userId"]
            );
            
            $sql = "DELETE FROM right_staff_role WHERE seller_id = :sellerId AND userId = :userId $condition";
            
            $this->update_sql($sql, $event, $params);
            
        }        

        if (!empty($event->right_staff_role_add)) {
            $event->right_staff_role_data = $event->right_staff_role_add;
            $this->add($event);
        }   
        
    }

}
