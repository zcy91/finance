<?php
namespace console\models\user;

use console\models\BaseModel;

class View_GetUserNode extends BaseModel {
    
    public function fetch_all($event) {
        $args = &$event->RequestArgs;
        $seller_id = isset($args['seller_id'])?$args['seller_id']:$_SERVER['seller_info']['seller_id'];
        $userId = isset($args['userId'])?$args['userId']:0;
        $sql = "SELECT
                        DISTINCT t3.router,
                        t2.moduleId,
                        t2.dnames,
                        t2.router,
                        t2.display,
                        t2.icon,
                        t3.baseId,
                        t3.display as cdisplay,
                        t3.dnames as cdnames,
                        t3.router as crouter,
                        t3.action,
                        t3.attribute
                FROM
                        right_post_user t1
                RIGHT JOIN (
                        SELECT
                                t01.moduleId,
                                t01.postId,
                                t02.display,
                                t02.dnames,
                                t02.router,
                                t02.icon
                        FROM
                                right_post_module t01
                        LEFT JOIN right_module t02 ON t01.moduleId = t02.id
                        WHERE
                                t01.seller_id = :seller_id
                ) t2 ON t1.postId = t2.postId
                RIGHT JOIN (
                        SELECT
                                t03.baseId,
                                t03.postId,
                                t03.moduleId,
                                t04.display,
                                t04.dnames,
                                t04.router,
                                t04.sort,
                                t04.action,
                                t04.attribute
                        FROM
                                right_post_base t03
                        LEFT JOIN right_base t04 ON t03.baseId = t04.id
                        WHERE
                                t03.seller_id = :seller_id
                ) t3 ON t1.postId = t3.postId and t2.moduleId = t3.moduleId
                WHERE
                      t1.seller_id = :seller_id";
        
        $params = array(
            ":seller_id" => $seller_id
        );
        
        if($userId != 0){
            $condition = " AND t1.userId = :userId";
            $sql .= $condition;
            $params[":userId"] = $userId;
        }
        
        $sql.="ORDER BY t2.moduleId,
                t3.sort,
                t3.baseId ASC";
        $result = $this->query_SQL($sql, $event, null, $params); 
        
        $event->Postback($result);
    }      
    public function getAllNode($event) {
//        $args = &$event->RequestArgs;
       
        $sql = "SELECT
                        t1.id,
                        t1.moduleId,
                        t1.dnames,
                        t1.sort,
                        t1.router,
                        t1.attribute,
                        t1.display as is_menu,
                        t1.action,
                        t2.dnames as module_name,
                        t2.icon,
                        t2.router as module_router
                FROM
                        right_base t1
                LEFT JOIN right_module t2 ON t1.moduleId = t2.id
                WHERE
                        t2.display = 1
                ORDER BY t2.id, t1.sort,t1.id asc";
       
        $result = $this->query_SQL($sql, $event, null, []); 
        
        $event->Postback($result);
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
