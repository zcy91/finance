<?php
namespace console\models\user;

use yii\db\Expression;
use console\models\BaseModel;
use console\common\StaticFunction;
use console\models\user\View_UserLogin;
use console\models\user\View_UserSeller;
use console\models\right\View_RightStaffRole;
use console\models\right\View_RightSectionUser;
use console\models\right\View_RightPostUser;
use console\models\user\View_UserProfile;
use console\models\right\RightStaffRole;
use console\models\right\RightSectionUser;
use console\models\right\RightPostUser;

class List_User extends BaseModel {
    
    public function userLogin($event) {
        
        $data = &$event->RequestArgs;

        $isOpenId = 1;
        if (isset($data["openId"])) {
            if (!is_string($data["openId"]) || empty($data["openId"])) {
                return parent::go_error($event, -2001);
            }
            $account = $data["openId"];
        } else {
            if (!isset($data["account"]) || !is_string($data["account"]) || empty($data["account"]) ||
                 !isset($data["password"]) || !is_string($data["password"]) || empty($data["account"])) {
            return parent::go_error($event, -12);
        }        
            $isOpenId = 0;
        $account = $data["account"];
        $password = $data["password"];
        }
        
        $View_UserLogin = new View_UserLogin();
        if ($isOpenId) {
            $User = $View_UserLogin->getOneAccount($event, 0, $account);
        } else {
            $User = $View_UserLogin->getOneAccount($event, 1, $account);
        }
        unset($View_UserLogin);
        if (empty($User)) {
            return parent::go_error($event, -2001);
        }

        $userId = $User["id"];
        
        if (!$isOpenId) {
        $originalPasswd = $User["password"];
        $salt = $User["salt"];
        $volid = StaticFunction::checkPwd($originalPasswd, $password, $salt);
        if (!$volid) {
            return parent::go_error($event, -2002);
        }
        }
        
        $View_UserRole = new View_UserRole();
        $Roles = $View_UserRole->getDefaultRole($event, $userId);  
        unset($View_UserRole);
        if (empty($Roles)) {
            return parent::go_error($event, -2003);
        }
        
        $roleId = $Roles[0]["roleId"];
        
        $result = array(
            "userId" => $userId,
            "account" => $account,
            "currentRoleId" => $roleId,
            "currentRoleName" => $Roles[0]["roleNames"],
            "dnames"   => $User['dnames'],
            "images"   => $User['pic'],
            "mobile"   => $User['mobile']
        );
        
        if ($roleId == 1) {
            
            $View_UserSeller = new View_UserSeller();
           
            if ($_SERVER["seller_info"]["seller_id"] != 1) {
                $LogSeller = $View_UserSeller->getDefaultsSeller($event, $userId);        
                if (empty($LogSeller)) {
                    return parent::go_error($event, -2004);
                }

                $LogSeller = $LogSeller[0];

                $logSellerId = $LogSeller["seller_id"];                
            } else {
                $logSellerId = $_SERVER["seller_info"]["seller_id"];
            }
            
            $result["currentSellerId"] = $LogSeller["seller_id"];
            
            $SellerInfo = $View_UserSeller->isSuper($event, $userId, $logSellerId);
            unset($View_UserSeller);
            $isSuper = $SellerInfo["superd"];
            $result["isSuper"] = $isSuper;
            
            if (!$isSuper) {
                
                $View_RightStaffRole = new View_RightStaffRole();
                $LogRole = $View_RightStaffRole->getDefaultRole($event, $userId, $logSellerId);
                unset($View_RightStaffRole);
                if (empty($LogRole)) {
                    return parent::go_error($event, -2003);
                }

                $LogRole = $LogRole[0]; 
                $result["currentRoleId"] = $LogRole["roleId"];
                $result["currentRoleName"] = $LogRole["roleNames"];                
                
                $View_RightSectionUser = new View_RightSectionUser();
                $LogSection = $View_RightSectionUser->getDefaultSection($event, $userId, $logSellerId);   
                unset($View_RightSectionUser);
                if (empty($LogSection)) {
                    return parent::go_error($event, -2005);
                }

                $LogSection = $LogSection[0]; 
                $result["currentsectionId"] = $LogSection["sectionId"];
                $result["currentsectionName"] = $LogSection["sectionName"];                
            }
        } 
        
        if (!empty($data["loginIip"]) && is_string($data["loginIip"])) {
            $logingIp = $data["loginIip"];
        } else {
            $logingIp = $_SERVER["seller_info"]["client_ip"];
        }
        
        $event->user_login_data = array(       
            "id" => $userId,
            "loginIp" => $logingIp,
            "loginTime" => $nowTime = date('Y-m-d H:i:s'),
            "loginTimes" => new Expression('loginTimes+1')      
        ); 
        
        $event->Postback($result);
    }

