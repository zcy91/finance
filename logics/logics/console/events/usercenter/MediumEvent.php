<?php
namespace console\events\usercenter;

use console\events\BaseEvent;

class MediumEvent extends BaseEvent {
    
    const OwnType = 2;    
    
    public $userId;
    
    public $user_login_data;
    public $user_profile_data;
    public $user_medium_data;
    public $user_role_data;
    public $user_operate_data;
  
    public function set_user_id($userId){
        $this->userId = $userId;
    }
    

}

?>