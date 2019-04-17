<?php
namespace Kuba\Gateway;

require __DIR__ . '/Core/Init.class.php';
require __DIR__ . '/Core/ServerState.class.php';
require __DIR__ . '/Core/ServerResponse.php';
require __DIR__ . '/Core/ModuleFactory.class.php';

use Kuba\Core\ServerState;
use Kuba\Core\Init;
use Kuba\Core\ServerResponse;
use Kuba\Core\ModuleFactory;

/**
 * 电子商务平台网关类
 * @category   Gateway
 * @package  Gateway
 * @author    思涵 <si812cn@163.com>
 * @version   $Id: V01 2014-12-06 17:05:00Z shoperp $
 */
final class Services {

    private $access_module;

    private function jsonToArr($args) {      
        return json_decode($args, true);
        //return msgpack_unpack($args);
    }

    private function arrToJson($args) {
        return json_encode($args);
        //return msgpack_pack($args);
    }

    private function checkArgs($args,$type = 1) {

        if (empty($args)) {
            return NAN;
        }

        $args_array = $this->jsonToArr($args);

        if (empty($args_array) || !is_array($args_array)) {
            return INF;
        }

        if (!isset($args_array['signature']) || !isset($args_array['timestamp']) ||
            !isset($args_array['nonce']) || !isset($args_array['client_ip']) ||
            !isset($args_array['site_url']) || ($type && !isset($args_array['module_id']))) {
            return INF;
        }

        $signature = $args_array["signature"];
        $timestamp = floatval($args_array["timestamp"]);
        $nonce = $args_array["nonce"];
        $user_id = floatval($args_array["user_id"]);
        $module_id = $type ? intval($args_array["module_id"]) : 0;
        $client_ip = $args_array["client_ip"];
        $site_url = $args_array["site_url"];

        if ($timestamp == INF || ($type && $module_id == INF)) {
            return INF;
        }

        $params = array_key_exists("params", $args_array) ? $args_array["params"] : null;

        return array(
            'signature' => $signature,
            'timestamp' => $timestamp,
            'nonce' => $nonce,
            'user_id' => $user_id,
            'module_id' => $module_id,
            'client_ip' => $client_ip,
            'site_url' => $site_url,
            'params' => $params
        );
    }

