<?php
namespace console\models\user;

use console\models\BaseModel;
use console\common\StaticFunction;
use console\models\user\UserLogin;
use console\models\user\UserProfile;
use console\models\user\UserSellerRelation;
use console\models\user\UserRole;
use console\models\right\RightPostUser;
use console\models\right\RightStaffRole;
use console\models\right\RightSectionUser;
use console\models\user\UserOperate;
use console\models\user\View_UserLogin;
use console\models\user\View_UserSeller;
use console\models\user\View_UserRole;
use console\models\right\View_RightSection;
use console\models\right\View_RightPost;
use console\models\right\View_RightStaffRole;
use console\models\right\View_RightSectionUser;
use console\models\right\View_RightPostUser;


class InitData_User extends BaseModel {
    
    public function handleOneToMo($value){
        
        $newValue = [];
        if (is_numeric($value)) {
            $newValue[] = $value;
        } elseif (is_array($value)) {
          foreach ($value as $item) {
                if (is_numeric($item)) {
                    $newValue[] = $item;
                }
            }                
        }    
        
        return $newValue;
    } 

    public function concatSection($event, $sellerId, $sectionIds){

        $View_RightSection = new View_RightSection();
        $Sections = $View_RightSection->getOne($event, $sectionIds, $sellerId);
        unset($View_RightSection);
        
        $newSectionIds = array_column($Sections,"id");
        $newSectionNmes = array_column($Sections,"dnames");
        
        return array(
            "ids" => $newSectionIds,
            "names" => $newSectionNmes
        );
    } 
    
    public function concatRole($roleIds){
        
        $roles = [];
        foreach ($roleIds as $roleId) {
            switch ($roleId){
                case 4:
                    $roles[] = "业务员";
                    break;
                case 5:
                    $roles[] = "跟单员";
                    break;
                case 6:
                    $roles[] = "普通员工";
            }
        }
        
        return implode("|", $roles);
    }     
    
    public function concatPost($event, $sellerId, $postIds){

        $View_RightPost = new View_RightPost();
        $posts = $View_RightPost->getOne($event, $postIds, $sellerId);
        unset($View_RightPost);
        
        $newPostIds = array_column($posts,"id");
        $newPostNmes = array_column($posts,"dnames");
        
        return array(
            "ids" => $newPostIds,
            "names" => $newPostNmes
        );
    }     

