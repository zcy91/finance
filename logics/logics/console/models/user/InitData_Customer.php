<?php
namespace console\models\user;

use console\models\BaseModel;
use console\models\user\UserLogin;
use console\models\user\UserProfile;
use console\models\user\UserCustomer;
use console\models\user\UserOperate;
use console\models\user\View_UserCustomer;
use console\models\user\View_UserLogin;
use console\models\user\View_UserSeller;
use console\models\user\View_UserRole;

class InitData_Customer extends BaseModel {

    public function customerAdd($event){
        
        $data = &$event->RequestArgs;

        if (empty($data)) {
            return parent::go_error($event, -12);
        }
        
        if (!isset($data["mobile"]) || empty($data["mobile"]) || !is_string($data["mobile"]) ||
            !isset($data["openId"]) || empty($data["openId"]) || !is_string($data["openId"])) {
            return parent::go_error($event, -2010);
        }
        $mobile = $data["mobile"];

        $ownSellerId = View_UserLogin::getOperateSellerId($data);
        if (empty($ownSellerId)) {
            return parent::go_error($event, -2011);
        }        
        
        $logSectionId = View_UserLogin::getOperateSectionId($data);
        $logUserId = View_UserLogin::getOperateUserId($data);
        
        $View_UserLogin = new View_UserLogin();

        $checkMobile = $View_UserLogin->checkRepeat($event, 2, $mobile);
        if (!empty($checkMobile)) {
            unset($View_UserLogin);
            return parent::go_error($event, -2013);
        }

        if (empty($data["account"])) {
            $data["account"] = $mobile;
        }
        
        if (isset($data["account"]) && !empty($data["account"]) && is_string($data["account"])) {
            $names = $data["account"];
            $checkNames = $View_UserLogin->checkRepeat($event, 1, $names);
            if (!empty($checkNames)) {
                unset($View_UserLogin);
                return parent::go_error($event, -2014);
            }
        }        
        
        if (empty($data["dnames"])) {
            $data["dnames"] = $data["account"];
        }        
        
        $email = "";
        if (isset($data["email"]) && !empty($data["email"]) && is_string($data["email"])) {
            $email = $data["email"];
            $checkEmail = $View_UserLogin->checkRepeat($event, 3, $email);
            if (!empty($checkEmail)) {
                unset($View_UserLogin);
                return parent::go_error($event, -2015);
            }
        } 
        $data["email"] = $email;
        
        $openId = "";
        if (isset($data["openId"]) && !empty($data["openId"]) && is_string($data["openId"])) {
            $openId = $data["openId"];
            $checkOpenId = $View_UserLogin->checkRepeat($event, 4, $openId);
            if (!empty($checkOpenId)) {
                unset($View_UserLogin);
                return parent::go_error($event, -2015);
            }
        } 
                
        unset($View_UserLogin);

        $nowTime = date('Y-m-d H:i:s');

        UserLogin::setAddData($event, $data, $nowTime);
        UserProfile::setAddData($event, $data, $nowTime);
        UserCustomer::setAddData($event, $ownSellerId, $logUserId, 0, $nowTime);
        UserRole::setAddData($event, 3);
        
        $event->user_operate_data = array(
            "seller_id" => 0,
            "userId" => &$event->userId,
            "operate" => 1,
            "sectionId" => 0,
            "operateUid" => &$event->userId,
            "operateTime" => $nowTime              
        );        
        
        $event->bindAccount = 1;
        
/*        
var_dump("user_login_data",$event->user_login_data);  
var_dump("user_profile_data",$event->user_profile_data);  
var_dump("user_customer_data",$event->user_customer_data);  
var_dump("user_role_data",$event->user_role_data);  
var_dump("user_operate_data",$event->user_operate_data);  
return parent::go_error($event, -10000);
*/
    }
    
    public function customerEdit($event){
        
        $data = &$event->RequestArgs;
    
        if (empty($data) || !isset($data["id"]) || empty($data["id"]) || !is_numeric($data["id"])) {
            return parent::go_error($event, -12);
        }
        
        $userId = $event->userId = $data["id"];
        
        $View_UserLogin = new View_UserLogin();
        $User = $View_UserLogin->getOne($event, $userId);
        if (empty($User)) {
            unset($View_UserLogin);
            return parent::go_error($event, -2025);
        }

        $ownSellerId = View_UserLogin::getOperateSellerId($data);
        if (empty($ownSellerId)) {
            return parent::go_error($event, -2011);
        }        
        $logUserId = View_UserLogin::getOperateUserId($data);
        
        if (!empty($data["mobile"]) && is_string($data["mobile"]) && $User["mobile"] != $data["mobile"]) {
            $mobile = $data["mobile"];
            $checkMobile = $View_UserLogin->checkRepeat($event, 2, $mobile, $userId);
            if (!empty($checkMobile)) {
                unset($View_UserLogin);
                return parent::go_error($event, -2013);
            }            
        } 

        if (!empty($data["account"]) && is_string($data["account"]) && $User["account"] != $data["account"]) {
            $names = $data["account"];
            $checkNames = $View_UserLogin->checkRepeat($event, 1, $names, $userId);
            if (!empty($checkNames)) {
                unset($View_UserLogin);
                return parent::go_error($event, -2014);
            }
        }

        if (!empty($data["email"]) && is_string($data["email"])  && $User["email"] != $data["email"]) {
            $email = $data["email"];
            $checkEmail = $View_UserLogin->checkRepeat($event, 3, $email, $userId);
            if (!empty($checkEmail)) {
                unset($View_UserLogin);
                return parent::go_error($event, -2015);
            }
        } 
        
        unset($View_UserLogin);

        $nowTime = date('Y-m-d H:i:s');
        UserLogin::setEditData($event, $userId, $data, $User);
        $staffId = $User["staffId"];
        UserProfile::setEditData($event, $staffId, $data, $User);

        UserOperate::setAddData($event, 2, 0, 0, $logUserId, $nowTime);
/*            
var_dump("user_login_data",$event->user_login_data);  
var_dump("user_profile_data",$event->user_profile_data);  
var_dump("right_staff_role_add",$event->right_staff_role_add);  
var_dump("right_staff_role_del",$event->right_staff_role_del);   
var_dump("user_operate_data",$event->user_operate_data);  
return parent::go_error($event, -10000); 
*/
    }

