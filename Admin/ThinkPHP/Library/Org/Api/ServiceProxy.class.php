<?php
namespace Org\Api;
use Common\Model\SendmailModel;

class AccessModule
{
    /**
     * 判断缓存是否正常运行
     * @var type 
     */
    private static $s_is_cache_running = 1;
    
    // 随机数的长度
    const LEN_NONCE = 8;    
    
    /*
     * 当前访问模块的访问次数
     * 默认允许失败三次
     */
    private static $s_access_times = 0;
    
    /*
     * 访问一个模块的重试次数
     */
    const RETRY_TIMES = 3;
    
    /**
     * 更新通用交互信息时，必须设置当前状态为停用
     * 1:running 2:stop
     */
    const PROXY_RUNNING_STATE = 'proxy_running_state';   
    
    const PREFIX_CACHE = "proxy_new_";
    
    /**
     * 系统数据的缓存时间(一天)
     */
    const CACHE_TIMEOUT = 86400;

    public $user_Id;                    //登录会员ID
    public $seller_id;                 //平台客户编号
    public $logSectionId;              //登录部门id
    public $shop_id;                   //店铺编号
    public $client_ip;
  
    public $webserver_ip;
    public $dis_lang_id;
    public $site_url;
    public $sn = "";                     //序列值

    public $module;                     //模块名
    public $class;                     //类名
    public $func;                      //方法名
    public $params;                    //传入参数	
    	
    
    /*
     * 临时存放站点信息
     */
    private static $s_site_info = null;
    
    /**
     * 获取接口的运行状态
     * @return type
     */
    public static function get_running_state()
    {
        $running_state = S(self::PROXY_RUNNING_STATE);
       
        //判断当前Session是否有权限访问(或者当前IP是否有权限访问)
        //调试时，不判断session的访问次数，因为要性能测试
        if($running_state == 1 && !APP_DEBUG)
        {
            //判断当前Session是否有权继续访问
            //1、是否超出Session规则访问
            //2、是否超出IP访问次数
            
        }
        
        return empty($running_state) ? 1 : $running_state;
    }

    /**
     * 设置运行状态 1:running 2:stop
     * @param type $running_state
     */
    public static function set_running_state($running_state)
    {
        //设置运行状态
        if(in_array($running_state, [1,2]))
        {
            S(self::PROXY_RUNNING_STATE, $running_state);
        }
    }  
    
    /**
     * 邮件通知系统运行状态给管理员
     * @param type $error_msg
     * @param type $error_trace
     * @param type $args
     */
    public static function mail_to($error_msg,$error_trace,$access_info,$args)
    {
        $seler_email = ""; // (商家的邮件地址)
        
        $subject = "调用接口异常";       
        
        $subject = $subject." : ".$error_msg;
        
        $content = $subject."\n"."site url:".c_get_site_url();
        $content = $subject."\n"."client ip:".  c_get_client_ip();
        $content = $content."\n"."error message:".$error_msg; 
        $content = $content."\n"."error trace:".(is_array($error_trace) ? print_r($error_trace, true) : $error_trace); 
        $content = $content."\n"."access info:".print_r($access_info, true);        
        $content = $content."\n"."trans data:".print_r($args, true);  
        $content = $content."\n".$subject." ".date("Y-m-d H:i:s");
        
        if(APP_DEBUG)
        {
            var_dump([$subject,$content]);
        }
        
        SendmailModel::send($seler_email, $subject, $content);
    }    
    
    /**
     * 把后台的返回结果统一处理并传回Website前端展示系统
     * @param type $module_id
     * @param type $return_data
     * @param type $proxy_exception 代理调用异常
     */
    public function proxy_response($module_id,$return_data, $proxy_exception=null)
    {         
        if(empty($proxy_exception) &&
                isset($return_data["returnState"]) &&
                isset($return_data["returnData"]))
        {
            /*
             * 43	User	    UserSys	Usersyslogin  后台登陆
             * 118	Customer    Usershopper	memberLogin   前端买家登陆
             * 616	User	    BarnSeller	login         多用户入口登陆（米仓等代理商平台登陆入口）
             */            
            if(in_array($module_id, [43,118,616]))
            {
                //登录时，记录当前登陆的账号信息
                $this->save_current_login_info($module_id, $return_data);
            }
            
            goto kuba_end;
        }
        
        //调用接口出现异常
        if(!empty($proxy_exception))
        {
            self::mail_to($proxy_exception->getMessage(), $proxy_exception->getTrace(),$this, $this->params);
            
            goto kuba_end;    
        }
        
        //接口调用返回值格式不正确
        if(!isset($return_data["returnState"]))
        {
            self::mail_to("接口调用返回值格式不正确", "",$this, $this->params);
            
            goto kuba_end;             
        }                
        
        kuba_end:
            
        //封装返回结果
        if(empty($return_data) || !isset($return_data["returnState"]))
        {
            //访问服务器失败，请稍微重试（有可能Web端客户信息丢失）
            $return_data["returnState"] = -38;
            $return_data["returnData"] = [];
        }  
//         echo "tag4-38===========<br />";
        return $return_data;
    }
    
