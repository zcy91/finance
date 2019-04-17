<?php
namespace console\models\base;


use console\models\BaseModel;
use console\models\user\View_UserLogin;
use console\models\base\View_BaseAttr;

class List_BaseAttr extends BaseModel {
    
    public function attrList($event){
        
        $data = &$event->RequestArgs;

        $ownSellerId = View_UserLogin::getOperateSellerId($data);
        if (empty($ownSellerId)) {
            return parent::go_error($event, -2011);
        } 
        
        $now_time = date('Y-m-d H:i:s');
        $time_limit = isset($data["time_limit"]) && !empty($data["time_limit"]) ? $data["time_limit"] : $now_time;
        
        $limit_arr = $event->Pagination;//分页信息
        $limit = parent::getLimitArr($event);//获得分类信息数组或空值
        $ispage = isset($limit_arr["ispage"]) ? $limit_arr["ispage"] : 0; 
        $condition = "";
        $params = array(
            ":sellerId" => $ownSellerId
        );
        if(isset($data['dnames']) && !empty($data['dnames'])){
//            $condition = " and ba.dnames like '%:dnames%'";
//            $params[':dnames'] = $data['dnames'];
            $condition .= " AND ba.dnames LIKE :dnames";
            $params[":dnames"] = "%" . $data["dnames"] . "%";
        }
        $View_BaseAttr = new View_BaseAttr();
        $dataAttr = $View_BaseAttr->getAllAttr($event, $ispage, $condition, $params, $limit);
        unset($View_BaseAttr);
        if (empty($dataAttr)) {
            $dataAttr = [];
        }          
        
        if ($ispage) {
            
            $sql = " SELECT FOUND_ROWS() as record_count; ";
            $return_count = $this->query_SQL($sql, $event);        
            $recode_count = $return_count[0]['record_count']; 
            
            $return_data = array(
                "pagesize" => $limit_arr["pagesize"],
                "pageindex" => $limit_arr["pageindex"],
                "recordcount" => $recode_count,
                "time_limit" => $time_limit,
                "data" => &$dataAttr
            );              
        } else {
            $return_data = &$dataAttr;
        }            
        
        $event->Postback($return_data);        
    }
    
    public function attrDesc($event){
        
        $data = &$event->RequestArgs;
        
        if (!isset($data["id"]) || empty($data["id"]) || !is_numeric($data["id"])) {
            return parent::go_error($event, -12);
        }   

        $attrId = $data["id"];        

        $ownSellerId = View_UserLogin::getOperateSellerId($data);
        if (empty($ownSellerId)) {
            return parent::go_error($event, -2011);
        }   
        
        $View_BaseAttr = new View_BaseAttr();
        $dataAttr = $View_BaseAttr->getOne($event, $attrId, $ownSellerId);
        if (!empty($dataAttr)) {
        if (in_array($dataAttr["genre"], [3,4]) ) {
            $dataAttrItem = $View_BaseAttr->getAttrItems($event, $attrId, $ownSellerId);
                $dataAttr["attrItem"] = array_column($dataAttrItem,'id');
            }            
        }
        unset($View_BaseAttr);  
        
        if (empty($dataAttr)) {
            $dataAttr = [];
        }         
        
        $event->Postback($dataAttr); 
    }
}
