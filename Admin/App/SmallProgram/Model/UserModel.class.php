<?php
namespace SmallProgram\Model;
use SmallProgram\Model\BaseModel;
// 描述：用于商品资料管理下的操作
class UserModel extends BaseModel{
    
    private $Module = "user";
    private $User_Module = "usercenter";
    
    private $admin_controller = "user";
    private $admin_User_controller = "customer";
    private $intermediary_controller = "medium";
    
    private $login_router = "login";        
    private $bindaccount_router = "bindaccount";
    
    private $customerregister_router = "customerregister";        
    private $customerdesc_router = "customerdesc";        
    private $staffdesc_router = "staffdesc";        
    private $customeredit_router = "customeredit";        
    private $staffedit_router = "staffedit";  
    private $intermediaryadd_router = "mediumadd";
    private $intermediaryedit_router = "mediumedit";
    private $intermediarylist_router = "mediumlist";
    private $intermediarysingleview_router = "mediumdesc";
    private $rolestafflist_router = "rolestafflist";
    
    //staffsetpost 登录
    public function login($params) {
        $apiData = c_call_service($this->Module, $this->admin_controller, $this->login_router,$params);
        return $apiData;
    }
    
    //绑定openId  bindaccount
    public function bindaccount($params) {
        $apiData = c_call_service($this->Module, $this->admin_controller, $this->bindaccount_router,$params);
        return $apiData;
    }
    
    //绑定openId  bindaccount
    public function customerregister($params) {
        $apiData = c_call_service($this->User_Module, $this->admin_User_controller, $this->customerregister_router,$params);
        return $apiData;
    }
    
    //绑定openId  bindaccount
    public function user_single_view($params) {
        $apiData = c_call_service($this->User_Module, $this->admin_User_controller, $this->customerdesc_router,$params);
        return $apiData;
    }
    
    //绑定openId  bindaccount
    public function staff_single_view($params) {
        $apiData = c_call_service($this->Module, $this->admin_controller, $this->staffdesc_router,$params);
        return $apiData;
    }
    
    public function rolestafflist($params) {
        $apiData = c_call_service($this->Module, $this->admin_controller, $this->rolestafflist_router,$params);
        return $apiData;
    }
    
    //绑定openId  bindaccount
    public function customeredit($params) {
        $apiData = c_call_service($this->User_Module, $this->admin_User_controller, $this->customeredit_router,$params);
        return $apiData;
    }
    //绑定openId  bindaccount
    public function admin_save($params) {
        $apiData = c_call_service($this->Module, $this->admin_controller, $this->staffedit_router,$params);
        return $apiData;
    }
    
    public function intermediary_add($params) {
        $apiData = c_call_service($this->User_Module, $this->intermediary_controller, $this->intermediaryadd_router,$params);
        return $apiData;
    }
    public function intermediary_edit($params) {
        $apiData = c_call_service($this->User_Module, $this->intermediary_controller, $this->intermediaryedit_router,$params);
        return $apiData;
    }
    public function intermediary_list($params) {
        $apiData = c_call_service($this->User_Module, $this->intermediary_controller, $this->intermediarylist_router,$params);
        return $apiData;
    }
    public function intermediary_singleview($params) {
        $apiData = c_call_service($this->User_Module, $this->intermediary_controller, $this->intermediarysingleview_router,$params);
        return $apiData;
    }
    
}
