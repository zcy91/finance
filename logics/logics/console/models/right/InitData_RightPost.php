<?php
namespace console\models\right;
use console\models\right\RightPost;
use console\models\BaseModel;

class InitData_RightPost extends BaseModel {
    
    public function postAdd($event){
        $args = &$event->RequestArgs;
        $seller_id = isset($args['seller_id'])?$args['seller_id']:$_SERVER['seller_info']['seller_id'];
        $user_id = isset($args['user_id'])?$args['user_id']:$_SERVER['seller_info']['user_id'];
        $logSectionId = isset($_SERVER['seller_info']['logSectionId'])?$_SERVER['seller_info']['logSectionId']:0;
        
        if(!isset($args["dnames"]) || empty($args["dnames"])
            || !isset($args["power_node"]) || empty($args["power_node"])    
            || !isset($args["module_node"]) || empty($args["module_node"])    
                ){
            return parent::go_error($event, -12);
        } 
        
        $condition = " seller_id = :seller_id AND dnames = :dnames";
        $params = array(
            ":seller_id" =>  $seller_id,
            ":dnames"    =>  $args['dnames']
        );
        $column = " id ";
        $rightPost = new RightPost();
        $rightPostData = $rightPost->fetch_inner_base($event, $condition, $params, null, $column);
        unset($rightPost);
        if(!empty($rightPostData)){
            return parent::go_error($event, -4001);  //岗位名称已存在
        }
        
        
        $current_time = date("Y-m-d H:i:s");
        $dnames = $args['dnames'];
        $sectionName = $args['sectionName'];
        
        //岗位资料
        $event->right_post_data = array(
            "id" => &$event->post_id,
            "dnames" => $dnames,
            "display" => isset($args['display'])?$args['display']:0,
            "deleted" => 0,
            "seller_id" => $seller_id,
            "sectionName" => $sectionName,
            "nowTime" => $current_time,
        );
        
        //岗位模块
        $module_arr = array();
        foreach ($args['module_node'] as $val){
            $module_arr[] = array(
                "seller_id" => $seller_id,
                "postId" => &$event->post_id,
                "moduleId" => $val['moduleId']
            );
        }
        $event->right_post_module_data = $module_arr;
        
        //岗位权限
        $power_node = array();
        foreach($args["power_node"] as $val){
            $power_node[] = array(
                "seller_id" => $seller_id,
                "postId"    => &$event->post_id,
                "moduleId"  => $val['moduleId'],
                "baseId"    => $val['baseId']
            );
        }
        $event->right_post_base_data = $power_node;
        
         //岗位部门
        if(isset($args['section_node']) && !empty($args['section_node'])){
            $section_node = array();
            foreach($args["section_node"] as $val){
                $section_node[] = array(
                    "seller_id" => $seller_id,
                    "postId"    => &$event->post_id,
                    "sectionId"  => $val['sectionId']
                );
            }
            $event->right_post_section_data = $section_node;
        }
        
        //岗位操作日志
        $event->right_post_operate_data = array(
            "seller_id"     => $seller_id,
            "postId"        => &$event->post_id,
            "operate"       => 1,
            "sectionId"      => $logSectionId,
            "operateUid"    => $user_id,
            "operateTime"   => $current_time 
        );
    }
    
