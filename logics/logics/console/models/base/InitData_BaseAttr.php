<?php
namespace console\models\base;

use console\models\BaseModel;
use console\models\base\BaseAttr;
use console\models\user\View_UserLogin;
use console\models\base\View_BaseAttr;

class InitData_BaseAttr extends BaseModel {
    
    public function attrAdd($event){
        
        $data = &$event->RequestArgs;
        
        if (empty($data) ||
            !isset($data["dnames"]) || empty($data["dnames"]) || !is_string($data["dnames"]) ||
            !isset($data["genre"]) || !is_numeric($data["genre"]) || !in_array($data["genre"], [1,2,3,4,5])) {
            return $this->go_error($event, -12);
        }
        
        $genre = $data["genre"];
        
        $attrItems = [];
        if (in_array($genre, [3,4])) {  //3 单选框   4复选框
            if (!isset($data["attrItems"]) || empty($data["attrItems"]) || !is_array($data["attrItems"])) {
                return $this->go_error($event, -12);
            }
            $attrItems = $data["attrItems"];
        }
        
        $imageCount = 0;
        if ($genre == 5) {
            if (!isset($data["attrItems"]) || empty($data["attrItems"]) || !is_array($data["attrItems"])) {
                return $this->go_error($event, -12);
            }
            $attrItems = $data["attrItems"];
            if (!isset($attrItems[0]) || !is_numeric($attrItems[0])) {
                return $this->go_error($event, -12);
            }
            $imageCount = $attrItems[0];
        }
        
        $ownSellerId = View_UserLogin::getOperateSellerId($data);
        if (empty($ownSellerId)) {
            return parent::go_error($event, -2011);
        }        
        
        $dnames = $data["dnames"];
        $View_BaseAttr = new View_BaseAttr();
        $Section = $View_BaseAttr->chechName($event, $ownSellerId, $dnames);
        if (!empty($Section)) {
            unset($View_BaseAttr); 
            return parent::go_error($event, -2101);
        }   
        
        $logSectionId = View_UserLogin::getOperateSectionId($data);
        $logUserId = View_UserLogin::getOperateUserId($data);        
        $nowTime = date('Y-m-d H:i:s');        
        $values = "";
        
        $event->base_attr_data = array(
            "id" => &$event->attrId,
            "dnames" => $dnames,
            "genre" => $genre,
            "required" => (isset($data["required"]) && is_numeric($data["required"]) && in_array($data["required"], [0,1])) ? $data["required"] : 0,
            "imageCount" => $imageCount,
            "sort" => (isset($data["sort"]) && is_numeric($data["sort"])) ? $data["sort"] : 0,
            "values" => &$values,
            "seller_id" => $ownSellerId,
            "creatTime" => $nowTime,
            "nowTime" => $nowTime           
        );
        
        $valuesArr = [];
        foreach ($attrItems as $attrItemsItme) {
            if (!is_string($attrItemsItme)) {
                return $this->go_error($event, -12);
            }
            $event->base_attr_item_data[] = array(
                "dnames" => $attrItemsItme,
                "sort" => 0,
                "seller_id" => $ownSellerId,
                "attrId" => &$event->attrId
            );   
            $valuesArr[] = $attrItemsItme;
        }
        
        if (!empty($valuesArr)) {
            $values = implode("|", $valuesArr);
        }
        
        BaseAttrOperate::setAddData($event, 1, $ownSellerId, $logSectionId, $logUserId, $nowTime);
/*       
var_dump("base_attr_data",$event->base_attr_data);  
var_dump("base_attr_item_data",$event->base_attr_item_data);  
var_dump("base_attr_operate_data",$event->base_attr_operate_data);  
return parent::go_error($event, -10000);
*/
    }
    
