<?php

/*
$a = array(
    "seller_id"=>0,
    "shop_id"=>0,
    "user_id"=>0,
    "signature"=>"",
    "timestamp"=>0,
    "nonce"=>"",
    "module_id"=>0,
    "client_ip"=>"127.0.0.1",
    "site_url"=>"www.yingyun.com",
    "params"=>[]
);

$b = json_encode($a);

echo $b;

exit;
*/
define("IS_DEBUG", 1);
require __DIR__ . '/services.php';

use Kuba\Gateway\Services;
use Kuba\Gateway\AccessModule;

$timestamp = time();
$nonce = AccessModule::randomkeys(8);
$tmpArr = array("123456", $timestamp, $nonce);
sort($tmpArr, SORT_STRING);
$tmpStr = implode($tmpArr);
$tmpStr = sha1($tmpStr);

$module_id = 0;
$ip = '';
$user_id = 0;
$site_url = "api.zhenfang123.com";
$params = [];

$args_array = array(
    "signature" => $tmpStr,
    "timestamp" => $timestamp,
    "nonce" => $nonce,
    "user_id" => &$user_id,
    "client_ip" => &$ip,
    "site_url" => &$site_url,
    "module_id" => &$module_id,
    "params" => &$params
);

$module_id = 6;
$user_id = 0;
$ip = '127.0.0.1';
$site_url = "www.yingyun.com";
$params = json_decode('', true);
$json_Args = json_encode($args_array);

//登陆
$module_id = 221;
$user_id = 0;
$ip = '127.0.0.1';
$site_url = "www.yingyun.com";
$params = json_decode('{"account":"admin","password":"ec7f92c539ceafc8aab3396a86b3ffb3"}', true);
$json_Args = json_encode($args_array);

//添加员工
$module_id = 222;
$user_id = 100;
$ip = '127.0.0.1';
$site_url = "www.yingyun.com";
$params = json_decode('{"logSectionId":0,"dnames":"证券业务","mobile":"18057958348","passwd":"12345","role":4}', true);
$json_Args = json_encode($args_array);


$module_id = 300;
$user_id = 0;
$ip = '127.0.0.1';
$site_url = "admin.yhjr.com";
$params = json_decode('{"dnames":"小额贷3","pid":"12","algorithm":"1","level":"2","pid":"0"}', true);
$json_Args = json_encode($args_array);

$module_id = 301;
$user_id = 0;
$ip = '127.0.0.1';
$site_url = "admin.yhjr.com";
$params = json_decode('{"cate_id":"3","dnames":"房贷","sort":"2","algorithm":"1"}', true);
$json_Args = json_encode($args_array);

$module_id = 302;
$user_id = 0;
$ip = '127.0.0.1';
$site_url = "admin.yhjr.com";
$params = json_decode('{"cate_id":"2"}', true);
$json_Args = json_encode($args_array);

$module_id = 303;
$user_id = 0;
$ip = '127.0.0.1';
$site_url = "admin.yhjr.com";
$params = json_decode('{"cate_id":"3"}', true);
$json_Args = json_encode($args_array);

$module_id = 321;
$user_id = 0;
$ip = '127.0.0.1';
$site_url = "admin.yhjr.com";
$params = json_decode('{"dnames":"性别","genre":"3","attrItems":["男","女"],"ownSectionId":"1"}', true);
$json_Args = json_encode($args_array);

$module_id = 245;
$user_id = 0;
$ip = '127.0.0.1';
$site_url = "admin.yhjr.com";
$params = json_decode('{"dnames":"董事长","moduleId":[{1},{2}],"display":"1","sectionName":"技术部,房地产销售部","section_node":[{"sectionId":"2"},{"sectionId":"3"}],"power_node":[{"moduleId":"1","baseId":"2"},{"moduleId":"1","baseId":"3"}]}', true);

$module_id = 245;
$user_id = 100;
$ip = '127.0.0.1';
$site_url = "admin.yhjr.com";
$params = json_decode('{"dnames":"董事长","moduleId":[{1},{2}],"display":"1","sectionName":"技术部,房地产销售部","section_node":[{"sectionId":"2"},{"sectionId":"3"}],"power_node":[{"moduleId":"1","baseId":"2"},{"moduleId":"1","baseId":"3"}]}', true);
$json_Args = json_encode($args_array);

$module_id = 424;
$user_id = 100;
$ip = '127.0.0.1';
$site_url = "admin.yhjr.com";
$params = json_decode('{"pagination":{"pagesize":"10","pageindex":"1","recordcount":0},"status":"2","time_limit":""}', true);

$module_id = 342;
$user_id = 100;
$ip = '127.0.0.1';
$site_url = "admin.yhjr.com";
$params = json_decode('{"dnames":"\u6c7d\u8f66\u8d37","descr":"\u4f60\u5bb6\u6709\u8f66\u5c31\u53ef\u4ee5\u8d37\u7684\u4ea7\u54c1\uff0c\u6839\u636e\u4f60\u5bb6\u8f66\u7684\u4fdd\u503c\u60c5\u51b5\u7684\u8d37\u6b3e\u5546\u54c1\u30025\u5206\u949f\u5230\u8d26\uff0c\u89e3\u51b3\u71c3\u7709\u4e4b\u6025.","attrs":[{"id":"1","dnames":"\u6027\u522b","genre":"3","required":"0","imageCount":"0","sort":"0"},{"id":"16","dnames":"\u8eab\u4efd\u8bc1\u80cc\u9762","genre":"5","required":"1","imageCount":"2","sort":"2"},{"id":"17","dnames":"\u8eab\u4efd\u8bc1\u6b63\u9762","genre":"5","required":"1","imageCount":"1","sort":"3"},{"id":"19","dnames":"\u623f\u4ea7\u8bc1","genre":"5","required":"0","imageCount":"2","sort":"6"},{"id":"21","dnames":"\u6536\u5165\u6765\u6e90","genre":"4","required":"0","imageCount":"0","sort":"9"}],"display":"0","categoryId":"5","id":"13","image":"http:\/\/211.155.230.114:4869\/3d86ef6fd9e8af7d3d9597f6c521315a"}', true);


$module_id = 401;
$user_id = 0;
$ip = '127.0.0.1';
$site_url = "admin.yhjr.com";
$params = json_decode('{"dnames":"\u674e\u56db","mobile":"15258965126","idCard":"362565985525412589","address":"\u4e49\u4e4c","bank":"\u4e2d\u56fd\u94f6\u884c","bankNo":"251236212523651","wxNo":"","zfbNo":"","logUserId":"107"}', true);




$json_Args = json_encode($args_array);

//$json_Args = msgpack_pack($args_array);

$request = new Services();
//$returnData = $request->getSiteInfo($json_Args);
//$returnData = $request->loadSystemInfo($json_Args);
$returnData = $request->operate($json_Args);

echo 'end:';
var_dump(time("Y-m-d H:i:s"));

print_r(json_decode($returnData));
//print_r(msgpack_unpack($returnData));
?>