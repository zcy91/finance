<?php
namespace Home\Controller;
use Home\Model\ReportModel;
class ReportController extends CommonController {
   
    /**
    贷款业绩统计
     * $time_type: 1: 前3月  2：上月   3：上周  4：本周   5：本月  6：今年
    **/
    public function loan_achievement(){
        $post_data = I("post.");
        $Report = new ReportModel();
        
        $params = [];
        if((isset($post_data['begin_date']) && $post_data['begin_date']!= "") || (isset($post_data['end_date']) && $post_data['end_date']!= "")){
            if(isset($post_data['begin_date']) && $post_data['begin_date']){
                $params['begin_date'] = $post_data['begin_date'];
            }else{
                $params['end_date'] = $post_data['end_date'];
            }
        }else{
            $time_type = I("time_type",0,intval);
            switch($time_type){
                case 1: //前3月
                    $params['begin_date'] = date("Y-m", strtotime("-3 month"))."-01 00:00:00"; 
                    $params['end_date'] = date("Y-m")."-01 00:00:00";
                break;
                case 2: //上月
                    $params['begin_date'] = date("Y-m", strtotime("-1 month"))."-01 00:00:00";
                    $params['end_date'] = date("Y-m")."-01 00:00:00";
                break;    
                case 3: //上周
                    $w = date("w")+6;
                    $params['begin_date'] = date("Y-m-d", strtotime("-".$w." day"))." 00:00:00";
                    $params['end_date'] = date("Y-m-d", strtotime("-".(date("w")-1)." day"))." 00:00:00";
                break;
                case 4: //本周
                    $params['begin_date'] = date("Y-m-d", strtotime("-".(date("w")-1)." day"))." 00:00:00";
                    $params['end_date'] = date("Y-m-d H:i:s");
                break;
                case 5: //本月
                    $params['end_date'] = date("Y-m")."-01 00:00:00";
                    $params['end_date'] = date("Y-m-d H:i:s");
                break;
                default;  
                    $params['begin_date'] = date("Y")."-01-01 00:00:00"; 
                    $params['end_date'] = date("Y-m-d H:i:s");//默认为今年
            }
        }
        $params['summType'] = $post_data['summType'];
        
        $search_type = $post_data['search_type'];
       
        $customer = array();
        $orderItem = array();
        $amount = array();
        $returnData = array(
            "customer" => &$customer,
            "amount"   => &$amount,
            "ordercount" =>&$orderItem
        );
        
        $apiData = $Report->loan_achievement_fetchs($params);
        $customer = $apiData['returnData'];   
        
        $apiData = $Report->order_count($params);
        $orderItem = $apiData['returnData'];    
        
        $apiData = $Report->order_amount($params);
        $amount = $apiData['returnData'];    
       
        unset($Report);
        $info = "";
        if($apiData['returnState'] != 1 && $apiData['returnData']){
            $info = get_error_info($apiData['returnState']);
        }
        $this->ajaxReturn(array("status"=>$apiData['returnState'],"data"=>$returnData),json);
    }
    
}