    public function customerEnable($event){
        
        $data = &$event->RequestArgs;
    
        if (empty($data) || !isset($data["id"]) || empty($data["id"]) || !is_numeric($data["id"])) {
            return parent::go_error($event, -12);
        }

        $userId = $event->userId = $data["id"];
       
        $View_UserLogin = new View_UserLogin();
        $User = $View_UserLogin->getOne($event, $userId);
        unset($View_UserLogin);
        if (empty($User)) {
            return parent::go_error($event, -2025);
        }

        $ownSellerId = View_UserLogin::getOperateSellerId($data);
        if (empty($ownSellerId)) {
            return parent::go_error($event, -2011);
        }        
        $logSectionId = View_UserLogin::getOperateSectionId($data);
        $logUserId = View_UserLogin::getOperateUserId($data);        
        $nowTime = date('Y-m-d H:i:s');
        $enable = (isset($data["stop"]) && is_numeric($data["stop"]) && in_array($data["stop"], [1,2])) ? $data["stop"] : 1;

        //1:停用 2:启用
        $View_UserCustomer = new View_UserCustomer();
        $UserCustomer = $View_UserCustomer->getOneSeller($event, $userId, $ownSellerId);
        unset($View_UserCustomer);
        $staffId = $User["staffId"];
        
        if ($enable == 1) {
            
            if ($User["locked"] == 0) {
                $event->user_login_data = array(
                    "id" => $userId,
                    "locked" => 1,
                    "lockedTime" => $nowTime,
                );                
            }            
            
            if ($User["dstatus"] == 1) {
                $event->user_profile_data = array(
                    "id" => $staffId,
                    "dstatus" => 0,
                );                
            }
            
            if (!empty($UserCustomer)) {
                if ($UserCustomer["deleted"] == 0) {
                    UserCustomer::setEditData($event, $UserCustomer["id"], 1);
                }
            } else {
                UserCustomer::setAddData($event, $ownSellerId, $logUserId, 1, $nowTime);
            }            

            $View_UserSeller = new View_UserSeller();
            $otherSeller = $View_UserSeller->getExcpSeller($event, $userId, $ownSellerId); 
            unset($View_UserSeller);
            if (empty($otherSeller)) {
                $event->user_role_data = array(
                    "useId" => $userId,
                    "roleId" => 3,
                    "operate" => 0
                );
            }
            
            UserOperate::setAddData($event, 4, $ownSellerId, $logSectionId, $logUserId, $nowTime);
        } else {
           
            if ($User["locked"] == 1) {
                $event->user_login_data = array(
                    "id" => $userId,
                    "locked" => 0,
                    "lockedTime" => $nowTime,
                );                
            }              
           
            if ($User["dstatus"] == 0) {
                $event->user_profile_data = array(
                    "id" => $staffId,
                    "dstatus" => 1,
                );                
            } 
            
            if (!empty($UserCustomer)) {
                if ($UserCustomer["deleted"] == 1) {
                    UserCustomer::setEditData($event, $UserCustomer["id"], 0);
                }
            } else {
               UserCustomer::setAddData($event, $ownSellerId, $logUserId, 0, $nowTime);
            }            
            
            $View_UserRole = new View_UserRole();
            $Role = $View_UserRole->getOneUser($event, $userId, 3);
            unset($View_UserRole);
            if (empty($otherSeller)) {
                $event->user_role_data = array(
                    "useId" => $userId,
                    "roleId" => 3,
                    "operate" => 1
                );
            } 
            
            UserOperate::setAddData($event, 3, $ownSellerId, $logSectionId, $logUserId, $nowTime);
        }
/*           
var_dump("user_login_data",$event->user_login_data);  
var_dump("user_profile_data",$event->user_profile_data);  
var_dump("user_seller_relation_data",$event->user_seller_relation_data);  
var_dump("user_role_data",$event->user_role_data);   
var_dump("user_operate_data",$event->user_operate_data);  
return parent::go_error($event, -10000); 
*/      
    }    

}
