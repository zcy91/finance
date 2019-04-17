<?php
namespace SmallProgram\Controller;
use SmallProgram\Model\GoodsModel;
class GoodsController extends CommonController {
   
    public function getsessionid(){
        $this->ajaxReturn(array("sessionId" => session_id()),json);
    }
    
    /**
    商品列表
    **/
    public function goods_list(){
        $post_data = I("post.");
        $p = isset($_POST['p'])?$_POST['p']:1;
        $pagesize = I("post.page_size");
        
        $Goods = new GoodsModel();
        $params = array(
            "display" => 1,
            "pagination" => array(
                "pagesize" => $pagesize,
                "pageindex" => $p,
                "recordcount" => 0
            )
        );
        
        if(isset($post_data['dnames']) && !empty($post_data['dnames'])){
            $params['dnames'] = $post_data['dnames'];
        }
        
        if(isset($post_data['time_limit'])){
            $params['time_limit'] = $post_data['time_limit'];
        }
        
        $apiData = $Goods->fetchs($params);
        
        $is_more = 1;
        if($apiData['returnData']['recordcount'] <= ($pagesize*$p)){
            $is_more = 0;
        }
        ++$p;
        $returnData = array(
            "totalItem" => $apiData['returnData']['recordcount'],
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
        $goods_id = I("post.id",0,intval);
        if($goods_id == 0){
            $this->ajaxReturn(array("status"=>0,"info"=>"参数异常"));
        }
        
        $Goods = new GoodsModel();
        $apiData = $Goods->single_view(["id"=>$goods_id,"needItem"=>1]);
//        p($apiData);
        $response_data = [];$info = "";
        if($apiData['returnState'] == 1 && !empty($apiData['returnData'])){
            $response_data = $apiData['returnData'];
        }else{
            $info = get_error_info($apiData['returnState']);
        }
//        p($response_data);
        $this->ajaxReturn(array("status"=>1,"data"=>$response_data,"info"=>$info),json);
    }
    
}