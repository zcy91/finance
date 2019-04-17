<?php
//短信发送类
namespace SmallProgram\Plugin;
class Sms 
{
	var $sms_server;
	var $sms_account;
	var $sms_password;
	var $param;
	
	public function __construct($sms_server='smsbao',$sms_account='flb520',$sms_password='jiuyukeji123',$param = array()){
		$this->sms_server = $sms_server; 
		$this->sms_account = $sms_account; 
		$this->sms_password = $sms_password;
		$this->param = $param;
	}
	public function sendsms($phone,$content){
		//短信宝
		if($this->sms_server =="smsbao"){
			$sms_re = $this->re_curl('http://api.smsbao.com/sms?u='.$this->sms_account.'&p='.md5($this->sms_password).'&m='.$phone.'&c='.urlencode($content));
			
			if($sms_re){
				$send=0;
			}else{
				$send=1;
			}
		}
		return $send;
	}
	protected function re_curl($url){
		if(function_exists('file_get_contents')) {
			$file_contents = file_get_contents($url);
		} else {
			$ch = curl_init();
			$timeout = 5;
			curl_setopt ($ch, CURLOPT_URL, $url);
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			$file_contents = curl_exec($ch);
			curl_close($ch);
		}
		return $file_contents;
	}
}
?> 