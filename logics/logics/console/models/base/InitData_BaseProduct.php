<?php
namespace console\models\base;

use console\models\BaseModel;
use console\models\base\BaseProductOperate;
use console\models\user\View_UserLogin;
use console\models\base\View_BaseAttr;
use console\models\base\View_BaseProductcategory;
use console\models\base\View_BaseProductCommission;

class InitData_BaseProduct extends BaseModel {
    
    private $sellerId;
    private $attrs;  
    
    private function handlAttr($event){
        
        $View_BaseAttr = new View_BaseAttr();
        
        $index = 1;
        foreach ($this->attrs as $attrItem){

            if (!isset($attrItem["id"]) || !is_numeric($attrItem["id"]) || empty($attrItem["id"]) ||
                !isset($attrItem["required"]) || !is_numeric($attrItem["required"]) || !in_array($attrItem["required"],[0,1])){
                continue;
            }

            $attrId = $attrItem["id"];
            $attrData = $View_BaseAttr->getOne($event, $attrId, $this->sellerId);
                
            if (!empty($attrData)) {
                $event->base_product_attr_data[] = array(
                        "seller_id" => $this->sellerId,
                        "productId" => &$event->productId,
                    "attrId" => $attrId,
                    "attrName" => $attrData["dnames"],
                    "genre" => $attrData["genre"],
                    "required" => $attrItem["required"],
                    "imageCount" => $attrData["imageCount"],
                    "sort" => $index++,          
                    );                     
                $genre = $attrData["genre"];
                if (in_array($genre, [3,4])) {
                    $attrItemsData = $View_BaseAttr->getAttrItems($event, $attrId, $this->sellerId);

                foreach ($attrItemsData as $attrItemsItem) {
                        $event->base_product_attr_item_data[] = array(
                    "seller_id" => $this->sellerId,
                    "productId" => &$event->productId,
                        "attrId" => $attrId,
                        "attrItemId" => $attrItemsItem["id"],
                        "attrItemName" => $attrItemsItem["dnames"],
                        "sort" => $attrItemsItem["sort"]
                );                    
            }
        }
            }     

        }
        
        unset($View_BaseAttr);
    }
    
    public function productAdd($event){
        
        $data = &$event->RequestArgs;
    
        if (empty($data) ||
            /*!isset($data["dnos"]) || empty($data["dnos"]) || is_string($data["dnos"]) ||*/
            !isset($data["dnames"]) || empty($data["dnames"]) || !is_string($data["dnames"]) ||
            !isset($data["categoryId"]) || empty($data["categoryId"]) || !is_numeric($data["categoryId"])) {
            return parent::go_error($event, -12);
        }
        
        $ownSellerId = $this->sellerId = View_UserLogin::getOperateSellerId($data);
        if (empty($ownSellerId)) {
            return parent::go_error($event, -2011);
        }   
        
        $categoryId = $data["categoryId"];
        $View_BaseProductcategory = new View_BaseProductcategory();
        $Category = $View_BaseProductcategory->getOne($event, $categoryId, $ownSellerId);
        if (empty($Category)) {
            unset($View_BaseProductcategory); 
            return parent::go_error($event, -3000);
        }    
        unset($View_BaseProductcategory);
        
        /*
        $dnos = $data["dnos"];
        $View_BaseProduct = new View_BaseProduct();
        $chechNo = $View_BaseProduct->chechNo($event, $ownSellerId, $dnames);
        if (!empty($chechNo)) {
            unset($View_BaseProduct); 
            return parent::go_error($event, -2501);
        } 
        */
        
        $dnames = $data["dnames"];
        $View_BaseProduct = new View_BaseProduct();
        $chechNames = $View_BaseProduct->chechName($event, $dnames);
        
        unset($View_BaseProduct); 
        if (!empty($chechNames)) {
            return parent::go_error($event, -2131);
        }         
        
        $logSectionId = View_UserLogin::getOperateSectionId($data);
        $logUserId = View_UserLogin::getOperateUserId($data);        
        $nowTime = date('Y-m-d H:i:s');        
        
        $event->base_product_data = array(
            "id" => &$event->productId,
            //"dnos" => $dnos,
            "dnames" => $data["dnames"],
            "image" => $data["image"],
            "algorithm" => $Category["algorithm"],
            "display" => (isset($data["display"]) && is_string($data["display"]) && !empty($data["display"])) ? $data["display"] : 0,
            "descr" => (isset($data["descr"]) && is_string($data["descr"]) && !empty($data["descr"])) ? $data["descr"] : "",
            "seller_id" => $ownSellerId,
            "categoryId" => $categoryId,
            "creatTime" => $nowTime,
            "nowTime" => $nowTime               
        );
        
        if (isset($data["attrs"]) && !empty($data["attrs"]) && is_array($data["attrs"])) {
            $this->attrs = $data["attrs"];
            $this->handlAttr($event);
        }
        
        BaseProductOperate::setAddData($event, 1, $ownSellerId, $logSectionId, $logUserId, $nowTime);
/*       
var_dump("base_product_data",$event->base_product_data);  
var_dump("base_product_attr_data",$event->base_product_attr_data);  
var_dump("base_product_attr_item_data",$event->base_product_attr_item_data);  
return parent::go_error($event, -10000);
*/
    }
    
