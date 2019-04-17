<?php
namespace SmallProgram\Model;
use SmallProgram\Model\BaseModel;
// 描述：用于商品资料管理下的操作
class OrderModel extends BaseModel{
    
    private $Module = "business";
    private $C_Module = "usercenter";
    
    private $Controller = "order";
//    private $C_Controller = "customer";
    
    private $add_router = "orderadd";        
    private $edit_router = "orderedit";        
    private $orderlist_router = "orderlist";        
    private $orderdesc_router = "orderdesc";        
    private $orderdesced_router = "orderdesced";        
    private $ordercommit_router = "ordercommit";        
    private $orderdelete_router = "orderdelete";        
    
    //订单新增
    public function order_add($params) {
        $apiData = c_call_service($this->Module, $this->Controller, $this->add_router,$params);
        return $apiData;
    }
    
    //订单修改
    public function order_edit($params) {
        $apiData = c_call_service($this->Module, $this->Controller, $this->edit_router,$params);
        return $apiData;
    }
    
    //订单提交
    public function order_commit($params) {
        $apiData = c_call_service($this->Module, $this->Controller, $this->ordercommit_router,$params);
        return $apiData;
    }
    
    //订单删除
    public function order_delete($params) {
        $apiData = c_call_service($this->Module, $this->Controller, $this->orderdelete_router,$params);
        return $apiData;
    }
    
    //差个单个查询
    public function single_view($params) {
        $apiData = c_call_service($this->Module, $this->Controller, $this->orderdesced_router,$params);
        return $apiData;
    }
    
    //订单修改
    public function order_list($params,$roleId) {
        $controller = "";
        switch($roleId){
            case 2: $controller = "medium"; break;
            case 3: $controller = "customer"; break;
            case 4: $controller = "salesman"; break;
            case 5: $controller = "merchandiser"; break;
        }
        $apiData = c_call_service($this->C_Module, $controller, $this->orderlist_router,$params);
        return $apiData;
    }
    
}
