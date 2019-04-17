<?php
namespace console\models\user;

use console\models\BaseModel;

class View_UserSeller extends BaseModel {

    public function getDefaultsSeller($event, $userId) {    
 
        $sql = "SELECT usr.seller_id
                FROM  user_seller_relation AS usr
                WHERE usr.userId = :userId
                ORDER BY usr.defaulte DESC";
        
        $params = array(
            ":userId" => $userId
        );
      
        $result = $this->query_SQL($sql, $event, null, $params);  
        
        return $result;
    }   
    
    public function isSuper($event, $userId, $sellerId) {    
 
        $sql = "SELECT superd
                FROM user_seller_relation AS usr
                WHERE usr.seller_id = :sellerId AND usr.userId = :userId";
        
        $params = array(
            ":userId" => $userId,
            ":sellerId" => $sellerId
        );
      
        $result = $this->query_SQL($sql, $event, null, $params);  
        
        if (!empty($result)) {
            $result = $result[0];
        }
        
        return $result;
    }   
    
    public function getOneSeller($event, $userId, $sellerId){
        
        $sql = "SELECT id,
                       superd,
                       defaulte,
                       deleted
                FROM user_seller_relation AS usr
                WHERE usr.seller_id = :sellerId AND usr.userId = :userId    ";
        
        $params = array(
            ":userId" => $userId, 
            ":sellerId" => $sellerId, 
        );
        
        $result = $this->query_SQL($sql, $event, null, $params);  
        
        if (!empty($result)) {
            $result = $result[0];
        }
        
        return $result;        
        
    }
    
    public function getExcpSeller($event, $userId, $sellerId) {    
 
        $sql = "SELECT id,
                       superd,
                       defaulte
                FROM user_seller_relation AS usr
                WHERE usr.userId = :userId AND usr.seller_id != :sellerId AND
                      usr.deleted = 0";
        
        $params = array(
            ":userId" => $userId,
            ":sellerId" => $sellerId
        );
      
        $result = $this->query_SQL($sql, $event, null, $params); 
        
        return $result;
    }      
    
    public function getSellerStaff($event, $ispage, $condition, $params, $limit, $selectStr){
 
        $sql = "SELECT " . ($ispage ? " sql_calc_found_rows " : "") . " 
                       usr.seller_id,
                       usr.userId,
                       up.dnames AS userAccount,
                       up.dnames AS userName,
                       up.pic,
                       up.mobile $selectStr
                FROM user_seller_relation AS usr INNER JOIN
                     right_staff_role AS rsr ON usr.seller_id = rsr.seller_id AND
                      usr.userId = rsr.userId AND rsr.roleId = :roleId INNER JOIN
                     right_section_user AS rsu ON usr.seller_id = rsu.seller_id AND
                      usr.userId = rsu.userId INNER JOIN
                     user_login AS ul ON usr.userId = ul.id INNER JOIN
                     user_profile AS up ON usr.userId = up.userId
                WHERE usr.seller_id = :sellerId $condition";

        $result = $this->query_SQL($sql, $event, $limit, $params);  

        return $result;        
    }

    public function checkSalesMan($event, $sellerId, $userId) {
        
        $selectStr = ",
                       rsr.roleId,
                       rsu.sectionId ";

        $condition = " AND usr.userId = :userId";
        $params = array(
            ":sellerId" => $sellerId,
            ":roleId" => 4,
            ":userId" => $userId
        );
        $ispage = 0;
        $limit = null;
        
        $SalesMan = $this->getSellerStaff($event, $ispage, $condition, $params, $limit, $selectStr);
        
        if (!empty($SalesMan)) {
            $SalesMan = $SalesMan[0];
        }
        
        return $SalesMan;
    }
    
    public function checkMerchandiser($event, $sellerId, $userId) {
        
        $selectStr = ",
                       rsr.roleId,
                       rsu.sectionId ";

        $condition = " AND usr.userId = :userId";
        $params = array(
            ":sellerId" => $sellerId,
            ":roleId" => 5,
            ":userId" => $userId
        );
        $ispage = 0;
        $limit = null;
        
        $Merchandiser = $this->getSellerStaff($event, $ispage, $condition, $params, $limit, $selectStr);
        
        if (!empty($Merchandiser)) {
            $Merchandiser = $Merchandiser[0];
        }
        
        return $Merchandiser;
    }    
     
    public function staffDesc($event, $sellerId, $userId){
        
        $sql = "SELECT usr.seller_id,
                       ul.id,
                       ul.dnames AS account,
                       ul.mobile,
                       ul.email,
                       up.pic,
                       up.dnames,
                       up.mobile,
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
                       up.dstatus,
                       up.userId,
                       up.nativePlace,
                       up.major
                FROM user_seller_relation AS usr INNER JOIN
                     user_login AS ul ON usr.userId = ul.id INNER JOIN
                     user_profile AS up ON usr.userId = up.userId
                WHERE usr.seller_id = :sellerId AND usr.userId = :userId";
        
        $params = array(
            ":sellerId" => $sellerId,
            ":userId" => $userId
        );
        
        $result = $this->query_SQL($sql, $event, null, $params);
        
        if (!empty($result)) {
            $result = $result[0];
        }
        
        return $result;
    }
     
}
