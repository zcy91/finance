<?php
define("IS_DEBUG", 1);
require __DIR__ . '/services.php';
use Kuba\Gateway\Services;
use Kuba\Gateway\AccessModule;
function invoke($args,$route)
{
	try {
		$_kuba_args = array();
		
		$_kuba_args["kuba_route"] = $route;
		$_kuba_args["kuba_args"] = $args;
		
		$_SERVER["argv"][] = __FILE__;
		$_SERVER["argv"][] = $_kuba_args["kuba_route"];
		$_SERVER["argv"][] = $_kuba_args["kuba_args"];
		$_SERVER["argc"] = 3;
		
		require(__DIR__ . '/../logics/index.php');
		
		var_dump($application->ReturnData);
	}
	catch (Exception $e)
	{
		echo "E==>".$e->__toString();
	}
}

$timestamp = time();
$nonce=AccessModule::randomkeys(8);
//$tmpArr = array("b4c91401a360bfb", $timestamp, $nonce);
//$tmpArr = array("313f80646d41fbd", $timestamp, $nonce);
//$tmpArr = array("c1be5b14f468fcf", $timestamp, $nonce);
$tmpArr = array("9bbe4891f3f0093", $timestamp, $nonce);
sort($tmpArr, SORT_STRING);
$tmpStr = implode( $tmpArr );
$tmpStr = sha1( $tmpStr );


/////---------------------------
//$nonce=AccessModule::randomkeys(8);
//$tmpArr = array("9bb62797e81e2e7", 1445578982, '2mjl5366');
//sort($tmpArr, SORT_STRING);
//$tmpStr = implode( $tmpArr );
//$tmpStr = sha1( $tmpStr );
//echo $tmpStr;exit;
/////---------------------------

$params = array();

$sha1 = 0;
$seller_id = 0;
$ip = '';
$site_url = "p1.shoperp.cn";
$user_id = 1;
$shop_id = 1;

$args_array = array(
                "signature"=>$tmpStr,
                "timestamp"=>$timestamp,
                "nonce"=>$nonce,
                "customer_id"=>&$seller_id,
                "shop_id"=>&$shop_id,
                "user_id"=>&$user_id,
                "client_ip"=>&$ip,
                "site_url"=>&$site_url,
                "dis_lang_id"=>1,
                "sha1"=>&$sha1,
                "params"=>&$params
);


