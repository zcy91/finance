<?php
namespace console\models\base;

use console\models\BaseModel;

class BaseProductCommissionPercentage extends BaseModel {

    const TABLE_NAME = "base_product_commission_percentage";

    public function primaryKey() {
        return ['seller_id' => 'key','productId' => 'key', 'id' => 'auto'];
    }

    protected function resolveFields($v = array(), $default_arr = array()) {

        $subset = array(
            "id",
            "seller_id",
            "productId",
            "commission",
            "mediumCommission",
            "salesmanCommission"          
        );

        $info_arr = parent::key_values_intersect($v, $subset, $default_arr);
        return $info_arr;
    }
    
    public function delete($event) {
        
        $data = $event->base_product_commission_percentage_del;
        
        if (!empty($data)) {
            
            $sql = "DELETE FROM base_product_commission_percentage WHERE seller_id = :sellerId AND productId = :productId";
            $params = array(
                ":sellerId" => $data["sellerId"],
                ":productId" => $data["productId"],
            );
            
            $this->update_sql($sql, $event, $params);
            
        }
    }   
    
    public function handle($event) {
        
        if (!empty($event->base_product_commission_percentage_del)) {
            $this->delete($event);
        }
        
        $data = $event->base_product_commission_percentage_data;
        if (!empty($data)) {
            $Operate = $data["Operate"]; 
            if ($Operate) {
                $this->add($event);
            } else {
                $this->modify($event);
            }
        }
    }    

}
