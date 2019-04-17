<?php
return array(
    'LOAD_EXT_CONFIG' 		=> 'siteInfo',
    //'配置项'=>'配置值'
    'MODULE_ALLOW_LIST'    =>    array('Home','SmallProgram'),  //可访问模块
    'DEFAULT_MODULE'       =>    'Home',   //默认模块
    
    
    
    'SERVICE_URL'       =>  'http://api.yhjr.com/gateway/services.php',  //接口访问路径 
    
    //memcache设置
    'DATA_CACHE_TYPE' => 'Memcache',
    'DB_ALIAS_CACHE' => 'newshoperpflb',//自定义缓存前缀
    'MEMCACHE_HOST'   =>  '192.168.0.135',
    'MEMCACHE_PORT' => '11211',
    'DATA_CACHE_TIME' =>  86400,      			// 数据缓存有效期 0表示永久缓存，开发阶段设置默认5分钟
    
    //图片服务器地址
    'IMAGE_SERVER_URL' => "http://211.155.230.114:4869",
);