//$sha1 = 602;
//$seller_id = 17039360;
//$ip = "122.226.242.70";
//$params = json_decode('{"basic_info":{"seller_id":"17039360","cate_sys_alias_id":100007323,"goods_name":"pure Australia crystal women rings purple gem  zircon wedding brand engagement rings jewelry CZ classical BT0245","goods_no":"","type_goods":1,"unit_weight":40,"unit_sixze":52,"unit_qty":100000015,"manufacture_name":"","production_date":"","cost_price":0,"sale_price":"5.58","sale_state":1,"length":20,"width":20,"height":20,"warehouse_id":1,"store":0,"is_contain_std":0,"freighttemplate_id":"1000","currency_id":2,"wsValidNum":14,"deliveryTime":7},"category_shop_info":{"shop_id":"1","cate_shop_id":"1000"},"sys_attr_info":[{"attr_cate_sys_id":200000785,"sys_attr_item_info":[{"attr_item_cate_id":200003778}]},{"attr_cate_sys_id":200000137,"sys_attr_item_info":[{"attr_item_cate_id":361233}]},{"attr_cate_sys_id":200000631,"sys_attr_item_info":[{"attr_item_cate_id":349907}]},{"attr_cate_sys_id":20257,"sys_attr_item_info":[{"attr_item_cate_id":200001891}]},{"attr_cate_sys_id":326,"sys_attr_item_info":[{"attr_item_cate_id":200003768}]},{"attr_cate_sys_id":284,"sys_attr_item_info":[{"attr_item_cate_id":100006040}]},{"attr_cate_sys_id":20258,"sys_attr_item_info":[{"attr_item_cate_id":200000184}]},{"attr_cate_sys_id":10,"sys_attr_item_info":[{"attr_item_cate_id":1523}]},{"attr_cate_sys_id":1186,"sys_attr_item_info":[{"attr_item_cate_id":361242}]},{"attr_cate_sys_id":100005859,"sys_attr_item_info":[{"attr_item_cate_id":400}]},{"attr_cate_sys_id":200000784,"sys_attr_item_info":[{"attr_item_cate_id":353}]},{"attr_cate_sys_id":3,"goods_attr_value":"BT0245"}],"seller_attr_info":[{"attr_item_cate_id":"color","goods_attr_item_value":"purple"},{"attr_item_cate_id":"size","goods_attr_item_value":"17 18 19 mm"},{"attr_item_cate_id":"occasion","goods_attr_item_value":"engagement,gift,party,wedding"},{"attr_item_cate_id":"brand name","goods_attr_item_value":"Jooyoo"},{"attr_item_cate_id":"style","goods_attr_item_value":"romantic"},{"attr_item_cate_id":"weight","goods_attr_item_value":"4.4g"}],"sys_brand_info":{"brand_cate_alias_id":4,"goods_brand_value":"","sys_series_info":[{"series_brand_alias_id":""}]},"goods_pic_info":[{"image_url":"http:\/\/i02.i.aliimg.com\/img\/wsproduct\/19\/29\/14\/86\/1929148635_1.jpg?1430892000000","is_visibled":1},{"image_url":"http:\/\/i01.i.aliimg.com\/img\/wsproduct\/19\/29\/14\/86\/1929148635_2.jpg?1430892000000","is_visibled":1},{"image_url":"http:\/\/i00.i.aliimg.com\/img\/wsproduct\/19\/29\/14\/86\/1929148635_3.jpg?1430892000000","is_visibled":1}],"goods_std_info":[{"goods_sku_code":"","store":999,"weight":"300","cost_price":"5.58","sale_price":"5.58","std_mix_info":[{"std_cate_id":200000369,"specVal":null,"std_item_cate_id":3434},{"std_cate_id":200000783,"specVal":null,"std_item_cate_id":29}]},{"goods_sku_code":"","store":999,"weight":"300","cost_price":"5.58","sale_price":"5.58","std_mix_info":[{"std_cate_id":200000369,"specVal":null,"std_item_cate_id":3434},{"std_cate_id":200000783,"specVal":null,"std_item_cate_id":10}]},{"goods_sku_code":"","store":999,"weight":"300","cost_price":"5.58","sale_price":"5.58","std_mix_info":[{"std_cate_id":200000369,"specVal":null,"std_item_cate_id":3434},{"std_cate_id":200000783,"specVal":null,"std_item_cate_id":350852}]},{"goods_sku_code":"","store":999,"weight":"300","cost_price":"5.58","sale_price":"5.58","std_mix_info":[{"std_cate_id":200000369,"specVal":null,"std_item_cate_id":699},{"std_cate_id":200000783,"specVal":null,"std_item_cate_id":29}]},{"goods_sku_code":"","store":999,"weight":"300","cost_price":"5.58","sale_price":"5.58","std_mix_info":[{"std_cate_id":200000369,"specVal":null,"std_item_cate_id":699},{"std_cate_id":200000783,"specVal":null,"std_item_cate_id":10}]},{"goods_sku_code":"","store":999,"weight":"300","cost_price":"5.58","sale_price":"5.58","std_mix_info":[{"std_cate_id":200000369,"specVal":null,"std_item_cate_id":699},{"std_cate_id":200000783,"specVal":null,"std_item_cate_id":350852}]},{"goods_sku_code":"","store":999,"weight":"300","cost_price":"5.58","sale_price":"5.58","std_mix_info":[{"std_cate_id":200000369,"specVal":null,"std_item_cate_id":350262},{"std_cate_id":200000783,"specVal":null,"std_item_cate_id":29}]},{"goods_sku_code":"","store":999,"weight":"300","cost_price":"5.58","sale_price":"5.58","std_mix_info":[{"std_cate_id":200000369,"specVal":null,"std_item_cate_id":350262},{"std_cate_id":200000783,"specVal":null,"std_item_cate_id":10}]},{"goods_sku_code":"","store":999,"weight":"300","cost_price":"5.58","sale_price":"5.58","std_mix_info":[{"std_cate_id":200000369,"specVal":null,"std_item_cate_id":350262},{"std_cate_id":200000783,"specVal":null,"std_item_cate_id":350852}]}],"goods_desc_info":{"goods_name_alias":"pure Australia crystal women rings purple gem  zircon wedding brand engagement rings jewelry CZ classical BT0245","qty_store_alias":0,"weight_deliver_alias":"300","page_title":"pure Australia crystal women rings purple gem  zircon wedding brand engagement rings jewelry CZ classical BT0245","page_keywords":"gemstone jewelry","page_description":"pure Australia crystal women rings purple gem  zircon wedding brand engagement rings jewelry CZ classical BT0245"}}', true);