    public function staffList($event) {
        
        $data = &$event->RequestArgs;
        
        $ownSellerId = View_UserLogin::getOperateSellerId($data);
        if (empty($ownSellerId)) {
            return parent::go_error($event, -2011);
        } 
        
        $now_time = date('Y-m-d H:i:s');
        $time_limit = isset($data["time_limit"]) && !empty($data["time_limit"]) ? $data["time_limit"] : $now_time;
        
        $limit_arr = $event->Pagination;//分页信息
        $limit = parent::getLimitArr($event);//获得分类信息数组或空值
        $ispage = isset($limit_arr["ispage"]) ? $limit_arr["ispage"] : 0;  
        
        $fromStr = "";
        $condition = " AND usr.nowTime <= :time_limit";
        $params = array(
            ":sellerId" => $ownSellerId,
            ":time_limit" => $time_limit
        );  
        
        //姓名
        if (isset($data["staffName"]) && !empty($data["staffName"])) {
            $condition .= " AND up.dnames LIKE :staffName";
            $params[":staffName"] = "%" . $data["staffName"] . "%";
        } 
        
        //用户名
        if (isset($data["staffAccount"]) && !empty($data["staffAccount"])) {
            $condition .= " AND ul.dnames LIKE :staffAccount";
            $params[":staffAccount"] = "%" . $data["staffAccount"] . "%";
        }   
        
        //状态
        if (isset($data["dstatus"]) && $data["dstatus"]!= "") {
            $condition .= " AND up.dstatus = :dstatus";
            $params[":dstatus"] = $data["dstatus"];
        }         

        //类型
        if (isset($data["roleId"]) && !empty($data["roleId"])) {
            $fromStr .= RightStaffRole::creatCondition("usr", ["seller_id","userId"]);
            $condition .= " AND rsu.roleId = :roleId";
            $params[":roleId"] = $data["roleId"];
        }        
        
        //部门
        if (isset($data["sectionId"]) && !empty($data["sectionId"])) {
            $fromStr .= RightSectionUser::creatCondition("usr", ["seller_id","userId"]);
            $condition .= " AND rsu.sectionId = :sectionId";
            $params[":sectionId"] = $data["sectionId"];
        }  
        
        //岗位
        if (isset($data["postId"]) && !empty($data["postId"])) {
            $fromStr .= RightPostUser::creatCondition("usr", ["seller_id","userId"]);
            $condition .= " AND rpu.postId = :postId";
            $params[":postId"] = $data["postId"];
        }    
        
        //入职日期开始
        if (isset($data["begin_date"]) && !empty($data["begin_date"])) {
            $condition .= " AND up.entryTime >= :begin_date";
            $params[":begin_date"] = $data["begin_date"];
        } 

        //入职日期结束
        if (isset($data["end_date"]) && !empty($data["end_date"])) {
            $condition .= " AND up.entryTime < :end_date";
            $params[":end_date"] = $data["end_date"];
        }            
   
        
        $View_UserProfile = new View_UserProfile();
        $dataStaff = $View_UserProfile->staffList($event, $ispage, $condition, $params, $limit, $fromStr);
        unset($View_UserProfile);  
      
        if ($ispage) {
            
            $sql = " SELECT FOUND_ROWS() as record_count; ";
            $return_count = $this->query_SQL($sql, $event);        
            $recode_count = $return_count[0]['record_count']; 
            
            $return_data = array(
                "pagesize" => $limit_arr["pagesize"],
                "pageindex" => $limit_arr["pageindex"],
                "recordcount" => $recode_count,
                "time_limit" => $time_limit,
                "data" => &$dataStaff
            );              
        } else {
            $return_data = &$dataStaff;
        }            
        
        $event->Postback($return_data);
    }

    function staffDescEd($event){
        
        $data = &$event->RequestArgs;
        
        if (isset($data["id"]) || empty($data["id"]) || !is_numeric($data["id"])) {
            return parent::go_error($event, -12);
        }    
        
        $userId = isset($data["id"]);
        $oprate = (isset($data["oprate"]) && is_numeric($data["oprate"]) && empty($data["oprate"])) ? 0 : 1;
        
        $ownSellerId = View_UserLogin::getOperateSellerId($data);
        if (empty($ownSellerId)) {
            return parent::go_error($event, -2011);
        } 

        $View_UserSeller = new View_UserSeller();
        $sellerStaff = $View_UserSeller->staffDesc($event, $ownSellerId, $userId);
        unset($View_UserSeller);
        
        $View_RightStaffRole = new View_RightStaffRole();
        $Role = $View_RightStaffRole->getStaffRole($event, $userId, $ownSellerId);
        unset($View_RightStaffRole);
        
        if ($oprate) {
            $sellerStaff["role"] = array_column($Role, "roleId");
        } else {
            $sellerStaff["roleId"] = (!empty($Role)) ? $Role["roleId"] : 0;
        } 
        
        $View_RightSectionUser = new View_RightSectionUser();
        $Section = $View_RightSectionUser->getStaffSection($event, $userId, $ownSellerId);
        unset($View_RightSectionUser);
        
        if ($oprate) {
            $sellerStaff["section"] = array_column($Section, "sectionId");;
        } else {
            $sellerStaff["sectionId"] = (!empty($Section)) ? $Section["sectionId"] : 0;
        }         

        $event->Postback($sellerStaff);
        }        
        
