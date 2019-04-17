<?php
namespace Vendor\Paypal;
/* *
 * 类名：AlipaySubmit
 * 详细：构造各接口表单HTML文本，获取远程HTTP数据
 * 版本：3.3
 * 日期：2012-07-23
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。
 */
class Paypal {

    private $apiURL = 'https://sandbox.paypal.com/cgi-bin/webscr';
    //private $apiURL = 'https://www.paypal.com/cgi-bin/webscr';
	/**
	 * 构造表单
	 * $params 具体需要的参数
	 * $method 发送方式
	 * $charset 文件编码
	 */
	public function _buildForm($params, $method = 'post', $charset = 'utf-8')
	{
		$gateway=$this->apiURL;
		header("Content-type:text/html;charset={$charset}");
		$sHtml = "<form id='paysubmit' name='paysubmit' action='{$gateway}' method='{$method}'>";
	
		foreach ($params as $k => $v) {
			$sHtml .= "<input type=\"hidden\" name=\"{$k}\" value=\"{$v}\" />\n";
		}
	
		$sHtml = $sHtml . "</form>Loading......";
	
		$sHtml = $sHtml . "<script>document.forms['paysubmit'].submit();</script>";
		return $sHtml;
	}
	
	public function notifyFromCurl($email,$postData, $timeout = 30)
	{	
		if (empty(I('txn_id')) || I('receiver_email') != $email){
			return false;
		}
		foreach ($postData as $k=>$v){
			$postData[$k]=urlencode(stripslashes($v));
		}
		$postData = array_merge($postData, array("cmd" => "_notify-validate"));
		
		$match = parse_url($this->apiURL);
		$scheme = $match['scheme'];
		//        $host = $match['host'];
		//        $path = $match['path'];
		$ssl = $scheme == 'https' ? true : false;
		if (function_exists('curl_init')) {
			$ch = curl_init();
			$opt = array(
					CURLOPT_URL => $this->apiURL,
					CURLOPT_POST => 1,
					CURLOPT_HEADER => 1,
					CURLOPT_POSTFIELDS => $postData,
					CURLOPT_RETURNTRANSFER => 1,
					CURLOPT_TIMEOUT => $timeout,
			);
			if ($ssl) {
				$opt[CURLOPT_SSL_VERIFYHOST] = false;
				$opt[CURLOPT_SSL_VERIFYPEER] = false;
			}
			curl_setopt_array($ch, $opt);
			$data = curl_exec($ch);
			$status = curl_getinfo($ch);
			$errno = curl_errno($ch);
			curl_close($ch);
			var_dump($status,$errno,$data);
			
			if ($errno || $status['http_code'] != 200) {
				return;
			} else {
				$data = substr($data, $status['header_size']);
			
				return $data;
			}
		} else {
			E('curl not callable');
		}
	}
	/** 
	 * 第三方IPN通知
	 * PayPal接受到客户的付款后，向您的服务器指定的URL通过POST方式发送IPN；
	 * 在您的服务器收到IPN之后，您必须将收到的POST信息对原样返回给PayPal进行验证，PayPal通过此方法帮您防范欺骗或“中间人”攻击；（对IPN信息的验证过程我们称之为通知确认）； 
	 * PayPal返回验证信息，通过验证为VERIFIED，不通过则为INVALD； 
	 */
	/*IPN即时通知*/
	public function notifyurl($email,$post = array())
	{
		$is_valid = false;
		var_dump(I('txn_id'),I('receiver_email'));
		//判断返回数据
		if (empty(I('txn_id')) || I('receiver_email') != $email){
			return $is_valid;
		}
		//从 PayPal 出读取 POST 信息同时添加变量cmd
		$req = 'cmd=_notify-validate';
		foreach ($_POST as $key => $value) 
		{
			$value = urlencode(stripslashes($value));
			$req .= "&$key=$value";
		}
		
		//建议在此将接受到的信息记录到日志文件中以确认是否收到 IPN 信息
		//将信息 POST 回给 PayPal 进行验证		
		//从 PayPal 出读取 POST 信息同时添加变量„cmd‟
		//将信息	POST	回给	PayPal	进行验证			
		$header	.=	"POST /cgi-bin/webscr HTTP/1.0\r\n";					
		$header	.=	"Content-Type:application/x-www-form-urlencoded\r\n";					
		$header	.=	"Content-Length:".strlen($req)."\r\n\r\n";		
		//在	Sandbox	情况下，设置：					
		$fp	=	fsockopen("www.sandbox.paypal.com",80,$errno,$errstr,30);					
 		//$fp	=	fsockopen	('www.paypal.com',	80,	$errno,	$errstr,	30);
		//将	POST	变量记录在本地变量中					
		//该付款明细所有变量可参考：							
		//https://www.paypal.com/IntegrationCenter/ic_ipn-pdt-variable-reference.html							
		$item_name	=	$_POST['item_name'];					
		$item_number	=	$_POST['item_number'];					
		$payment_status	=	$_POST['payment_status'];					
		$payment_amount	=	$_POST['mc_gross'];					
		$payment_currency	=	$_POST['mc_currency'];					
		$txn_id	=	$_POST['txn_id'];					
		$receiver_email	=	$_POST['receiver_email'];					
		$payer_email	=	$_POST['payer_email'];
		//…							
		//判断回复	POST是否创建成功					
		
		if	(!$fp)	
		{		
			var_dump("can't connect ");
		//HTTP错误						
		}else
		{						
			//将回复POST信息写入	SOCKET	端口			
			fputs($fp,	$header	.$req);
			//开始接受	PayPal对回复POST信息的认证信息	
			$res = "";
			while(!feof($fp)){	
				echo 'in while';
				$res .=fgets($fp,	1024);		
var_dump($res);				
			}							
			fclose	($fp);	
			echo 'after close';
			var_dump($res);
				var_dump($errno);
				var_dump($errstr);			
			//已经通过认证							
			if	(strcmp	($res,	VERIFIED)	==	0)	{	
				//检查付款状态							
				//检查	txn_id	是否已经处理过					
				//检查	receiver_email	是否是您的	PayPal	账户中的	EMAIL	地址	
				//检查付款金额和货币单位是否正确							
				//处理这次付款，包括写数据库	
				$is_valid = true;

			}else	if	(strcmp	($res,	INVALID)	==	0)	{
				//未通过认证，有可能是编码错误或非法的	POST	信息

			} 
			
			return $is_valid;
		}
	}
	
}
?>