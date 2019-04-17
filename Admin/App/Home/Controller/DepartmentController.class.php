<?php
namespace Home\Controller;
use Home\Model\DepartmentModel;
class DepartmentController extends CommonController {
   
    /**
    部门列表
    **/
    public function department_list(){
        $Department = new DepartmentModel();
        
        $apiData = $Department->fetchs();
        
        $result_data = [];$info="";
        
        if($apiData['returnState'] == 1 && !empty($apiData['returnData'])){
            $result_data = $apiData['returnData'];
        }else{
            $info = get_error_info($apiData['returnState']);
        }
        
        $this->ajaxReturn(array("status"=>$apiData['returnState'],"data"=>$result_data,"info"=>$info),json);
    }
    
    //查询单个属性信息
    public function single_view(){
        $department_id = I("post.department_id",0,intval);
        if($department_id == 0){
            $this->ajaxReturn(array("status"=>0,"info"=>"参数异常"));
        }
        
        $Department = new DepartmentModel();
        $returnData = $Department->single_view($department_id);
        
        $this->ajaxReturn(array("status"=>1,"data"=>$returnData));
    }
    
    /**
    部门添加修改操作
     * */
    public function add_save(){
        $post_data = I("post.");
        
        if(empty($post_data)){
            $this->ajaxReturn(array("status"=>0,"info"=>"参数异常"));
        }
        
        $department_id = I("id",0,intval);
        $Department = new DepartmentModel();
        $id = $department_id;
        if($department_id == 0){
            $apiData = $Department->add($post_data);
        }else{
            $apiData = $Department->save($post_data);
        }
        
        $info = "";
        if($apiData['returnState'] != 1){
            $info = get_error_info($apiData['returnState']);
        }else{
            $id = $apiData['returnData']['right_section']['id'];
        }
        
        $this->ajaxReturn(array("status"=>$apiData['returnState'],"info"=>$info,"id"=>$id),json);
    }
 
    /**
    删除部门
    **/
    public function delete(){
        $post_data = I("post.");
        
        if(!isset($post_data['id']) || $post_data['id'] == "" ||  $post_data['id'] == 0){
            $this->ajaxReturn(array("status"=>0,"info"=>"参数异常"));
        }

        $Department = new DepartmentModel();
        $apiData = $Department->delete($post_data);
        
        $info = "";
        if($apiData['returnState'] != 1){
            $info = get_error_info($apiData['returnState']);
        }
        $this->ajaxReturn(array("status"=>$apiData['returnState'],"info"=>$info));
    }
    
}