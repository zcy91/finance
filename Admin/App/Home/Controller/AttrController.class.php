<?php
namespace Home\Controller;
use Home\Model\AttrModel;
class AttrController extends CommonController {
   
    /**
    商品列表
    **/
    public function attr_list(){
        $post_data = I("post.");
        $p = isset($_POST['p'])?$_POST['p']:1;
        $pagesize = I("post.page_size");
        
        $search_str = [];
        
        
        $Attr = new AttrModel();
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
        $apiData = $Attr->fetchs($params);
        
        $totalItem = 0;$items = [];
        if($apiData['returnState'] == 1 && !empty($apiData['returnData'])){
            $totalItem = $apiData['returnData']['recordcount'];
            $items = $apiData['returnData']['data'];
        }
        
        $returnData = array(
            "totalItem" => $totalItem,
            "p" => $p,
            "items"      => $items
        );
        
        $this->ajaxReturn(array("status"=>1,"data"=>$returnData));
    }
    
    //查询单个属性信息
    public function single_view(){
        $attr_id = I("post.id",0,intval);
        if($attr_id == 0){
            $this->ajaxReturn(array("status"=>0,"info"=>"参数异常"));
        }
        
        $Attr = new AttrModel();
        $apiData = $Attr->single_view(["id"=>$attr_id]);
       
        $info = "";
        if($apiData['returnState'] != 1){
            $info = get_error_info($apiData['returnState']);
        }else{
            $data = $apiData['returnData'];
        }
        
        $this->ajaxReturn(array("status"=>$apiData['returnState'],"data"=>$data,"info"=>$info),json);
    }
    
    /**
    商品资料添加修改操作
     *      */
    public function add_save(){
        $post_data = I("post.");
//        p($post_data);
        if(empty($post_data)){
            $this->ajaxReturn(array("status"=>0,"info"=>"参数异常"));
        }
        $attr_id = I("id",0,intval);
        $Attr = new AttrModel();
        $apiData = [];
        if($attr_id == 0){
            $apiData = $Attr->add($post_data);
        }else{
            $apiData = $Attr->save($post_data);
        }
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

        $Attr = new AttrModel();
        $apiData = $Attr->delete($post_data);
        
        $info = "";
        if($apiData['returnState'] != 1){
            $info = get_error_info($apiData['returnState']);
        }
        
        $this->ajaxReturn(array("status"=>$apiData['returnState'],"info"=>$info),json);
    }
    
}