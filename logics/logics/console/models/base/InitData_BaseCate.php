<?php
namespace console\models\base;
use console\models\BaseModel;
use console\models\base\BaseProductCategory;

class InitData_BaseCate extends BaseModel{
    
   
    public function cateAdd($event){
        $args = &$event->RequestArgs;
        $seller_id = isset($args['seller_id'])?$args['seller_id']:$_SERVER['seller_info']['seller_id'];
        
        if(!isset($args["dnames"]) || empty($args["dnames"]) || 
           !isset($args['pid']) ||
           !isset($args['algorithm']) || empty($args["algorithm"])){
            return parent::go_error($event, -12);
        } 
        $condition = " seller_id = :seller_id AND dnames = :dnames";
        $params = array(
            ":seller_id" =>  $seller_id,
            ":dnames" =>  $args['dnames']
        );
        $column = " id ";
        $baseProductCategory = new BaseProductCategory();
        $baseProductCategoryData = $baseProductCategory->fetch_inner_base($event, $condition, $params, null, $column);
        
        if(!empty($baseProductCategoryData)){
            return parent::go_error($event, -3003);  //分类名称已存在
        }
        
        $level = 1;
        if($args['pid'] == 0){   //构造parentsPath
            $event->parentPath = "0|";
        }else{
            $condition = " seller_id = :seller_id AND id = :cate_id";
            $params = array(
                ":seller_id" =>  $seller_id,
                ":cate_id" =>  $args['pid']
            );
            $column = " parentsPath,`level` ";
            $baseProductCategoryData = $baseProductCategory->fetch_inner_base($event, $condition, $params, null, $column);
            
            if(empty($baseProductCategoryData)){
                return parent::go_error($event, -3002);
            }
            $event->parentPath = $baseProductCategoryData[0]['parentsPath'];
            $level = $baseProductCategoryData[0]['level'] + 1;
        }
        unset($baseProductCategory);
        
        $event->base_product_category_data = array(
            "id"            => &$event->cate_id,
            "dnames"        => isset($args['dnames'])?$args['dnames']:"",
            "sort"          => isset($args['sort'])?$args['sort']:"99",
            "parentsPath"   => &$event->selfParentPath,
            "pid"           => isset($args['pid'])?$args['pid']:"0",
            "algorithm"     => $args['algorithm'],
            "seller_id"     => $seller_id,
            "level"         => $level,
            "nowTime"       => date('Y-m-d H:i:s')
        );
    }
    
    public function cateEdit($event){
        $args = &$event->RequestArgs;
        $seller_id = isset($args['seller_id'])?$args['seller_id']:$_SERVER['seller_info']['seller_id'];
        
        if(!isset($args["cate_id"]) || empty($args["cate_id"])){
            return parent::go_error($event, -12);
        }
        $cate_id = $args['cate_id'];
        
        $baseProductCategory = new BaseProductCategory();
        $params = array(
            ":seller_id" =>  $seller_id
        );
        $condition = " seller_id = :seller_id";
        if(isset($args['dnames']) && !empty($args['dnames'])){ //验证修改的名字是否有用
            $params[":dnames"] = $args['dnames'];
            $condition_str = $condition." and dnames= :dnames";
            $column = " id ";
            $baseProductCategoryData = $baseProductCategory->fetch_inner_base($event, $condition_str, $params, null, $column);
            if($cate_id != $baseProductCategoryData[0]['id'] && !empty($baseProductCategoryData)){
                return parent::go_error($event, -3003);  //分类名称已存在
            }
            unset($params[":dnames"]);
        }
        $params[":cate_id"] = $cate_id;
        $condition_self = $condition." and id = :cate_id";
        
        $column = " dnames, sort, pid , parentsPath , `level` , algorithm ";
        
        $baseProductCategoryData = $baseProductCategory->fetch_inner_base($event, $condition_self, $params, null, $column);
        
        unset($baseProductCategory);
        if(empty($baseProductCategoryData)){
            return parent::go_error($event,-3000);  //修改的数据不存在
        }
        $single_data = $baseProductCategoryData[0];
        
        $category_data = array(
            "id"            => $cate_id,
            "dnames"        => isset($args['dnames'])?$args['dnames']:$single_data['dnames'],
            "sort"          => isset($args['sort'])?$args['sort']:$single_data['sort'],
            "algorithm"     => isset($args['algorithm'])?$args['algorithm']:$single_data['algorithm'],
//            "level"         => isset($args['level'])?$args['level']:$single_data['level'],
//            "pid"           => isset($args['pid'])?$args['pid']:$single_data['pid'],
//            "parentsPath"   => $single_data['parentsPath'],
            "nowTime"       => date('Y-m-d H:i:s')
        );
        
//        if(isset($args['pid'])){
//            $condition.= " and pid = :cate_id";
//            $column = " id ";
//            $baseProductCategoryData = $baseProductCategory->fetch_inner_base($event, $condition_self, $params, null, $column);
//            if(!empty($baseProductCategoryData)){
//                return parent::go_error($event,-3004);  //有子分类不能修改父类
//            }else{   //可以修改父类
//                if($args['pid'] == 0){
//                    $event->parentPath = "0|";
//                }else{
//                    $params[":cate_id"] = $args['pid']; 
//                    $condition = " seller_id = :seller_id AND id = :cate_id";
//                    $column = " parentsPath ";
//                    $baseProductCategoryData = $baseProductCategory->fetch_inner_base($event, $condition, $params, null, $column);
//                    if(empty($baseProductCategoryData)){
//                        return parent::go_error($event, -3002);
//                    }
//                    $event->parentPath = $baseProductCategoryData[0]['parentsPath'];
//                }
//                $category_data['parentsPath'] = &$event->selfParentPath;
//            }
//        }
        unset($baseProductCategory);
        
        
        $event->base_product_category_data = $category_data;
    }
    
    public function cateDelete($event){
        $args = &$event->RequestArgs;
        $seller_id = isset($args['seller_id'])?$args['seller_id']:$_SERVER['seller_info']['seller_id'];
        
        if(!isset($args["cate_id"]) || empty($args["cate_id"])){
            return parent::go_error($event, -12);
        }
        
        $cate_id = $args['cate_id']; 
        $condition = " seller_id = :seller_id AND pid = :cate_id";
        $params = array(
            ":seller_id" =>  $seller_id,
            ":cate_id" =>  $cate_id
        );
        $column = " id ";
        $baseProductCategory = new BaseProductCategory();
        $baseProductCategoryData = $baseProductCategory->fetch_inner_base($event, $condition, $params, null, $column);
        unset($baseProductCategory);
        if(!empty($baseProductCategoryData)){
            return parent::go_error($event,-3001);  //还有子级分类，不能删除
        }
        $event->Condition = " seller_id = :seller_id and id = :cate_id";
        $event->ParamsList = array(
            ":seller_id" => $seller_id,
            ":cate_id"   => $cate_id
        );
        
    }
}