    public function operate($args) {
        $signature = "";
        $timestamp = 0;
        $nonce = "";
        $seller_id = 0;
        $shop_id = 0;
        $user_id = 0;
        $module_id = 0;
        $client_ip = "";
        $site_url = "";
        $params = "";

        $returnData = null;

        try {
            $args_In = $this->checkArgs($args);
            if ($args_In == INF || $args_In == NAN) {
                //参数解包失败
                $returnData = ServerResponse::response(-1);
                goto kuba_end;
            } else {
                $signature = $args_In["signature"];
                $timestamp = $args_In["timestamp"];
                $nonce = $args_In["nonce"];
                $module_id = $args_In["module_id"];
                $user_id = $args_In["user_id"];
                $client_ip = $args_In["client_ip"];
                $site_url = $args_In["site_url"];
            }
                    
            //判断Url对应的Seller_id是否正确
            $site_info = ModuleFactory::getSiteInfo($site_url);
            
            if (empty($site_info) || count($site_info) != 1) {
                //非法域名
                $returnData = ServerResponse::response(-2);

                goto kuba_end;
            }
            
            $site_info = $site_info[0];

            $seller_id = $args_In["seller_id"] = $site_info["seller_id"];
            $shop_id = $args_In["shop_id"] = $site_info["shop_id"];                
            $params = $args_In["params"];
            
            //把参数作为字符串存到数据库表(access_log)
            $args = json_encode($params);            
            
            //检查服务器状态
            if (!ServerState::check()) {
                //服务器没有正常运行
                $returnData = ServerResponse::response(-3);

                goto kuba_end;
            }

            //加载初始数据
            if (!Init::load()) {
                //初始化失败
                $returnData = ServerResponse::response(-4);
                goto kuba_end;
            }
            
            //创建访问对象
            $access_module = new AccessModule();
            $access_module->set($signature, $timestamp, $nonce, $seller_id, $shop_id, $user_id, $module_id, $client_ip, $site_url);

            if (!defined('IS_DEBUG') || !IS_DEBUG) {
                if (!in_array($access_module->webserver_ip, array('192.168.0.146', '192.168.0.193', '192.168.0.196', '192.168.0.125', '::1', '127.0.0.1', '122.226.242.70', '112.74.87.99', '203.152.92.114'))) {
                    //非法地址
                    $returnData = ServerResponse::response(-5);
                    goto kuba_end;
                }
            }

            //获取模块的编号
            $module_obj = ModuleFactory::getModuleInfo($module_id);

            if (empty($module_obj) || count($module_obj) != 1) {
                //非法模块
                $returnData = ServerResponse::response(-6);

                goto kuba_end;
            }
            
            $module_obj = $module_obj[0];

            //如果不是调试状态，则按正常执行
            if (!defined('IS_DEBUG') || !IS_DEBUG) {
                //获取Web服务器的Token值	
                if (empty($site_info["token"])) {
                    //token为空
                    $returnData = ServerResponse::response(-7);
                    goto kuba_end;
                }

                //验证客户是否有权限访问接口服务 
                if (!$access_module->checkSign($site_info["token"])) {
                    //非法签名
                    $returnData = ServerResponse::response(-8);
                    goto kuba_end;
                }
            }

            //记录新访问对象到服务队列
            ServerState::access_add($access_module);

            $returnData = ModuleFactory::run(
                $module_obj["module_name"], $module_obj["controller_name"], $module_obj["action_name"], $module_obj["route"], $args, $params, $seller_id, $shop_id, $user_id, $client_ip, $site_url, $access_module->webserver_ip
            );
            if (empty($returnData)) {
                //业务执行失败
                $returnData = ServerResponse::response(-9);
            }
        } catch (\Exception $e) {
            ModuleFactory::saveErrorInfo(-10, $e);
            ServerState::mailTo("网关处理过程中发生不可预测异常", $e->__toString());
            //发生不可预测异常
            $returnData = ServerResponse::response(-10, array(
                "error_message" => $e->getMessage(),
                "error_trace" => $e->getTrace()
            ));
        }

        kuba_end:

        return $this->arrToJson($returnData);
    }