    public function attrEdit($event){
        
        $data = &$event->RequestArgs;
        
        if (empty($data) ||
            !isset($data["id"]) || empty($data["id"]) || !is_numeric($data["id"])) {
            return $this->go_error($event, -12);
        }  
        
        if (!(isset($data["dnames"]) && !empty($data["dnames"]) && is_string($data["dnames"])) ||
            !(isset($data["genre"]) && !empty($data["genre"]) && is_string($data["genre"])) ||
            !(isset($data["required"]) && !empty($data["required"]) && is_string($data["required"]))) {
            return;
        }
        
        $ownSellerId = View_UserLogin::getOperateSellerId($data);
        if (empty($ownSellerId)) {
            return parent::go_error($event, -2011);
        }    
        
        $View_BaseAttr = new View_BaseAttr();

        $id = $event->attrId = $data["id"];
        $oldAttr = $View_BaseAttr->getOne($event, $id, $ownSellerId);
        if (empty($oldAttr)) {
            unset($View_BaseAttr); 
            return parent::go_error($event, -2102);
        }
        
        if (isset($data["genre"]) && !empty($data["genre"]) && is_string($data["genre"])) {
            $genre = $data["genre"];
        } else {
            $genre = $oldAttr["genre"];
        }
        
        $attrItems = [];
        if (in_array($genre, [3,4]) && isset($data["attrItems"]) && 
            !empty($data["attrItems"]) && is_array($data["attrItems"])) {
            $attrItems = $data["attrItems"];
        }
        
        if ($genre == 5 && isset($data["attrItems"]) && is_array($data["attrItems"]) && !empty($data["attrItems"])) {
            $attrItems = $data["attrItems"];
            if (!isset($attrItems[0]) || !is_numeric($attrItems[0])) {
                return parent::go_error($event, -12);
            }
            $data["imageCount"] = $data["attrItems"];
            unset($data["attrItems"]);
        }        
        
        if (isset($data["dnames"]) && !empty($data["dnames"]) && 
            is_string($data["dnames"]) && $data["dnames"] != $oldAttr["dnames"]) {
            
            $dnames = $data["dnames"];
            $chechName = $View_BaseAttr->chechName($event, $ownSellerId, $dnames, $id);
            if (!empty($chechName)) {
                unset($View_BaseAttr); 
                return parent::go_error($event, -2101);
            }               
        }

        unset($View_BaseAttr); 
        
        $valuesArr = [];
        foreach ($attrItems as $attrItemsItme) {
            if (!is_string($attrItemsItme)) {
                return $this->go_error($event, -12);
            }
            $event->base_attr_item_add[] = array(
                "dnames" => $attrItemsItme,
                "sort" => 0,
                "seller_id" => $ownSellerId,
                "attrId" => $id
            );   
            $valuesArr[] = $attrItemsItme;
        }
        
        if (!empty($attrItems)) {
            $event->base_attr_item_del = array(
                "attrId" => $id, 
                "seller_id" => $ownSellerId,
            );              
        }
        
        if (!empty($valuesArr)) {
            $data["values"] = implode("|", $valuesArr); 
        }
        
        $logSectionId = View_UserLogin::getOperateSectionId($data);
        $logUserId = View_UserLogin::getOperateUserId($data);        
        $nowTime = date('Y-m-d H:i:s'); 
        $newAttr = $data;

        BaseAttr::setEditData($event, $id, $nowTime, $newAttr, $oldAttr);

        BaseAttrOperate::setAddData($event, 2, $ownSellerId, $logSectionId, $logUserId, $nowTime);
    }    
    
    public function attrDelete($event){
        
        $data = &$event->RequestArgs;
        
        if (empty($data) || !isset($data["id"]) || empty($data["id"]) || !is_numeric($data["id"])) {
            return parent::go_error($event, -12);
        }
        
        $ownSellerId = View_UserLogin::getOperateSellerId($data);
        if (empty($ownSellerId)) {
            return parent::go_error($event, -2011);
        }  

        $View_BaseAttr = new View_BaseAttr();
        
        $id = $event->attrId = $data["id"];
        $chechkExistUse = $View_BaseAttr->chechkExistUse($event, $id, $ownSellerId);
        if ($chechkExistUse == 1) {
            unset($View_BaseAttr); 
            return parent::go_error($event, -2102);
        }   
        if ($chechkExistUse == 2) {
            unset($View_BaseAttr); 
            return parent::go_error($event, -2103);
        }            
        
        unset($View_BaseAttr);
     
        $event->base_attr_data = array(
            "id" => $id, 
            "seller_id" => $ownSellerId,
        ); 
        
        $event->base_attr_item_data = array(
            "attrId" => $id, 
            "sellerId" => $ownSellerId,
        );        
        
        $logSectionId = View_UserLogin::getOperateSectionId($data);
        $logUserId = View_UserLogin::getOperateUserId($data);        
        $nowTime = date('Y-m-d H:i:s');  
        
        BaseAttrOperate::setAddData($event, 3, $ownSellerId, $logSectionId, $logUserId, $nowTime);
/*       
var_dump("base_attr_data",$event->base_attr_data);  
var_dump("base_attr_item_data",$event->base_attr_item_data);  
var_dump("base_attr_operate_data",$event->base_attr_operate_data);  
return parent::go_error($event, -10000);
*/        
    }       

}
