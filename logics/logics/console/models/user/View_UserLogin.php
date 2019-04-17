<?php

namespace console\models\user;

use console\models\BaseModel;
use console\models\user\View_UserProfile;

class View_UserLogin extends BaseModel {

    public static function getOperateSellerId($data) {

        if ($_SERVER["seller_info"]["seller_id"] == 1) {
            if (isset($data["logSellerId"]) && is_numeric($data["logSellerId"])) {
                $ownSellerId = $data["logSellerId"];
            }
            if (isset($data["ownSellerId"]) && is_numeric($data["ownSellerId"])) {
                $ownSellerId = $data["ownSellerId"];
            }
        } else {
            $ownSellerId = $_SERVER["seller_info"]["seller_id"];
        }        
        
        return $ownSellerId;
    }
    
    public static function getOperateSectionId($data) {

        if (isset($data["logSectionId"]) && is_numeric($data["logSectionId"])) {
            $ownSectionId = $data["logSectionId"];
        } else if (isset($data["ownSectionId"]) && is_numeric($data["ownSectionId"])) {
            $ownSectionId = $data["ownSectionId"];
        } else if (isset($_SERVER["seller_info"]["sectionId"])){
            $ownSectionId = $_SERVER["seller_info"]["sectionId"]; 
        } else {
            $ownSectionId = 0;
        }
        
//        if($ownSectionId == 0){
//            $ownSectionId = true;
//        }
        
        return $ownSectionId;            
    }    
    
    public static function getOperateUserId($data) {

        if (isset($data["logUserId"]) && is_numeric($data["logUserId"])) {
            $logUserId = $data["logUserId"];
        } else {
           $logUserId = $_SERVER["seller_info"]["user_id"];
        }    

        return $logUserId;
    }    

    public function getOneAccount($event, $operate, $account) {
        
        if ($operate) {
            $condition = " t1.dnames = :account OR t1.mobile = :account OR t1.email = :account";
        } else {
            $condition = " t1.openId = :account";
        }

        $sql = "SELECT t1.id,
                       t1.dnames,
                       t1.mobile,
                       t1.email,
                       t1.password,
                       t1.salt,
                       t1.locked,
                       t1.deleted,
                       t2.pic
                FROM user_login t1
                LEFT JOIN user_profile t2 ON t1.id = t2.userId
                WHERE $condition";

        $params = array(
            ":account" => $account
        );

        $result = $this->query_SQL($sql, $event, null, $params);

        if (!empty($result)) {
            $result = $result[0];
        }

        return $result;
    }

    public function getOne($event, $userId) {

        $sql = "SELECT ul.dnames As account,
                       ul.mobile,
                       ul.email,
                       ul.password,
                       ul.salt,
                       ul.locked,
                       ul.deleted,
                       up.id AS staffId,
                       up.pic,
                       up.dnames,
                       up.entryTime,
                       up.salary,
                       up.sex,
                       up.officePhone,
                       up.idCard,
                       up.bank,
                       up.bankNo,
                       up.wxNo,
                       up.zfbNo,                       
                       up.qq,
                       up.qqPasswd,
                       up.birthDay,
                       up.ethnic,
                       up.homeAddress,
                       up.nowAddress,
                       up.educational,
                       up.graduateSchool,
                       up.graduateTime,
                       up.profession,
                       up.speciality,
                       up.dstatus
                FROM user_login AS ul INNER JOIN
                     user_profile AS up ON ul.id = up.userId
                WHERE ul.id = :userId";

        $params = array(
            ":userId" => $userId
        );

        $result = $this->query_SQL($sql, $event, null, $params);

        if (!empty($result)) {
            $result = $result[0];
        }

        return $result;
    }

    public function checkRepeat($event, $key, $Value, $id = 0) {

        $conditions = "";
        $params = [":account" => $Value];

        if (!empty($id) && is_numeric($id)) {
            $conditions .= "AND id != :id";
            $params[":id"] = $id;
        }

        switch ($key) {
            case 1:
                $sql = "SELECT id FROM user_login WHERE dnames = :account $conditions";
                break;
            case 2:
                $sql = "SELECT id FROM user_login WHERE mobile = :account $conditions";
                break;
            case 3:
                $sql = "SELECT id FROM user_login WHERE email = :account $conditions";
            default:
                $sql = "SELECT id FROM user_login WHERE openId = :account $conditions";
        }

        $result = $this->query_SQL($sql, $event, null, $params);
        return $result;
    }
    
    public function fetchDataType($event, $userId, $sellerId){

        $sql = "SELECT usr.id,
                       usr.seller_id,
                       usr.superd,
                       rsu.sectionId
                FROM user_seller_relation AS usr LEFT OUTER JOIN
                     right_section_user AS rsu ON usr.seller_id = rsu.seller_id AND
                      usr.userId = rsu.userId 
                WHERE usr.seller_id = :sellerId AND usr.userId = :userId
                LIMIT 1";

        $params = array(
            ":userId" => $userId,
            ":sellerId" => $sellerId
        );

        $result = $this->query_SQL($sql, $event, null, $params);
        
        $fetchDataType = 0;
        if (!empty($result)) {
            $result = $result[0];
            if ($result["superd"]) {
                $fetchDataType = 1;
            } elseif ($result["sectionId"]) {
                $fetchDataType = 2;
            } else {
                $fetchDataType = 3;
            }
        }

        return $fetchDataType;        
    }    
    
    public static function getStaffInfo($event ,$sellerId, $userId){
        
        $View_UserProfile = new View_UserProfile();
        $User = $View_UserProfile->getStaffInfo($event, $sellerId, $userId);
        unset($View_UserProfile);
        
        return $User;
        
    }
    
    public static function getOrderLogUser($event, $sellerId, $userId){
        
        $View_UserProfile = new View_UserProfile();
        $User = $View_UserProfile->getOrderLogUser($event, $sellerId, $userId);
        unset($View_UserProfile);
        
        return $User;
        
    }

}
