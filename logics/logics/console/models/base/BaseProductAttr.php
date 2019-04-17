<?php
namespace console\models\base;

use console\models\BaseModel;

class BaseProductAttr extends BaseModel {

    const TABLE_NAME = "base_product_attr";

    public function primaryKey() {
        return ['id' => 'auto'];
    }

    protected function resolveFields($v = array(), $default_arr = array()) {

        $subset = array(           
            "id",
            "sort",
            "genre",
            "required",
            "imageCount",
            "seller_id",
            "productId",
            "attrId",
            "attrName"          
        );

        $info_arr = parent::key_values_intersect($v, $subset, $default_arr);
        return $info_arr;
    }
    
    public function delete($event) {
        
        $data = $event->base_product_attr_del;
            
        if (!empty($data)) {
            
            $sql = "DELETE FROM base_product_attr WHERE seller_id = :sellerId AND productId = :productId";
            $params = array(
                ":sellerId" => $data["sellerId"],
                ":productId" => $data["productId"],
            );       

            $this->update_sql($sql, $event, $params);
            
        } 
    }     

    public function deleteAdd($event){
        
        $this->delete($event);
        
        $this->add($event);
    }    

}
