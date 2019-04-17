<?php

namespace yii\gii\generators\myinterface;

/**
 * This is the model class for table "shop_basic".
 
 */
class ShopBasic{
    /**
     * @inheritdoc
     */
    const TABLE_NAME = "shop_basic";
     public function colulist(){
        return  array("seller_id","shop_id","shop_description","page_title","page_keywords","page_description","shop_url","shop_tag","mix_sys_id","lang_name_view","lang_name_sys","currency_name_view","currency_name_sys","image_url","image_base_name","image_tips","shop_address1","shop_address2","services_tel","copyright","session_period","cart_period","is_shop_closed",); 
     }
     public function checkdata($args){
         $colulist=$this->colulist(); //返回字段列表
         $count=0;
         foreach($args as $key=>$v){
            if(in_array($key,$colulist)){
                $count++; //匹配数加1
              }
         }
         if($count<count($args)){
            return 0;
         }else{
            return 1;
         }
     }
}
