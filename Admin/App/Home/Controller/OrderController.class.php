<?php
namespace Home\Controller;
use Home\Model\GoodsModel;
use Home\Model\OrderModel;
class OrderController extends CommonController {
   
    /**
    订单列表
    **/
    public function order_list(){
        $post_data = I("post.");
        $p = isset($_POST['p'])?$_POST['p']:1;
        $pagesize = I("post.page_size");
        
        $Order = new OrderModel();
        $params = array(
            "pagination" => array(
                "pagesize" => $pagesize,
                "pageindex" => $p,
                "recordcount" => 0
            )
        );
        
        if(isset($post_data['status'])){
            $params['status'] = $post_data['status'];//0:新订单 2:审核订单 3:签约订单 4:回款订单
        }
        
        if(isset($post_data['orderStatus']) && $post_data['orderStatus'] != ""){ //0：全部 2:提交, 7:审核，8:拒绝审核，12:签约, 13:拒绝签约, 17:回款
            $params['orderStatus'] = $post_data['orderStatus'];
        }
        
        if(isset($post_data['applyName']) && !empty($post_data['applyName'])){
            $params['applyName'] = $post_data['applyName'];
        }
        
        if(isset($post_data['applyMobile']) && !empty($post_data['applyMobile'])){
            $params['applyMobile'] = $post_data['applyMobile'];
        }
        
        if(isset($post_data['time_limit']) && !empty($post_data['time_limit'])){
            $params['time_limit'] = $post_data['time_limit'];
        }
        
        if(isset($post_data['begin_date']) && !empty($post_data['begin_date'])){
            $params['begin_date'] = $post_data['begin_date'];
        }
        
        if(isset($post_data['end_date']) && !empty($post_data['end_date'])){
            $params['end_date'] = $post_data['end_date'];
        }
        
        $apiData = $Order->fetchs($params);
//        p($apiData);
        $returnData = array(
            "totalItem" => $apiData['returnData']['recordcount'],
            "time_limit" => $apiData['returnData']['time_limit'],
            "p" => $p,
            "items"      => $apiData['returnData']['data']
        );
        
        $info = get_error_info($apiData['returnState']);
        
        $this->ajaxReturn(array("status"=>$apiData['returnState'],"data"=>$returnData,"info"=>$info));
    }
    
    /**
    删除商品
    **/
    public function delete(){
        $post_data = I("post.");
        
        if(!isset($post_data['id']) || $post_data['id'] == "" ||  $post_data['id'] == 0){
            $this->ajaxReturn(array("status"=>0,"info"=>"参数异常"));
        }

        $Goods = new GoodsModel();
        $apiData = $Goods->delete($post_data);
        $info = "";
        if($apiData['returnState'] != 1){
            $info = get_error_info($apiData['returnState']);
        }
        
        $this->ajaxReturn(array("status"=>$apiData['returnState'],"info"=>$info),json);
    }
    
    
    /**
    订单审核
     *      */
    public function order_check(){
        $post_data = I("post.");
        
        if(!isset($post_data['id']) || empty($post_data['id'])
                || !isset($post_data['status']) || empty($post_data['status'])
                ){
            $this->ajaxReturn(array("status"=>0,"info"=>"参数异常"));
        }
        
        if($post_data['status'] == 8){
            if(!isset($post_data['merchandiserId']) || empty($post_data['merchandiserId'])){
                $this->ajaxReturn(array("status"=>0,"info"=>"参数异常"));
            }
        }
        
        if($post_data['status'] == 7){
            if(!isset($post_data['reason']) || empty($post_data['reason'])){
                $this->ajaxReturn(array("status"=>0,"info"=>"参数异常"));
            }
        }
        
        $post_data['logSectionId'] = session('sectionId');
        $Order = new OrderModel();
        $apiData = $Order->order_check($post_data);
        $info = "";
        if($apiData['returnState'] != 1){
            $info = get_error_info($apiData['returnState']);
        }
        
        $this->ajaxReturn(array("status"=>$apiData['returnState'],"info"=>$info),json);
    }
    /**
    订单签约
     *      */
    public function order_sign(){
        $post_data = I("post.");
        if(!isset($post_data['id']) || empty($post_data['id']) 
                || !isset($post_data['status']) || empty($post_data['status']) 
                || !isset($post_data['algorithm'])
                ){
            $this->ajaxReturn(array("status"=>0,"info"=>"参数异常"));
        }
        if($post_data['status'] == 12){  //驳回 
            if(!isset($post_data['reason']) || empty($post_data['reason'])){
                $this->ajaxReturn(array("status"=>0,"info"=>"参数异常"));
            }
        }else{ //通过
            if(!isset($post_data['onePriced'])){
                $this->ajaxReturn(array("status"=>0,"info"=>"参数异常"));
            }
            if(empty($post_data['onePriced'])){
                if(!isset($post_data['commission']) || empty($post_data['commission'])){
                    $this->ajaxReturn(array("status"=>0,"info"=>"参数异常"));
                }
            }else{
                if(!isset($post_data['actualAmount']) || empty($post_data['actualAmount'])){
                    $this->ajaxReturn(array("status"=>0,"info"=>"参数异常"));
                }
            }
            
            if($post_data['algorithm'] == 1 && (!isset($post_data['actualDays']) || empty($post_data['actualDays']))){
                $this->ajaxReturn(array("status"=>0,"info"=>"参数异常"));
            }
        }
        
        
        $post_data['logSectionId'] = session('sectionId');
        $post_data['logUserId'] = session("userId");
        $Order = new OrderModel();
        $apiData = $Order->order_sign($post_data);
        $info = "";
        if($apiData['returnState'] != 1){
            $info = get_error_info($apiData['returnState']);
        }
        
        $this->ajaxReturn(array("status"=>$apiData['returnState'],"info"=>$info),json);
    }
    
