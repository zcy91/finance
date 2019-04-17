<?php
$api = new \Org\Api\ServiceProxy();

$sellerInfo = $api->fetch_site_info(true);
if($sellerInfo["returnState"] == 1){
    $sellerInfo = $sellerInfo["returnData"];
    //客户编号
    defined('SELLER_ID') or define('SELLER_ID', $sellerInfo['seller_id']);
    
    //店铺编号
    defined('SHOP_ID') or define('SHOP_ID', $sellerInfo['shop_id']);
    
    //token信息
    defined('TOKEN') or define('TOKEN', $sellerInfo['TOKEN']);
    
    
    if($_SERVER['HTTP_HOST'] == 'smallprogram.yhjr.com'){
        C('DEFAULT_MODULE','SmallProgram');
    }

}else{
     //服务器维护中
    throw new Exception("禁止非法访问！");
}
