<?php
namespace console\models\business;

use console\models\BaseModel;

class BusinessOrderCommissionQuot extends BaseModel {

    const TABLE_NAME = "business_order_commission_quot";

    public function primaryKey() {
        return ['seller_id'=>'key', 'orderId'=>'key', 'id' => 'auto'];
    }

    protected function resolveFields($v = array(), $default_arr = array()) {

        $subset = array( 
            "id",
            "seller_id",
            "orderId",
            "minAmount",
            "minDays",
            "commission",
            "mediumCommission",
            "salesmanCommission",
            "applyDays",
            "resultDays",
            "actualDays", 
            "receiveDays"
        );

        $info_arr = parent::key_values_intersect($v, $subset, $default_arr);
        return $info_arr;
    }
    
    public static function calculateCreatCommission($event, $Commission, $orderCommission) {
        $result = 0;
        if (isset($orderCommission["applyDays"])) {
            if ($Commission["minDays"] > $orderCommission["applyDays"]) {
                $calculateDays = $Commission["minDays"];
                $result = 1;
            } else {
                $calculateDays = $orderCommission["applyDays"];
            }      
            $event->applyDays = $orderCommission["applyDays"];
            $event->resultDays = $calculateDays;
        }
        
        if (isset($orderCommission["applyAmount"])) {
            if ($Commission["minAmount"] > $orderCommission["applyAmount"]) {
                $calculateAmount = $Commission["minAmount"];
                $result = 2;
            } else {
                $calculateAmount = $orderCommission["applyAmount"];
            }        

            $event->resultAmount = $calculateAmount;            
        }
        
        return $result;
    }  
    
    public function delete($event) {
        
        $data = $event->business_order_commission_percentage_delete;
        
        if (!empty($data)) {
            
            $sql = "DELETE FROM business_order_commission_percentage WHERE seller_id = :sellerId AND orderId = :orderId";
            $params = array(
                ":sellerId" => $data["sellerId"],
                ":orderId" => $data["orderId"],
            );
            
            $this->update_sql($sql, $event, $params);
            
        }
    } 
    
    public function handleAll($event){
        
        $this->delete($event);
        
        $data = $event->business_order_commission_percentage_data;
        
        if ($data["operate"] == 1) {
            $this->add($event);
        } else {
            $this->modify($event);
        }

    }    

}
