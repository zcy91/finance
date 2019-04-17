<?php

namespace Think\Pay\Driver;
/** 
 * 共启支付
 *  */
class Qiyuan extends \Think\Pay\Pay {
// 	protected $gateway = 'http://cashier-test.gofpay.com/v1/gateway';//测试环境
        protected $gateway = 'https://cashier.gofpay.com/v1/gateway';//真实环境
	protected $Mode="Redirect";
    protected $config = array(
    	'mode'=>'',
        'key' => '',
        'websiteId' => ''
    );
	/*
	 * (non-PHPdoc) @see \Think\Pay\Pay::buildRequestForm()
	 */
	
	public function check() {
		$this->config['mode'] = isset($this->config['qiyuan_mode']) ? $this->config['qiyuan_mode'] : '';
        $this->config['key'] = isset($this->config['qiyuan_key']) ? $this->config['qiyuan_key'] : '';
        $this->config['websiteId'] = isset($this->config['websiteId']) ? $this->config['websiteId'] : '';
        if (!$this->config['mode'] || !$this->config['key'] || !$this->config['websiteId']) {
//            E(L('ALIPAY_PARAM_ERROR'));
              return false;
        }
        return true;
	}
	public function buildRequestForm(\Think\Pay\PayVo $vo) {
		/* 取得订单详情，，，部分参数组成签名 */	
		$orderdetail=$vo->getorderdetail();
		$para=array(
				"WebsiteId"=>$this->config['websiteId'],
				"OrderId"=>$orderdetail['OrderId'],
				"Email"=>$orderdetail['Email'],
				"Currency"=>$orderdetail['Currency'],
				"Amount"=>$orderdetail['Amount'],
				"Discount"=>$orderdetail['Discount'],
				"Tax"=>$orderdetail['Tax'],
				"Freight"=>$orderdetail['Freight'],
				"key"=>$this->config['key'],
		);
		// 构造要请求的参数数组
		$param = array ();
		foreach ($vo->getorderdetail() as $k=>$v){
			$param[$k]=$v;
		}
		$param['WebsiteId']=$this->config['websiteId'];
		$param['Mode']=$this->config['mode'];
		foreach ($vo->getproductdetail() as $k=>$v){
			$param[$k]=$v;
		}
		foreach ($vo->getShipping() as $k=>$v){
			$param[$k]=$v;
		}
		foreach ($vo->getbill() as $k=>$v){
			$param[$k]=$v;
		}
		
		$param ['Signature'] = $this->createSign ( $para );
		$sHtml = $this->_buildForm ( $param, $this->gateway, 'post' );
		return $sHtml;
	}
	
	
	public function verifyNotify($notify) {
		//所有的参数加上秘钥组成签名
		
		//生成签名结果
        $isSign = $this->getSignVeryfy($notify, $notify["signature"]);
        if ($isSign) {
            return true;
        } else {
            return false;
        }
	}
	/**
	 * 获取返回时的签名验证结果
	 * @param $para_temp 通知返回来的参数数组
	 * @param $sign 返回的签名结果
	 * @return 签名验证结果
	 */
	protected function getSignVeryfy($param, $sign) {
		//除去待签名参数数组中的空值和签名参数
		$param_filter = array();
		while (list ($key, $val) = each($param)) {
			if ($key == "signature" || $key == "sign_type" || $val == "" || $key == "apitype" || $key=="method") {
				continue;
			} else {
				$param_filter[$key] = $param[$key];
			}
		}
		//把数组所有元素按照顺序拼接成字符串
		$prestr = "";
		while (list ($key, $val) = each($param_filter)) {
			$prestr.=$val ;
		}
		$mysgin = md5($prestr.$this->config['key']);/*md5($prestr.$this->config['key']);	*/
		if ($mysgin == $sign) {
			return true;
		} else {
			return false;
		}
	}
	
	
	/**
	 * 创建MD5签名
	 * 
	 * @param array $para        	
	 * @return string
	 */
	protected function createSign($para) {
		$arg = "";
		while ( list ( $key, $val ) = each ( $para ) ) {
			if ($key == "sign" || $key == "sign_type" || $val == "")
				continue;
			$arg .= $val;
		}
		return md5 ( $arg);
	}
}

?>