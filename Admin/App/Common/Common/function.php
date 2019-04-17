<?php
//快捷输出各种格式内容2014-12-12@Alei
function p($a){
	echo '<pre>';
	if($a == '' || $a === NULL || $a === false || $a === 0 || (is_array($a)&&count($a) == 0) || $a === true)
		var_dump($a);
	else
		print_r($a);
	echo '</pre>';
}




//调用接口
function c_call_service($p_module,$p_class,$p_fun,$p_params='', $access_type=1)
{
    $proxy = new \Org\Api\ServiceProxy($p_module, $p_class, $p_fun, $p_params, $access_type);
    return $proxy->getData();
}

/**
 * 获取站点URL
 * @return type
 */
function c_get_site_url()
{
    return c_host_url();
}


function c_host_url()
{
    $site_url = $_SERVER['HTTP_HOST'];

    return c_parse_host($site_url) ;
}

/**
* 或的url的host
* 2013年4月26日20:33:25
* 2013年5月9日20:28:05
*/
function c_parse_host($url)
{
    if(!is_string($url) || $url==''){return "";}

    $info=parse_url($url);


    $host=isset($info['host'])?$info['host']:(isset($info['path']) ? $info['path'] : "" );
    if($host==""){return "";}

    if(preg_match('/^192\.168\.\d{1,3}\.\d{1,3}¦127\.\d{1,3}\.\d{1,3}\.\d{1,3}¦255\.\d{1,3}\.\d{1,3}\.\d{1,3}$/',$host)){return "";}
    if(!preg_match('/\.[a-z]+$/i',$host) && !preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/",$host)){return "";}
    return $host;
}


/**
 * 获取访问终端的IP
 * @return type
 */
function c_get_client_ip()
{
    return c_get_ip();
}

function c_get_ip(){
    
    global $ip;
    if (getenv("HTTP_CLIENT_IP")){
            $ip = getenv("HTTP_CLIENT_IP");
    }
    else if(getenv("HTTP_X_FORWARDED_FOR")){
            $ip = getenv("HTTP_X_FORWARDED_FOR");
    }
    else if(getenv("REMOTE_ADDR")){
            $ip = getenv("REMOTE_ADDR");
    }
    else {
            $ip = "Unknow";
    }

    return $ip;
}


function c_add_cache_key($name,$value,$options = 5)
{
    $name = C('DB_ALIAS_CACHE').'_'.$name;    
            
    static $cache   =   '';
    if(is_array($options) && empty($cache)){
        // 缓存操作的同时初始化
        $type       =   isset($options['type'])?$options['type']:'';
        $cache      =   Think\Cache::getInstance($type,$options);
    }elseif(is_array($name)) { // 缓存初始化
        $type       =   isset($name['type'])?$name['type']:'';
        $cache      =   Think\Cache::getInstance($type,$name);
        return $cache;
    }elseif(empty($cache)) { // 自动初始化
        $cache      =   Think\Cache::getInstance();
    }
    
    if(is_array($options)) {
        $expire     =   isset($options['expire'])?$options['expire']:NULL;
    }else{
        $expire     =   is_numeric($options)?$options:NULL;
    }
    return $cache->add($name, $value, $expire);   
}

/**
 * 添加数据到缓存
 * 自增子
 * 自增不影响key的生存周期
 * @staticvar string $cache
 * @param string $name
 * @param type $value
 * @param type $options
 * @return type
 */
function c_incr_cache_key($name,$value=1,$options=null)
{
    $name = C('DB_ALIAS_CACHE').'_'.$name;    
            
    static $cache   =   '';
    if(is_array($options) && empty($cache)){
        // 缓存操作的同时初始化
        $type       =   isset($options['type'])?$options['type']:'';
        $cache      =   Think\Cache::getInstance($type,$options);
    }elseif(is_array($name)) { // 缓存初始化
        $type       =   isset($name['type'])?$name['type']:'';
        $cache      =   Think\Cache::getInstance($type,$name);
        return $cache;
    }elseif(empty($cache)) { // 自动初始化
        $cache      =   Think\Cache::getInstance();
    }
    
    if(is_array($options)) {
        $expire     =   isset($options['expire'])?$options['expire']:NULL;
    }else{
        $expire     =   is_numeric($options)?$options:NULL;
    }
    return $cache->incr($name, $value);   
}

/*
	功能：返回你要的尺寸大小图片
	$path 图片路径地址
	$w 裁剪的宽  $h 裁剪的高度
*/
function c_get_match_site($path,$w=50,$h=50,$is_ad=0){
    
    if(empty($path))
    {
        return '/Public/Common/images/waityou.jpg';
    }
    
    $info = [];
    preg_match('@119.37.197.41|i00.i.aliimg.com|i02.i.aliimg.com|i03.i.aliimg.com|i04.i.aliimg.com|i05.i.aliimg.com|i01.i.aliimg.com|alicdn.com\/kf|alicdn.com\/img|cub1.alicdn.com|cub2.alicdn.com|cub3.alicdn.com|cub4.alicdn.com|img.china.alibaba.com@', $path,$info);
    $match_str = $info[0];
    $path_arr = explode("?",$path);
    $return_path = $path_arr[0];
    
    if(!empty($info)){
		switch($match_str){
			case "119.37.197.41": 
                            $q = $is_ad == 1 ? 100 : 65;
                            $return_path.="?q=".$q."&w=".$w."&h=".$h; 
                            break; //图片服务器
			case 'alicdn.com/kf': 
//                            $return_path.="_".$w."x".$h.".jpg"; 
//                            break; //速卖通图片
			case 'i00.i.aliimg.com': 
			case 'i01.i.aliimg.com': 
                        case 'i02.i.aliimg.com':  
                        case 'i03.i.aliimg.com': 
                        case 'i04.i.aliimg.com':
                        case 'i05.i.aliimg.com':
                            if($w<=50)
                            {
                                $w = 50;
                                $h = 50;
                            }
                            else if($w<=100)
                            {                                
                                $w = 100;
                                $h = 100;
                            }
                            else if($w<=150)
                            {                                
                                $w = 150;
                                $h = 150;
                            }
                            else if($w<=200)
                            {                                
                                $w = 200;
                                $h = 200;
                            }
                            else if($w<=250)
                            {                                
                                $w = 250;
                                $h = 250;
                            }
                            else
                            {                                
                                $w = 350;
                                $h = 350;
                            }                            
                            
                            $return_path.="_".$w."x".$h.".jpg"; break;//速卖通图片
			case 'alicdn.com/img': //$return_path; break;//1688图片
                        case 'cub1.alicdn.com': //$return_path; break;//1688图片
                        case 'cub2.alicdn.com': //$return_path; break;//1688图片
                        case 'cub3.alicdn.com': //$return_path; break;//1688图片
                        case 'cub4.alicdn.com': 
                        case 'img.china.alibaba.com':
                            if($w<=64)
                            {
                                $w = 64;
                                $h = 64;
                            }
                            else if($w<=100)
                            {                                
                                $w = 100;
                                $h = 100;
                            }
                            else if($w<=150)
                            {                                
                                $w = 150;
                                $h = 150;
                            }
                            else if($w<=220)
                            {                                
                                $w = 220;
                                $h = 220;
                            }
                            else
                            {                                
                                $w = 310;
                                $h = 310;
                            }    
                            
                            $return_path.="_".$w."x".$h.".jpg"; break;//1688图片
			default: $return_path; break;
		}
    }
    return $return_path;
}

function c_filter_special_chars($str)
{
    $new_str = strip_tags($str);
    $new_str = preg_replace("/(&amp;|&quot;|&#039;|&lt;|&gt;)/i", "", $new_str);
    
    return $new_str;
}

//获取随机码
function c_get_rand($length = 6){
    $str = "abcdefghigklmnopqrstuvwxyz0123456789ABCDEFGHJKLMNPQRSTUVWXYZ";
    $returnStr = "";
    for($i=0;$i<$length;$i++){
        $tmp_index = rand(0,62);
        $returnStr.= substr($str,$tmp_index,1);
    }
    return md5($returnStr);
}

function c_filter_http_cookie($str){
   
    preg_match('/(?<=X-XSRF-YHJR\=).*(?=;)/',$str,$result);
    
    return $result[0];
}
function get_error_info($error_id){
    return S("proxy_new_error_info_".$error_id);
}