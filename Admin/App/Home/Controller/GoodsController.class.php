<?php
namespace Home\Controller;
use Home\Model\GoodsModel;
use Home\Plugin\Upfile;
class GoodsController extends CommonController {
   
    /**
    商品列表
    **/
    public function goods_list(){
//        $name = S('name','zcy');
//      $apiData = c_call_service("system", "sys", "fetchsysinfo");
        $post_data = I("post.");
        $p = isset($_POST['p'])?$_POST['p']:1;
        $pagesize = I("post.page_size");
        
        
        $post_data = I("post.");
        
        $search_str = [];
        if(!empty($post_data)){
            $search_type = $post_data['search_type'];
            switch($search_type){
                case 1: $search_str['goods_name'] = $post_data['search_str']; break;  //商品名称
                case 2: $search_str['goods_no'] = $post_data['search_str']; break;    //商品货号
                case 3: $search_str['is_visible'] = $post_data['search_str']; break;  //上架类型
            }
        }
        
        $Goods = new GoodsModel();
        $params = array(
            "pagination" => array(
                "pagesize" => $pagesize,
                "pageindex" => $p,
                "recordcount" => 0
            )
        );
        
        if(isset($post_data['dnames']) && !empty($post_data['dnames'])){
            $params['dnames'] = $post_data['dnames'];
        }
        if(isset($post_data['display'])){
            $params['display'] = $post_data['display'];
        }
        if(isset($post_data['time_limit'])){
            $params['time_limit'] = $post_data['time_limit'];
        }
        $apiData = $Goods->fetchs($params);
        $returnData = array(
            "totalItem" => $apiData['returnData']['recordcount'],
            "time_limit" => $apiData['returnData']['time_limit'],
            "p" => $p,
            "items"      => $apiData['returnData']['data']
        );
        
        $info = get_error_info($apiData['returnState']);
        
        $this->ajaxReturn(array("status"=>$apiData['returnState'],"data"=>$returnData,"info"=>$info));
    }
    
    //查询单个商品
    public function single_view(){
        $goods_id = I("post.id",1,intval);
        if($goods_id == 0){
            $this->ajaxReturn(array("status"=>0,"info"=>"参数异常"));
        }
        
        $Goods = new GoodsModel();
        $apiData = $Goods->single_view(["id"=>$goods_id]);
        $response_data = [];$info = "";
        if($apiData['returnState'] == 1 && !empty($apiData['returnData'])){
            $response_data = $apiData['returnData'];
        }else{
            $info = get_error_info($apiData['returnState']);
        }
        $this->ajaxReturn(array("status"=>1,"data"=>$response_data,"info"=>$info),json);
    }
    
    /**
    商品添加修改操作
     *      */
    public function add_save(){
        $post_data = $_POST;
        if(empty($post_data)){
            $this->ajaxReturn(array("status"=>0,"info"=>"参数异常"));
        }
        
        //处理图片上传
        if($_FILES['file']['name']){
            $file = new Upfile($_FILES['file']);
//            p($_FILES['file']);die;
            $post_data['image']= $file->get_url();
        }
//        p($post_data);die;
        $post_data['attrs']= json_decode($post_data['attrs'],'json');
        $goods_id = I("id",0,intval);
       
        $Goods = new GoodsModel();
        if($goods_id == 0){
            $apiData = $Goods->add($post_data);
        }else{
            $apiData = $Goods->save($post_data);
        }
        
        $info = "";
        if($apiData['returnState'] != 1){
            $info = get_error_info($apiData['returnState']);
        }else{
            $id = $apiData['returnData']['base_product']['id'];
        }
        
        $this->ajaxReturn(array("status"=>$apiData['returnState'],"info"=>$info,"id"=>$id),json);
        
    }
    
    /**
    设置商品上下架
    **/
    public function set_visible(){
        $post_data = I("post.");
        if(empty($post_data) || !isset($post_data['display']) || !isset($post_data['ids']) || !is_array($post_data['ids']) || count($post_data['ids']) == 0){
            $this->ajaxReturn(array("status"=>0,"info"=>"参数异常"));
        }
        
        $Goods = new GoodsModel();
        $apiData = $Goods->set_visible($post_data);
        
        $info = "";
        if($apiData['returnState'] != 1){
            $info = get_error_info($apiData['returnState']);
        }
        
        $this->ajaxReturn(array("status"=>$apiData['returnState'],"info"=>$info),json);
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
    
}