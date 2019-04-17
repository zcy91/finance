<?php
namespace Vendor\Qiyuan;
/* *
 * 类名：AlipaySubmit
 * 功能：支付宝各接口请求提交类
 * 详细：构造支付宝各接口表单HTML文本，获取远程HTTP数据
 * 版本：3.3
 * 日期：2012-07-23
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。
 */
class QiyuanSubmit {

	/**
	 * 构造表单
	 */
	public function _buildForm($params, $gateway, $method = 'post', $charset = 'utf-8')
	{
	
		header("Content-type:text/html;charset={$charset}");
		$sHtml = "<form id='paysubmit' name='paysubmit' action='{$gateway}' method='{$method}'>";
	
		foreach ($params as $k => $v) {
			$sHtml .= "<input type=\"hidden\" name=\"{$k}\" value=\"{$v}\" />\n";
		}
	
		$sHtml = $sHtml . "</form>Loading......";
	
		$sHtml = $sHtml . "<script>document.forms['paysubmit'].submit();</script>";
		return $sHtml;
	}
}
?>