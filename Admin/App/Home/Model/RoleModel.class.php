<?php
namespace Home\Model;
use Home\Model\BaseModel;
// 描述：用于商品资料管理下的操作
class RoleModel extends BaseModel{
    private $Module = "right";
    private $Controller = "post";
    private $Power_Module = "user";
    private $Power_Controller = "user";
    
    private $fetchs_router = "postlist";
    private $power_fetchs_router = "fetchusernode";
    private $getallnode_router = "getallnode";
    private $add_router = "postadd";
    private $save_router = "postedit";
    private $delete_router = "postdelete";
    private $singleview_router = "postsingle";
   
    //列表
    public function fetchs($params) {
        $apiData = c_call_service($this->Module, $this->Controller, $this->fetchs_router,$params);
        return $apiData;
    }
    
    //列表
    public function get_user_node($params) {
        $apiData = c_call_service($this->Power_Module, $this->Power_Controller, $this->power_fetchs_router,$params);
        return $apiData;
    }
    
    //列表
    public function get_all_node($params) {
        $apiData = c_call_service($this->Power_Module, $this->Power_Controller, $this->getallnode_router,$params);
        return $apiData;
    }
    
    //单个查询
    public function single_view($params) {
        $apiData = c_call_service($this->Module, $this->Controller, $this->singleview_router,$params);
        return $apiData;
    }
    
    /*
    添加
    **/
    public function add($params) {
        $apiData = c_call_service($this->Module, $this->Controller, $this->add_router,$params);
        return $apiData;
    }
    
    /*
    修改
     * */
    public function save($params) {
        $apiData = c_call_service($this->Module, $this->Controller, $this->save_router,$params);
        return $apiData;
    }
    
    //删除
    public function delete($params) {
        $apiData = c_call_service($this->Module, $this->Controller, $this->delete_router,$params);
        return $apiData;
    }
    
}
