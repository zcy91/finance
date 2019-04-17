<?php
namespace console\models\right;

use console\models\BaseModel;
use console\models\right\RightSection;
use console\models\user\View_UserLogin;
use console\models\right\View_RightSection;

class InitData_RightSection extends BaseModel {
    
    public function sectionAdd($event){
        
        $data = &$event->RequestArgs;
 
        if (!isset($data["dnames"]) || empty($data["dnames"]) || 
            !isset($data["parentId"])) {        
            return $this->go_error($event, -12);
        }

        $ownSellerId = View_UserLogin::getOperateSellerId($data);
        if (empty($ownSellerId)) {
            return parent::go_error($event, -2011);
        }        
        
        $dnames = $data["dnames"];
        $View_RightSection = new View_RightSection();
        $Section = $View_RightSection->chechName($event, $ownSellerId, $dnames);
        if (!empty($Section)) {
            unset($View_RightSection); 
            return parent::go_error($event, -2501);
        }
        
        $parentId = $data["parentId"];
        if ($parentId == 0) {
            $dLevel = 1;
            $event->parentsPath = "0|";
        } else {
            $ParentSection = $View_RightSection->getOne($event, $parentId, $ownSellerId);
            if (empty($ParentSection)) {
                unset($View_RightSection); 
                return parent::go_error($event, -2052);
            }
            $dLevel = $ParentSection["dLevel"] + 1;
            if ($dLevel > 5) {
                return parent::go_error($event, -2055);
            }
            $parentsPath = $ParentSection["parentsPath"];
            $event->parentsPath = $parentsPath;
        }  
        
        unset($View_RightSection); 
        
        $logSectionId = View_UserLogin::getOperateSectionId($data);
        $logUserId = View_UserLogin::getOperateUserId($data);        
        $nowTime = date('Y-m-d H:i:s');
        
        $event->right_section_data = array(
            "id" => &$event->sectionId,
            "dnames" => $dnames,
            "seller_id" => $ownSellerId,
            "parentId" => $parentId,
            "parentsPath" => &$event->selfParentsPath,
            "dLevel" => $dLevel,
            "sort" => (isset($data["sort"]) && is_numeric($data["sort"])) ? $data["sort"] : 99,
            "creatTime" => $nowTime,
            "nowTime" => $nowTime
        );
        
        RightSectionOperate::setAddData($event, 1, $ownSellerId, $logSectionId, $logUserId, $nowTime);
/*        
var_dump("right_section_data",$event->right_section_data); 
var_dump("right_section_operate_data",$event->right_section_operate_data); 
return $this->go_error($event, -10000);
*/
    }
    
    public function sectionEdit($event){
        
        $data = &$event->RequestArgs;
    
        if (empty($data) || !isset($data["id"]) || empty($data["id"]) || !is_numeric($data["id"])) {
            return parent::go_error($event, -12);
        }
        
        if ((!isset($data["dnames"]) || empty($data["dnames"])) &&
             (!isset($data["sort"]) || empty($data["sort"]))) {
            return;
        }
        
        $ownSellerId = View_UserLogin::getOperateSellerId($data);
        if (empty($ownSellerId)) {
            return parent::go_error($event, -2011);
        }    
        
        $View_RightSection = new View_RightSection();
        
        $id = $event->sectionId = $data["id"];
        $Section = $View_RightSection->getOne($event, $id, $ownSellerId);
        if (empty($Section)) {
            unset($View_RightSection); 
            return parent::go_error($event, -2053);
        }
        
        $updateSection = [];
        if (isset($data["dnames"]) && is_string($data["dnames"]) && !empty($data["dnames"]) && $Section["dnames"] != $data["dnames"]) {
        $dnames = $data["dnames"];
        $chechName = $View_RightSection->chechName($event, $ownSellerId, $dnames, $id);
        if (!empty($chechName)) {
            unset($View_RightSection); 
            return parent::go_error($event, -2501);
        }        
            $updateSection["dnames"] = $dnames;
        }
       
        unset($View_RightSection);
        
        if (isset($data["sort"]) && is_string($data["sort"]) && !empty($data["sort"]) && $Section["sort"] != $data["sort"]) {
            $updateSection["sort"] = $data["sort"];
        }
        
        if (!empty($updateSection)) {
            
            $logSectionId = View_UserLogin::getOperateSectionId($data);
            $logUserId = View_UserLogin::getOperateUserId($data);             
            $nowTime = date('Y-m-d H:i:s');  
            
            $event->right_section_data = array(
                "id" => $id,
                "nowTime" => $nowTime 
            ); 

            $event->right_section_data = array_merge($event->right_section_data, $updateSection);
            RightSectionOperate::setAddData($event, 2, $ownSellerId, $logSectionId, $logUserId, $nowTime);            
        }
/*
var_dump("right_section_data",$event->right_section_data); 
var_dump("right_section_operate_data",$event->right_section_operate_data); 
return $this->go_error($event, -10000); 
*/
        
    }

    public function sectionDelete($event){
        
        $data = &$event->RequestArgs;
        
        if (empty($data) || !isset($data["id"]) || empty($data["id"]) || !is_numeric($data["id"])) {
            return parent::go_error($event, -12);
        }
        
        $ownSellerId = View_UserLogin::getOperateSellerId($data);
        if (empty($ownSellerId)) {
            return parent::go_error($event, -2011);
        }  

        $View_RightSection = new View_RightSection();
        
        $id = $event->sectionId = $data["id"];
        $chechkExistChild = $View_RightSection->chechkExistChild($event, $id, $ownSellerId);
        if ($chechkExistChild == 1) {
            unset($View_RightSection); 
            return parent::go_error($event, -2054);
        }   
        if ($chechkExistChild == 2) {
            unset($View_RightSection); 
            return parent::go_error($event, -2053);
        }            
        
        unset($View_RightSection);
     
        $event->right_section_data = array(
            "id" => $id, 
            "sellerId" => $ownSellerId,
        ); 
        
        $logSectionId = View_UserLogin::getOperateSectionId($data);
        $logUserId = View_UserLogin::getOperateUserId($data);        
        $nowTime = date('Y-m-d H:i:s');  
        
        RightSectionOperate::setAddData($event, 3, $ownSellerId, $logSectionId, $logUserId, $nowTime);
/*        
var_dump("right_section_data",$event->right_section_data); 
var_dump("right_section_operate_data",$event->right_section_operate_data); 
return $this->go_error($event, -10000); 
*/
    }    
    
    

}
