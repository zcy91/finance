<?php
namespace console\models\base;

use console\models\BaseModel;
use console\models\user\View_UserLogin;

class View_BaseAttr extends BaseModel {
    
    public function getOne($event, $attrId, $sellerId){
        
        $sql = "SELECT ba.id,
                       ba.dnames,
                       ba.genre,
                       ba.required,
                       ba.imageCount,
                       ba.sort,
                       ba.values,
                       ba.seller_id
                FROM base_attr AS ba
                WHERE ba.seller_id = :sellerId AND ba.id = :sectionId ";
        
        $params = array(
            ":sellerId" => $sellerId,
            ":sectionId" => $attrId
        );
        
        $result = $this->query_SQL($sql, $event, null, $params);  
        
        if (!empty($result)) {
            $result = $result[0];
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
        
        $sql = "SELECT ba.id,
                       ba.dnames,
                       ba.seller_id,
                       ba.genre,
                       ba.sort
                FROM base_attr AS ba
                WHERE ba.seller_id = :sellerId AND ba.dnames = :dnames ";
        
        $result = $this->query_SQL($sql, $event, null, $params);  
        
        if (!empty($result)) {
            $result = $result[0];
        }
        
        return $result;        
        
    }
    
    public function chechkExistUse($event, $sellerId, $attrId){

        $sql = "SELECT ba.id,
                       bpa.attrId
                FROM base_attr AS ba LEFT OUTER JOIN
                     base_product_attr AS bpa ON ba.id = bpa.attrId
                WHERE ba.seller_id = :sellerId AND ba.id = :attrId
                LIMIT 1";
        

        $params = array(
            ":sellerId" => $sellerId,
            ":attrId" => $attrId
        ); 
        
        $result = $this->query_SQL($sql, $event, null, $params);  
        
        if(empty($result)) {
            $return = 1;
        } else if($result[0]["attrId"] != 0) {
            $return = 2;
        } else {
            $return = 0;
        }

        return $result;         
    }    
    
    public function getAttrItems($event, $attrId, $sellerId){
        
        $sql = "SELECT bai.id,
                       bai.dnames,
                       bai.sort
                FROM base_attr_item AS bai
                WHERE bai.seller_id = :sellerId AND bai.attrId = :attrId 
                ORDER BY bai.sort ";
        
        $params = array(
            ":sellerId" => $sellerId,
            ":attrId" => $attrId
        );
        
        $result = $this->query_SQL($sql, $event, null, $params);  

        return $result;        
        
    }     
    
    public function getAllAttr($event, $ispage, $condition, $params, $limit) { 

        $sql = "SELECT " . ($ispage ? " sql_calc_found_rows " : "") . "
                       ba.id,
                       ba.dnames,
                       ba.genre,
                       ba.required,
                       ba.imageCount,
                       ba.sort
                FROM base_attr AS ba
                WHERE ba.seller_id = :sellerId
                      $condition
                ORDER BY ba.sort";
        
        $result = $this->query_SQL($sql, $event, $limit, $params);

        return $result;        
    }    
    
}