//$sha1 = 20;
//$seller_id = 17039360;
//$ip = "122.226.242.70";
//$params = json_decode('{"goods_desc_info":{"seller_id":"17039360","goods_id":"7390","shop_id":"1","goods_detail_desc":"&lt;p style=&quot;color:#ffffff;font-size:8.0pt;&quot;&gt;\r\n\t&lt;span style=&quot;font-family:times;color:#00429a;font-size:large;&quot;&gt;&lt;span style=&quot;color:#002cfd;&quot;&gt;&lt;strong&gt;&lt;span style=&quot;color:#002cfd;font-size:large;&quot;&gt;&lt;span&gt;&lt;span style=&quot;font-family:arial;font-size:14.0pt;&quot;&gt;&lt;span style=&quot;line-height:115.0%;font-family:arial , sans-serif;font-size:14.0pt;&quot;&gt;&lt;span style=&quot;line-height:115.0%;font-family:arial , sans-serif;font-size:14.0pt;&quot;&gt;Hello! Welcome to our store!&lt;\/span&gt;&lt;\/span&gt;&lt;\/span&gt;&lt;\/span&gt;&lt;\/span&gt;&lt;br \/&gt;\r\n&lt;br \/&gt;\r\n&lt;span style=&quot;color:#ff0010;&quot;&gt;&lt;span&gt;&lt;span style=&quot;font-family:arial;font-size:14.0pt;&quot;&gt;&lt;span style=&quot;line-height:115.0%;font-family:arial , sans-serif;font-size:14.0pt;&quot;&gt;&lt;span style=&quot;line-height:115.0%;font-family:arial , sans-serif;font-size:14.0pt;&quot;&gt;Quality is the first with best service. customers all are our friends.&lt;\/span&gt;&lt;\/span&gt;&lt;\/span&gt;&lt;\/span&gt;&lt;\/span&gt;&lt;\/strong&gt;&lt;br \/&gt;\r\n&lt;span&gt;&lt;span style=&quot;font-family:arial;color:olive;font-size:14.0pt;&quot;&gt;&lt;span style=&quot;line-height:115.0%;font-family:arial , sans-serif;color:olive;font-size:14.0pt;&quot;&gt;&lt;span style=&quot;line-height:115.0%;font-family:arial , sans-serif;color:black;font-size:14.0pt;&quot;&gt;&lt;span style=&quot;font-size:medium;&quot;&gt;&lt;strong&gt;Fashion design,100% Brand New,high quality!&lt;br \/&gt;\r\n&lt;span style=&quot;font-size:small;&quot;&gt;Material: &lt;\/span&gt;&lt;\/strong&gt;&lt;\/span&gt;&lt;\/span&gt;&lt;\/span&gt;&lt;\/span&gt;&lt;\/span&gt;&lt;\/span&gt;&lt;\/span&gt;&lt;span style=&quot;color:#000000;&quot;&gt;&lt;span style=&quot;font-family:arial;font-size:large;&quot;&gt;&lt;span style=&quot;font-size:14.0pt;&quot;&gt;&lt;span style=&quot;line-height:115.0%;font-size:14.0pt;&quot;&gt;&lt;span style=&quot;line-height:115.0%;font-size:14.0pt;&quot;&gt;&lt;span style=&quot;font-size:medium;&quot;&gt;&lt;span style=&quot;font-size:small;&quot;&gt;&lt;strong&gt;Cotton Blend,Lace&lt;\/strong&gt;&lt;\/span&gt;&lt;\/span&gt;&lt;\/span&gt;&lt;\/span&gt;&lt;\/span&gt;&lt;\/span&gt;&lt;\/span&gt;&lt;br \/&gt;\r\n&lt;span style=&quot;font-family:times;color:#00429a;font-size:large;&quot;&gt;&lt;span style=&quot;color:#002cfd;&quot;&gt;&lt;span&gt;&lt;span style=&quot;font-family:arial;color:olive;font-size:14.0pt;&quot;&gt;&lt;span style=&quot;line-height:115.0%;font-family:arial , sans-serif;color:olive;font-size:14.0pt;&quot;&gt;&lt;span style=&quot;line-height:115.0%;font-family:arial , sans-serif;color:black;font-size:14.0pt;&quot;&gt;&lt;span style=&quot;font-size:medium;&quot;&gt;&lt;span style=&quot;font-size:small;&quot;&gt;&lt;strong&gt;Color: White \/Gray &lt;\/strong&gt;&lt;\/span&gt;&lt;\/span&gt;&lt;\/span&gt;&lt;\/span&gt;&lt;\/span&gt;&lt;\/span&gt;&lt;\/span&gt;&lt;\/span&gt; \r\n&lt;\/p&gt;\r\n&lt;div align=&quot;left&quot;&gt;\r\n\t&lt;div align=&quot;center&quot;&gt;\r\n\t\t&lt;span style=&quot;font-size:16.0px;&quot;&gt;&lt;strong&gt;&lt;span style=&quot;font-family:times;color:#00429a;&quot;&gt;&lt;span style=&quot;color:#002cfd;&quot;&gt;&lt;span style=&quot;font-family:arial;color:olive;&quot;&gt;&lt;span style=&quot;line-height:115.0%;font-family:arial , sans-serif;color:olive;&quot;&gt;&lt;span style=&quot;line-height:115.0%;font-family:arial , sans-serif;color:black;&quot;&gt;&amp;nbsp;Size: S, M, L&lt;\/span&gt;&lt;\/span&gt;&lt;\/span&gt;&lt;\/span&gt;&lt;\/span&gt;, &lt;span style=&quot;font-family:arial;&quot;&gt;XL,XXL&lt;\/span&gt;&lt;\/strong&gt;&lt;\/span&gt; \r\n\t&lt;\/div&gt;\r\n&lt;\/div&gt;\r\n&lt;p style=&quot;text-align:center;&quot;&gt;\r\n\t&lt;b&gt;&lt;span style=&quot;line-height:115.0%;font-family:arial , sans-serif;color:black;font-size:14.0pt;&quot;&gt;&lt;span style=&quot;font-size:medium;&quot;&gt;&lt;span style=&quot;color:#ff0010;&quot;&gt;&lt;span style=&quot;color:#202020;&quot;&gt;There is 2-3% difference according to manual measurement.&lt;br \/&gt;\r\nplease check the measurement chart carefully before you buy the item.&lt;br \/&gt;\r\n1 inch = 2.54 cm&lt;\/span&gt;&lt;\/span&gt;&lt;\/span&gt;&lt;\/span&gt;&lt;\/b&gt; \r\n&lt;\/p&gt;\r\n&lt;p style=&quot;text-align:center;&quot;&gt;\r\n\t&lt;span style=&quot;color:#ff0000;&quot;&gt;&lt;span style=&quot;font-size:14.0px;&quot;&gt;(Please note that Asian Size is smaller than other sizes, and the dress is shortter than the model picture)&lt;\/span&gt;&lt;\/span&gt; \r\n&lt;\/p&gt;\r\n&lt;p&gt;\r\n\t&lt;strong&gt;&lt;img src=&quot;http:\/\/g04.a.alicdn.com\/kf\/HTB1MuTwGFXXXXXmXVXXq6xXFXXXZ\/205603029\/HTB1MuTwGFXXXXXmXVXXq6xXFXXXZ.jpg&quot; width=&quot;937&quot; \/&gt; &lt;\/strong&gt; \r\n&lt;\/p&gt;\r\n&lt;p style=&quot;color:#ffffff;font-size:8.0pt;&quot;&gt;\r\n\t&lt;b&gt;&lt;span style=&quot;line-height:115.0%;font-family:arial , sans-serif;color:black;font-size:14.0pt;&quot;&gt;&lt;span style=&quot;font-size:medium;&quot;&gt;&lt;span style=&quot;color:#ff0010;&quot;&gt;Please note that slight color difference should be acceptable due to the light and screen.&lt;br \/&gt;\r\n&lt;br \/&gt;\r\nWhat You Get:&lt;br \/&gt;\r\n1 x Fashion Dress&lt;\/span&gt;&lt;\/span&gt;&lt;\/span&gt;&lt;\/b&gt; \r\n&lt;\/p&gt;\r\n&lt;p style=&quot;text-align:center;&quot; align=&quot;center&quot;&gt;\r\n\t&amp;nbsp;\r\n&lt;\/p&gt;\r\n&lt;p style=&quot;text-align:center;&quot; align=&quot;center&quot;&gt;\r\n\t&lt;span style=&quot;font-family:arial;color:#002cfd;font-size:medium;&quot;&gt;&lt;b&gt;&lt;span&gt;&lt;span style=&quot;font-family:arial;color:olive;font-size:14.0pt;&quot;&gt;&lt;span style=&quot;line-height:115.0%;font-family:arial , sans-serif;color:olive;font-size:14.0pt;&quot;&gt;&lt;span style=&quot;color:#002cfd;&quot;&gt;&lt;b&gt;&lt;span style=&quot;line-height:115.0%;font-family:arial , sans-serif;font-size:14.0pt;&quot;&gt;Preview&lt;\/span&gt;&lt;\/b&gt;&lt;\/span&gt;&lt;\/span&gt;&lt;\/span&gt;&lt;\/span&gt;:&lt;\/b&gt;&lt;\/span&gt; \r\n&lt;\/p&gt;\r\n&lt;p style=&quot;text-align:center;&quot; align=&quot;center&quot;&gt;\r\n\t&amp;nbsp;\r\n&lt;\/p&gt;\r\n&lt;img src=&quot;http:\/\/g01.a.alicdn.com\/kf\/HTB1GJnxGFXXXXagXFXXq6xXFXXXJ\/205603029\/HTB1GJnxGFXXXXagXFXXq6xXFXXXJ.jpg&quot; \/&gt; &lt;br \/&gt;\r\n&lt;img alt=&quot;3&quot; src=&quot;http:\/\/g02.a.alicdn.com\/kf\/HTB1z.dOHVXXXXaSXVXXq6xXFXXX4\/205603029\/HTB1z.dOHVXXXXaSXVXXq6xXFXXX4.jpg?size=228606&amp;amp;height=1000&amp;amp;width=1000&amp;amp;hash=ca17031f46145b859fb84a2ea38ff418&quot; \/&gt; &lt;img alt=&quot;4&quot; src=&quot;http:\/\/g02.a.alicdn.com\/kf\/HTB1zpGeHVXXXXaEXXXXq6xXFXXX6\/205603029\/HTB1zpGeHVXXXXaEXXXXq6xXFXXX6.jpg?size=203993&amp;amp;height=1000&amp;amp;width=1000&amp;amp;hash=83d24c990b0dc5d530e4ca2edb56b2ce&quot; \/&gt; &lt;img alt=&quot;9&quot; src=&quot;http:\/\/g03.a.alicdn.com\/kf\/HTB1lmFOHVXXXXa9XVXXq6xXFXXXP\/205603029\/HTB1lmFOHVXXXXa9XVXXq6xXFXXXP.jpg?size=199936&amp;amp;height=1000&amp;amp;width=1000&amp;amp;hash=22f0bdb821ef3f5fd3fc1c3afdfddfb2&quot; \/&gt; &lt;img alt=&quot;11&quot; src=&quot;http:\/\/g01.a.alicdn.com\/kf\/HTB1FbNVHVXXXXctXFXXq6xXFXXXF\/205603029\/HTB1FbNVHVXXXXctXFXXq6xXFXXXF.jpg?size=170918&amp;amp;height=1000&amp;amp;width=1000&amp;amp;hash=eb8a545794c0f65b7c37ae9c9bee2a6c&quot; \/&gt; &lt;img alt=&quot;12&quot; src=&quot;http:\/\/g03.a.alicdn.com\/kf\/HTB1LOlKHVXXXXcMXVXXq6xXFXXXY\/205603029\/HTB1LOlKHVXXXXcMXVXXq6xXFXXXY.jpg?size=172973&amp;amp;height=1000&amp;amp;width=1000&amp;amp;hash=34e037f66d1641cb63fda2c067f596ea&quot; \/&gt; &lt;img alt=&quot;8&quot; src=&quot;http:\/\/g02.a.alicdn.com\/kf\/HTB1VuagHVXXXXXAXXXXq6xXFXXXm\/205603029\/HTB1VuagHVXXXXXAXXXXq6xXFXXXm.jpg?size=287177&amp;amp;height=1000&amp;amp;width=1000&amp;amp;hash=e25646255f3ab7dc13065117736e1b68&quot; \/&gt; &lt;img alt=&quot;6&quot; src=&quot;http:\/\/g01.a.alicdn.com\/kf\/HTB1YFBVHVXXXXcZXFXXq6xXFXXXl\/205603029\/HTB1YFBVHVXXXXcZXFXXq6xXFXXXl.jpg?size=224639&amp;amp;height=1000&amp;amp;width=1000&amp;amp;hash=cc9b4c40cacb172bd93abb6ea453242d&quot; \/&gt; &lt;img alt=&quot;5&quot; src=&quot;http:\/\/g02.a.alicdn.com\/kf\/HTB1xUt3HVXXXXafXFXXq6xXFXXXz\/205603029\/HTB1xUt3HVXXXXafXFXXq6xXFXXXz.jpg?size=394274&amp;amp;height=1000&amp;amp;width=1000&amp;amp;hash=bee53cb073000ec01575a34ee0c38f21&quot; \/&gt; &lt;img alt=&quot;1&quot; src=&quot;http:\/\/g03.a.alicdn.com\/kf\/HTB1VQ05HVXXXXc7XpXXq6xXFXXXM\/205603029\/HTB1VQ05HVXXXXc7XpXXq6xXFXXXM.jpg?size=219692&amp;amp;height=1000&amp;amp;width=1000&amp;amp;hash=5849722050d97867c0e578b9583e1402&quot; \/&gt; &lt;img alt=&quot;14&quot; src=&quot;http:\/\/g01.a.alicdn.com\/kf\/HTB184edHVXXXXaVXXXXq6xXFXXXA\/205603029\/HTB184edHVXXXXaVXXXXq6xXFXXXA.jpg?size=193161&amp;amp;height=1000&amp;amp;width=1000&amp;amp;hash=476fa3945907749bee4aa51934d05a51&quot; \/&gt; &lt;img alt=&quot;7&quot; src=&quot;http:\/\/g03.a.alicdn.com\/kf\/HTB1EUX1HVXXXXaSXFXXq6xXFXXX1\/205603029\/HTB1EUX1HVXXXXaSXFXXq6xXFXXX1.jpg?size=301574&amp;amp;height=1000&amp;amp;width=1000&amp;amp;hash=6f90aea5ef822b8949604390f1daf2cc&quot; \/&gt; &lt;img alt=&quot;15&quot; src=&quot;http:\/\/g03.a.alicdn.com\/kf\/HTB1wzpMHVXXXXbLXVXXq6xXFXXXJ\/205603029\/HTB1wzpMHVXXXXbLXVXXq6xXFXXXJ.jpg?size=153708&amp;amp;height=854&amp;amp;width=1000&amp;amp;hash=adb384cd4a61d048d612025aee8bcb22&quot; \/&gt; &lt;img alt=&quot;13&quot; src=&quot;http:\/\/g03.a.alicdn.com\/kf\/HTB1Zj8KHVXXXXXfaXXXq6xXFXXX5\/205603029\/HTB1Zj8KHVXXXXXfaXXXq6xXFXXX5.jpg?size=227197&amp;amp;height=1000&amp;amp;width=1000&amp;amp;hash=8f1df627c0d2d4c2b7a791ee3d44691f&quot; \/&gt;&lt;br \/&gt;","dis_lang_id":"2","is_update":0}}', true);

