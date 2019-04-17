<?php

namespace Think\Pay\Driver;

class Alipay extends \Think\Pay\Pay {

    protected $gateway = 'https://mapi.alipay.com/gateway.do';
    protected $verify_url = 'http://notify.alipay.com/trade/notify_query.do';
    protected $config = array(
        'email' => '',
        'key' => '',
        'partner' => ''
    );       

    public function check() {
        $this->config['email'] = isset($this->config['alipay_account']) ? $this->config['alipay_account'] : '';
        $this->config['key'] = isset($this->config['alipay_key']) ? $this->config['alipay_key'] : '';
        $this->config['partner'] = isset($this->config['alipay_partner']) ? $this->config['alipay_partner'] : '';
        if (!$this->config['email'] || !$this->config['key'] || !$this->config['partner']) {
//            E(L('ALIPAY_PARAM_ERROR'));
              return false;
        }
        return true;
    }

    public function buildRequestForm(\Think\Pay\PayVo $vo) {
        $param = array(
            'service' => 'create_direct_pay_by_user',
            'payment_type' => '1',
            '_input_charset' => 'utf-8',
            'seller_email' => $this->config['email'],
            'partner' => $this->config['partner'],
            'notify_url' => $this->config['notify_url'],
            'return_url' => $this->config['return_url'],
            'out_trade_no' => $vo->getOrderNo(),
            'subject' => $vo->getTitle(),
            'body' => $vo->getBody(),
            'total_fee' => $vo->getFee()
        );

        ksort($param);
        reset($param);

        $arg = '';
        foreach ($param as $key => $value) {
            if ($value) {
                $arg .= "$key=$value&";
            }
        }

        $param['sign'] = md5(substr($arg, 0, -1) . $this->config['key']);
        $param['sign_type'] = 'MD5';

		

        $sHtml = $this->_buildForm($param, $this->gateway, 'get');

        return $sHtml;
    }

    /**
     * 获取返回时的签名验证结果
     * @param $para_temp 通知返回来的参数数组
     * @param $sign 返回的签名结果
     * @return 签名验证结果
     */
    protected function getSignVeryfy($param, $sign) {
        //除去待签名参数数组中的空值和签名参数
		unset($param['_URL_']);
        $param_filter = array();
        while (list ($key, $val) = each($param)) {
            if ($key == "sign" || $key == "sign_type" || $val == "") {
                continue;
            } else {
                $param_filter[$key] = $param[$key];
            }
        }

        ksort($param_filter);
        reset($param_filter);

        //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $prestr = "";
        while (list ($key, $val) = each($param_filter)) {
            $prestr.=$key . "=" . urlencode($val) . "&";
        }
        //去掉最后一个&字符
        $prestr = substr($prestr, 0, -1);
        echo '----------getSignVeryfy'.'-------1---------';
var_dump($prestr);
echo '----------getSignVeryfy'.'--------2--------';
        $prestr = $prestr . $this->config['key'];
        var_dump($prestr);
        echo '----------getSignVeryfy'.'--------3--------';
        $mysgin = md5($prestr);
echo 'mysign:'.$mysgin.'----'.$sign;
        if ($mysgin == $sign) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 针对notify_url验证消息是否是支付宝发出的合法消息
     * @return 验证结果
     */
    public function verifyNotify($notify) {
        echo '---------verity------';
        var_dump($notify);
        //生成签名结果
        $isSign = $this->getSignVeryfy($notify, $notify["sign"]);
        //获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
        $responseTxt = 'true';
        
        echo 'isSing:'.'---'.$isSign.'------';
        
        if (!empty($notify["notify_id"])) {
            $responseTxt = $this->getResponse($notify["notify_id"]);
            echo '-------------responseTxt'.$responseTxt.'------------';
        }

        if (preg_match("/true$/i", $responseTxt) && $isSign) {
            echo '----preg_match--------';
            $this->setInfo($notify);
            echo '----after preg_match--------';
            return true;
        } else {
            return false;
        }
    }

    protected function setInfo($notify) {
        $info = array();
        //支付状态
        $info['status'] = ($notify['trade_status'] == 'TRADE_FINISHED' || $notify['trade_status'] == 'TRADE_SUCCESS') ? true : false;
        $info['money'] = $notify['total_fee'];
        $info['out_trade_no'] = $notify['out_trade_no'];
        $this->info = $info;
    }

    /**
     * 获取远程服务器ATN结果,验证返回URL
     * @param $notify_id 通知校验ID
     * @return 服务器ATN结果
     * 验证结果集：
     * invalid命令参数不对 出现这个错误，请检测返回处理中partner和key是否为空 
     * true 返回正确信息
     * false 请检查防火墙或者是服务器阻止端口问题以及验证时间是否超过一分钟
     */
    protected function getResponse($notify_id) {
        var_dump($this->config);
        $partner = $this->config['partner'];
        $veryfy_url = $this->verify_url . "?partner=" . $partner . "&notify_id=" . $notify_id;
        $cacert = VENDOR_PATH . 'Payments/payment/cacert.pem';
        $responseTxt = $this->getHttpResponseGET($veryfy_url,$cacert);
//        $responseTxt = $this->fsockOpen($veryfy_url);
        return $responseTxt;
    }

}
