<?php
/*
后台订单控制器
 *  */
namespace Home\Controller;
use Home\Model\AdminOrderModel;
class AdminOrderController extends CommonController {
   
    /**
    获取各个订单状态的数量，展示在平台后台首页
    **/
    public function get_order_num(){
        $post_info = I("post.");
        
        $Order = new AdminOrderModel();
        $returnData = $Order->getOrderNum($post_info);
        
        $this->ajaxReturn(array("status"=>0,"data"=>$returnData),json);
    }
    
    /**
    订单列表  
     $order_type  1:待审核订单  
    **/
    public function order_list(){
        $post_info = I("post.");
        
        $Order = new AdminOrderModel();
        $apiData = $Order->fetchs($post_info);
        
        $returnData = array(
            "totalItem" => 108,
            "totalPage" => 6,
            "data"      => array()
        );
        
        $this->ajaxReturn(array("status"=>1,"data"=>$returnData),json);
    }
    
}