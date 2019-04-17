<?php
namespace Kuba\Core;

class MessageBase{
	
	/**
	 * 消息类型
	 * I：信息
	 * W：警告
	 * 
	 * @var char(1)
	 */
	public $msg_type;
	public $msg_code;
	public $msg_content;
}

final class MessageFactory{
	
	public static function get($msg_code){
		$msg_list = array (
  				-1=> array('msg_type' => 'I','msg_content' => '服务器没有正常运行'),
				-2=> array('msg_type' => 'I','msg_content' => '初始化失败'),
				-3=> array('msg_type' => 'I','msg_content' => '取不到模块值'),
				-4=> array('msg_type' => 'I','msg_content' => '取不到客户对象'),
				-5=> array('msg_type' => 'I','msg_content' => '验证失败'),
				-6=> array('msg_type' => 'I','msg_content' => '业务逻辑处理失败'),
				-7=> array('msg_type' => 'I','msg_content' => '数据库操作失败'),
				-8=> array('msg_type' => 'I','msg_content' => '发生不可预测异常'),
			);
		
		$cur_data = $msg_list[$msg_code];
		
		$msg_obj = new MessageBase();
		
		$msg_obj ->msg_code = $msg_code;
		
		if(isset($cur_data)){
			$msg_obj->msg_content = $cur_data["msg_content"];
			$msg_obj->msg_type = $cur_data["msg_type"];
		}
		
		return $msg_obj;		
	}
}

/**
 * 函数返回码	说明
 * 0	处理成功
 * -40001	校验签名失败
 * -40002	解析xml失败
 * -40003	计算签名失败
 * -40004	不合法的AESKey
 * -40005	校验AppID失败
 * -40006	AES加密失败
 * -40007	AES解密失败
 * -40008	公众平台发送的xml不合法
 * -40009	Base64编码失败
 * -40010	Base64解码失败
 * -40011	公众帐号生成回包xml失败
 * @author Administrator
 *
 */
class YarError extends \Exception{
	
}
class DBError extends \Exception{

}
class LogicsError extends \Exception{
	
}

class DisplayError extends \Exception{
	
}
/*
 * protected $message = 'Unknown exception'; // 异常信息 
 * protected $code = 0; // 用户自定义异常代码 
 * protected $file; // 发生异常的文件名 
 * protected $line; // 发生异常的代码行号 
 * function __construct($message = null, $code = 0); 
 * final function getMessage(); // 返回异常信息 
 * final function getCode(); // 返回异常代码 
 * final function getFile(); // 返回发生异常的文件名 
 * final function getLine(); // 返回发生异常的代码行号 
 * final function getTrace(); // backtrace() 数组 
 * final function getTraceAsString(); // 已格成化成字符串的 getTrace() 信息 
 *  可重载的方法 
 * function __toString(); // 可输出的字符串 
 */
?>