<?php
namespace Home\Model;
use Home\Model\BaseModel;
// 描述：用于商品资料管理下的操作
class ReportModel extends BaseModel{
    
    //贷款业绩统计
    public function loan_achievement_fetchs($params) {
        $apiData = c_call_service("usercenter", "customer", "customersumm",$params);
        return $apiData;
    }
    //贷款业绩统计
    public function order_count($params) {
        $apiData = c_call_service("business", "order", "ordercountsumm",$params);
        return $apiData;
    }
    
    //贷款业绩统计
    public function order_amount($params) {
        $apiData = c_call_service("business", "order", "orderamountsumm",$params);
        return $apiData;
    }
    
    
}