/*
$sha1 = 575;
$seller_id = 17170432;
$ip = '116.193.49.234';
$params = json_decode('{"seller_id":17039360,"shop_id":1,"user_id":"165","euser_id":"165","customer_id":"17170432","bonus_amount":0,"down_amount":0,"booking_id":10,"template_id":0,"pay_type":22,"deliver_id":3,"deliver_price":0,"memo":"","detail":[{"goods_id":"278","std_goods_id":"0","order_qty":"11","sale_price":"10.00","template_id":"3","express_id":"14","bonus":0,"cart_uuid":"","sub_id":0}]}', true);
*/

/*
$sha1 = 20;
$seller_id = 16777216;
$ip = '122.226.242.70';
$params = json_decode('{"goods_desc_info":{"seller_id":"16777216","goods_id":"1","shop_id":"1","goods_detail_desc":"&lt;img alt=&quot;&quot; src=&quot;http:\/\/211.155.231.22:4869\/10474c6159e3abde7ff8a921c0fb5471&quot; \/&gt;&lt;img alt=&quot;&quot; src=&quot;http:\/\/211.155.231.22:4869\/ b81c4840ec9e4dc21585f433ad6feb8f&quot; \/&gt;&lt;img alt=&quot;&quot; src=&quot;http:\/\/211.155.231.22:4869\/04971ddad816781a4567a817d1c49d8a&quot; \/&gt;&lt;img alt=&quot; &quot; src=&quot;http:\/\/211.155.231.22:4869\/f0dd86e8cb57f1f7c18ba6d9b6e07eb3&quot; \/&gt;","dis_lang_id":"1","is_update":0}}', true);
*/
echo 'begin:';
var_dump(time("Y-m-d H:i:s"));