    /**
     * 保存当前的登陆信息
     * 1、为登陆信息设置Session有效期
     * 2、登出操作后，自动消除Session里的登陆信息
     * 3、超时提示登陆超时
     * 4、免登陆处理(前端商城买家)
     * 5、后台免登陆处理
     * @param type $module_id
     * @param type $return_data
     */
    private function save_current_login_info($module_id,$return_data)
    {        
        if(!in_array($module_id, [43,118,616]) ||
                empty($return_data) ||
                $return_data["returnState"] != 1)
        {
            return;
        }
        
        //保存登陆信息到session
        /*
         * 客户编号
         * seller_id
         * 客户登陆账号
         * user_id
         * 访问店铺
         * shop_id
         * 可访问店铺列表
         * shop_id_list
         * 可访问仓库列表
         * warehouse_id_list
         * 访问临时Token
         * sn
         * 模块访问日志，防止过度操作
         * access_log
         */        
        
        $user_info = $return_data["returnData"][0];
        
        /*
         * 生成serviceproxy对象专用session值
         * 43	User	    UserSys	Usersyslogin  后台登陆
         * 118	Customer    Usershopper	memberLogin   前端买家登陆
         * 616	User	    BarnSeller	login         多用户入口登陆（米仓等代理商平台登陆入口）
         */        
        switch ($module_id)
        {
            case 43:
                if(isset($user_info["seller_id"]) &&
                        isset($user_info["user_id"]) &&
                        isset($user_info["nick_name"]))
                {
                    session("serviceproxy.seller_id",$user_info["seller_id"]);
                    session("serviceproxy.user_id",$user_info["user_id"]);
                    session("serviceproxy.nick_name",$user_info["nick_name"]);                 
                }                

                break;
            case 118:
                if(isset($user_info["seller_id"]) &&
                        isset($user_info["user_id"]) &&
                        isset($user_info["shop_id"]) &&
                        isset($user_info["nick_name"]))
                {
                    session("serviceproxy.seller_id",$user_info["seller_id"]);
                    session("serviceproxy.user_id",$user_info["user_id"]);
                    session("serviceproxy.nick_name",$user_info["nick_name"]);
                    session("serviceproxy.shop_id",$user_info["shop_id"]);                  
                }                
                
                break;                
            case 616:
                if(isset($user_info["seller_id"]) &&
                        isset($user_info["user_id"]) &&
                        isset($user_info["nick_name"]))
                {
                    session("serviceproxy.seller_id",$user_info["seller_id"]);
                    session("serviceproxy.user_id",$user_info["user_id"]);
                    session("serviceproxy.nick_name",$user_info["nick_name"]);                   
                }
                                
                break;                 
        }                
    }

    /**
     * 设置访问参数
     * @param type $module
     * @param type $class
     * @param type $func
     * @param type $params
     * @param type $AccessType (1=Seller,2=Buyer)
     */
    public function init_data($module='',$class='',$func='',$params='',$access_type=2, $dis_lang_id = 0)
    {	
        //判断当前用户的登陆账号是否可用,不能用的时候，不允许访问
        
        $this->seller_id = SELLER_ID;
        $this->shop_id = SHOP_ID;
        
        if($access_type == 2)
        {
            $this->user_Id = session('?user_id') ? session('user_id') : 0;
        }
        else
        {
            $this->user_Id = session("?ADMIN_ID") ? session("ADMIN_ID") : 0;
        }
        
//         if(empty($this->user_Id))
//         {
//             $this->user_Id = session("?ADMIN_ID") ? session("ADMIN_ID") : (session('?user_id') ? session('user_id') : 0);
//         }
//        $params["logSectionId"] = session("sectionId")?session("sectionId"):0;
        $this->client_ip = c_get_client_ip();
        $this->logSectionId = session("?sectionId") ? session("sectionId"):0;

        $this->module = $module;
        $this->class = $class;
        $this->func = $func;
        $this->params = $params;
        $this->site_url = c_get_site_url();
        

        //当前店铺的显示语言
        $this->dis_lang_id = $dis_lang_id > 0 ? $dis_lang_id : ( defined("LANGUAGE_VIWE") ? LANGUAGE_VIWE : 1);
    }	
    
