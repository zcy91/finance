<?php
$client = new yar_client("http://ccc.finance.com/services.php");

$str = '{"seller_id":0,"shop_id":0,"user_id":0,"signature":"","timestamp":0,"nonce":"","module_id":0,"client_ip":"127.0.0.1","site_url":"www.yingyun.com","params":[]}';
$str = '{"signature":"7cd42b595eaa8e2b9a3e63c5a996bab6e9c7ee67","timestamp":1550729976,"nonce":"50btv73t","seller_id":"100","shop_id":"1","dis_lang_id":1,"user_id":0,"client_ip":"127.0.0.1","site_url":"admin.yhjr.com","sha1":0,"params":""}';


//var_dump($client->getSiteInfo($str));
var_dump(json_decode($client->loadSystemInfo($str),TRUE));
//var_dump($client->add(1, 2));
//
//var_dump($client->call("add", array(3, 2)));

//var_dump($client->_add(1, 2));
