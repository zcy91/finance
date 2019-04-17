<?php
namespace console\models\system;

use console\models\BaseModel;

class View_SystemAlgorithm extends BaseModel {
    
    public function fetchModule($event, $algorithmId) {
        
        $sql = "SELECT id,
                       names,
                       classNames
                FROM system_algorithm
                WHERE id = :id "; 
        
        $params = array(
            ":id" => $algorithmId
        );

        $result = $this->query_SQL($sql, $event, null, $params);

        if (!empty($result)) {
            $result = $result[0];
        }
        
        return $result;
    }      

}
