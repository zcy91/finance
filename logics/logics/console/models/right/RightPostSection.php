<?php
namespace console\models\right;

use console\models\BaseModel;

class RightPostSection extends BaseModel {

    const TABLE_NAME = "right_post_section";

    public function primaryKey() {
        return ['id' => 'auto'];
    }

    protected function resolveFields($v = array(), $default_arr = array()) {

        $subset = array(
            "id",
            "seller_id",
            "postId",
            "sectionId"           
        );

        $info_arr = parent::key_values_intersect($v, $subset, $default_arr);
        return $info_arr;
    }
    
    public function deleteAdd($event){
        $args = &$event->RequestArgs;
        $seller_id = isset($args['seller_id'])?$args['seller_id']:$_SERVER['seller_info']['seller_id'];
        if(!isset($args['post_id']) || empty($args['post_id'])){
            parent::go_error($event, -12);
        }
        $post_id = $args['post_id'];
        $condition = " seller_id = :seller_id AND postId = :postId ";
        $params = array(
            ":seller_id" => $seller_id,
            ":postId"    => $post_id
        );
        parent::deleteAll(self::TABLE_NAME, $condition, $event, $params);
        
        $this->add($event);
    }
    public function deletePost($event){
        $args = &$event->RequestArgs;
        $seller_id = isset($args['seller_id'])?$args['seller_id']:$_SERVER['seller_info']['seller_id'];
      
        if(!isset($args['post_id']) || empty($args['post_id'])){
            return parent::go_error($event, -12);
        }
        $post_id = $args['post_id'];
        $condition = " seller_id = :seller_id AND postId = :postId ";
        $params = array(
            ":seller_id" => $seller_id,
            ":postId"    => $post_id
        );
        parent::deleteAll(self::TABLE_NAME, $condition, $event, $params);        
    }

}
