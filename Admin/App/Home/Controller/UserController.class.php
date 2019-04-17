<?php
/*
用户控制器
 *  */
namespace Home\Controller;
use Home\Model\UserModel;
use Home\Model\RoleModel;
use Home\Plugin\Upfile;
class UserController extends CommonController {
   
    /**
    获取用户信息登录
    **/
    public function get_user_info(){
        $post_data = I("post.");

        $tmp_noce = c_get_rand();
        cookie("X-XSRF-YHJR",$tmp_noce);
        session("X-XSRF-YHJR",$tmp_noce);
        $status = 0; $user_info = array();
        
        $User = new UserModel();
        $apiData = $User->login($post_data);
        $power_node = [];
        $isSuper = 0;
        $user_info = [];
        $info = "";
        $returnData = array(
            "status" => &$status,
            "isSuper" => &$isSuper,
            "data"   => &$user_info,
            "info"   => &$info,
//            "accessMenu" => $power_node['accessMenu'],
//            "accessOperate" => &$power_node
        );
        if($apiData['returnState'] == 1 && !empty($apiData['returnData'])){
            $status = 1;
            $user_info = $apiData['returnData'];
            
            session("userId",$user_info['userId']);
            session("ADMIN_ID",$user_info['userId']);
            session("currentRoleId",$user_info['currentRoleId']);
            $isSuper = $user_info['isSuper'];
            if($isSuper == 1){
                session("sectionId",0);  //超级管理员 
            }else{
                $user_id = session("userId");
                session("sectionId",$user_info['currentsectionId']);
            }
        }else{
            $status = $apiData['returnState'];
            $info = get_error_info($status);
        }
        $this->ajaxReturn($returnData,json);
    }
    
    public function get_power_node(){
        $user_id = isset($_POST['userId'])?$_POST['userId']:session("userId");
        if(session("sectionId") == 0){
            $Role = new RoleModel();
            $result = $Role->get_all_node();
            if($result['returnState'] == 1 && !empty($result['returnData'])){
                $power_node_arr = self::handle_all_power($result['returnData'],1);
            }
        }else{
            $power_node_arr = self::get_staff_node($user_id);
        }
        $returnData = array(
            "status" => 1,
            "accessMenu" => $power_node_arr,
//            "accessOperate" => $power_node_arr['access_operate']
        );
        $this->ajaxReturn($returnData,json);
    }
    
    //获取员工的权限
    private function get_staff_node($user_id = 0){
        $Role = new RoleModel();
        $returnData = [];
        $apiData = $Role->get_user_node(["userId"=>$user_id]);
       
        if($apiData['returnState'] == 1 && !empty($apiData['returnData'])){
//            $returnData = self::hander_power_node($apiData['returnData']);
            $returnData = self::handle_all_power($apiData['returnData']);
        }
        return $returnData;
    }
    
