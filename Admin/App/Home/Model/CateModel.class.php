<?php
namespace Home\Model;
use Home\Model\BaseModel;
// 描述：用于商品类别管理下的操作
class CateModel extends BaseModel{
    
    private $Module = "base";
    private $Controller = "cate";
    
    private $fetchs_router = "fetchall";
    private $add_router = "cateadd";
    private $save_router = "catesave";
    private $delete_router = "catedelete";
   
    //列表
    public function fetchs($params) {
        $apiData = c_call_service($this->Module, $this->Controller, $this->fetchs_router,$params);
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
