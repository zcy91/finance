<?php
namespace console\models\user;

use console\models\BaseModel;

class View_UserShop extends BaseModel {
    
    public function fetchSiteInfo($event) {
        
        $data = &$event->RequestArgs;

        if (!isset($data["site_url"]) && !is_string($data["site_url"])) {
            return parent::go_error($event, -12);
        }        
 
        $sql = "SELECT bp.seller_id,
                       bp.shop_id,
                       bp.token,
                       bp.is_closed
                FROM user_shop AS bp INNER JOIN
                     user_seller AS bs ON bp.seller_id = bs.seller_id
                WHERE bp.shop_url = :site_url OR bp.shop_url_self = :site_url";
        
        $params = array(
            ":site_url" => $data["site_url"]
        );
    
        $result = $this->query_SQL($sql, $event, null, $params);      
       
        $event->Postback($result);
    }

}
