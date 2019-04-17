<?php
namespace Home\Model;
use Home\Model\BaseModel;
// 描述：用于商品类别管理下的操作
class OrderModel extends BaseModel{
    
    private $Module = "business";
    private $Controller = "order";
    
    private $fetchs_router = "orderlist";
    private $add_router = "cateadd";
    private $save_router = "catesave";
    private $delete_router = "catedelete";
    private $ordercheck_router = "ordercheck";
    private $ordersign_router = "ordersign";
    private $orderreceive_router = "orderreceive";
    private $orderdelay_router = "orderdelay";
    private $singleview_router = "orderdesc";
    private $orderconsolesumm_router = "orderconsolesumm";
   
    //列表
    public function fetchs($params) {
        $apiData = c_call_service($this->Module, $this->Controller, $this->fetchs_router,$params);
        return $apiData;
    }
    
    //列表
    public function orderdesc($params) {
        $apiData = c_call_service($this->Module, $this->Controller, $this->singleview_router,$params);
        return $apiData;
    }
    
    //列表
    public function order_check($params) {
        $apiData = c_call_service($this->Module, $this->Controller, $this->ordercheck_router,$params);
        return $apiData;
    }
    //签约
    public function order_sign($params) {
        $apiData = c_call_service($this->Module, $this->Controller, $this->ordersign_router,$params);
        return $apiData;
    }
    
    //回款
    public function order_receive($params) {
        $apiData = c_call_service($this->Module, $this->Controller, $this->orderreceive_router,$params);
        return $apiData;
    }
    
    //延期
    public function order_delay($params) {
        $apiData = c_call_service($this->Module, $this->Controller, $this->orderdelay_router,$params);
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
    //删除
    public function orderconsolesumm($params) {
        $apiData = c_call_service($this->Module, $this->Controller, $this->orderconsolesumm_router,$params);
        return $apiData;
    }
    
}
