<?php
namespace Home\Model;
use Home\Model\BaseModel;
// 描述：用于商品资料管理下的操作
class AdminOrderModel extends BaseModel{
    
    private $Module = "order";
    private $Controller = "order";
    
    private $get_order_num_router = "";
    private $fetchs_router = "";
    private $add_router = "";
    private $save_router = "";
    private $delete_router = "";
    private $singleview_router = "";
   
    //获取首页订单数量
    public function get_order_num($params) {
        $apiData = c_call_service($this->Module, $this->Controller, $this->get_order_num_router,$params);
        return $apiData;
    }
    
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
