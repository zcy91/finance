<?php
namespace console\models\system;

use console\models\BaseModel;
use console\models\system\View_Error;
use console\models\system\View_Module;

class List_Sys extends BaseModel {
    
    public function fetchSysInfo($event){
        
        $View_Error = new View_Error();
        $error = $View_Error->fetchError($event);
        unset($View_Error);
        
        
        $View_Module = new View_Module();
        $module = $View_Module->fetchModule($event);
        unset($View_Module);
        
        $result = array(
            "error" => &$error,
            "module" => &$module
        );
        
        $event->Postback($result);   
        
    }   

}