//删除店铺分类
$sha1 = 68;
$seller_id = 17039360;
$ip = '122.226.242.70';
$params = json_decode('{"condition":"seller_id=:seller_id and shop_id=:shop_id and cate_shop_id=:id","params_list":{":seller_id":"17039360",":shop_id":"1",":id":33554432}}', true);

//查询友情链接
$sha1 = 151;
$seller_id = 17039360;
$ip = '122.226.242.70';
$params = json_decode('{"condition":"seller_id=:seller_id and shop_id=:shop_id","params_list":{":seller_id":"17039360",":shop_id":"1"},"pagination":{"pagesize":5,"pageindex":1,"recordcount":0}}', true);

//查询店铺分类父分类
$sha1 = 52;
$seller_id = 17039360;
$ip = '122.226.242.70';
$params = json_decode('{"condition":"seller_id=:seller_id and shop_id=:shop_id","params_list":{":seller_id":"16777216",":shop_id":"1"},"cate_shop_id":"","return_self":1}', true);



//删除店铺分类
$sha1 = 68;
$seller_id = 17039360;
$ip = '122.226.242.70';
$params = json_decode('{"condition":"seller_id=:seller_id and shop_id=:shop_id and cate_shop_id=:id","params_list":{":seller_id":"17039360",":shop_id":"1",":id":540016640}}', true);

