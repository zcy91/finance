<?php
namespace console\models\right;

use console\models\BaseModel;
use console\models\user\View_UserLogin;

class View_RightSection extends BaseModel {
    
    public function getOne($event, $sectionId, $sellerId){
        
        $one = 0;
        $isArray = is_array($sectionId) ? 1 : 0;
        if ((is_array($sectionId) && count($sectionId) == 1) || is_numeric($sectionId)) {
            $one = 1;
            $condition = " AND rs.id = :sectionId ";
            $sectionId = is_array($sectionId) ? $sectionId[0] : $sectionId;        
        } else {
            $sectionId = implode(",", $sectionId);
            $condition = " AND rs.id IN ($sectionId)";
        }
        
        $sql = "SELECT rs.id,
                       rs.dnames,
                       rs.seller_id,
                       rs.parentId,
                       rs.parentsPath,
                       rs.dLevel,
                       rs.sort
                FROM right_section AS rs
                WHERE rs.seller_id = :sellerId $condition ";
        
        $params = array(
            ":sellerId" => $sellerId
        );
        
        if ($one) {
            $params[":sectionId"] = $sectionId;
        }        
        
        $result = $this->query_SQL($sql, $event, null, $params);  
        
        if (!$isArray) {
        if (!empty($result)) {
            $result = $result[0];
        }
        }
        
        return $result;        
        
    }   
    
    public function chechName($event, $sellerId, $name, $id = 0){
        
        $conditions = "";
        $params = array(
            ":sellerId" => $sellerId,
            ":dnames" => $name
        );

        if (!empty($id) && is_numeric($id)) {
            $conditions .= "AND id != :id";
            $params[":id"] = $id;
        }        
        
        $sql = "SELECT rs.id,
                       rs.dnames,
                       rs.seller_id,
                       rs.parentId,
                       rs.parentsPath
                FROM right_section AS rs
                WHERE rs.seller_id = :sellerId AND rs.dnames = :dnames ";
        
        $result = $this->query_SQL($sql, $event, null, $params);  
        
        if (!empty($result)) {
            $result = $result[0];
        }
        
        return $result;        
        
    } 
    
    public function chechkExistChild($event, $sellerId, $sectionId){

        $sql = "SELECT rs.id,
                       rsu.sectionId
                FROM right_section AS rs LEFT OUTER JOIN
                     right_section AS rsc ON rs.seller_id = rsc.seller_id AND
                       rsc.parentsPath LIKE rs.parentsPath LEFT OUTER JOIN
                     right_section_user AS rsu ON rsc.seller_id = rsu.seller_id AND 
                       rsc.id = rsu.sectionId
                WHERE rs.seller_id = :sellerId AND rs.id = :sectionId
                LIMIT 2";
        

        $params = array(
            ":sellerId" => $sellerId,
            ":sectionId" => $sectionId
        ); 
        
        $result = $this->query_SQL($sql, $event, null, $params);  
        
        if (count($result) > 1) {
            $return = 1;
        } else if(empty($result)) {
            $return = 2;
        } else if($result[0]["sectionId"] != 0) {
            $return = 1;
        } else {
            $return = 0;
        }

        return $result;         
    }     
    
    public function getLeveSection($event, $sellerId, $leve) { 

        $sql = "SELECT rs.id,
                       rs.dnames,
                       rs.parentsPath,
                       rs.sort
                FROM right_section AS rs
                WHERE rs.seller_id = :sellerId And dLevel = :dLevel
                ORDER BY rs.sort";

        $params = array(
            ":sellerId" => $sellerId,
            ":dLevel" => $leve
        );
        
        $result = $this->query_SQL($sql, $event, null, $params);

        return $result;        
    }


