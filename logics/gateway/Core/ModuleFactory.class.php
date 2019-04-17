<?php

namespace Kuba\Core;

final class ModuleFactory {
    /*
     * 服务器网关调用Yii的实例
     */

    private static $yii_app = null;
    private static $logger_id = 0;

    private static function setServerArgs($route, $request_params, $seller_id = 0, $shop_id = 0, $user_id = 0, $client_ip = "", $site_url = "", $webserver_ip = "", $args = []) {
        $_SERVER["argv"] = array();
        $_SERVER["argv"][] = __FILE__;
        $_SERVER["argv"][] = $route;
        $_SERVER["argv"][] = "";
        $_SERVER["argc"] = 3;
        $_SERVER["request_params"] = empty($request_params) ? "" : $request_params;

        if (!empty($seller_id)) {
            $_SERVER["seller_info"] = array(
                "seller_id" => $seller_id,
                "shop_id" => $shop_id,
                "user_id" => $user_id,
                "client_ip" => $client_ip,
                "site_url" => $site_url,
                "webserver_ip" => $webserver_ip,
                "args" => $args
            );
        }
    }

    public static function run($model, $class, $func, $route, $client_args, $request_params, $seller_id, $shop_id, $user_id, $client_ip, $site_url, $webserver_ip) {

        try {
            //保存访问信息到日志表
            self::saveAccessInfo($model, $class, $func, $client_args, $seller_id, $shop_id, $user_id, $client_ip, $site_url, $webserver_ip);

            self::setServerArgs($route, $request_params, $seller_id, $shop_id, $user_id, $client_ip, $site_url, $webserver_ip, $client_args);

            $returnData = self::invokeBase();

            return $returnData;
        } catch (\Exception $e) {
            //数据库操作执行失败
            if (ModuleFactory::saveErrorInfo(-7, $e) < 1) {
                //如果插入到日志服务器失败，汇报到服务器状态里
                ServerState::errors_add();
            }

            return null;
        }
    }

    public static function saveAccessInfo($model, $class, $func, $args, $seller_id, $shop_id, $user_id, $client_ip, $site_url, $webserver_ip) {
        
        $returnFlag = 1;

        try {
            $args_1 = array(
                "module_name" => $model,
                "controller_name" => $class,
                "action_name" => $func,
                "args" => $args,
                "seller_id" => $seller_id,
                "site_id" => $shop_id,
                "user_id" => $user_id,
                "src_ip" => $client_ip,
                "webserver_url" => $site_url,
                "webserver_ip" => $webserver_ip
            );

            self::setServerArgs("system/sys/syslogadd", $args_1);

            $returnData = self::invokeBase();

            //执行失败时
            if ($returnData["returnState"] < 0) {
                ModuleFactory::$logger_id = 0;
                $returnFlag = 0;
            } else {
                ModuleFactory::$logger_id = $returnData["returnData"];
            }
        } catch (\Exception $e) {
            $returnFlag = 0;
            ServerState::mailTo("访问日志记录失败，请检查日志服务器是否正常。", $e->__toString());
        }

        return $returnFlag;
    }

    public static function saveErrorInfo($error_code, $e) {
        
        $returnFlag = 1;

        try {
            $params = array(
                "logger_id" => ModuleFactory::$logger_id,
                "error_code" => $error_code,
                "message" => "网管模块监控到异常。",
                "sys_trace" => $e->__toString()
            );

            self::setServerArgs("system/sys/syserradd", $params);

            $returnData = self::invokeBase();

            //执行失败时
            if ($returnData["returnState"] < 0) {
                $returnFlag = 0;
            }
        } catch (\Exception $e) {var_dump($e);
            $returnFlag = 0;
        }

        return $returnFlag;
    }

    public static function getModuleInfo($module_id = 0) {

        $returnData = null;

        try {

            if ($module_id > 0) {
                $params = array(
                    "module_id" => $module_id
                );
            } else {
                $params = null;
            }

            self::setServerArgs("system/sys/fetchmoduleinfo", $params);

            $returnData = self::invokeBase();

            //执行失败时
            if ($returnData["returnState"] < 0) {
                $returnData = null;
            }
        } catch (\Exception $e) {
            ServerState::mailTo("访问日志记录失败，请检查日志服务器是否正常。", $e->__toString());
        }

        return $returnData["returnData"];
    }

    public static function getErrorInfo($error_code = 0) {

        $returnData = null;

        try {

            if ($error_code > 0) {
                $params = array(
                    "error_code" => $error_code
                );
            } else {
                $params = null;
            }

            self::setServerArgs("system/sys/fetcherrorinfo", $params);

            $returnData = self::invokeBase();

            //执行失败时
            if ($returnData["returnState"] < 0) {
                $returnData = null;
            }
        } catch (\Exception $e) {
            ServerState::mailTo("访问日志记录失败，请检查日志服务器是否正常。", $e->__toString());
        }

        return $returnData["returnData"];
    }
    
    public static function getSysInfo($error_code = 0) {

        $returnData = null;

        try {

            if ($error_code > 0) {
                $params = array(
                    "error_code" => $error_code
                );
            } else {
                $params = null;
            }

            self::setServerArgs("system/sys/fetcherrorinfo", $params);

            $returnData = self::invokeBase();

            //执行失败时
            if ($returnData["returnState"] < 0) {
                $returnData = null;
            }
        } catch (\Exception $e) {
            ServerState::mailTo("访问日志记录失败，请检查日志服务器是否正常。", $e->__toString());
        }

        return $returnData["returnData"];
    }    

    public static function getSiteInfo($site_url) {

        $returnData = null;
        
        try {
            $data = array(
                "site_url" => $site_url,
            );

            self::setServerArgs("user/seller/fetchsiteinfo", $data);

            $returnData = self::invokeBase();

            //执行失败时
            if ($returnData["returnState"] < 0) {
                $returnData = null;
            }
        } catch (\Exception $e) {
            ServerState::mailTo("访问日志记录失败，请检查日志服务器是否正常。", $e->__toString());
        }

        return $returnData["returnData"];
    }

    public static function invokeBase() { 
        
        if (empty(self::$yii_app)) {
            require(__DIR__ . '/../../logics/index.php');          
            self::$yii_app = $application;
        }
        $route = $_SERVER["argv"][1];
        $args = array($_SERVER["request_params"]);  

        self::$yii_app->runAction($route, $args);
        $returnData = self::$yii_app->ReturnData;
        return $returnData;
    }

}

?>