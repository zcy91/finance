<?php
namespace Home\Model;
use Home\Model\BaseModel;
// 描述：用于商品资料管理下的操作
class AttrModel extends BaseModel{
    
    private $Module = "base";
    private $Controller = "attr";
    
    private $fetchs_router = "attrattrlist";
    private $add_router = "attradd";
    private $save_router = "attrsave";
    private $delete_router = "attrdelete";
    private $singleview_router = "attrattrdesc";
   
    //列表
    public function fetchs($params) {
        $apiData = c_call_service($this->Module, $this->Controller, $this->fetchs_router,$params);
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
     *      */
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
