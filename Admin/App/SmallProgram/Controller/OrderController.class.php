<?php
namespace SmallProgram\Controller;
use SmallProgram\Model\OrderModel;
use SmallProgram\Plugin\Upfile;
class OrderController extends CommonController {
   
    /**
    新增订单
    **/
    public function order_add(){
        $post_data = $_POST;
        if(!isset($post_data['applyMobile']) || empty($post_data['applyMobile']) ||
                !isset($post_data['applyName']) || empty($post_data['applyName']) ||
                !isset($post_data['applyCard']) || empty($post_data['applyCard']) ||
                !isset($post_data['productId']) || empty($post_data['productId']) ||
                !isset($post_data['applyAmount']) || empty($post_data['applyAmount']) ||
                !isset($post_data['attrs']) || empty($post_data['attrs']) ||
                !isset($post_data['salesManId']) || empty($post_data['salesManId'])){
            $this->ajaxReturn(array("status"=>0,"info"=>"参数错误"),'json');
        }
        $attrs = json_decode($post_data['attrs'],true);
        
        $attrVal = [];
        foreach ($attrs as $val){
            if(isset($val['id'])){
                $attrVal[$val['id']] = $val['value'];
            }
        }
        $post_data['attrs'] = $attrVal;
        
        $Order = new OrderModel(); 
        
        $id = I("post.id",0,intval);
        $apiData = [];
        if($id == 0){
            $apiData = $Order->order_add($post_data);
        }else{
            $apiData = $Order->order_edit($post_data);
        }
        unset($Order);
        $info = "";
        if($apiData['returnState'] == 1 && !empty($apiData['returnData'])){
            $id = $apiData['returnData']['business_order']['id'];
        }else{
            $info = get_error_info($apiData['returnState']);
        }
        
        $this->ajaxReturn(array("status"=>$apiData['returnState'],"id"=>$id,"info"=>$info));
    }
    
    public function order_commit(){
        $post_data = I("post.");
        if(!isset($post_data['id']) || empty($post_data['id']) 
                || !isset($post_data['logUserId']) || empty($post_data['logUserId'])
                ){
            $this->ajaxReturn(array("status"=>0,"info"=>"参数错误"),'json');
        }
        $Order = new OrderModel(); 
        $apiData = $Order->order_commit($post_data);
        unset($Order);
        $info = "";
        if($apiData['returnState'] == 1 && !empty($apiData['returnData'])){
            $id = $post_data['id'];
        }else{
            $info = get_error_info($apiData['returnState']);
        }
        $this->ajaxReturn(array("status"=>$apiData['returnState'],"id"=>$id,"info"=>$info),'json');
    }
    public function order_delete(){
        $post_data = I("post.");
        if(!isset($post_data['id']) || empty($post_data['id']) 
                || !isset($post_data['logUserId']) || empty($post_data['logUserId'])
                ){
            $this->ajaxReturn(array("status"=>0,"info"=>"参数错误"),'json');
        }
        $Order = new OrderModel(); 
        $apiData = $Order->order_delete($post_data);
        unset($Order);
        $info = "";
        if($apiData['returnState'] == 1 && !empty($apiData['returnData'])){
            $id = $post_data['id'];
        }else{
            $info = get_error_info($apiData['returnState']);
        }
        $this->ajaxReturn(array("status"=>$apiData['returnState'],"id"=>$id,"info"=>$info),'json');
    }
    
    public function order_list(){
        $post_data = I("post.");
        if(!isset($post_data['logUserId']) || empty($post_data['logUserId'])
                || !isset($post_data['roleId']) || empty($post_data['roleId']) || (!in_array($post_data['roleId'],[2,3,4,5]))
                ){
            $this->ajaxReturn(array("status"=>0,"info"=>"参数错误"));
        }
        
        $post_data = I("post.");
        $p = isset($_POST['p'])?$_POST['p']:1;
        $pagesize = I("post.page_size");
        
        $post_data["pagination"] = array(
                "pagesize" => $pagesize,
                "pageindex" => $p,
                "recordcount" => 0
        );
        $Order = new OrderModel();
        $roleId = I("post.roleId");
        
        $apiData = $Order->order_list($post_data,$roleId); 
        
        $is_more = 1;
        if($apiData['returnData']['recordcount'] <= ($pagesize*$p)){
            $is_more = 0;
        }
        ++$p;
        
        $returnData = array(
            "totalItem"  => $apiData['returnData']['recordcount'],
            "time_limit" => $apiData['returnData']['time_limit'],
            "items"      => $apiData['returnData']['data'],
            "is_more"    => $is_more,
            "p"          => $p  
        );
        $info = get_error_info($apiData['returnState']);
        
        $this->ajaxReturn(array("status"=>$apiData['returnState'],"data"=>$returnData,"info"=>$info));
    }
    
    
    //查询单个商品
    public function single_view(){
        $order_id = I("post.id",0,intval);
        if($order_id == 0){
            $this->ajaxReturn(array("status"=>0,"info"=>"参数异常"));
        }
        
        $Order = new OrderModel();
        $apiData = $Order->single_view(["id"=>$order_id]);
       
        $response_data = [];$info = "";
        if($apiData['returnState'] == 1 && !empty($apiData['returnData'])){
            $response_data = $apiData['returnData'];
        }else{
            $info = get_error_info($apiData['returnState']);
        }
        
        $response_data = self::handle_data($response_data);
//        p($response_data);
        unset($Order);
        $this->ajaxReturn(array("status"=>1,"data"=>$response_data,"info"=>$info),'json');
    }
    
    
    private function handle_data($response_data){
        $attrs = $response_data['attrs'];
        
        $arr_old = [];
        foreach ($attrs as $val){
            $single_val = ["id"=>"","value"=>""];
            if(!empty($val['attrItemId']) || !empty($val['attrValue'])){
                switch($val['genre']){
                    case 1: ;
                    case 2: $single_val = array(
                                "id" => $val['attrId'],
                                "value" => $val['attrValue']
                            );
                    break;
                    case 3: $single_val = array(
                                "id" => $val['attrId'],
                                "value" => $val['attrItemId']
                            );
                    break;
                    case 4: $single_val = array(
                                "id" => $val['attrId'],
                                "value" => array_column($val['attrValue'], "attrItemId")
                            );
                    break;
                    case 5: 
                            if($val['imageCount'] == 1){
                               $val['attrValue'] = [$val['attrValue']];
                            }
                            $single_val = array(
                                "id" => $val['attrId'],
                                "value" => $val['attrValue']
                            );
                    break;
                }
                
                
            }
            $arr_old[] = $single_val;
        }
        
        $response_data['attr_old'] = $arr_old;
        
        return $response_data;
    }
    
    public function uploadImg(){
        $pic_url = "";
        if($_FILES['file']['name']){
            $file = new Upfile($_FILES['file']);
            $pic_url = $file->get_url();
        }
        
        $this->ajaxReturn(array("status"=>1,"picurl"=>$pic_url),'json');
    }
    
}