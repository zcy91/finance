<?php
namespace SmallProgram\Model;

// 描述：用于地区管理下的操作
class BaseModel extends \Common\Model\BaseModel{
    
    /**
     * 返回数据格式
     * $apiData 数据格式
     *  $type 0：不带分页格式    1：带分页格式
     * */
    protected function return_data($apiData,$type=false){
       
        if($apiData['returnState']==1){
            if(!$type){
                if(empty($apiData['returnData'])){
                    $result = 2;//接口调用成功，但没有取到数据
                }else{
                    $result = $apiData['returnData'];
                }
            }else{
                if(empty($apiData['returnData']['data'])){
                    $result = 2;//接口调用成功，但没有取到数据
                }else{
                    $result = $apiData['returnData']['data'];
                }
            }
            
        }else{
            $result = $apiData['returnState'];//接口未调成功 返回状态不正常
        }
        
        return $result;
    }
}