    public function productEdit($event){
        
        $data = &$event->RequestArgs;
        
        if (empty($data) ||
            !isset($data["id"]) || empty($data["id"]) || !is_numeric($data["id"])) {
            return $this->go_error($event, -12);
        }  
        
        if (/*!(isset($data["dnos"]) && !empty($data["dnos"]) && is_string($data["dnos"])) ||*/
            !(isset($data["dnames"]) && !empty($data["dnames"]) && is_string($data["dnames"])) ||
            !(isset($data["descr"]) && !empty($data["descr"]) && is_string($data["descr"])) ||
            !(isset($data["display"]) && is_numeric($data["display"])) ||
            !(isset($data["categoryId"]) && !empty($data["categoryId"]) && is_numeric($data["categoryId"]))) {
            return parent::go_error($event, -12);
        } 
        
        $ownSellerId = $this->sellerId = View_UserLogin::getOperateSellerId($data);
        if (empty($ownSellerId)) {
            return parent::go_error($event, -2011);
        }    

        $View_BaseProduct = new View_BaseProduct();

        $id = $event->productId = $data["id"];
        $oldProduct = $View_BaseProduct->getOne($event, $id, $ownSellerId);
            unset($View_BaseProduct); 
        if (empty($oldProduct)) {
            return parent::go_error($event, -2132);
        }
        
        $logSectionId = View_UserLogin::getOperateSectionId($data);
        $logUserId = View_UserLogin::getOperateUserId($data);        
        $nowTime = date('Y-m-d H:i:s');         
        $newProduct = $data;
        
        BaseProduct::setEditData($event, $id, $nowTime, $newProduct, $oldProduct);
        
        if (isset($data["attrs"]) && !empty($data["attrs"]) && is_array($data["attrs"])) {
            $this->attrs = $data["attrs"];
            $this->handlAttr($event);
            
            $event->base_product_attr_del = array(
                "sellerId" => $ownSellerId,
                "productId" => $event->productId
            );    
            
            $event->base_product_attr_item_del = array(
                "sellerId" => $ownSellerId,
                "productId" => $event->productId
            );              
        }     
        
        BaseProductOperate::setAddData($event, 2, $ownSellerId, $logSectionId, $logUserId, $nowTime);
/*       
var_dump("base_product_data",$event->base_product_data);  
var_dump("base_product_attr_data",$event->base_product_attr_data);  
var_dump("base_product_attr_del",$event->base_product_attr_del); 
var_dump("base_product_attr_item_data",$event->base_product_attr_item_data);  
var_dump("base_product_attr_item_del",$event->base_product_attr_item_del); 
return parent::go_error($event, -10000);
*/        
    }

