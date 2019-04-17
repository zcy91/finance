<?php 
namespace Vendor\Wxpay;

use Vendor\Wxpay\lib\WxPayConfig;
use Vendor\Wxpay\lib\WxPayApi;
use Vendor\Wxpay\JsApiPay;
use Vendor\Wxpay\lib\WxPayUnifiedOrder;

class WxpaySubmit{
    
    var $wxpay_config;

    function __construct($wxpay_config){
        
        $this->wxpay_config = $wxpay_config;
            
        WxPayConfig::$APPID = $wxpay_config["APPID"];
        WxPayConfig::$MCHID = $wxpay_config["MCHID"];
        WxPayConfig::$KEY = $wxpay_config["KEY"];
        WxPayConfig::$APPSECRET = $wxpay_config["APPSECRET"];
        WxPayConfig::$SSLCERT_PATH = $wxpay_config["SSLCERT_PATH"];
        WxPayConfig::$SSLKEY_PATH = $wxpay_config["SSLKEY_PATH"];
    }
    
    function buildRequestForm($request_params){
        
        $tools = new JsApiPay();
        
        $openId = $tools->GetOpenid();
        
        //②、统一下单
        $input = new WxPayUnifiedOrder();
        $input->SetBody($request_params['Body']);
        $input->SetAttach($request_params['Attach']);
        $input->SetOut_trade_no($request_params['Out_trade_no']);
        $input->SetTotal_fee($request_params['Total_fee']);
        $input->SetTime_start($request_params['Time_start']);
        $input->SetTime_expire($request_params['Time_expire']);
        $input->SetGoods_tag($request_params['Goods_tag']);
        $input->SetNotify_url($request_params['Notify_url']);
        $input->SetTrade_type($request_params['Trade_type']);
        
        $input->SetOpenid($openId);
        
        $order = WxPayApi::unifiedOrder($input);
        
        //echo '<font color="#f00"><b>统一下单支付单信息</b></font><br/>';
        //var_dump($order);
        
        $result = [];
        
        $jsApiParameters = $tools->GetJsApiParameters($order);
        
        $result['jsApiParameters'] = $jsApiParameters;
        
        $editAddress = $tools->GetEditAddressParameters();
        
        $result['editAddress'] = $editAddress;
        
        return $result;
    }
    
}