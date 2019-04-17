<?php
namespace console\models\user;

use console\models\BaseModel;

class View_UserRole extends BaseModel {
    
    public function getDefaultRole($event, $userId) {    
 
        $sql = "SELECT ur.roleId,
                       sr.dnames AS roleNames,
                       ur.defaulte
                FROM user_role AS ur INNER JOIN
                     system_role AS sr ON ur.roleId = sr.id
                WHERE useId = :userId 
                ORDER BY ur.defaulte DESC ";
        
        $params = array(
            ":userId" => $userId
        );
      
        $result = $this->query_SQL($sql, $event, null, $params); 
        
        return $result;
    }      

    public function getOneUser($event, $userId, $roleId){
        
        $sql = "SELECT id,
                       defaulte
                FROM user_role AS ur
                WHERE ur.useId = :userId AND ur.roleId = :roleId ";
        
        $params = array(
            ":userId" => $userId, 
            ":roleId" => $roleId, 
        );
        
        $result = $this->query_SQL($sql, $event, null, $params);  
        
        if (!empty($result)) {
            $result = $result[0];
        }
        
        return $result;        
        
    }    

}