    function staffDesc($event){
        
        $data = &$event->RequestArgs;
        
        if (!isset($data["id"]) || empty($data["id"]) || !is_numeric($data["id"])) {
            return parent::go_error($event, -12);
        }    
        
        $userId = $data["id"];
        $oprate = (isset($data["oprate"]) && is_numeric($data["oprate"]) && empty($data["oprate"])) ? 0 : 1;
        
        $ownSellerId = View_UserLogin::getOperateSellerId($data);
        if (empty($ownSellerId)) {
            return parent::go_error($event, -2011);
        } 

        $View_UserSeller = new View_UserSeller();
        $sellerStaff = $View_UserSeller->staffDesc($event, $ownSellerId, $userId);
        unset($View_UserSeller);

        $View_RightStaffRole = new View_RightStaffRole();
        $Role = $View_RightStaffRole->getStaffRole($event, $userId, $ownSellerId);
        unset($View_RightStaffRole);
        
        if (!$oprate) {
            $sellerStaff["role"] = $Role;
        } else {
            $sellerStaff["roleId"] = 0;
            $sellerStaff["roles"] = "";               
            if (!empty($Role)) {
                $Role = $Role[0];
                $sellerStaff["roleId"] = $Role["roleId"];
                $sellerStaff["roles"] = $Role["dnames"];                
            }
        }            
        
        $View_RightSectionUser = new View_RightSectionUser();
        $Section = $View_RightSectionUser->getStaffSection($event, $userId, $ownSellerId);
        unset($View_RightSectionUser);
      
        if (!$oprate) {
            $sellerStaff["section"] = $Section;
        } else {
            $sellerStaff["sectionId"] = 0;
            $sellerStaff["sections"] = "";             
            if (!empty($Section)) {
                $Section = $Section[0];
                $sellerStaff["sectionId"] = $Section["sectionId"];
                $sellerStaff["sections"] = $Section["dnames"];                
            }
        }         
            
        $View_RightPostUser = new View_RightPostUser();
        $Post = $View_RightPostUser->getStaffPost($event, $userId, $ownSellerId);
        unset($View_RightPostUser);
            
        if (!$oprate) {
            $sellerStaff["post"] = $Post;
        } else {
            $sellerStaff["postId"] = 0;
            $sellerStaff["posts"] = "";              
            if (!empty($Post)) {
                $Post = $Post[0];
                $sellerStaff["postId"] = $Post["postId"];
                $sellerStaff["posts"] = $Post["dnames"];                
            }
        }            
        
        $event->Postback($sellerStaff);
    }    

    public function roleStaffList($event) {
        
        $data = &$event->RequestArgs;
        
        if (!isset($data["roleId"]) || !is_numeric($data["roleId"]) || !in_array($data["roleId"], [4,5])) {
            return parent::go_error($event, -12);
        }
        
        $ownSellerId = View_UserLogin::getOperateSellerId($data);
        if (empty($ownSellerId)) {
            return parent::go_error($event, -2011);
        } 

        $roleId = $data["roleId"];
        $limit_arr = $event->Pagination;//分页信息
        $limit = parent::getLimitArr($event);//获得分类信息数组或空值
        $ispage = isset($limit_arr["ispage"]) ? $limit_arr["ispage"] : 0;  

        $selectStr = "";
        $condition = "";
        $params = array(
            ":sellerId" => $ownSellerId,
            ":roleId" => $roleId,
        );  
        
        //姓名
        if (isset($data["staffName"]) && !empty($data["staffName"])) {
            $condition .= " AND up.dnames LIKE :staffName";
            $params[":staffName"] = "%" . $data["staffName"] . "%";
        } 

        //状态
        if (isset($data["dstatus"]) && $data["dstatus"]!= "") {
            $condition .= " AND up.dstatus = :dstatus";
            $params[":dstatus"] = $data["dstatus"];
        }         

        $View_UserSeller = new View_UserSeller();
        $dataStaff = $View_UserSeller->getSellerStaff($event, $ispage, $condition, $params, $limit, $selectStr);
        unset($View_UserSeller);  
      
        if ($ispage) {
            
            $sql = " SELECT FOUND_ROWS() as record_count; ";
            $return_count = $this->query_SQL($sql, $event);        
            $recode_count = $return_count[0]['record_count']; 
            
            $return_data = array(
                "pagesize" => $limit_arr["pagesize"],
                "pageindex" => $limit_arr["pageindex"],
                "recordcount" => $recode_count,
                "data" => &$dataStaff
            );              
        } else {
            $return_data = &$dataStaff;
        }            
        
        $event->Postback($return_data);
    }    

}
