<?php
namespace Vendor\Wxpay;

use Vendor\Wxpay\lib\WxPayConfig;
use Vendor\Wxpay\lib\WxPayApi;
use Vendor\Wxpay\lib\WxPayNotify;
use Vendor\Wxpay\lib\WxPayOrderQuery;
class WxpayNotice extends WxPayNotify
{
        var $wxpay_config;
        var $return_data;

        function __construct($wxpay_config){
            
            $this->wxpay_config = $wxpay_config;
            
            WxPayConfig::$APPID = $wxpay_config["APPID"];
            WxPayConfig::$MCHID = $wxpay_config["MCHID"];
            WxPayConfig::$KEY = $wxpay_config["KEY"];
            WxPayConfig::$APPSECRET = $wxpay_config["APPSECRET"];
            WxPayConfig::$SSLCERT_PATH = $wxpay_config["SSLCERT_PATH"];
            WxPayConfig::$SSLKEY_PATH = $wxpay_config["SSLKEY_PATH"];
        }
	//查询订单
	public function Queryorder($transaction_id)
	{
		$input = new WxPayOrderQuery();
		$input->SetTransaction_id($transaction_id);
		$result = WxPayApi::orderQuery($input);
		//Log::DEBUG("query:" . json_encode($result));
		if(array_key_exists("return_code", $result)
			&& array_key_exists("result_code", $result)
			&& $result["return_code"] == "SUCCESS"
			&& $result["result_code"] == "SUCCESS")
		{
			return true;
		}
		return false;
	}
	
	//重写回调处理函数
	public function NotifyProcess($data, &$msg)
	{		
		if(!array_key_exists("transaction_id", $data)){
			$msg = "输入参数不正确";
			return false;
		}
		//查询订单，判断订单真实性
		if(!$this->Queryorder($data["transaction_id"])){
			$msg = "订单查询失败";
			return false;
		}
                
                //设置返回数据    
                $this->return_data = json_encode($data);
                
		return true;
	}
}

