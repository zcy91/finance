<?php
namespace Home\Controller;
use Home\Model\CateModel;
class CateController extends CommonController {
   
    /**
    分类列表
    **/
    public function cate_list(){
        $Cate = new CateModel();
        $apiData = $Cate->fetchs();
        $status = 0;
        $processData = [];
        if($apiData['returnState'] == 1 && !empty($apiData['returnData'])){
            $status = 1;
            $resultData = $apiData['returnData'];
            $processData = process_class($resultData['level1'] , $resultData['level2'] , $resultData['level3'] , $resultData['level4'] , $resultData['level5']);
        }
        $this->ajaxReturn(array("status"=>$status,"data"=>$processData),json);
    }
    
    /**
     * 根据分类id获取子分类
     * **/
    public function get_child_cate(){
        if(S("cate_".SELLER_ID) == null){
            $Cate = new CateModel();
            $apiData = $Cate->fetchs();
            if($apiData['returnState'] == 1 && !empty($apiData['returnData'])){
                S("cate_".SELLER_ID,$apiData['returnData']);
            }
        }
        $returnData = S("cate_".SELLER_ID);
        $cate_id = I("post.cate_id",0,intval);
        $level = I("post.level",1,intval);
        $response_data = []; $status = 0;
        switch($level){
            case 2: $response_data = self::get_cate($cate_id, $returnData['level2']); break;
            case 3: $response_data = self::get_cate($cate_id, $returnData['level3']); break;
            case 4: $response_data = self::get_cate($cate_id, $returnData['level4']); break;
            case 5: $response_data = self::get_cate($cate_id, $returnData['level5']); break;
            default: $response_data = $returnData['level1']; break;
        }
        if(!empty($response_data)) $status = 1;
        $this->ajaxReturn(array("status"=>$status,"data"=>$response_data),json);
    }
    
    private function get_cate($cate_id,$cate_arr){
        $result = [];
        foreach($cate_arr as $val){
            if($cate_id == $val['pid']){
                array_push($result, $val);
            }
        }
        return $result;
    }
    
    
    //查询单个信息
    public function single_view(){
        $cate_id = I("post.cate_id",0,intval);
        if($cate_id == 0){
            $this->ajaxReturn(array("status"=>0,"info"=>"参数异常"));
        }
        
        $params = array(
            "cate_id" => $cate_id
        );
        $Cate = new CateModel();
        $returnData = $Cate->fetchs($params);
        
        $this->ajaxReturn(array("status"=>1,"data"=>$returnData));
    }
    
    /**
    分类添加修改操作
     * */
    public function add_save(){
        $post_data = I("post.");
        if(empty($post_data)){
            $this->ajaxReturn(array("status"=>0,"info"=>"参数异常"));
        }
        
        $cate_id = I("post.cate_id",0,intval);
        $Cate = new CateModel();
        $id = $cate_id;
        if($cate_id == 0){
            $apiData = $Cate->add($post_data);
        }else{
            $apiData = $Cate->save($post_data);
        }
        $info = "";
        if($apiData['returnState'] != 1){
            $info = get_error_info($apiData['returnState']);
        }else{
           $id = $apiData['returnData']['base_product_category']['id']; 
        }
        $this->ajaxReturn(array("status"=>$apiData['returnState'],"info"=>$info));
    }
 
    /**
    删除分类
    **/
    public function delete(){
        $post_data = I("post.");
        
        if(!isset($post_data['cate_id']) || $post_data['cate_id'] == "" ||  $post_data['cate_id'] == 0){
            $this->ajaxReturn(array("status"=>0,"info"=>"参数异常"));
        }

        $Cate = new CateModel();
        $apiData = $Cate->delete($post_data);
        
        $info = "";
        if($apiData['returnState'] != 1){
            $info = get_error_info($apiData['returnState']);
        }
        
        $this->ajaxReturn(array("status"=>$apiData['returnState'],"info"=>$info),json);
    }
    
}