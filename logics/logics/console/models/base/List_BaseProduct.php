<?php
namespace console\models\base;


use console\models\BaseModel;
use console\models\user\View_UserLogin;
use console\models\base\View_BaseProduct;
use console\models\base\View_BaseProductAttr;
use console\models\base\View_BaseProductAttrItem;
use console\models\base\View_BaseProductCommission;

class List_BaseProduct extends BaseModel {
    
    public function productList($event){
        
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
        
        $condition = " AND bp.creatTime <= :time_limit";
        $params = array(
            ":sellerId" => $ownSellerId,
            ":time_limit" => $time_limit
        );
        
        //产品名称
        if (isset($data["dnames"]) && !empty($data["dnames"])) {
            $condition .= " AND bp.dnames LIKE :dnames";
            $params[":dnames"] = "%" . $data["dnames"] . "%";
        }   
        
        //上架/下架
        if (isset($data["display"]) && $data["display"]!= "") {
            $condition .= " AND bp.display = :display";
            $params[":display"] = $data["display"];
        }           
        
        $View_BaseProduct = new View_BaseProduct();
        $dataAttr = $View_BaseProduct->getAllProduct($event, $ispage, $condition, $params, $limit);
        unset($View_BaseProduct);
        
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
    
    public function productDesc($event){
        
        $data = &$event->RequestArgs;
        
        if (!isset($data["id"]) || empty($data["id"]) || !is_numeric($data["id"])) {
            return parent::go_error($event, -12);
        }   

        $productId = $data["id"];        
        $needItem = (isset($data["needItem"]) && is_numeric($data["needItem"]) && in_array($data["needItem"], [0,1])) ? $data["needItem"] : 0;

        $ownSellerId = View_UserLogin::getOperateSellerId($data);
        if (empty($ownSellerId)) {
            return parent::go_error($event, -2011);
        }   

        $condition = " AND bp.id = :productId";
        $params = array(
            ":sellerId" => $ownSellerId,
            ":productId" => $productId
        );

        $View_BaseProduct = new View_BaseProduct();
        $dataProduct = $View_BaseProduct->getAllProduct($event, 0, $condition, $params, null);
        unset($View_BaseProduct);  
        
        if (!empty($dataProduct)) {
            $dataProduct = $dataProduct[0]; 
            $View_BaseProductAttr = new View_BaseProductAttr();
            $dataAttr = $View_BaseProductAttr->getProductAttr($event, $ownSellerId, $productId);
            unset($View_BaseProductAttr);
            if (!empty($dataAttr)) {
                $dataProduct["attrs"] = &$dataAttr;
                if ($needItem) {
                    $View_BaseProductAttrItem = new View_BaseProductAttrItem();
                    $dataAttrItem = $View_BaseProductAttrItem->getProductAttrItem($event, $ownSellerId, $productId);
                    unset($View_BaseProductAttrItem);     
                    
                    $attrItems = [];
                    foreach ($dataAttrItem as $item) {
                        $attrId = $item["attrId"] . "_id";
                        $attrItems[$attrId][] = $item;
                    }
                    
                    foreach ($dataAttr as &$itemAttr) {
                      
                        $keyId = $itemAttr["attrId"] . "_id";

                        if (array_key_exists($keyId, $attrItems)) {
                            $itemAttr["items"] = $attrItems[$keyId];
                        }

                        unset($itemAttr);

                    }
                }                
            }            
        }
        
        if (empty($dataProduct)) {
            $dataProduct = [];
        }        
        
        $event->Postback($dataProduct); 
    }
    
    public function productCommissionDesc($event){
        
        $data = &$event->RequestArgs;
        
        if (!isset($data["id"]) || empty($data["id"]) || !is_numeric($data["id"])) {
            return parent::go_error($event, -12);
        }   

        $productId = $data["id"];        

        $ownSellerId = View_UserLogin::getOperateSellerId($data);
        if (empty($ownSellerId)) {
            return parent::go_error($event, -2011);
        }   
        
        $View_BaseProduct = new View_BaseProduct();
        $dataProduct = $View_BaseProduct->getOne($event, $productId, $ownSellerId);
        unset($View_BaseProduct);  
        
        $Commission = [];
        if (!empty($dataProduct)) {
            $algorithm = $dataProduct["algorithm"];
            $View_BaseProductCommission = new View_BaseProductCommission();
            if ($algorithm == 1) {
                $Commission = $View_BaseProductCommission->getOneCommissionQuot($event, $ownSellerId, $productId);
            } else {
                $Commission = $View_BaseProductCommission->getOneCommissionPercentage($event, $ownSellerId, $productId);
            }            
            $Commission["algorithm"] = $algorithm;
            unset($View_BaseProductCommission);
        }
        
        if (empty($Commission)) {
            $Commission = [];
        }        
        
        $event->Postback($Commission); 
    }    
}
