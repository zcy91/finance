<?php
namespace console\events\user;

use console\events\BaseEvent;

class UserEvent extends BaseEvent {

    public $userId;
    
    public $user_login_data;
    public $user_profile_data;
    public $user_seller_relation_data;
    public $right_section_user_data;
    public $right_staff_role_data;
    public $right_post_user_data;
    public $user_role_data;
    public $user_operate_data;
    
    public $right_staff_role_add;
    public $right_staff_role_del;  
    public $right_section_user_add;
    public $right_section_user_del;  
    public $right_post_user_add;
    public $right_post_user_del;  
    public $bindAccount = 0;  
    
    public function set_user_id($userId){
        $this->userId = $userId;
    }
    

}

?>