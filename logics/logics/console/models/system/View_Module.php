<?php
namespace console\models\system;

use console\models\BaseModel;

class View_Module extends BaseModel {
    
    
    public function fetchModule($event, $condition = "", $params = []) {
        
        $sql = "SELECT module_id,
                       module_name,
                       controller_name,
                       action_name,
                       route
                FROM module_info
                $condition ";        
        
        $result = $this->query_SQL($sql, $event, null, $params);

        return $result;
    }
    
    public function fetchModuleOne($event){
        
        $data = &$event->RequestArgs;
        
        if (!isset($data["module_id"]) && !is_string($data["module_id"])) {
            return parent::go_error($event, -12);
        }    

        $condition = "WHERE module_id = :module_id";        

        $params = array(
            ":module_id" => $data["module_id"]
        );            

        $result = $this->fetchModule($event, $condition, $params);

        $event->Postback($result);
    }
 
}
