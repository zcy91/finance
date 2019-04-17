<?php
namespace Home\Controller;
use Think\Controller;
class CommonController extends Controller {
    
    private $AllowAcessUrl = [
        "test.zhenfang123.com",
        "admin.yhjr.com",
        "211.155.230.114:4869",
        "finance123123.com"
    ];  //允许访问的域名列表
    
    //不用验证的方法
    private  $un_check_action = [
            "GET_USER_INFO"
    ];

    protected function _initialize(){

        $allow_acess_url = $this->AllowAcessUrl;
        $acess_url = $_SERVER["HTTP_HOST"];
        if(in_array($acess_url, $allow_acess_url)){
            isset($_SERVER['HTTP_ORIGIN']) ? header('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']) : '';
//            header('Access-Control-Allow-Origin: http://192.168.0.150:8080/');  //不加则允许任务域名访问  否则只允许配置域名访问
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Expose-Headers: Authorization');
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type,X-XSRF-YHJR, Accept, authKey, sessionId");
        }  else {
            echo json_encode(array("status"=>0,"info"=>"非法访问"));exit;
        }
        
        if(!in_array(strtoupper(ACTION_NAME),$this->un_check_action)){
//            $http_cookies = $_SERVER['HTTP_COOKIE'];
//            $check_http_cookie = c_filter_http_cookie($http_cookies);
            $check_http_cookie = cookie("X-XSRF-YHJR");
            if($check_http_cookie != session("X-XSRF-YHJR")){
                $this->ajaxReturn(array("status"=>0,"info"=>"非法访问"));
            }
        }
        
 
    }
}