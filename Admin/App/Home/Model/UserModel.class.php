<?php
namespace Home\Model;
use Home\Model\BaseModel;
// 描述：用于商品资料管理下的操作
class UserModel extends BaseModel{
    
    private $Module = "user";
    private $user_Module = "usercenter";
    
    private $admin_controller = "user";
    private $user_controller = "customer";
    private $intermediary_controller = "medium";
    
    private $login_router = "login";                   //登录
    
    private $admin_fetchs_router = "stafflist";
    private $user_fetchs_router = "customerlist";
    private $intermediary_fetchs_router = "mediumlist";
    private $merchandiser_fetchs_router = "rolestafflist";
    
    private $admin_add_router = "staffadd";
    private $user_add_router = "";
    private $intermediary_add_router = "";
    
    private $admin_save_router = "staffedit";
    private $user_save_router = "";
    private $intermediary_save_router = "";
    
    private $admin_delete_router = "staffenable";  //启用 停用  1停用  2启用
    private $user_delete_router = "";
    private $intermediary_delete_router = "";
    
    private $admin_singleview_router = "staffdesc";
    private $user_singleview_router = "";
    private $intermediary_singleview_router = "";
    
    private $setpost_router = "staffsetpost";
    
   //staffsetpost
     //登录
    public function login($params) {
        $apiData = c_call_service($this->Module, $this->admin_controller, $this->login_router,$params);
        return $apiData;
    }
    
    //员工列表
    public function admin_fetchs($params) {
        $apiData = c_call_service($this->Module, $this->admin_controller, $this->admin_fetchs_router,$params);
        return $apiData;
    }
    
    //前端列表
    public function user_fetchs($params) {
        $apiData = c_call_service($this->user_Module, $this->user_controller, $this->user_fetchs_router,$params);
        return $apiData;
    }
    
    //中介列表
    public function intermediary_fetchs($params) {
        $apiData = c_call_service($this->user_Module, $this->intermediary_controller, $this->intermediary_fetchs_router,$params);
        return $apiData;
    }
    //中介列表
    public function merchandiser_fetchs($params) {
        $apiData = c_call_service($this->Module, $this->admin_controller, $this->merchandiser_fetchs_router,$params);
        return $apiData;
    }
    
    //单个查询员工
    public function admin_single_view($params) {
        $apiData = c_call_service($this->Module, $this->admin_controller, $this->admin_singleview_router,$params);
        return $apiData;
    }
    
    //单个查询终端用户
    public function user_single_view($params) {
        $apiData = c_call_service($this->Module, $this->Controller, $this->user_singleview_router,$params);
        return $apiData;
    }
    
    //单个查询中介信息
    public function intermediary_single_view($params) {
        $apiData = c_call_service($this->Module, $this->Controller, $this->intermediary_singleview_router,$params);
        return $apiData;
    }
    
    /**
    添加员工
    **/
    public function admin_add($params) {
        $apiData = c_call_service($this->Module, $this->admin_controller, $this->admin_add_router,$params);
        return $apiData;
    }
    
    /**
    终端用户添加
    **/
    public function user_add($params) {
        $apiData = c_call_service($this->Module, $this->Controller, $this->user_add_router,$params);
        return $apiData;
    }
    
    /**
    添加中介
    **/
    public function intermediary_add($params) {
        $apiData = c_call_service($this->Module, $this->admin_controller, $this->intermediary_add_router,$params);
        return $apiData;
    }
    
    /**
    修改员工
     **/
    public function admin_save($params) {
        $apiData = c_call_service($this->Module, $this->admin_controller, $this->admin_save_router,$params);
        return $apiData;
    }
    
    /**
    修改终端用户
     **/
    public function user_save($params) {
        $apiData = c_call_service($this->Module, $this->Controller, $this->user_save_router,$params);
        return $apiData;
    }
    
    /**
    修改中介
     **/
    public function intermediary_save($params) {
        $apiData = c_call_service($this->Module, $this->Controller, $this->intermediary_save_router,$params);
        return $apiData;
    }
    
    //员工删除
    public function admin_delete($params) {
        
        $apiData = c_call_service($this->Module, $this->admin_controller, $this->admin_delete_router,$params);
        return $apiData;
    }
    
    //终端客户删除
    public function user_delete($params) {
        $apiData = c_call_service($this->Module, $this->Controller, $this->user_delete_router,$params);
        return $apiData;
    }
    
    //中介删除
    public function intermediary_delete($params) {
        $apiData = c_call_service($this->Module, $this->Controller, $this->intermediary_delete_router,$params);
        return $apiData;
    }
    
    //设置岗位权限
    public function set_post($params) {
        $apiData = c_call_service($this->Module, $this->admin_controller, $this->setpost_router,$params);
        return $apiData;
    }
    
}
