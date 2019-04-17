<?php
namespace console\models\system;

use console\models\BaseModel;

class View_Error extends BaseModel {
    
    public function fetchError($event, $condition = "", $params = []) {
        
        $sql = "SELECT error_code,
                       error_type,
                       message
                FROM error_info
                $condition ";        
        
        $result = $this->query_SQL($sql, $event, null, $params);
        
        return $result;

    }
    
    public function fetchErrorOne($event){
        
        $data = &$event->RequestArgs;
        
        if (!isset($data["error_code"]) && !is_string($data["error_code"])) {
            return parent::go_error($event, -12);
        }    

        $condition = "WHERE error_code = :error_code";        

        $params = array(
            ":error_code" => $data["error_code"]
        );            

        $result = $this->fetchError($event, $condition, $params);

        $event->Postback($result);
    }
   
}