    /**
     * 创建中间层访问参数
     * @param type $module_id
     * @param type $token
     * @return type
     */
    public function createOperateArgs($module_id = 0, $token = "")
    {
            //$token2 = isset($token)?$token:S($this->site_url."_token");
        
           $token2 = S($this->site_url."_token");
            $token_info = $this->publish_sign($token2);
            if($this->site_url == 'app.wanghuo.hk' || $this->site_url == 'wei.wanghuo.hk' || $this->site_url == 'yun.wanghuo.hk' || $this->site_url == 'door.wanghuo.hk'){
                $this->site_url = 'seller_'.SELLER_ID;
            }
            $args_array = array(
                            "signature"=>$token_info['signature'],
                            "timestamp"=>$token_info['timestamp'],
                            "nonce"=>$token_info['nonce'],
                            "seller_id"=>$this->seller_id,
                            "shop_id"=>$this->shop_id,
                            "dis_lang_id"=>$this->dis_lang_id,
                            "user_id"=>$this->user_Id,
                            "client_ip"=>$this->client_ip,
                            "site_url"=>$this->site_url,
                            //"sha1"=>$module_id,
                            "module_id"=>$module_id,
                            "params"=>$this->params
            );
            return $args_array;
    }    
    
    /**
     * 验证中间层的访问参数是否合法
     * @return int 
     * 1:合法 负数不合法
     */
    public function check_trans_args()
    {
        if(empty($this->seller_id) ||
            empty($this->shop_id))
        {
            //38	I	客户编号和站点编号未赋值，异常请求被拒绝	2016-03-04 08:47:45
            return -38;
        }
        return 1;
    }