    public function postEdit($event){
        $args = &$event->RequestArgs;
        $seller_id = isset($args['seller_id'])?$args['seller_id']:$_SERVER['seller_info']['seller_id'];
        $user_id = isset($args['user_id'])?$args['user_id']:$_SERVER['seller_info']['user_id'];
        $logSectionId = isset($_SERVER['seller_info']['logSectionId'])?$_SERVER['seller_info']['logSectionId']:0;
        
        if(!isset($args["dnames"]) || empty($args["dnames"])
            || !isset($args["post_id"]) || empty($args["post_id"])
            || !isset($args["power_node"]) || empty($args["power_node"])    
            || !isset($args["module_node"]) || empty($args["module_node"])){
            return parent::go_error($event, -12);
        } 
        $post_id = $args['post_id'];
        $current_time = date("Y-m-d H:i:s");
        $dnames = $args['dnames'];
        $sectionName = $args['sectionName'];
        
        $rightPost = new RightPost();
        $condition = " seller_id = :seller_id AND id = :postId";
        $params = array(
            ":seller_id" =>  $seller_id,
            ":postId"    =>  $post_id
        );
        $column = " id ";
        $rightPostData = $rightPost->fetch_inner_base($event, $condition, $params, null, $column);
        
        if(empty($rightPostData)){
            return parent::go_error($event, -4002);  //角色不存在
        }
        
        $condition = " seller_id = :seller_id AND dnames = :dnames";
        $params = array(
            ":seller_id" =>  $seller_id,
            ":dnames"    =>  $args['dnames']
        );
        
        $rightPostData = $rightPost->fetch_inner_base($event, $condition, $params, null, $column);
        unset($rightPost);

        
        
        if(!empty($rightPostData) && $post_id != $rightPostData[0]['id']){
            return parent::go_error($event, -4001);  //岗位名称已存在
        }
        
        //岗位资料
        $event->right_post_data = array(
            "id" => $post_id,
            "dnames" => $dnames,
            "display" => isset($args['display'])?$args['display']:0,
            "deleted" => 0,
            "seller_id" => $seller_id,
            "sectionName" => $sectionName,
            "nowTime" => $current_time,
        );
        
        
        //岗位模块
        $module_arr = array();
        foreach ($args['module_node'] as $val){
            $module_arr[] = array(
                "seller_id" => $seller_id,
                "postId" => $post_id,
                "moduleId" => $val['moduleId']
            );
        }
        $event->right_post_module_data = $module_arr;
        
        //岗位权限
        $power_node = array();
        foreach($args["power_node"] as $val){
            $power_node[] = array(
                "seller_id" => $seller_id,
                "postId"    => $post_id,
                "moduleId"  => $val['moduleId'],
                "baseId"    => $val['baseId']
            );
        }
        $event->right_post_base_data = $power_node;
        
         //岗位部门
        if(isset($args['section_node']) && !empty($args['section_node'])){
            $section_node = array();
            foreach($args["section_node"] as $val){
                $section_node[] = array(
                    "seller_id" => $seller_id,
                    "postId"    => $post_id,
                    "sectionId"  => $val['sectionId']
                );
            }
            $event->right_post_section_data = $section_node;
        }
        
        //岗位操作日志
        $event->right_post_operate_data = array(
            "seller_id"     => $seller_id,
            "postId"        => $post_id,
            "operate"       => 2,
            "sectionId"      => $logSectionId,
            "operateUid"    => $user_id,
            "operateTime"   => $current_time 
        );
    }

    public function postDelete($event){
        $args = &$event->RequestArgs;
        $seller_id = isset($args['seller_id'])?$args['seller_id']:$_SERVER['seller_info']['seller_id'];
        $user_id = isset($args['user_id'])?$args['user_id']:$_SERVER['seller_info']['user_id'];
        $logSectionId = isset($_SERVER['seller_info']['logSectionId'])?$_SERVER['seller_info']['logSectionId']:0;
        
        if(!isset($args["post_id"]) || empty($args["post_id"])){
            return parent::go_error($event, -12);
        } 
        $post_id = $args['post_id'];
        
        $rightPost = new RightPost();
        $condition = " seller_id = :seller_id AND id = :postId";
        $params = array(
            ":seller_id" =>  $seller_id,
            ":postId"    =>  $post_id
        );
        $column = " id ";
        $rightPostData = $rightPost->fetch_inner_base($event, $condition, $params, null, $column);
        
        if(empty($rightPostData)){
            return parent::go_error($event, -4002);  //角色不存在
        }
        
        $sql = "SELECT postId FROM right_post_user WHERE postId = ".$post_id;
             
        $result = $this->query_SQL($sql, $event, $params); 
        
        if($result === true){
            return parent::go_error($event, -4003);//已绑定过员工，不能删除该岗位
        }
        
        //岗位操作日志
        $event->right_post_operate_data = array(
            "seller_id"     => $seller_id,
            "postId"        => $post_id,
            "operate"       => 3,
            "sectionId"      => $logSectionId,
            "operateUid"    => $user_id,
            "operateTime"   => date("Y-m-d H:i:s")
        );    
    }
}
