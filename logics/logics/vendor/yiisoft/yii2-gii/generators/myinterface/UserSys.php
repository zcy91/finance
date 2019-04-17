<?php

namespace yii\gii\generators\myinterface;

/**
 * This is the model class for table "user_sys".
 
 */
class UserSys{

     public function colulist(){
        $a=array("user_id","nick_name","mobile_no","email_address","account_pwd","is_deleted","create_time","login_time","login_times",); 
        return $a;
     }
     public function checkdata($args){
         $colulist=$this->colulist(); //返回字段列表
         foreach($args as $key=>$v){
            if(!in_array($key,$colulist)){
                return $key;
            }else{
                return $key;
            }
         }
     }
}