    /*
     * 创建临时的签名，防止中途被篡改
     */
    private function publish_sign($site_token)
    {
        $timestamp = time();
        $nonce = self::randomkeys(self::LEN_NONCE);

        $tmpArr = array($site_token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        return array("signature"=>$tmpStr,"timestamp"=>$timestamp,"nonce"=>$nonce);
    }

    /**
     * 生成随机数
     * @param int $length
     */
    private static function randomkeys($length=8)
    {
        $key = "";

        $pattern='1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';

        for($i=0;$i<$length;$i++)
        {
            $key .= $pattern{mt_rand(0,35)};    //生成php随机数
        }

        return $key;
    }

    /**
     * 保存当前站点的信息
     * @param type $return_data 中间层返回的站点数据
     * @param type $p_site_url 更新指定的站点的缓存（代理商更新下属供应商的语言，域名等）
     */
    public function save_site_info($site_info, $p_site_url = '')
    {
        try
        {
            //存放站点信息到临时静态变量
            self::$s_site_info = $site_info;
            
            $seller_id = $site_info["seller_id"];    
            $shop_id = $site_info["shop_id"];             
            $shop_url = $site_info["shop_url"];            
            $platform_domain = $site_info["platform_domain"]; 

            $key = self::PREFIX_CACHE.$seller_id.'_'.$shop_id;
            $key_site_url = self::PREFIX_CACHE.$shop_url;

            //客户编号变更时，shop_id = 1的site_url也同时变更，但可以保持原有的其他shop_id的信息不变           

            //保存站点信息到缓存
            S($key, json_encode($site_info), self::CACHE_TIMEOUT);    

            //保存seller_id，shop_id到缓存
            S($key_site_url, $key, self::CACHE_TIMEOUT);     
            
            //如果指定了特定的站点，则更新该站点的缓存
            $current_site_url = empty($p_site_url) ? c_get_site_url() : $p_site_url;
            
            //如果当前登陆时，使用的是管理后台域名，则把管理后台的域名也要缓存起来
            //但是对应的站点信息与总店相同
            if($current_site_url != $shop_url)
            {
                $key_site_url = self::PREFIX_CACHE.$current_site_url;
                
                //保存seller_id，shop_id到缓存
                S($key_site_url, $key, self::CACHE_TIMEOUT); 
            }

            //如果当前使用的是短域名，则缓存短域名的token，
            //防止短域名与长域名冲突，token要区分保存
            if('www.'.$current_site_url == $shop_url)
            {
                //保存当前的站点的token
                $key_token = self::PREFIX_CACHE.$shop_url."_token";

                S($key_token, $site_info["token"], self::CACHE_TIMEOUT);
            }else if($current_site_url != $shop_url && $current_site_url == $platform_domain){
                //保存当前的站点的token
                $key_token = self::PREFIX_CACHE.$platform_domain."_token";

                S($key_token, $site_info["token"], self::CACHE_TIMEOUT);                 
            }
            
            //保存当前的站点的token
            $key_token = self::PREFIX_CACHE.$current_site_url."_token";

            S($key_token, $site_info["token"], self::CACHE_TIMEOUT);                            
        }
        catch (\Exception $e)
        {
            //发送邮件通知
            self::mail_to('更新站点信息的缓存失败', $e->getMessage(), $this, $site_info);
        }        
    }            
    
    /**
     * 获取当前站点的客户信息
     */
    public static function get_seller_info()
    {
        //该数据是为了同一个请求里不需要多次访问缓存
        $site_info = self::$s_site_info;		

        //如果是本次前端访问的首次连接中间层，则为空
        if(empty($site_info))
        {
            //站点信息的缓存里的存放关键字
            $cache_key_Name = self::PREFIX_CACHE.  c_get_site_url();            
            
            //获取站点信息对应的缓存关键字
            $key_cache_data = S($cache_key_Name);
            
            if(isset($key_cache_data) && !empty($key_cache_data))
            {
                $site_info = json_decode(S($key_cache_data), true);  
                
                self::$s_site_info = $site_info;                
            }                    
        }
        	
        return $site_info;        
    }
    
    /**
     * 获取中间层访问权限
     */
    public function get_access_token()
    {              
        $token = "";

        $site_info = self::get_seller_info();

        if(!empty($site_info))
        {
            $token = $site_info["token"];                      
        }

        return $token;        
    }        

    /**
     * 获取访问模块的模块编号
     * @param type $module_name
     * @param type $controller_name
     * @param type $action_name
     */
    public function get_module_id($module_name="", $controller_name="", $action_name="")
    {
        $module_id = 0;
        //echo $module_name.'---'.$controller_name.'------'.$action_name;
        if(empty($module_name) ||
            empty($controller_name) ||
            empty($action_name))
        {
            $single_module_key = self::PREFIX_CACHE."module_info"."_".$this->module."_".$this->class."_".$this->func;
        }
        else
        {
            $single_module_key = self::PREFIX_CACHE."module_info"."_".$val['module_name']."_".$val['controller_name']."_".$val['action_name'];
        }
        try
        {            
            $module_id = S($single_module_key); 
        }
        catch (\Exception $e)
        {
            //发送邮件通知
            self::mail_to('获取特定模块信息失败', $e->getMessage(), $this, array(
                "module_name" => $module_name,
                "controller_name" => $controller_name,
                "action_name" => $action_name
            ));
        }
        
        return $module_id;
    }
    
    /**
     * 获取访问模块的模块编号
     * @param type $module_name
     * @param type $controller_name
     * @param type $action_name
     */
    public function get_error_id($error_code)
    {
        $error_msg = "";
        $single_error_key = self::PREFIX_CACHE."error_info"."_".$error_code;
        
        try
        {            
            $error_msg = S($single_error_key);             
        }
        catch (\Exception $e)
        {
            //发送邮件通知
            self::mail_to('获取特定出错信息失败', $e->getMessage(), $this, array(
                "error_code" => $error_code,
            ));
        }
        
        return $error_msg;
    }    
    
    /**
     * 保存系统信息到缓存
     * @param type $sys_info
     */
    public function save_sys_info($sys_info = null)
    {
        
        if(empty($sys_info) ||
                !is_array($sys_info) ||
                !isset($sys_info["module"]) ||
                !isset($sys_info["error"]))
        {
            return;
        }
                
        try
        {    
            //处理模块信息
            foreach($sys_info['module'] as $val)
            {
                $single_module_key = self::PREFIX_CACHE."module_info"."_".$val['module_name']."_".$val['controller_name']."_".$val['action_name'];
                
                S($single_module_key, $val['module_id'], self::CACHE_TIMEOUT);
            }			

            foreach($sys_info['error'] as $val)
            {
                $errorInfo["-".$val["error_code"]] = array(
                    "error_type" => $val["error_type"],
                    "message" => $val["message"],
                );   

                $single_error_key = self::PREFIX_CACHE."error_info"."_"."-".$val["error_code"];

                S($single_error_key, $val["message"], self::CACHE_TIMEOUT);            
            }			              
        }
        catch (\Exception $e)
        {
            //发送邮件通知
            self::mail_to('更新系统信息的缓存失败', $e->getMessage(), $this, $sys_info);
        }     
    }

}	

class ServiceProxy {
    /*
     * 最大验证码更新次数
     */
    const SN_UPDATE_TIMES = 3;
    private $count_Sn_update=0;            //序列值过期次数

