<?php
namespace console\events\usercenter;

use console\events\BaseEvent;

class CustomerEvent extends BaseEvent {
    
    const OwnType = 3;

    public $userId;
    public $bindAccount;
    
    public $user_login_data;
    public $user_profile_data;
    public $user_customer_data;
    public $user_role_data;
    public $user_operate_data;
  
    public function set_user_id($userId){
        $this->userId = $userId;
    }
    
}

?>