//同步速卖通订单
$sha1 = 876;
$seller_id = 16973824;
$ip = '127.0.0.1';
$user_id = 0;
$site_url = "flb.fashion-jewelry-suppliers.com";

$params = json_decode('{"user_info":{"buyerInfo":{"lastName":"Babakhanian","loginId":"us1094209737","email":null,"firstName":"Olga","country":"US"},"receiptAddress":{"zip":"91604","phoneNumber":"8745250","province":"California","phoneArea":"310","phoneCountry":"1","contactPerson":"Olga Babakhanian","mobileNo":"3108745250","detailAddress":"12526 Ventura Blvd Aesthetic Skin Care Studio City,California,United States","country":"US","city":"Studio City"}},"logisticInfo":{"logisticsTypeCode":"EMS_ZX_ZX_US","gmtReceived":"","logisticsServiceName":"ePacket","logisticsNo":"","gmtSend":"","receiveStatus":""},"logisticsAmount":{"amount":"0.00","cent":"","currencyCode":"USD","currency":{"defaultFractionDigits":"2","currencyCode":"USD","symbol":"$"}},"order_basic":{"seller_id":"16973824","shop_id":"1","user_id":"0","order_id":"82320133496209","initOderAmount":"21.98","logisticsAmount":"0.00","fundStatus":null,"frozenStatus":null,"issueStatus":null,"gmtcreate":"2017-03-02 01:43:51","gmtTradeEnd":"","vendor_shop_id":"2939783936","vendor_shop_name":"qq","pay_time":"2017-03-02 01:51:07","remaining_time":"2017-03-13 21:36:52","orderMsgList":[]},"goods_detail":[{"productId":"1582","sku":"0","productName":"50 pcs Permanent Makeup Repair Gel Tattoo Nursing Ointment A&D Anti Scar Tattoo Aftercare Cream for Eyebrow and Lips","quantity":"1","unitPrice":"21.98"}]}', true);


//同步速卖通订单
$sha1 = 616;
$seller_id = 805306368;
$ip = '127.0.0.1';
$user_id = 0;
$site_url = "www.ed400.com";

$params = json_decode('{"condition":"","params_list":{":user_name":"",":password":"d41d8cd98f00b204e9800998ecf8427e"}}', true);

//实名认证
$sha1 = 918;
$seller_id = 805371934;
$ip = '127.0.0.1';
$user_id = 2058;
$site_url = "805371934.ed400.com";

$params = json_decode('{"condition":"","SellerSysData":{"seller_id":"805371934","user_id":"2058","seller_name":"\u4e2a\u4eba","legal_person":"\u534e\u91d1\u8f89","auth_file1":"http:\/\/119.37.197.41:4869\/a7d35fde8eb5f13f384c8439f8e9613b","auth_file9":"http:\/\/119.37.197.41:4869\/7eb47d35bde935d7bee2630953451314","auth_file4":"http:\/\/119.37.197.41:4869\/b183336fafc837f81c4f927d581a0454"}}', true);




$sha1 = 605;
$seller_id = 805306389;
$ip = '127.0.0.1';
$user_id = 0;
$site_url = "8053063893.wanghuo.hk";

$params = json_decode('{"condition":"seller_id=:seller_id and shop_id=:shop_id","params_list":{":seller_id":"805306389",":shop_id":"3"},"search_list":{"dis_lang_id":"1"}}', true);

$sha1 = 836;
$seller_id = 805306389;
$ip = '127.0.0.1';
$user_id = 2453;
$site_url = "805306389.ed400.com";
$params = json_decode('{"search_list":{"seller_id":"805306389","shop_id":3,"order_type":{"begin_date":"2017-5-22","end_date":"2017-05-28"}},"pagination":{"pagesize":"","pageindex":0,"recordcount":0}}', true);

$sha1 = 43;
$seller_id = 805306388;
$ip = '127.0.0.1';
$user_id = 11;
$site_url = "805306388.ed400.com";
$params = json_decode('{"condition":"","params_list":{":user_name":"18757936028",":password":"e10adc3949ba59abbe56e057f20f883e",":seller_id":"805306388","login_client_ip":"122.226.242.70","login_client_ip_area":"\u4e2d\u56fd \u6d59\u6c5f\u7701 \u91d1\u534e\u5e02"}}', true);


$sha1 = 124;
$seller_id = 805306409;
$ip = '127.0.0.1';
$user_id = 2550;
$site_url = "805306409.ed400.com";
$params = json_decode('{"UserShopperData":{"account_pwd":"e10adc3949ba59abbe56e057f20f883e","nick_name":"ceshi","email_address":"ceshi@123.com"}}', true);

$sha1 = 955;
$seller_id = 805306389;
$ip = '127.0.0.1';
$user_id = 2453;
$site_url = "805306389.ed400.com";
$params = json_decode('{"seller_id":"805306389","shop_id":"4","order_uuid":"5371819480082035016","op_user_id":"2453","warehouse_id":"1581","detail":[{"sub_id":"1","stock_qty":"1"}]}', true);

$sha1 = 947;
$seller_id = 805306389;
$ip = '127.0.0.1';
$user_id = 2453;
$site_url = "805306389.ed400.com";
$params = json_decode('{"seller_id":"805306388","shop_id":"4","order_uuid":"7456540444031560432","check_step":"1","op_user_id":"2452","detail":[{"sub_id":"1","old_sale_price":"1458.02"},{"sub_id":"2","old_sale_price":"311045.65"},{"sub_id":"3","old_sale_price":"17496.33"}]}', true);