     /**
    订单回款
     *      */
    public function order_receive(){
        $post_data = I("post.");
        
        if(!isset($post_data['id']) || empty($post_data['id'])
                || !isset($post_data['receiveDays']) || empty($post_data['receiveDays'])
                ){
            $this->ajaxReturn(array("status"=>0,"info"=>"参数异常"));
        }
        
        $post_data['logSectionId'] = session('sectionId');
        $post_data['logUserId'] = session("userId");
        $Order = new OrderModel();
        $apiData = $Order->order_receive($post_data);
        $info = "";
        if($apiData['returnState'] != 1){
            $info = get_error_info($apiData['returnState']);
        }
        
        $this->ajaxReturn(array("status"=>$apiData['returnState'],"info"=>$info),json);
    }
     /**
    订单延期
     *      */
    public function order_delay(){
        $post_data = I("post.");
        
        if(!isset($post_data['id']) || empty($post_data['id'])
                || !isset($post_data['delayDays']) || empty($post_data['delayDays'])
                ){
            $this->ajaxReturn(array("status"=>0,"info"=>"参数异常"));
        }
        
        $post_data['logSectionId'] = session('sectionId');
        $post_data['logUserId'] = session("userId");
        $Order = new OrderModel();
        $apiData = $Order->order_delay($post_data);
        $info = "";
        if($apiData['returnState'] != 1){
            $info = get_error_info($apiData['returnState']);
        }
        
        $this->ajaxReturn(array("status"=>$apiData['returnState'],"info"=>$info),json);
    }
    
    public function orderconsolesumm(){
        $pagesize = I("post.page_size");
        $p = I("post.p",1,intval);
        $Goods = new OrderModel();
        $params = array(
            "pagination" => array(
                "pagesize" => $pagesize,
                "pageindex" => $p,
                "recordcount" => 0
            )
        );
        
        $Order = new OrderModel();
        $apiData = $Order->orderconsolesumm($params);
        $returnData = array(
            "totalItem" => $apiData['returnData']['recordcount'],
            "time_limit" => $apiData['returnData']['time_limit'],
            "showArr" => $apiData['returnData']['showArr'],
            "showArr" => $apiData['returnData']['showArr'],
            "showId" => $apiData['returnData']['showId'],
            "summ" => $apiData['returnData']['summ'],
            "p" => $p,
            "items"      => $apiData['returnData']['data']
        );
        
        $info = get_error_info($apiData['returnState']);
        
        $this->ajaxReturn(array("status"=>$apiData['returnState'],"data"=>$returnData,"info"=>$info));
    }
    
    
    /**
    订单详情
     *      */
    public function orderdesc(){
        $post_data = I("post.");
        
        if(!isset($post_data['id']) || empty($post_data['status'])
                || !isset($post_data['status']) || empty($post_data['status'])
                ){
            $this->ajaxReturn(array("status"=>0,"info"=>"参数异常"));
        }
        //1:新订单，2:审核订单，3:签约订单，4:回款订单 5:(中介/客户)

        $Goods = new OrderModel();
        $apiData = $Goods->orderdesc($post_data);
        
        $info = ""; $responseData = [];
        if($apiData['returnState'] != 1){
            $info = get_error_info($apiData['returnState']);
        }else{
            $responseData = $apiData['returnData'];
        }
        
        $this->ajaxReturn(array("status"=>$apiData['returnState'],"data"=>$responseData,"info"=>$info),json);
    }
    
}