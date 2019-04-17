<?php
//佣金设置
namespace Home\Controller;
use Home\Model\CommissionModel;
class CommissionController extends CommonController {
   
    /**
    贷款业绩统计
     * $set_type  1：金额   2：比例
    **/
    public function set_commission(){
        
        $post_data = I("post.");
        $set_type = I("post.algorithm",1,intval); //默认为金额
        
        $params = array(
            "id"            => $post_data["id"],
            "algorithm"             => $set_type,
            "mediumCommission"     => $post_data["mediumCommission"]
        );
        if($set_type == 1){
            
        }else{
            
        }
        
        $CommissionModel = new CommissionModel();
        
        $apiData = $CommissionModel->set_commission($post_data);
        
        $info = "";$responseData = [];
        if($apiData['returnState'] == 1 && !empty($apiData['returnData'])){
            $responseData = $apiData["returnData"];
        }else{
            $info = get_error_info($apiData['returnState']);
        }
        $this->ajaxReturn(array("status"=>$apiData['returnState'],"info"=>$info));
    }
    
    public function view_single(){
        
        $post_data = I("post.");
        
        if(empty($post_data) || !isset($post_data['id']) || empty($post_data['id'])){
            $this->ajaxReturn(array("status"=>-12,"info"=>"参数错误"));
        }
        
        
        $CommissionModel = new CommissionModel();
        
        $apiData = $CommissionModel->view_single($post_data);
        $info = "";$responseData = [];
        if($apiData['returnState'] == 1 && !empty($apiData['returnData'])){
            $responseData = $apiData["returnData"];
        }else{
            $info = get_error_info($apiData['returnState']);
        }
        $this->ajaxReturn(array("status"=>$apiData['returnState'],"data"=>$responseData,"info"=>$info));
        
    }
    
}