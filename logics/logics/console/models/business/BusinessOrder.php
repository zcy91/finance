<?php
namespace console\models\business;

use console\models\BaseModel;

class BusinessOrder extends BaseModel {

    const TABLE_NAME = "business_order";

    public function primaryKey() {
        return ['id' => 'auto'];
    }

    protected function resolveFields($v = array(), $default_arr = array()) {

        $subset = array(              
            "id",
            "nos",
            "customerUserName",
            "customerUserAccount",
            "applyName",
            "applyMobile",
            "applyCard",
            "productNos",
            "productNames",
            "productImage",
            "applyAmount",
            "algorithm",
            "commission",            
            "resultAmount",
            "orderAmount",
            "onePriced",
            "actualAmount",
            "receiveAmount",
            "dstatus",
            "seller_id",
            "productId",
            "customerUserId",
            "creatTime",
            "nowTime"         
        );

        $info_arr = parent::key_values_intersect($v, $subset, $default_arr);
        return $info_arr;
    }
    
    public function get_seq_no($event, $data_arr, $array_dim) {

        $count = $array_dim == 1 ? 1 : count($data_arr);
        $seq_no = 0;
        $this->proc_call('getKeyValue', array(301, $count), $seq_no, $event);

        return $seq_no;
    }

    public function refer_to($event, $data_arr, $array_dim, $seq_no, $count) {
        $event->set_order_id($seq_no);
    }
    
    public function delete($event) {
        
        $data = $event->business_order_data;
        
        if (!empty($data)) {
            
            $sql = "DELETE FROM business_order WHERE seller_id = :sellerId AND id = :id";
            $params = array(
                ":sellerId" => $data["sellerId"],
                ":id" => $data["id"],
            );
            
            $this->update_sql($sql, $event, $params);
            
        }
    }     

}