    private function handle_all_power($power_node,$is_all=0){
        $returnData = [];  //菜单节点
        $mC = [];
        foreach ($power_node as $val){
            $child = array(
                "menuName" => $is_all === 1 ? $val['dnames']:$val['cdnames'],
                "attribute" => $val['attribute'],
                "dnames" => $val['dnames'],
                "action" => $val['action'],
                "isShow" => $is_all === 1 ? $val['is_menu']:$val['cdisplay'],
                "router" => $is_all === 1 ? $val['router']:$val['crouter'],
                "moduleId" => $val['moduleId']."_".( $is_all === 1 ? $val['id']:$val['baseId']),
            );
            $module_id = $val['moduleId'];
            if(!isset($mC[$module_id."_".$val['attribute']])){
                $mC[$module_id."_".$val['attribute']] = $child;
            }else{
                $mC[$module_id."_".$val['attribute']]['actionNode'][] = $child['action'];
            }
            
            if(isset($returnData[$module_id])){
                $returnData[$module_id][$module_id."_".$val['attribute']] = $mC[$module_id."_".$val['attribute']];
            }else{
                $returnData[$module_id] = array(
                    "menuName" => $is_all === 1 ? $val['module_name']:$val['dnames'],
                    "moduleId" => $val['moduleId'],
                    "icon" => $val['icon'],
                    "module_router" => $is_all === 1 ? $val['module_router']:$val['router'],
                    $module_id."_".$val['attribute']  => $mC[$module_id."_".$val['attribute']]
                );
            }
        }
        
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
    
    private function hander_power_node($power_node){
        $accessMenu = [];  //菜单节点
        $access_operate = []; //权限节点
        foreach ($power_node as $val){
            if($val['cdisplay'] == 1){
                $child = array(
                    "menuName" => $val['cdnames'],
                    "router"   => $val['crouter']
                );
                $module_id = $val['moduleId'];
                if(isset($accessMenu[$module_id])){
                    $children = $accessMenu[$val['moduleId']]['child'];
                    $children[] = $child;
                    
                    $accessMenu[$module_id]['child'] = $children;
                }else{
                   
                    $accessMenu[$module_id] = array(
                        "menuName" => $val['dnames'],
                        "icon"     => $val['icon'],
                        "router"   => $val['router'],
                        "child"    => [$child] 
                    );
                }
            }
            
            $access_operate[] = array(
                "node"      => $val['cdnames'],
                "action"    => $val['action'],
                "attribute"   => $val['attribute']
            );
            
        }
        return array(
            "accessMenu" => array_values($accessMenu),
            "access_operate" => $access_operate
        );
    }
    
    
    /**
    客户列表
     * 1： 后台  2：中介  3：普通会员  4:业务员  5：跟单员 
     * $customer_type 1：普通员工/跟单员  2：中介  3：普通会员  4:业务员  5：跟单员 
     *  */
    public function user_lists(){
        $post_data = I("post.");
        $p = isset($_POST['p'])?$_POST['p']:1;
        $pagesize = I("post.page_size");
        
        
        $User = new UserModel();
        
        $customer_type = I("post.customer_type",1,intval);
        
        $params = array(
            "pagination" => array(
                "pagesize" => $pagesize,
                "pageindex" => $p,
                "recordcount" => 0
            )
        );
        
        if($customer_type == 1){
            if(isset($post_data['staffName']) && !empty($post_data['staffName'])){
                $params['staffName'] = $post_data['staffName'];
            }
            if(isset($post_data['staffAccount']) && !empty($post_data['staffAccount'])){
                $params['staffAccount'] = $post_data['staffAccount'];
            }
            if(isset($post_data['dstatus']) && $post_data['dstatus']!= ""){
                $params['dstatus'] = $post_data['dstatus'];
            }
            if(isset($post_data['roleId']) && !empty($post_data['roleId'])){
                $params['roleId'] = $post_data['roleId'];
            }
            if(isset($post_data['sectionId']) && !empty($post_data['sectionId'])){
                $params['sectionId'] = $post_data['sectionId'];
            }

            if(isset($post_data['postId']) && !empty($post_data['postId'])){
                $params['postId'] = $post_data['postId'];
            }
            if(isset($post_data['begin_date']) && !empty($post_data['begin_date'])){
                $params['begin_date'] = $post_data['begin_date']." 00:00:00";
            }

            if(isset($post_data['end_date']) && !empty($post_data['end_date'])){
                $params['end_date'] = $post_data['end_date']." 23:59:59";
            }

            if(isset($post_data['time_limit']) && !empty($post_data['time_limit'])){
                $params['time_limit'] = $post_data['time_limit'];
            }
        }else if($customer_type == 2){
            if(isset($post_data['dnames']) && !empty($post_data['dnames'])){
                $params['dnames'] = $post_data['dnames'];
            }
            if(isset($post_data['account']) && !empty($post_data['account'])){
                $params['account'] = $post_data['account'];
            }
            if(isset($post_data['mobile']) && $post_data['mobile']!= ""){
                $params['mobile'] = $post_data['mobile'];
            }
            if(isset($post_data['time_limit']) && !empty($post_data['time_limit'])){
                $params['time_limit'] = $post_data['time_limit'];
            }
        }else if($customer_type == 3){
            if(isset($post_data['dnames']) && !empty($post_data['dnames'])){
                $params['dnames'] = $post_data['dnames'];
            }
            if(isset($post_data['account']) && !empty($post_data['account'])){
                $params['account'] = $post_data['account'];
            }
            if(isset($post_data['mobile']) && $post_data['mobile']!= ""){
                $params['mobile'] = $post_data['mobile'];
            }
            if(isset($post_data['time_limit']) && !empty($post_data['time_limit'])){
                $params['time_limit'] = $post_data['time_limit'];
            }
        }else if($customer_type == 4){
            $params['roleId'] = 5;
        }
        switch($customer_type){
            case 1:$apiData = $User->admin_fetchs($params); break;
            case 2:$apiData = $User->user_fetchs($params);break;
            case 3:$apiData = $User->intermediary_fetchs($params);break;
            case 4:$apiData = $User->merchandiser_fetchs($params);break; //跟单员
        }
        $totalItem = 0;
        $items = [];
        $time_limit = "";
        $returnData = array(
            "totalItem" => &$totalItem,
            "items"     => &$items,
            "p" => $p,
            "time_limit" => &$time_limit
        );
        
        if($apiData['returnState'] == 1 && !empty($apiData['returnData']['data'])){
            $totalItem = $apiData['returnData']['recordcount'];
            $items = $apiData['returnData']['data'];
            $time_limit = $apiData['returnData']['time_limit'];
        }
        $this->ajaxReturn(array("status"=>1,"data"=>$returnData));
    }
    
    /**
    客户列表
     * $customer_type 1：普通员工/跟单员  2：中介  3：普通会员  4:业务员
     *  */
    public function view_single_info(){
        $post_data = I("post.");
        $User = new UserModel();
        
        $customer_type = I("post.customer_type",1,intval);
        
        switch($customer_type){
            case 1: $apiData = $User->admin_single_view($post_data); break;
            case 2: $User->user_single_view($post_data);break;
            case 3: $User->intermediary_single_view($post_data);break;
        }
        $info = "";$responseData = [];
        if($apiData['returnState'] == 1 && !empty($apiData['returnData'])){
            $responseData = $apiData["returnData"];
        }else{
            $info = get_error_info($apiData['returnState']);
        }
        $this->ajaxReturn(array("status"=>$apiData['returnState'],"data"=>$responseData,"info"=>$info));
    }
    
     /**
    添加修改客户
     * $customer_type 1：普通员工/跟单员  2：中介  3：普通会员  4:业务员
     *  */
    public function add_save(){
        $post_data = I("post.");
        $User = new UserModel();
        if(!isset($post_data['dnames']) || empty($post_data['dnames'])
                || !isset($post_data['roleId']) || empty($post_data['roleId'])
                || !isset($post_data['account']) || empty($post_data['account'])
                || !isset($post_data['sex']) || $post_data['sex'] === false){
            $this->ajaxReturn(array("status"=>0,"info"=>"参数错误"));
        }
        
        $user_id = I("post.id",0,intval);
        $role_type = I("post.roleId",1,intval);
        $role_type = 4;
        //处理图片上传
        if($_FILES['file']['name']){
            $file = new Upfile($_FILES['file']);
            $post_data['pic']= $file->get_url();
        }
        $apiData = [];
        if($user_id == 0){
            switch($role_type){
                case 4: $apiData = $User->admin_add($post_data); break;
                case 5: $apiData = $User->user_add($post_data);break;
                case 6: $apiData = $User->intermediary_add($post_data);break;
            }
        }else{
            switch($role_type){
                case 4: $apiData = $User->admin_save($post_data); break;
                case 5: $apiData = $User->user_save($post_data);break;
                case 6: $apiData = $User->intermediary_save($post_data);break;
            }
        }
        $status = 0; $info = "";
        if($apiData['returnState'] == 1){
            $status = 1;
        }else{
            $info = get_error_info($apiData['returnState']);
        }
        
        $this->ajaxReturn(array("status"=>$status,"info"=>$info),'json');
    }
    
    public function set_post(){
        $post_data = I("post.");
        $User = new UserModel();
        
        if(empty($post_data)){
            $this->ajaxReturn(array("status"=>0,"info"=>"参数错误"),json);
        }
        
        $apiData = $User->set_post($post_data);
        
        $info = "";
        if($apiData['returnState'] != 1){
            $info = get_error_info($apiData['returnState']);
        }
        
        $this->ajaxReturn(array("status"=>$apiData['returnState'],"info"=>$info));
    }
    
     /**
    停用/启用客户
     * $customer_type 1：普通员工/跟单员  2：中介  3：普通会员  4:业务员
     *  */
    public function delete(){
        $post_data = I("post.");
        $User = new UserModel();
        
        $customer_type = I("post.customer_type",1,intval);
        
        switch($customer_type){
            case 1: $apiData = $User->admin_delete($post_data); break;
            case 2: $User->user_delete($post_data);break;
            case 3: $User->intermediary_delete($post_data);break;
        }
        $info = "";
        if($apiData['returnState'] != 1){
            $info = get_error_info($apiData['returnState']);
        }
        $this->ajaxReturn(array("status"=>$apiData['returnState'],"info"=>$info));
    }
    
}