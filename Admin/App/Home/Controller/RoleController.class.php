<?php
namespace Home\Controller;
use Home\Model\RoleModel;
class RoleController extends CommonController {
   
    /**
    角色列表
    **/
    public function role_list(){
        $Role = new RoleModel();
        $apiData = $Role->fetchs();
        
        $response_data = [];$info = "";
        if($apiData['returnState'] == 1 && !empty($apiData['returnData'])){
            $response_data = $apiData['returnData'];
        }else{
            $info = get_error_info($apiData['returnState']);
        }
        $this->ajaxReturn(array("status"=>1,"data"=>$response_data,"info"=>$info),json);
    }
    
    //查询单个角色信息
    public function single_view(){
        $post_id = I("post.post_id",0,intval);
        $is_edit = I("post.is_edit",1,intval);
        if($post_id == 0){
            $this->ajaxReturn(array("status"=>0,"info"=>"参数异常"));
        }
        $Role = new RoleModel();
        $apiData = $Role->single_view(["post_id"=>$post_id]);
//        p($apiData);
        $response_data = [];$info = "";
        if($apiData['returnState'] == 1 && !empty($apiData['returnData'])){
            $response_data = $apiData['returnData'];
        }else{
            $info = get_error_info($apiData['returnState']);
        }
        
        $edit_arr = array();
        if($is_edit != 0 && !empty($response_data)){
            $edit_arr = self::handel_single_power($response_data);
        }
        $this->ajaxReturn(array("status"=>1,"data"=>$response_data,"info"=>$info,"edit_arr"=>$edit_arr),json);
    }
    
    
    private function handel_single_power($data){
        $returnData = [];
        $node_arr = [];
        foreach($data as $key => $val){
            if($key == 0){
                $returnData['display'] = $val['display'];
                $returnData['dnames'] = $val['dnames'];
                $section_arr = explode(",",$val['sectionId']);
                if(!empty($section_arr)){
                    $returnData['sectionId'] = $section_arr;
                }
            }
            $node_arr[] = $val['moudleId']."_".$val["baseId"];
        }
        $returnData["power_node"] = $node_arr;
        
        return $returnData;
    }
    
    private function hander_power_node($power_node){
        $returnData = [];  //菜单节点
        foreach ($power_node as $val){
            $child = array(
                "moduleName" => $val['cdnames'],
                "moduleId" => $val['moduleId']."_".$val['baseId'],
            );
            $module_id = $val['moduleId'];
            if(isset($returnData[$module_id])){
                $children = $returnData[$val['moduleId']]['children'];
                $children[] = $child;
                $returnData[$module_id]['children'] = $children;
            }else{
                $children[] = $child;
                $returnData[$module_id] = array(
                    "postName" => $val['dnames'],
                    "moduleName" => $val['dnames'],
                    "moduleId" => $val['moduleId'],
                    "sectionName" => $val['sectionName'],
                    "nowTime" => $val['nowTime'],
                    "display"   => $val['display'],
                    "children"    => $children 
                );
            }
        }
        
        return array_values($returnData);
    }
    
    
    /**
    部门添加修改操作
     * */
    public function add_save(){
        $post_data = I("post.");
        if(empty($post_data)){
            $this->ajaxReturn(array("status"=>0,"info"=>"参数异常"));
        }
        $role_id = I("post.post_id",0,intval);
        $Role = new RoleModel();
        if($role_id == 0){
            $apiData = $Role->add($post_data);
        }else{
            $apiData = $Role->save($post_data);
        }
        $info = "";
        if($apiData['returnState'] != 1){
            $info = get_error_info($apiData['returnState']);
        }
        
        $this->ajaxReturn(array("status"=>$apiData['returnState'],"info"=>$info),json);
    }
 
    /**
    删除角色
    **/
    public function delete(){
        $post_data = I("post.");
        
        if(!isset($post_data['post_id']) || $post_data['post_id'] == "" ||  $post_data['post_id'] == 0){
            $this->ajaxReturn(array("status"=>0,"info"=>"参数异常"));
        }

        $Role = new RoleModel();
        $apiData = $Role->delete($post_data);
        $info = "";
        if($apiData['returnState'] != 1){
            $info = get_error_info($apiData['returnState']);
        }
        
        $this->ajaxReturn(array("status"=>$apiData['returnState'],"info"=>$info),json);
    }
    
    public function get_all_node(){
        $Role = new RoleModel();
        $returnData = [];
//        $apiData = $Role->get_user_node();
        $apiData = $Role->get_all_node();
        if($apiData['returnState'] == 1 && !empty($apiData['returnData'])){
//            $returnData = self::hander_power_node($apiData['returnData']);
            $returnData = self::handle_all_power($apiData['returnData']);
        }
//        p($returnData);
        $this->ajaxReturn(array("status"=>1,"data"=>$returnData),json);
    }
    
    
    private function handle_all_power($power_node){
        $returnData = [];  //菜单节点
        $mC = [];
        foreach ($power_node as $val){
            $child = array(
                "moduleName" => $val['dnames'],
                "attribute" => $val['attribute'],
                "dnames" => $val['dnames'],
                "isShow" => $val['is_menu'],
                "router" => $val['router'],
                "moduleId" => $val['moduleId']."_".$val['id'],
            );
            
            $module_id = $val['moduleId'];
            if(!isset($mC[$module_id."_".$val['attribute']])){
                $mC[$module_id."_".$val['attribute']] = $child;
            }else{
                $mC[$module_id."_".$val['attribute']]['children'][] = $child;
            }
            
            if(isset($returnData[$module_id])){
                $returnData[$module_id][$module_id."_".$val['attribute']] = $mC[$module_id."_".$val['attribute']];
            }else{
                $returnData[$module_id] = array(
                    "moduleName" => $val['module_name'],
                    "moduleId" => $val['moduleId'],
                    "attribute" => $val['attribute'],
                    "icon" => $val['icon'],
                    "module_router" => $val['module_router'],
                    $module_id."_".$val['attribute']  => $mC[$module_id."_".$val['attribute']] 
                );
            }
        }
//        p($returnData);
        foreach($returnData as $key => $val){
            $key_arr = array_keys($val);
            $module_id = $val['moduleId'];
            for($i = 0;$i<count($key_arr);$i++){
                if(substr($key_arr[$i], 0, strlen($module_id."_")) === $module_id."_"){
                    $returnData[$key]['children'][] = $val[$key_arr[$i]];
                     unset($returnData[$key][$key_arr[$i]]);
                }
            }
        }
        return array_values($returnData);
    }
    
}