$sha1 = 979;
$seller_id = 805306389;
$ip = '127.0.0.1';
$user_id = 2453;
$site_url = "805306389.ed400.com";
$params = json_decode('{"search_list":{"seller_id":"805306389","shop_id":"4","order_start_time":"","order_end_time":"","start_time":"","end_time":"","customer_id":"155","order_no":"","goods_name":"","pay_status":""}}', true);

$sha1 = 991;
$seller_id = 805306388;
$ip = '127.0.0.1';
$user_id = 2453;
$site_url = "805306388.ed400.com";
$params = json_decode('{"search_list":{"seller_id":"805306388","shop_id":"1","warehouse_id":"1580"}}', true);


$sha1 = 1000;
$seller_id = 805306422;
$ip = '127.0.0.1';
$user_id = 2453;
$site_url = "805306422.ed400.com";
$params = json_decode('{"seller_id":"805306422","dis_lang_id":"2","std_info":[{"std_id":"0","std_name":"\u5c3a\u5bf8","std_items":[{"std_item_id":"0","std_item_name":"ML"}]},{"std_id":"0","std_name":"\u989c\u8272","std_items":[{"std_item_id":"0","std_item_name":"\u7ea2\u8272"},{"std_item_id":"0","std_item_name":"\u9ec4\u8272"},{"std_item_id":"0","std_item_name":"\u9ed1\u8272"}]}]}', true);


$sha1 = 1000;
$seller_id = 805306388;
$ip = '127.0.0.1';
$user_id = 2452;
$site_url = "805306388.ed400.com";
$params = json_decode('{"seller_id":"805306388","dis_lang_id":"1","std_info":[{"std_id":"2087","std_name":"\u5c3a\u5bf8","std_items":[{"std_item_id":"37302","std_item_name":"XL"},{"std_item_id":"37303","std_item_name":"ML"},{"std_item_id":"37307","std_item_name":"MML"}]},{"std_id":"0","std_name":"\u6750\u8d28","std_items":[{"std_item_id":"37313","std_item_name":"\u4e0d\u9508\u94a2"},{"std_item_id":"0","std_item_name":"\u94c1"},{"std_item_id":"0","std_item_name":"\u94dd"}]}]}', true);

$sha1 = 837;
$seller_id = 805306388;
$ip = '127.0.0.1';
$user_id = 2452;
$site_url = "805306388.ed400.com";
$params = json_decode('{"seller_id":"805306388","shop_id":"4","customer_id":0,"cart_uuid":"0254b548-1a77-421c-9541-2e546c57e3e4","session_id":"eccdrbrgr2d76206uangko32t7","memo":"\u626b\u7801\u4e0b\u5355\u5546\u54c1","user_id":0,"from_scan":1,"detail":[{"goods_id":"103","std_goods_id":"99719","order_qty":"1","sale_price":"89.50","sale_unit_id":"54","sale_unit_name":"\u4e2a"}]}', true);

$sha1 = 81;
$seller_id = 805306429;
$ip = '127.0.0.1';
$user_id = 3522;
$site_url = "805306429.ed400.com";
$params = json_decode('{"seller_id":"805306429","shop_id":"2","customer_id":4,"cart_uuid":"dd202789-9c9d-45b0-a3ab-0f5d988f66bb","session_id":"6ijkve30679eirpgojkp3en9p3","memo":"","user_id":3522,"detail":[{"customer_id":4,"goods_id":"27","std_goods_id":"99829","order_qty":"20"}]}', true);

$sha1 = 10;
$seller_id = 805306442;
$ip = '127.0.0.1';
$user_id = 3522;
$site_url = "805306442.ed400.com";
$params = json_decode('{"sys_std_info":[{"std_cate_id":"2121","input_type":2,"sys_std_item_info":["37357","37358"]},{"std_cate_id":"2122","input_type":2,"sys_std_item_info":["37358"]},{"std_cate_id":"2123","input_type":2,"sys_std_item_info":["37359"]},{"std_cate_id":"2124","input_type":2,"sys_std_item_info":["37359"]}],"goods_std_info":[{"cost_price":"1","sale_price":"3.6","store":"","weight":"2","goods_sku_code":1,"moq":1,"front_code":"","spec_img":"","std_mix_info":[{"std_item_alias_name":"40\u9897","std_cate_id":"2121","specVal":"40\u9897","std_item_cate_id":"37357"},{"std_item_alias_name":"45\u9897","std_cate_id":"2122","specVal":"45\u9897","std_item_cate_id":"37358"},{"std_item_alias_name":"1\u5757","std_cate_id":"2123","specVal":"1\u5757","std_item_cate_id":"37359"},{"std_item_alias_name":"1\u5757","std_cate_id":"2124","specVal":"1\u5757","std_item_cate_id":"37359"}]},{"cost_price":"1","sale_price":"3.8","store":"","weight":"2","goods_sku_code":2,"moq":1,"front_code":"","spec_img":"","std_mix_info":[{"std_item_alias_name":"45\u9897","std_cate_id":"2121","specVal":"45\u9897","std_item_cate_id":"37358"},{"std_item_alias_name":"45\u9897","std_cate_id":"2122","specVal":"45\u9897","std_item_cate_id":"37358"},{"std_item_alias_name":"1\u5757","std_cate_id":"2123","specVal":"1\u5757","std_item_cate_id":"37359"},{"std_item_alias_name":"1\u5757","std_cate_id":"2124","specVal":"1\u5757","std_item_cate_id":"37359"}]}],"basic_info":{"seller_id":"805306442","cate_sys_alias_id":3391,"goods_name":"\u9506\u77f3\u6212\u6307","goods_no":"22","barcode":"","type_goods":1,"unit_weight":0,"unit_sixze":69,"unit_qty":54,"qty_per_case":1,"sales_unit":1,"purchase_unit":1,"manufacture_name":"","production_date":"0000-00-00","cost_price":"1","sale_price":"3.6","sale_state":0,"goods_style":2,"length":0,"width":0,"height":0,"warehouse_id":"1760","warehouse_storage_id":1,"freighttemplate_id":8,"store":"","moq":1,"show_company_price":"","is_contain_std":1},"category_shop_info":{"shop_id":"1","cate_shop_id":16777216,"dis_lang_id":"1"},"seller_attr_info":[{"attr_cust_id":1,"sys_attr_item_info":[{"attr_item_cust_id":0}]}],"relation_goods_info":"","relation_goods_supplier_info":"","goods_pic_info":[{"image_url":"http:\/\/119.37.197.41:4869\/8a5be19f0cf4c13787c76134fce07a08","is_visibled":1}],"goods_desc_info":{"goods_name_alias":"\u9506\u77f3\u6212\u6307","qty_store_alias":0,"weight_deliver_alias":1,"net_weight":1,"page_title":"\u9506\u77f3\u6212\u6307","page_keywords":null,"page_description":""}}', true);