    public function staffAdd($event){
        
        $data = &$event->RequestArgs;
   
        if (empty($data)) {
            return parent::go_error($event, -12);
        }
        
        if (!isset($data["mobile"]) || empty($data["mobile"]) || !is_string($data["mobile"]) ||
            !isset($data["roleId"]) || empty($data["roleId"]) ||
            !isset($data["sectionId"]) || empty($data["sectionId"])) {
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
        
        if (isset($data["account"]) && !empty($data["account"]) && is_string($data["account"])) {
            $names = $data["account"];
            $checkNames = $View_UserLogin->checkRepeat($event, 1, $names);
            if (!empty($checkNames)) {
                unset($View_UserLogin);
                return parent::go_error($event, -2014);
            }
        }
        
        if (empty($data["account"])) {
            $names = $mobile;
        }
        $data["account"] = $names;
        
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
                
        unset($View_UserLogin);
        
        if (!isset($data["password"]) || empty($data["password"]) || !is_string($data["password"])) {
            return parent::go_error($event, -2016);
        }
        
        $passwd = $data["password"];
        $salt = StaticFunction::randomkeys(8);
        $data["salt"] = $salt;
        $passwd = StaticFunction::resetPwd($passwd, $salt);
        $data["password"] = $passwd;
         
        if (empty($data["roleId"])) {
            return parent::go_error($event, -2012);
        }

        $nowTime = date('Y-m-d H:i:s');
        $sectionValues = $roleValues = "";
        UserLogin::setAddData($event, $data, $nowTime);
        UserProfile::setAddData($event, $data, $nowTime);
        UserSellerRelation::setAddData($event, $ownSellerId, 0, $sectionValues, $roleValues, $nowTime);
        
        $Roles = $this->handleOneToMo($data["roleId"]);
        $roleValues = $this->concatRole($Roles);
        $Roles = array_intersect([4,5,6], $Roles);
        $doSu = RightStaffRole::setAddData($event, $ownSellerId, $Roles);
        if ($doSu) {
            return parent::go_error($event, -2017);
        }
        UserRole::setAddData($event, 1);
        
        $Sections = $this->handleOneToMo($data["sectionId"]);
        $resultSections = $this->concatSection($event, $ownSellerId, $Sections);
        RightSectionUser::setAddData($event, $ownSellerId, $resultSections["ids"]);
        $sectionValues = implode("|", $resultSections["names"]);
        UserOperate::setAddData($event, 1, $ownSellerId, $logSectionId, $logUserId, $nowTime);
/*        
var_dump("user_login_data",$event->user_login_data);  
var_dump("user_profile_data",$event->user_profile_data);  
var_dump("user_seller_relation_data",$event->user_seller_relation_data);  
var_dump("right_staff_role_data",$event->right_staff_role_data);  
var_dump("right_section_user_data",$event->right_section_user_data); 
var_dump("user_role_data",$event->user_role_data);  
var_dump("user_operate_data",$event->user_operate_data);  
return parent::go_error($event, -10000);
*/
    }

    public function staffEdit($event){
        
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
        $logSectionId = View_UserLogin::getOperateSectionId($data);
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
        
        if (!empty($data["passwd"]) && is_string($data["passwd"])) {
            $passwd = $data["passwd"];
            $salt = StaticFunction::randomkeys(8);
            $data["salt"] = $salt;
            $passwd = StaticFunction::resetPwd($passwd, $salt);
            $data["passwd"] = $passwd;            
        }

        $nowTime = date('Y-m-d H:i:s');
        UserLogin::setEditData($event, $userId, $data, $User);
        $staffId = $User["staffId"];
        UserProfile::setEditData($event, $staffId, $data, $User);
        
        if (isset($data["role"]) && !empty($data["role"])) {
            $Roles = $this->handleOneToMo($data["role"]);
            $roleValues = $this->concatRole($Roles);
            $Roles = array_intersect([4,5,6], $Roles);            
            if (is_array($Roles)) {
                $View_RightStaffRole = new View_RightStaffRole();
                $staffSellerRole = $View_RightStaffRole->getOneSeller($event, $userId, $ownSellerId);              
                unset($View_RightStaffRole);
                $oldRoles = array_column($staffSellerRole, "roleId");
                RightStaffRole::setEditData($event, $userId, $ownSellerId, $Roles, $oldRoles);
            }
        }
        
        if (isset($data["sectionId"]) && !empty($data["sectionId"])) {
            $Sections = $this->handleOneToMo($data["sectionId"]);
            $resultSections = $this->concatSection($event, $ownSellerId, $Sections);
            $sectionValues = implode("|", $resultSections["names"]);            
            
            $View_RightSectionUser = new View_RightSectionUser();
            $staffSections = $View_RightSectionUser->getOneSeller($event, $userId, $ownSellerId);
            unset($View_RightSectionUser);
            $oldSections = array_column($staffSections, "sectionId");
            RightSectionUser::setEditData($event, $userId, $ownSellerId, $resultSections["ids"], $oldSections);            
        }

        if ((isset($roleValues) && !empty($roleValues)) ||
            (isset($sectionValues) && !empty($sectionValues))) {
            $event->user_seller_relation_data = array(   
                "seller_id" => $ownSellerId,
                "userId" => &$event->userId,                
                "nowTime" => $nowTime  
            );                

            if (isset($roleValues) && !empty($roleValues)) {
                $event->user_seller_relation_data["sections"] = $roleValues;
            }
            
            if (isset($sectionValues) && !empty($sectionValues)) {
                $event->user_seller_relation_data["roles"] = $sectionValues;
            }            
        }
        
        UserOperate::setAddData($event, 2, $ownSellerId, $logSectionId, $logUserId, $nowTime);
/*            
var_dump("user_login_data",$event->user_login_data);  
var_dump("user_profile_data",$event->user_profile_data);  
var_dump("right_staff_role_add",$event->right_staff_role_add);  
var_dump("right_staff_role_del",$event->right_staff_role_del);   
var_dump("user_operate_data",$event->user_operate_data);  
return parent::go_error($event, -10000); 
*/
    }

    public function staffEnable($event){
        
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
        $View_UserSeller = new View_UserSeller();
        $ownSeller = $View_UserSeller->getOneSeller($event, $userId, $ownSellerId);
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

            if (!empty($ownSeller)) {
                if ($ownSeller["deleted"] == 0) {
                    UserSellerRelation::setEditData($event, $ownSeller["id"], 1, $nowTime);
                }
            } else {
                $event->user_seller_relation_data = array(   
                    "superd" => 0,
                    "defaulte" => 0,
                    "deleted" => 1,
                    "seller_id" => $ownSellerId,
                    "userId" => &$event->userId,
                    "nowTime" => $nowTime,
                    "operate" => 1
                ); 
            }

            $otherSeller = $View_UserSeller->getExcpSeller($event, $userId, $ownSellerId); 
            if (empty($otherSeller)) {
                $event->user_role_data = array(
                    "useId" => $userId,
                    "roleId" => 1,
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
            
            if (!empty($ownSeller)) {
                if ($ownSeller["deleted"] == 1) {
                    UserSellerRelation::setEditData($event, $ownSeller["id"], 0, $nowTime);
                }
            } else {
                $event->user_seller_relation_data = array(   
                    "superd" => 0,
                    "defaulte" => 0,
                    "deleted" => 0,
                    "seller_id" => $ownSellerId,
                    "userId" => &$event->userId,
                    "nowTime" => $nowTime,
                    "operate" => 1
                ); 
            }
           
            $View_UserRole = new View_UserRole();
            $Role = $View_UserRole->getOneUser($event, $userId, 1);
            unset($View_UserRole);
            if (empty($Role)) {
                $event->user_role_data = array(
                    "useId" => $userId,
                    "roleId" => 1,
                    "operate" => 1
                );
            } 
            
            UserOperate::setAddData($event, 3, $ownSellerId, $logSectionId, $logUserId, $nowTime);
        }
        
        unset($View_UserSeller);
/*           
var_dump("user_login_data",$event->user_login_data);  
var_dump("user_profile_data",$event->user_profile_data);  
var_dump("user_seller_relation_data",$event->user_seller_relation_data);  
var_dump("user_role_data",$event->user_role_data);   
var_dump("user_operate_data",$event->user_operate_data);  
return parent::go_error($event, -10000); 
*/      
    }    
    
    public function setSection($event){
        
        $data = &$event->RequestArgs;
    
        if (empty($data) || 
            !isset($data["id"]) || empty($data["id"]) || !is_numeric($data["id"]) ||
            !isset($data["sectionId"]) || empty($data["sectionId"])) {
            return parent::go_error($event, -12);
        }   
        
        $userId = $event->userId = $data["id"];
        $ownSellerId = View_UserLogin::getOperateSellerId($data);

        if (empty($ownSellerId)) {
            return parent::go_error($event, -2011);
        }   

        $logSectionId = View_UserLogin::getOperateSectionId($event, $data);
        $logUserId = View_UserLogin::getOperateUserId($event, $data);        
        $nowTime = date('Y-m-d H:i:s');  
        
        $newSections = [];
        $Sections = $data["sectionId"];
        if (is_numeric($Sections)) {
            $newSections[] = $Sections;
        } elseif (is_array($Sections)) {
          foreach ($Sections as $sectionItem) {
                if (is_numeric($sectionItem)) {
                    $newSections[] = $sectionItem;
                }
            }                
        }

        if (is_array($newSections)) {
            $View_RightSectionUser = new View_RightSectionUser();
            $staffSections = $View_RightSectionUser->getOneSeller($event, $userId, $ownSellerId);
            unset($View_RightSectionUser);
            $oldSections = array_column($staffSections, "sectionId");
            RightSectionUser::setEditData($event, $userId, $ownSellerId, $newSections, $oldSections);
        }  
        
        UserOperate::setAddData($event, 5, $ownSellerId, $logSectionId, $logUserId, $nowTime);
        
    }
    
    public function setPost($event){
        
        $data = &$event->RequestArgs;
    
        if (empty($data) || 
            !isset($data["id"]) || empty($data["id"]) || !is_numeric($data["id"]) ||
            !isset($data["postId"]) || empty($data["postId"])) {
            return parent::go_error($event, -12);
        }   
        
        $userId = $event->userId = $data["id"];
        $ownSellerId = View_UserLogin::getOperateSellerId($data);

        if (empty($ownSellerId)) {
            return parent::go_error($event, -2011);
        }   

        $logSectionId = View_UserLogin::getOperateSectionId($data);
        $logUserId = View_UserLogin::getOperateUserId($data);        
        $nowTime = date('Y-m-d H:i:s');  

        $Posts = $this->handleOneToMo($data["postId"]);
        $resultPosts = $this->concatPost($event, $ownSellerId, $Posts);
        $postValues = implode("|", $resultPosts["names"]);            
        $postIdValues = implode("|", $resultPosts["ids"]);
                    
        $newPosts = $resultPosts["ids"];        
        if (is_array($newPosts)) {
            $View_RightPostUser = new View_RightPostUser();
            $staffPosts = $View_RightPostUser->getOneSeller($event, $userId, $ownSellerId);
            unset($View_RightPostUser);
            $oldPosts = array_column($staffPosts, "postId");
            RightPostUser::setEditData($event, $userId, $ownSellerId, $resultPosts["ids"], $oldPosts);
            $event->user_seller_relation_data = array(   
                "seller_id" => $ownSellerId,
                "userId" => &$event->userId,    
                "postId" => $postIdValues,  
                "posts" => $postValues,
                "nowTime" => $nowTime  
            );           
        }      
        
        UserOperate::setAddData($event, 6, $ownSellerId, $logSectionId, $logUserId, $nowTime);
/*          
var_dump("right_post_user_add",$event->right_post_user_add);  
var_dump("right_post_user_del",$event->right_post_user_del);  
var_dump("user_seller_relation_data",$event->user_seller_relation_data);  
var_dump("user_operate_data",$event->user_operate_data);  
return parent::go_error($event, -10000); 
*/
    }    

    public function bindAccount($event){
        $data = &$event->RequestArgs;
        
        if (empty($data) || 
            !isset($data["mobile_no"]) || empty($data["openId"]) ||
            !isset($data["mobile_no"]) || empty($data["openId"])) {
            return parent::go_error($event, -12);
        }
        $mobile_no = $data['mobile_no'];
        $openId = $data['openId'];
        
        $condition = " mobile = :mobile_no ";
        $params = array(
            ":mobile_no" => $mobile_no
        );
        
        $userLogin = new UserLogin();
        $userLoginData = $userLogin->fetch_inner_base($event, $condition, $params);
        
        if(empty($userLoginData)){
            return parent::go_error($event, -4005);
        }
        $userId = $userLoginData[0]['id'];
        
        $event->user_login_data = array(
            "id"     => $userId,
            "mobile" => $mobile_no,
            "openId" => $openId
        );
        
        $event->bindAccount = 1;
    }
}