    public function productDisplay($event){
        
        $data = &$event->RequestArgs;
        
        if (empty($data) ||
            !isset($data["ids"]) || empty($data["ids"]) || !is_array($data["ids"]) ||
            !isset($data["display"]) || !is_numeric($data["display"])|| !in_array($data["display"],[0,1]) ) {
            return $this->go_error($event, -12);
        }  
        
        $ownSellerId = $this->sellerId = View_UserLogin::getOperateSellerId($data);
        if (empty($ownSellerId)) {
            return parent::go_error($event, -2011);
        }    

        $View_BaseProduct = new View_BaseProduct();

        $ids = $data["ids"];
        //1:上架 0:下架
        $display = $data["display"];
        
        $logSectionId = View_UserLogin::getOperateSectionId($data);
        $logUserId = View_UserLogin::getOperateUserId($data);        
        $nowTime = date('Y-m-d H:i:s'); 
        
        foreach ($ids as $id) {
            
        $oldProduct = $View_BaseProduct->getOne($event, $id, $ownSellerId);
            $newProduct = array(
                "display" => $display
            );
            
            if (empty($oldProduct) || $display == $oldProduct["display"]  || !$oldProduct["isSetAlgorithm"]) {
                continue;
        }
        
            $event->base_product_data[] = array(
                "id" => $id,
                "display" => $display,
                "nowTime" => $nowTime
            );
            
            $base_product_operate_data = array(
                "seller_id" => $ownSellerId,
                "productId" => $id,
                "sectionId" => $logSectionId,
                "operateUid" => $logUserId,
                "operateTime" => $nowTime              
            );            
            
        if ($display) {
                $base_product_operate_data["operate"] = 4;
        } else {
                $base_product_operate_data["operate"] = 5;
        }
            
            $event->base_product_operate_data[] = $base_product_operate_data;
        }
        unset($View_BaseProduct); 
/*       
var_dump("base_product_data",$event->base_product_data);  
var_dump("base_product_operate_data",$event->base_product_operate_data);  
return parent::go_error($event, -10000);
*/         
    }    

    public function productDelete($event){
        
        $data = &$event->RequestArgs;
        
        if (empty($data) || !isset($data["id"]) || empty($data["id"]) || !is_numeric($data["id"])) {
            return parent::go_error($event, -12);
        }
        
        $ownSellerId = View_UserLogin::getOperateSellerId($data);
        if (empty($ownSellerId)) {
            return parent::go_error($event, -2011);
        }  
        
        $id = $event->productId = $data["id"];
        
        $View_BaseProduct = new View_BaseProduct();
        $oldProduct = $View_BaseProduct->getOne($event, $id, $ownSellerId);
        unset($View_BaseProduct); 
        if (empty($oldProduct)) {
            return parent::go_error($event, -2132);
        }        
        
        $event->base_product_data = array(
            "id" => $id,
            "sellerId" => $ownSellerId
        );
        
        $event->base_product_attr_del = array(
            "productId" => $id,
            "sellerId" => $ownSellerId
        );

        $event->base_product_attr_item_del = array(
            "productId" => $id,
            "sellerId" => $ownSellerId
        );
                
        $event->base_product_commission_quot_del = array(
            "productId" => $id,
            "sellerId" => $ownSellerId
        );
                
        $event->base_product_commission_percentage_del = array(
            "productId" => $id,
            "sellerId" => $ownSellerId
        );        

        $logSectionId = View_UserLogin::getOperateSectionId($data);
        $logUserId = View_UserLogin::getOperateUserId($data);        
        $nowTime = date('Y-m-d H:i:s');          

        BaseProductOperate::setAddData($event, 3, $ownSellerId, $logSectionId, $logUserId, $nowTime);
/*       
var_dump("base_product_data",$event->base_product_data);  
var_dump("base_product_attr_del",$event->base_product_attr_del);  
var_dump("base_product_attr_item_del",$event->base_product_attr_item_del); 
var_dump("base_product_commission_quot_del",$event->base_product_commission_quot_del);  
var_dump("base_product_commission_percentage_del",$event->base_product_commission_percentage_del); 
return parent::go_error($event, -10000);
*/           
    }   
    