    private $maxSize=50;             //传入参数包限制
    private $maxSysResTime;          //同步响应最大时间，单位秒

    private $serviceUrl;             //中间层的访问入口
    private $serviceSSLUrl;          //加密访问地址

    private $certificate;            //证书地址	

    private $access_module;
    
    /*
     * 当前访问的中间层连接对象
     * 目的是为了让同一客户的请求只创建一个连接对象(一个前端客户一个时间点只创建一个请求)
     */
    
    private static $s_yar_client = null;

    /*
     * $AccessType(1=Seller,2=Buyer)
     */
    public function __construct($module='',$class='',$func='',$params='',$AccessType=1){
        
        $this->serviceUrl=C('SERVICE_URL');
        $this->serviceSSLUrl=C('SERVICE_SSL_URL');

        $this->maxSysResTime=C('MAX_SYSRES_TIME');
        $this->certificate=C('CERTIFICATE_PATH');

        $access_module = new AccessModule();

        $access_module->init_data($module, $class, $func,$params,$AccessType);                

        $this->access_module = $access_module;   
    }
    
    /**
     * 获取yar客户端对象
     * @param $reset 是否重置对象
     */
    private function getYarClient($reset = 0){
        
        //中间层是取本地连接还是服务器连接
        //$url=self::$isLocal?C('LOCAL_SERVICE_URL'):$this->serviceUrl;
        $url=$this->serviceUrl;

        $yarClient = null;
        
        if(!empty(self::$s_yar_client) && $reset == 0)
        {
            $yarClient = self::$s_yar_client;
        }
        else
        {
            ini_set("yar.timeout",$this->maxSysResTime);
            $yarClient=new \Yar_Client($url);
            $yarClient->SetOpt(YAR_OPT_CONNECT_TIMEOUT, $this->maxSysResTime);           
        }
        
        return $yarClient;
    }
    
    
    /**
     * 通过中间层获取当前站点信息
     * @param type $is_clear_all 是否重新获取当前站点的信息
     * @param type $p_site_url 指定站点的缓存
     * @param type $per_seller_url 用指定的seller_id去取对应的信息
     * 特殊域名app.wanghuo.hk  wei.wanghuo.hk yun.wanghuo.hk door.wanghuo.hk 才存在这种情况
     */
    public function fetch_site_info($is_clear_all = false, $p_site_url = '',$per_seller_url='')
    {
        //返回标签数组
        $return_data = [];
        $return_state = 1;
        
        $response_data = array(
            "returnData" => &$return_data,
            "returnState" => &$return_state
        );  
        
        $current_site_info = null;
        
        //代理商添加或修改所属供应商或者分销上的店铺信息，例如语言，域名等
        //获取指定站点的时候，必定是重新获取，is_clear_all = true
        $current_site_url1 = empty($p_site_url) ? c_get_site_url() : $p_site_url;
        
        $current_site_url = !empty($per_seller_url) ? $per_seller_url : $current_site_url1;
       
        if(!$is_clear_all)
        {
            $current_site_info = $this->access_module->get_seller_info();

            //如果已经存在，则直接返回
            if(!empty($current_site_info))
            {
                $return_data = $current_site_info;

                goto kuba_end;
            }
        }
        
        $retry_times = 0;

        $json_site_url = self::arrToJson(
                array(
                    "site_url"=>  $current_site_url,
                    "client_ip"=>  c_get_client_ip(),
                    "seller_id"=>0,
                    "shop_id"=>0,
                    "user_id"=>0,
                    "signature"=>"",
                    "timestamp"=>0,
                    "nonce"=>"",
                    "module_id"=>0
                    )
                );
        //失败时，允许重试两次
        while($retry_times < self::SN_UPDATE_TIMES)
        {
            try {
                
                $module_id = $this->checkAccessModule();
               
                //只有第一次重试时，需要重新生成yarclient对象，因为首次执行时，有可能该对象已经失效，但是第一次重试之后，应该推断yarclient是良好的
                $reset = $retry_times == 1 ? 1 : 0;
                $clientObj = $this->getYarClient($reset);
                $responseStr = $clientObj->getSiteInfo($json_site_url);
                $responseObj=self::jsonToArr($responseStr);

                $current_site_info = $responseObj["returnData"];
                S(c_get_site_url()."_token",$current_site_info['token']);
                /**
                 * 如果返回的结果不是空，且返回的状态值不为以下几个，表示中间层调用是成功的
                 * -4 当前客户不存在，有可能是客户更改了域名或者关闭了站点
                 * -5 没有服务器的访问权限，请联系管理员(需要重新生成token再试)
                 */
                if(!empty($responseObj) && !in_array($responseObj["returnState"],[-4,-5]))
                {
                    $this->access_module->save_site_info($current_site_info, $p_site_url);

                    //因为获取的数据可能在save时，进行了重构
                    $return_data = $this->access_module->get_seller_info();

                    $retry_times = self::SN_UPDATE_TIMES;

                    goto kuba_end;
                }
                else
                {
                    $retry_times ++;
                }
            }
            catch (\Yar_Client_Exception $e)
            {       
                $retry_times ++;

                if($retry_times == self::SN_UPDATE_TIMES)
                {
                    AccessModule::mail_to($e->getMessage(),$e->getTrace(), $args);   
                }                
            }                         
        }
        //38	I	访问服务器失败，请稍微重试（有可能Web端客户信息丢失）	2016-03-04 09:41:32
        $return_state = -38;
        
        kuba_end:
            return $response_data;
    }
    
