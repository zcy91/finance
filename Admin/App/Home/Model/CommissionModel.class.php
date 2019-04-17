<?php
namespace Home\Model;
use Home\Model\BaseModel;
// 描述：用于商品佣金设置
class CommissionModel extends BaseModel{
    
    private $Module = "base";
    private $Controller = "product";
    
    private $set_commission_router = "productcommission";
    private $view_single_router = "productcommissiondesc";
   
    //设置佣金
    public function set_commission($params) {
        $apiData = c_call_service($this->Module, $this->Controller, $this->set_commission_router,$params);
        return $apiData;
    }
    
    //查询单个佣金信息
    public function view_single($params) {
        $apiData = c_call_service($this->Module, $this->Controller, $this->view_single_router,$params);
        return $apiData;
    }
    
    
    
   
}