    public function getSiteInfo($args) {
        
        $signature = "";
        $timestamp = 0;
        $nonce = "";
        $seller_id = 0;
        $shop_id = 0;
        $user_id = 0;
        $module_id = 1;
        $client_ip = "";
        $site_url = "";
        $params = "";
        
        $module_name = "user";
        $controller_name = "seller";
        $action_name = "fetchsiteinfo";
        
        $returnData = null;

        try {
            $args_In = $this->checkArgs($args);
            
            if ($args_In == INF || $args_In == NAN) {
                //参数解包失败
                $returnData = ServerResponse::response(-1);
                goto kuba_end;
            } else {
                $client_ip = $args_In["client_ip"];
                $site_url = $args_In["site_url"];
            }                 
            //判断Url对应的Seller_id是否正确
            $site_info = ModuleFactory::getSiteInfo($site_url);

            if (empty($site_info) || count($site_info) != 1) {
                //非法域名
                $returnData = ServerResponse::response(-2);
                goto kuba_end;
            }
            
            $site_info = $site_info[0];

            $seller_id = $args_In["seller_id"] = $site_info["seller_id"];
            $shop_id = $args_In["shop_id"] = $site_info["shop_id"];                
            $params = $args_In["params"];
            
            //把参数作为字符串存到数据库表(access_log)
            $args = json_encode($params);            
            
            //检查服务器状态
            if (!ServerState::check()) {
                //服务器没有正常运行
                $returnData = ServerResponse::response(-3);

                goto kuba_end;
            }

            //加载初始数据
            if (!Init::load()) {
                //初始化失败
                $returnData = ServerResponse::response(-4);
                goto kuba_end;
            }
            
            //创建访问对象
            $access_module = new AccessModule();    
            $access_module->set($signature, $timestamp, $nonce, $seller_id, $shop_id, $user_id, $module_id, $client_ip, $site_url);
            if (!defined('IS_DEBUG') || !IS_DEBUG) {
                if (!in_array($access_module->webserver_ip, array('192.168.0.146', '192.168.0.193', '192.168.0.196', '192.168.0.125', '::1', '127.0.0.1', '122.226.242.70', '112.74.87.99', '203.152.92.114'))) {
                    //非法地址
                    $returnData = ServerResponse::response(-5);
                    goto kuba_end;
                }
            }
            
            //记录新访问对象到服务队列
            ServerState::access_add($access_module);

            $webserver_ip = AccessModule::getIP();
            ModuleFactory::saveAccessInfo($module_name, $controller_name, $action_name, $args, $seller_id, $shop_id, $user_id, $client_ip, $site_url, $webserver_ip);

            if (empty($returnData)) {
                //业务执行失败
                $returnData = ServerResponse::response(-9);
            }
            
            $returnData = ServerResponse::response(1,$site_info);
        } catch (\Exception $e) {
            ModuleFactory::saveErrorInfo(-10, $e);
            ServerState::mailTo("网关处理过程中发生不可预测异常", $e->__toString());
            //发生不可预测异常
            $returnData = ServerResponse::response(-10, array(
                "error_message" => $e->getMessage(),
                "error_trace" => $e->getTrace()
            ));
        }

        kuba_end:

        return $this->arrToJson($returnData);
    }
    
    public function loadSystemInfo($args) {
        $signature = "";
        $timestamp = 0;
        $nonce = "";
        $seller_id = 0;
        $shop_id = 0;
        $user_id = 0;
        $module_id = 0;
        $client_ip = "";
        $site_url = "";
        $params = "";
        
        $module_name = "system";
        $controller_name = "sys";
        $action_name = "fetchsysinfo";        
        $route = "system/sys/fetchsysinfo";
        
        $returnData = null;

        try {
            $args_In = $this->checkArgs($args,0);
            if ($args_In == INF || $args_In == NAN) {
                //参数解包失败
                $returnData = ServerResponse::response(-1);
                goto kuba_end;
            } else {
                $signature = $args_In["signature"];
                $timestamp = $args_In["timestamp"];
                $nonce = $args_In["nonce"];
                $module_id = $args_In["module_id"];
                $user_id = $args_In["user_id"];
                $client_ip = $args_In["client_ip"];
                $site_url = $args_In["site_url"];
            }
                        
            //判断Url对应的Seller_id是否正确
            $site_info = ModuleFactory::getSiteInfo($site_url);

            if (empty($site_info) || count($site_info) != 1) {
                //非法域名
                $returnData = ServerResponse::response(-2);

                goto kuba_end;
            }
            
            $site_info = $site_info[0];

            $seller_id = $args_In["seller_id"] = $site_info["seller_id"];
            $shop_id = $args_In["shop_id"] = $site_info["shop_id"];                
            $params = $args_In["params"];       
            //把参数作为字符串存到数据库表(access_log)
            $args = json_encode($params);            
         
            //检查服务器状态
            if (!ServerState::check()) {
                //服务器没有正常运行
                $returnData = ServerResponse::response(-3);

                goto kuba_end;
            }

            //加载初始数据
            if (!Init::load()) {
                //初始化失败
                $returnData = ServerResponse::response(-4);
                goto kuba_end;
            }
            
            //创建访问对象
            $access_module = new AccessModule();
            $access_module->set($signature, $timestamp, $nonce, $seller_id, $shop_id, $user_id, $module_id, $client_ip, $site_url);

            if (!defined('IS_DEBUG') || !IS_DEBUG) {
                if (!in_array($access_module->webserver_ip, array('192.168.0.146', '192.168.0.193', '192.168.0.196', '192.168.0.125', '::1', '127.0.0.1', '122.226.242.70', '112.74.87.99', '203.152.92.114'))) {
                    //非法地址
                    $returnData = ServerResponse::response(-5);
                    goto kuba_end;
                }
            }

            //如果不是调试状态，则按正常执行
            if (!defined('IS_DEBUG') || !IS_DEBUG) {
                //获取Web服务器的Token值	
                if (empty($site_info["token"])) {
                    //token为空
                    $returnData = ServerResponse::response(-7);
                    goto kuba_end;
                }

                //验证客户是否有权限访问接口服务 
                if (!$access_module->checkSign($site_info["token"])) {
                    //非法签名
                    $returnData = ServerResponse::response(-8);
                    goto kuba_end;
                }
            }

            //记录新访问对象到服务队列
            ServerState::access_add($access_module);

            $returnData = ModuleFactory::run(
                $module_name, $controller_name, $action_name, $route, $args, $params, $seller_id, $shop_id, $user_id, $client_ip, $site_url, $access_module->webserver_ip
            );

            if (empty($returnData)) {
                //业务执行失败
                $returnData = ServerResponse::response(-9);
            }
        } catch (\Exception $e) {
            ModuleFactory::saveErrorInfo(-10, $e);
            ServerState::mailTo("网关处理过程中发生不可预测异常", $e->__toString());
            //发生不可预测异常
            $returnData = ServerResponse::response(-10, array(
                "error_message" => $e->getMessage(),
                "error_trace" => $e->getTrace()
            ));
        }

        kuba_end:

        return $this->arrToJson($returnData);
    }       
    
}