    /**
     * 获取系统信息：模块信息和错误信息
     */
    public function cache_system_info()
    {      
        //把接口状态改为停止，防止其他用户在更新时使用中间层
        if(AccessModule::get_running_state() == 1)
        {
            AccessModule::set_running_state(2);
        }
        else
        {
            return;
        }
        
        $retry_times = 0;

//        $json_system_info = self::arrToJson(
//                array(
//                    "site_url"=>  c_get_site_url(),
//                    "client_ip"=>  c_get_client_ip()
//                )
//            );
        
        
        $json_system_info = self::arrToJson($this->access_module->createOperateArgs());

        //失败时，允许重试两次
        while($retry_times < self::SN_UPDATE_TIMES)
        {
            try {
                //只有第一次重试时，需要重新生成yarclient对象，因为首次执行时，有可能该对象已经失效，但是第一次重试之后，应该推断yarclient是良好的
                $reset = $retry_times == 1 ? 1 : 0;
                $clientObj = $this->getYarClient($reset);

                $responseStr = $clientObj->loadSystemInfo($json_system_info);

                $responseObj = self::jsonToArr($responseStr);
                
                $current_sys_info = $responseObj["returnData"];
                /**
                 * 如果返回的结果不是空，且返回的状态值不为以下几个，表示中间层调用是成功的
                 * -4 当前客户不存在，有可能是客户更改了域名或者关闭了站点
                 * -5 没有服务器的访问权限，请联系管理员(需要重新生成token再试)
                 */
                if(!empty($responseObj) && !in_array($responseObj["returnState"],[-4,-5]))
                {
                    $this->access_module->save_sys_info($current_sys_info);

                    $retry_times = self::SN_UPDATE_TIMES;
                }
                else
                {
                    $retry_times ++;
                }
            }
            catch (\Yar_Client_Exception $e)
            {       
                $retry_times ++;
                
                if($retry_times == self::SN_UPDATE_TIMES)
                {
                    AccessModule::mail_to($e->getMessage(),$e->getTrace(),$this->access_module, $args);  
                }                  
            }                         
        }  
        
        //更新完后恢复为接口可用
        AccessModule::set_running_state(1);        
    }