    public function sectionList($event) {
        
        $data = &$event->RequestArgs;
        
        $ownSellerId = View_UserLogin::getOperateSellerId($data);
        if (empty($ownSellerId)) {
            return parent::go_error($event, -2011);
        } 
        
        $result = $rdata = [];
        
        $leve1Section = $this->getLeveSection($event, $ownSellerId, 1);
        foreach ($leve1Section as $leve1Item) {
            
            $ddata = array(
                "id" => $leve1Item["id"],
                "dnames" => $leve1Item["dnames"],
                "sort" => $leve1Item["sort"],
                "children" => []
            );

            $result[] = &$ddata;
            
            $rdata[$leve1Item["id"]] = array(
                "children" => [],
                "map" => &$ddata
            
            );

            unset($ddata);
        }
        
        $leve2Section = $this->getLeveSection($event, $ownSellerId, 2);       
        foreach ($leve2Section as $leve2Item) {
            
            $parentsPath = $leve2Item["parentsPath"];
            unset($leve2Item["parentsPath"]);
            $parents = explode("|", $parentsPath);
            
            $ddata = array(
                "id" => $leve2Item["id"],
                "dnames" => $leve2Item["dnames"],
                "sort" => $leve2Item["sort"],
                "children" => []
            );  
            
            $opRdata = &$rdata[$parents[1]];
            $opRdata["map"]["children"][] = &$ddata;
            
            $opRdata["children"][$parents[2]] = array(
                "children" => [],
                "map" => &$ddata
            );
            
            unset($opRdata);
            unset($ddata);
        }
        
        $leve3Section = $this->getLeveSection($event, $ownSellerId, 3);     
        foreach ($leve3Section as $leve3Item) {
            
            $parentsPath = $leve3Item["parentsPath"];
            unset($leve3Item["parentsPath"]);
            $parents = explode("|", $parentsPath);
            
            $ddata = array(
                "id" => $leve3Item["id"],
                "dnames" => $leve3Item["dnames"],
                "sort" => $leve3Item["sort"],
                "children" => []
            );  
            
            $opRdata = &$rdata[$parents[1]]["children"][$parents[2]];
            $opRdata["map"]["children"][] = &$ddata;
            
            $opRdata["children"][$parents[3]] = array(
                "children" => [],
                "map" => &$ddata
            );
            
            unset($opRdata);
            unset($ddata);
        }   
        
        $leve4Section = $this->getLeveSection($event, $ownSellerId, 4);
        foreach ($leve4Section as $leve4Item) {
            
            $parentsPath = $leve4Item["parentsPath"];
            unset($leve4Item["parentsPath"]);
            $parents = explode("|", $parentsPath);
            
            $ddata = array(
                "id" => $leve4Item["id"],
                "dnames" => $leve4Item["dnames"],
                "sort" => $leve4Item["sort"],
                "children" => []
            );  
            
            $opRdata = &$rdata[$parents[1]]["children"][$parents[2]]["children"][$parents[3]];
            $opRdata["map"]["children"][] = &$ddata;
            
            $opRdata["children"][$parents[4]] = array(
                "children" => [],
                "map" => &$ddata
            );
            
            unset($opRdata);
            unset($ddata);
        }  

        $leve5Section = $this->getLeveSection($event, $ownSellerId, 5);
        foreach ($leve5Section as $leve5Item) {
            
            $parentsPath = $leve5Item["parentsPath"];
            unset($leve5Item["parentsPath"]);
            $parents = explode("|", $parentsPath);
            
            $ddata = array(
                "id" => $leve5Item["id"],
                "dnames" => $leve5Item["dnames"],
                "sort" => $leve5Item["sort"]
            );  
            
            $opRdata = &$rdata[$parents[1]]["children"][$parents[2]]["children"][$parents[3]]["children"][$parents[4]];
            $opRdata["map"]["children"][] = &$ddata;
            
            $opRdata["children"][$parents[5]] = array(
                "map" => &$ddata
            );
            
            unset($opRdata);
            unset($ddata);
        }         

        $event->Postback($result);
    }

   
}
