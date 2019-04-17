<?php
return array(
	//'配置项'=>'配置值'
    'URL_CASE_INSENSITIVE'  =>  true,       //不区分大小写
    'URL_MODEL'             =>  2,       	// URL访问模式,可选参数0、1、2、3,代表以下四种模式：
    // 0 (普通模式); 1 (PATHINFO 模式); 2 (REWRITE  模式); 3 (兼容模式)  默认为PATHINFO 模式
    'URL_HTML_SUFFIX'       =>  'html',  	// URL伪静态后缀设置
    'URL_ROUTER_ON'   => true, 
    'URL_ROUTE_RULES'       =>  array(		//优先匹配单页路由，再匹配控制器路由，注意避免冲突
        //'/^do_login_out/'       => 'Index/do_login_out',   //登出
        '/^g\/([\w]+)$/'	=> 'Goods/:1',                     //商品管理
        '/^u\/([\w]+)$/'	=> 'User/:1',                      //会员管理
        '/^o\/([\w]+)$/'	=> 'Order/:1',                     //订单管理
    ),
);