    /**
     * 检查返回结果，如果是序列值过期，则自动使用ssl证书获取序列值，重新获取数据
     */
    private function checkSign(){

        $token = $this->access_module->get_access_token(); 
        
        if(empty($token))
        {
            $this->fetch_site_info();
            
            $token = $this->access_module->get_access_token(); 
        }
        
        return $token;
    }
    
    /**
     * 检查参数正确性
     * Enter description here ...
     */
    private function checkParams(){
        
            return true;

            //获取传入数据限制
            try{
                    $moduleInfoes=$this->getSystemInfo(2);//获取模块信息
                    $m_info=$moduleInfoes[$this->model][$this->class][$this->func];
                    $this->maxSize=$m_info['params_length'];
                    $this->ha1=$m_info['module_id'];
            }catch (\Exception $e){
                    E($e->getMessage());
            }

            if(strlen($this->params)>$this->maxSize){
                    E('传入参数超出范围');
                    return false;
            }

            return true;
    }   
    
    /**
     * 核对相应的中间层模块是否存在
     * @param type $module_name
     * @param type $controller_name
     * @param type $action_name
     * @return type
     */
    private function checkAccessModule($module_name="", $controller_name="", $action_name="")
    {
        $module_id = $this->access_module->get_module_id($module_name, $controller_name, $action_name);
      
        //有可能是新的模板，需要更新系统信息
        if(empty($module_id))
        {
            $this->cache_system_info();
            //echo $module_name.'----111----'.$controller_name.'---222'.$action_name;
            $module_id = $this->access_module->get_module_id($module_name, $controller_name, $action_name);
           
        }
        
        return $module_id;
    }

    /**
     * 获取数据
     * Enter description here ...
     */
    public function getData()
    {
        //返回标签数组
        $return_data = [];
        $return_state = 1;
        
        $response_data = array(
            "returnData" => &$return_data,
            "returnState" => &$return_state
        );  

        if(!ACK_Monitor::filter($this->access_module))
        {
            //41	I	黑客攻击，拒绝访问	2016-04-05 23:33:36
            $return_state = -41;
            
            goto kuba_end;            
        }
        
        $token = $this->checkSign();
        //如果token为空，则返回错误
        if(empty($token))
        {
            //38	I	访问服务器失败，请稍微重试（有可能Web端客户信息丢失）	2016-03-04 09:41:32
            $return_state = -38;
            goto kuba_end;
        }
        
        $checkFlag=$this->checkParams();
        
        //判断传入参数是否有问题
        if(!$checkFlag)
        {
            //12	I	传入参数有问题，请使用正确的参数。	2014-12-29 16:51:01
            $return_state = -12;
            
            goto kuba_end;
        }
        //核对当前访问的模块的编号
        $module_id = $this->checkAccessModule();
        if(empty($module_id))
        {
            //3	I	模块调用错误，请核对是否有模块的使用权限。	2014-12-19 12:10:13
            $return_state = -3;
            
            goto kuba_end;
        }
        $retry_times = 0;
        
        $args = self::arrToJson($this->access_module->createOperateArgs($module_id,$token));
        //失败时，允许重试两次
        while($retry_times < self::SN_UPDATE_TIMES)
        {        
            try{                
                //只有第一次重试时，需要重新生成yarclient对象，因为首次执行时，有可能该对象已经失效，但是第一次重试之后，应该推断yarclient是良好的
                $reset = $retry_times == 1 ? 1 : 0;
                $clientObj = $this->getYarClient($reset);
                $reData=$clientObj->operate($args);

                $result=self::jsonToArr($reData); 
               //p($result);echo 556677;
                /**
                 * 如果返回的结果不是空，且返回的状态值不为以下几个，表示中间层调用是成功的
                 * -4 当前客户不存在，有可能是客户更改了域名或者关闭了站点
                 * -5 没有服务器的访问权限，请联系管理员(需要重新生成token再试)
                 */
                if(!empty($result) && !in_array($result["returnState"],[-4,-5]))
                {
                    $response_data = $this->access_module->proxy_response($module_id, $result);                    

                    $retry_times = self::SN_UPDATE_TIMES;

                    goto kuba_end;
                }
                else
                {
                    //Seller_token被更新过了，需要重新获取
                    //但只会重新取一次
                    if($result["returnState"] == -5 && $retry_times == 1)
                    {
                        $this->fetch_site_info(true);
                    }
                    
                    $retry_times ++;
                }                                
            }
            catch(\Yar_Client_Exception $e)
            {        
                AccessModule::mail_to($e->getMessage(),$e->getTrace(),$this->access_module, $args);

                $response_data = $this->access_module->proxy_response($module_id, null, $e);  

                $retry_times = self::SN_UPDATE_TIMES;
                
                goto kuba_end;
            }                          
        }
        //38	I	访问服务器失败，请稍微重试（有可能Web端客户信息丢失）	2016-03-04 09:41:32
        $return_state = -38;
        
        kuba_end:
            return $response_data;
    }	