/**
 * 访问模块类
 * @category   Gateway
 * @package  Gateway
 * @author    思涵 <si812cn@163.com>
 * @version   $Id: V01 2014-12-06 17:05:00Z shoperp $
 */
final class AccessModule {

    // 随机数的长度
    const LEN_NONCE = 8;

    /**
     * 传输值签名
     * 该值的生成方式
     * 1)在中间层服务器生成服务器证书
     * 2)在中间层服务器，通过服务器证书分配客户端访问证书
     * 3)把客户端访问证书部署到Web服务器上
     * 4)Web服务器首次访问中间层服务器，会先通过SSL通道获取token(序列号)
     * 5)每次Web服务器调用中间层服务器，都会附加token(序列号)进行访问
     * 6)服务器定期刷新序列值，防止序列值被冒用
     * 7)Web服务器接收到序列值不正确时，重新通过SSL通道获取新的token(序列号)
     * 8）再重新访问中间层服务器的服务
     * 9)$signature为合成数据的哈希值{token(序列号)+timestamp(访问时的时间戳)+nonce(访问时随机生成的数据)}
     * @var varchar(160)
     */
    public $signature;

    /**
     * 服务请求提交时的时间戳
     */
    public $timestamp;

    /**
     * 随机数
     * 为保证活跃度以及避免受重复攻击
     */
    public $nonce;

    /**
     * 数据库表记录的自增编号值{模块名称、对象名称和方法名称}
     * 在数据库里存在哈希值与{模块名称、对象名称和方法名称}的对应关系
     * @var varchar(40)
     */
    public $module_id;
    public $model; //模块名
    public $class; //类名
    public $func; //方法名
    public $client_ip;
    public $site_url;
    public $webserver_ip;
    public $params; //传入参数

    /**
     * 平台客户编号
     * @var int
     */
    public $seller_id;

    /**
     * 站点编号
     * 未登录时，只能通过前端店铺页面访问中间层
     * @var int
     */
    public $shop_id;
    
    private $token;

