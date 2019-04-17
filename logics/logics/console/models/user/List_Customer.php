<?php
namespace console\models\user;

use console\models\BaseModel;
use console\models\user\View_UserCustomer;

class List_Customer extends BaseModel {
    
    public function customerList($event) {
        
        $data = &$event->RequestArgs;
        
        $ownSellerId = View_UserLogin::getOperateSellerId($data);
        if (empty($ownSellerId)) {
            return parent::go_error($event, -2011);
        } 
        
        $now_time = date('Y-m-d H:i:s');
        $time_limit = isset($data["time_limit"]) && !empty($data["time_limit"]) ? $data["time_limit"] : $now_time;
        
        $limit_arr = $event->Pagination;//分页信息
        $limit = parent::getLimitArr($event);//获得分类信息数组或空值
        $ispage = isset($limit_arr["ispage"]) ? $limit_arr["ispage"] : 0;  
        
        $condition = " AND uc.createtime < :time_limit";
        $params = array(
            ":sellerId" => $ownSellerId,
            ":time_limit" => $time_limit
        );  
        
        //姓名
        if (isset($data["dnames"]) && !empty($data["dnames"])) {
            $condition .= " AND up.dnames LIKE :dnames";
            $params[":dnames"] = "%" . $data["dnames"] . "%";
        } 
        
        //用户名
        if (isset($data["account"]) && !empty($data["account"])) {
            $condition .= " AND ul.dnames LIKE :account";
            $params[":account"] = "%" . $data["account"] . "%";
        }  
        
        //手机
        if (isset($data["mobile"]) && !empty($data["mobile"])) {
            $condition .= " AND ul.mobile LIKE :mobile";
            $params[":mobile"] = "%" . $data["mobile"] . "%";
        }   
        
        //状态
        if (isset($data["dstatus"]) && !empty($data["dstatus"])) {
            $condition .= " AND up.dstatus = :dstatus";
            $params[":dstatus"] = "%" . $data["dstatus"] . "%";
        }         

        //日期开始
        if (isset($data["begin_date"]) && !empty($data["begin_date"])) {
            $condition .= " AND uc.createtime >= :begin_date";
            $params[":begin_date"] = $data["begin_date"];
        } 

        //日期结束
        if (isset($data["end_date"]) && !empty($data["end_date"])) {
            $condition .= " AND uc.createtime >= :end_date";
            $params[":end_date"] = $data["end_date"];
        }            
   
        $View_UserCustomer = new View_UserCustomer();
        $dataCustomer = $View_UserCustomer->customerList($event, $ispage, $condition, $params, $limit);
        unset($View_UserCustomer);  
      
        if ($ispage) {
            
            $sql = " SELECT FOUND_ROWS() as record_count; ";
            $return_count = $this->query_SQL($sql, $event);        
            $recode_count = $return_count[0]['record_count']; 
            
            $return_data = array(
                "pagesize" => $limit_arr["pagesize"],
                "pageindex" => $limit_arr["pageindex"],
                "recordcount" => $recode_count,
                "data" => &$dataCustomer
            );              
        } else {
            $return_data = &$dataCustomer;
        }            
        
        $event->Postback($return_data);
    }
    
    function customerDesc($event){
        
        $data = &$event->RequestArgs;
     
        if (!isset($data["id"]) || empty($data["id"]) || !is_numeric($data["id"])) {
            return parent::go_error($event, -12);
        }    
        
        $customerId = $data["id"];
        
        $ownSellerId = View_UserLogin::getOperateSellerId($data);
        if (empty($ownSellerId)) {
            return parent::go_error($event, -2011);
        }
        
        $View_UserCustomer = new View_UserCustomer();
        $customerStaff = $View_UserCustomer->customerDesc($event, $ownSellerId, $customerId);
        unset($View_UserCustomer);
        
        if (empty($customerStaff)) {
            $customerStaff = [];
        }

        $event->Postback($customerStaff);
    }    
    
    public function customerSumm($event){
        
        $data = &$event->RequestArgs;      
        
        if (!isset($data["summType"]) || !is_numeric($data["summType"]) || !in_array($data["summType"], [1,2,3])) {
            return parent::go_error($event, -12);
        }             
        
        $ownSellerId = View_UserLogin::getOperateSellerId($data);
        $summType= $data["summType"];
        
        $condition = "";
        $params = array(
            ":sellerId" => $ownSellerId
        );      
        
        //日期开始
        if (isset($data["begin_date"]) && !empty($data["begin_date"])) {
            $condition .= " AND uc.createtime >= :begin_date";
            $params[":begin_date"] = $data["begin_date"];
        } 

        //日期结束
        if (isset($data["end_date"]) && !empty($data["end_date"])) {
            $condition .= " AND uc.createtime <= :end_date";
            $params[":end_date"] = $data["end_date"];
        }         
        
        $View_UserCustomer = new View_UserCustomer();
        $return_data = $View_UserCustomer->customerSumm($event, $summType, $condition, $params);
        unset($View_UserCustomer);    
        
        $event->Postback($return_data);    
    }      
    

}