$sha1 = 73;
$seller_id = 805306447;
$ip = '127.0.0.1';
$user_id = 3522;
$site_url = "805306447.ed400.com";
$params = json_decode('{"sys_std_info":[{"std_cate_id":"2137","input_type":2,"sys_std_item_info":["37378"]},{"std_cate_id":"2138","input_type":2,"sys_std_item_info":["37379","37380"]}],"goods_std_info":[{"cost_price":"12.00","sale_price":"34.00","store":"44","weight":"4","goods_sku_code":1,"moq":1,"front_code":"5677","spec_img":"","std_goods_id":"99900","std_mix_info":[{"std_item_alias_name":"\u9ec4\u8272","std_cate_id":"2137","specVal":"\u9ec4\u8272","std_item_cate_id":"37378"},{"std_item_alias_name":"xl","std_cate_id":"2138","specVal":"xl","std_item_cate_id":"37379"}]},{"cost_price":"13.00","sale_price":"39.00","store":"45","weight":"4","goods_sku_code":2,"moq":1,"front_code":"5677","spec_img":"","std_goods_id":"99901","std_mix_info":[{"std_item_alias_name":"\u9ec4\u8272","std_cate_id":"2137","specVal":"\u9ec4\u8272","std_item_cate_id":"37378"},{"std_item_alias_name":"ml","std_cate_id":"2138","specVal":"ml","std_item_cate_id":"37380"}]}],"basic_info":{"seller_id":"805306447","cate_sys_alias_id":3391,"goods_name":"\u6d4b\u8bd5\u5546\u54c1202","goods_no":"5677","barcode":"","type_goods":1,"unit_weight":0,"unit_qty":54,"manufacture_name":"","production_date":"0000-00-00","cost_price":["12.00","13.00"],"sale_price":["34.00","39.00"],"sale_state":1,"goods_style":2,"length":0,"width":0,"height":0,"warehouse_id":"1765","warehouse_storage_id":1,"freighttemplate_id":58,"store":0,"moq":1,"show_company_price":"","qty_per_case":1,"unit_sixze":54,"sales_unit":1,"purchase_unit":1,"is_contain_std":0,"goods_id":28},"category_shop_info":{"shop_id":"4","cate_shop_id":0,"dis_lang_id":"1"},"seller_attr_info":[{"attr_cust_id":1,"sys_attr_item_info":[{"attr_item_cust_id":0}]}],"relation_goods_info":"","relation_goods_supplier_info":"","goods_pic_info":[],"goods_desc_info":{"goods_name_alias":"\u6d4b\u8bd5\u5546\u54c1202","qty_store_alias":0,"weight_deliver_alias":4,"net_weight":4,"page_title":"\u6d4b\u8bd5\u5546\u54c1202","page_keywords":null,"page_description":""}}', true);

//赠送
$sha1 = 922;
$seller_id = 184549376;
$ip = '127.0.0.1';
$user_id = 2710;
$site_url = "data.albbj.com";
$params = json_decode('{"ShopBaseData":{"seller_id":"185139200","shop_id":"2","num_years":"1","create_time":"2018-02-05 09:25:34","period_time":"2018-02-06 10:37:00","type":1}}', true);

//$json_Args = json_encode($args_array);
$json_Args = msgpack_pack($args_array);
	
$request = new Services();

//$returnData = $request->load_system_info();

//$args_site_info = msgpack_pack(array(
//	"site_url"=>"www.99pinpin.com",
//    "client_ip"=>"127.0.0.1"
//));

//$returnData = $request->get_site_info($args_site_info);

//$args_load_system_info = msgpack_pack(array("site_url"=>"s13.shoperp.cn", "client_ip"=>"127.0.0.1"));

//$returnData = $request->get_site_info($args_site_info);
//$returnData = $request->load_system_info($args_load_system_info);
$returnData = $request->operate($json_Args);

echo 'end:';
var_dump(time("Y-m-d H:i:s"));

//print_r(json_decode($returnData));
print_r(msgpack_unpack($returnData));



?>