    /**
     * 设置参数
     * @param 传入的参数
     */
    public function setParams($params){		
            if(isset($params) && !empty($params)){
                    if(is_array($params)){
                            $this->params=self::arrToJson($params);
                    }elseif(is_string($params)){
                            $this->params=self::arrToJson($params);
                    }
            }
    }
    /**
     * 通过证书访问中间层获取序列值
     * @return string $sn 序列值
     */
    protected function getSn(){
            //先查看有没有序列值，没有的话，通过证书获取序列值
            $sn=S('sn');
            if(!isset($sn) || empty($sn)){
                    //$yar_ssl=new \yar_client($this->serviceSSLUrl);
                    //$sn=$yar_ssl->getSn($this->certificate);
                    $sn=md5('85454245454545');
                    S('sn',$sn,0);
            }
            return $sn;	
    }


    /**
     * 获取错误消息
     */
    public function getErrorMsg(){
            $language=C('LANGUAGE');
            try{
                    $errorMsg=self::$yarClient->getErrorMsg($language);
                    return $errorMsg;
            }catch (\Exception $e){
                    E($e->getMessage());
            }
    }

    /**
     * 析构函数
     * Enter description here ...
     */
    public function __destruct(){
            //unset(self::$yarClient);
    }	

    public static function arrToJson($data)
    {
            $jsonData=json_encode($data,true);
            //$jsonData=msgpack_pack($data);
            return $jsonData;
    }

    /**
     * json字符串转数组
     * @param unknown_type $reData
     */
    public static function jsonToArr($reData){
            //$objData=msgpack_unpack($reData);
            $objData=json_decode($reData,true);
            return $objData;
    }
    /** 
     * 对象转数组
     * @param unknown_type $objData
     */
    public static function objectToArr($objData){
            $reArr=array();
            foreach($objData as $key=>$val){
                    if(is_object($val)||is_array($val)){
                            $reArr[$key]=self::objectToArr($val);
                    }else{
                            $reArr[$key]=$val;
                    }
            }
            return $reArr;
    }                
}

class ACK_Monitor
{
    public static function filter($access_info,$sha1= 0)
    {
        $ipstr = c_get_ip();
        
        if($ipstr == '122.226.242.70')
        {
            return true;
        }
        
        $ip = sprintf("%u", ip2long($ipstr));
        $key_access_times_per_ip = "ack_times_".$ip;
        $key_black_ip = "ack_black_".$ip;
                        
        if(!empty(S($key_black_ip)))
        {
            return false;
        }
        
        //如果当前IP在一分钟内是第一次访问，则通过
        if(c_add_cache_key($key_access_times_per_ip, 1, 60))
        {
            return true;
        }
        else
        {
            //获取当前IP的访问次数
            $access_times = c_incr_cache_key($key_access_times_per_ip,1);            
            
            //如果同一个IP一分钟内访问了超过600次请求，即每三秒30个请求
            if($access_times > 600)
            {
                //添加当前客户的IP到黑名单一个小时
                if(!c_add_cache_key($key_black_ip, 1, 3600))
                {
                    //别的线程已添加，这里不需要通知了
                    AccessModule::mail_to("有黑客攻击", "已添加：每分钟访问次数已超过".$access_times."次", $access_info, []);
                }
                else
                {
                    //已经在黑名单了
                    AccessModule::mail_to("有黑客攻击", "每分钟访问次数已超过".$access_times."次", $access_info, []);
                }
                
                return false;
            }
            else
            {
                //查看某个方法是否超过了n次
                return true;
            }
        }
    }
}