    public function productCommission($event){
        
        $data = &$event->RequestArgs;
        
        if (empty($data) ||
            !isset($data["id"]) || empty($data["id"]) || !is_numeric($data["id"])) {
            return $this->go_error($event, -12);
        }  

        $ownSellerId = $this->sellerId = View_UserLogin::getOperateSellerId($data);
        if (empty($ownSellerId)) {
            return parent::go_error($event, -2011);
        }    

        $View_BaseProduct = new View_BaseProduct();

        $id = $event->productId = $data["id"];
        $oldProduct = $View_BaseProduct->getOne($event, $id, $ownSellerId);
        unset($View_BaseProduct); 
        if (empty($oldProduct)) {
            return parent::go_error($event, -2132);
        }
        
        if (isset($data["algorithm"]) && is_numeric($data["algorithm"]) && in_array($data["algorithm"], [1,2])) {
            $algorithm = $data["algorithm"];
        } else {
            $algorithm = $oldProduct["algorithm"];
        }
        
        $chang = 0;
        $newalgorithm = $data["algorithm"];
        $oldalgorithm = $oldProduct["algorithm"];
        
        //1:定额,2:百分比
        
        if ($algorithm == 1) {
            if (!isset($data["minAmount"]) || !is_numeric($data["minAmount"]) || empty($data["minAmount"]) ||
                !isset($data["minDays"]) || !is_numeric($data["minDays"]) || empty($data["minDays"]) ||
                !isset($data["commission"]) || !is_numeric($data["commission"]) || empty($data["commission"]) ||
                !isset($data["mediumCommission"]) || !is_numeric($data["mediumCommission"]) || empty($data["mediumCommission"]) ||
                !isset($data["salesmanCommission"]) || !is_numeric($data["salesmanCommission"]) || empty($data["salesmanCommission"])){
                return parent::go_error($event, -12);
            }
            
            $event->base_product_commission_quot_data = array(
                "seller_id" => $ownSellerId,
                "productId" => $id,
                "minAmount" => $data["minAmount"],
                "minDays" => $data["minDays"],
                "commission" => $data["commission"],
                "mediumCommission" => $data["mediumCommission"],
                "salesmanCommission" => $data["salesmanCommission"]                     
            );
            
            if ($newalgorithm != $oldalgorithm) {
                $event->base_product_commission_quot_data["Operate"] = 1;
                $event->base_product_commission_percentage_del = array(
                    "sellerId" => $ownSellerId,
                    "productId" => $id,                    
                );
            } else {
                $View_BaseProductCommission = new View_BaseProductCommission();
                $CommissionQuot = $View_BaseProductCommission->getOneCommissionQuot($event, $ownSellerId, $id);
                unset($View_BaseProductCommission);    
                if (empty($CommissionQuot)) {
                    $event->base_product_commission_quot_data["Operate"] = 1;
                } else {
                $event->base_product_commission_quot_data["Operate"] = 0;
            }
            }
            
            $chang = 1;
        } else {
            if (!isset($data["commission"]) || !is_numeric($data["commission"]) || empty($data["commission"]) ||
                !isset($data["mediumCommission"]) || !is_numeric($data["mediumCommission"]) || empty($data["mediumCommission"]) ||
                !isset($data["salesmanCommission"]) || !is_numeric($data["salesmanCommission"]) || empty($data["salesmanCommission"])){
                return parent::go_error($event, -12);
            }            
            $event->base_product_commission_percentage_data = array(
                "seller_id" => $ownSellerId,
                "productId" => $id,
                "commission" => $data["commission"],
                "mediumCommission" => $data["mediumCommission"],
                "salesmanCommission" => $data["salesmanCommission"]                     
            );   
            
            if ($newalgorithm != $oldalgorithm) {
                $event->base_product_commission_percentage_data["Operate"] = 1;
                $event->base_product_commission_quot_del = array(
                    "sellerId" => $ownSellerId,
                    "productId" => $id,                    
                );
            } else {
                $View_BaseProductCommission = new View_BaseProductCommission();
                $CommissionPercentage = $View_BaseProductCommission->getOneCommissionPercentage($event, $ownSellerId, $id);
                unset($View_BaseProductCommission);    
                if (empty($CommissionPercentage)) {
                    $event->base_product_commission_percentage_data["Operate"] = 1;
                } else {
                $event->base_product_commission_percentage_data["Operate"] = 0;
            }            
            }    
            
            $chang = 1;
        }
        
        $nowTime = date('Y-m-d H:i:s');  
            $event->base_product_data = array(
                "id" => $id,
                "algorithm" => $newalgorithm,
                "isSetAlgorithm" => 1,
                "nowTime" => $nowTime
            );          
            
        $logSectionId = View_UserLogin::getOperateSectionId($data);
        $logUserId = View_UserLogin::getOperateUserId($data);        
        
        BaseProductOperate::setAddData($event, 3, $ownSellerId, $logSectionId, $logUserId, $nowTime);
/*       
var_dump("base_product_data",$event->base_product_data);  
var_dump("base_product_commission_quot_data",$event->base_product_commission_quot_data);  
var_dump("base_product_commission_quot_del",$event->base_product_commission_quot_del);  
var_dump("base_product_commission_percentage_data",$event->base_product_commission_percentage_data);  
var_dump("base_product_commission_percentage_del",$event->base_product_commission_percentage_del); 
var_dump("base_product_operate_data",$event->base_product_operate_data); 
return parent::go_error($event, -10000);
*/   
    }    

}
