<?php
namespace console;

use yii\base\Component;
/**
 * 处理返回值给本地调用方
 * @author Thinkpad
 *
 */
class LocalResponse extends Component
{
	/**
	 * 调用接口的返回值
	 * returnState
	 * 1表示成功；
	 * 0表示没有查到数据；
	 * 负数表示出现错误（负数的绝对值对应的错误信息）
	 * @var smallint
	 */
	public  $RetrunData = array();	
	
	public function onSuccess($rtn_data)
	{
		$this->RetrunData["returnState"] = 1;
		$this->RetrunData["returnData"] = $rtn_data;	
		
		$this->collect();
	}
	
	public function onFailure($error_code,$rtn_data = null)
	{
		$this->RetrunData["returnState"] = $error_code;		
		$this->RetrunData["returnData"] = $rtn_data;
		
		$this->collect();
	}

	private function collect()
	{
		\Yii::$app->ReturnData = $this->RetrunData;
	}
}