    /**
     * 获取数据库里的模块对应哈希值
     * 通过哈希值反向获取模块名称、对象名称和方法名称
     * Web服务器通过SSL获取该列表值
     * 通信过程中用哈希值传递，减少被破译的情况
     * @param array $module_list
     */
    public function get($module_obj) {

        if (!isset($this->$module_obj)) {

            return false;
        }

        $this->model = $module_obj["model"];
        $this->class = $module_obj["class"];
        $this->func = $module_obj["func"];

        return true;
    }

    /**
     * 实例化访问对象
     * 从接口传入的参数组装成新的访问对象
     * @param varchar(40) $signature
     * @param int $timestamp
     * @param varchar(8) $nonce
     * @param int $customer_id
     * @param smallint $site_id
     * @param int $sha1
     */
    public function set($signature, $timestamp, $nonce, $seller_id, $shop_id, $user_id, $module_id, $client_ip, $site_url) {
        $this->signature = $signature;
        $this->timestamp = $timestamp;
        $this->nonce = $nonce;
        $this->seller_id = $seller_id;
        $this->shop_id = $shop_id;
        $this->user_id = $user_id;
        $this->module_id = $module_id;
        $this->client_ip = $client_ip;
        $this->site_url = $site_url;

        $this->webserver_ip = self::getIP();
    }

    public function checkSign($token) {

        $is_volid = false;

        $signature = isset($this->signature) ? $this->signature : '';
        $timestamp = isset($this->timestamp) ? $this->timestamp : '';
        $nonce = isset($this->nonce) ? $this->nonce : '';
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        
        if ($tmpStr == $signature) {
            $is_volid = true;
        }

        return $is_volid;
    }

    public static function getIP() {

        global $ip;
        if (getenv("HTTP_CLIENT_IP")) {
            $ip = getenv("HTTP_CLIENT_IP");
        } else if (getenv("HTTP_X_FORWARDED_FOR")) {
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        } else if (getenv("REMOTE_ADDR")) {
            $ip = getenv("REMOTE_ADDR");
        } else {
            $ip = "Unknow";
        }

        return $ip;
    }

    /**
     * 或的url的host
     * 2013年4月26日20:33:25
     * 2013年5月9日20:28:05
     */
    function parse_host($url) {
        if (!is_string($url) || $url == '')
            return "";

        $info = parse_url($url);
        $host = isset($info['host']) ? $info['host'] : (isset($info['path']) ? $info['path'] : "" );
        if ($host == "")
            return "";

        if (preg_match('/^192\.168\.\d{1,3}\.\d{1,3}¦127\.\d{1,3}\.\d{1,3}\.\d{1,3}¦255\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $host))
            return "";
        if (!preg_match('/\.[a-z]+$/i",$host) && !preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $host))
            return "";
        return $host;
    }

    /*
     * 获得url的domain
     * 2013年5月9日20:27:56
     */

    function parse_domain($url) {
        $host = $this->parse_host($url);
        if ($host === "")
            return "";

        // 纯IP
        if (preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $host)) {
            preg_match('/^(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})$/', $host, $matches);
            if ($matches)
                return $matches[1];
        }
        else {
            preg_match('/(.*?)([^\.]+\.[^\.]+)$/', $host, $matches);
            if ($matches)
                return $matches[2];
        }
        return "";
    }

    /**
     * 生成随机数
     * @param int $length
     */
    public static function randomkeys($length) {
        $key = "";

        $pattern = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';

        for ($i = 0; $i < $length; $i++) {
            $key .= $pattern{mt_rand(0, 35)};    //生成php随机数
        }

        return $key;
    }

    /**
     * 给Web站点生成一个随机序列号
     * @param varchar(250) $site_url
     */
    public static function publishToken($site_url) {
        $timestamp = time();
        $nonce = self::randomkeys(self::LEN_NONCE);

        $new_token = md5($site_url . $timestamp . $nonce);
        $new_token = substr($new_token, 2, 15);

        return array(
            "token" => $new_token,
            "timestamp" => $timestamp,
            "nonce" => $nonce);
    }

}

$Server = new \Yar_Server(new Services());
$Server->handle();
?>