<?php
namespace Home\Plugin;
class Upfile {
	public $files;
	private $error = false;
	private $url = '';
	private $e = 0;
	private $curl = 'http://211.155.230.114:4869';
	private $sort;
	/**
	 * 功能：CURL 图片上传 构造函数
	 * @param $files 为$_FILES['file'], 也可以是数组
	 * @param $sort 如果为数组，是否需要按顺序返回
	 * 2015-1-3@Alei
	 */
	public function __construct($files = '', $sort = false) {
		$this->sort = $sort;
                
                
		if('' === $files){
			$this->files = $_FILES;
			$this->dealFiles();
		}else{
			$this->files = $files;
		}
		if(empty($this->files)){
                    
			$this->error = '没有上传的文件！';
			return false;
		}
		if(!$this->isfile()){
                    
			$this->error = '文件不存在！';
			//return false;
		}
		$this->up_load();
	}
	/**
	 * 功能：curl上传
	 * 2015-1-3@Alei
	 */
	private function up_load() {
            
		if(is_array($this->files['tmp_name'])){
			for($i=0; $i < count($this->files['tmp_name']); $i++){
				if($this->sort&&!is_file($this->files['tmp_name'][$i])){$this->url[] = '';continue;}
				$file = $this->exec_up($this->files['tmp_name'][$i]);
			}
		}else{
			$file = $this->exec_up($this->files['tmp_name']);
		}
		if($e > 0)
                {
                    $this->error = '你有 '.$e.' 个文件上传失败';                    
                }
		else
                {
                    return true;
                }
	}
	/**
	 * 转换上传文件数组变量为正确的方式
	 * 2015-1-3@Alei
	 */
	private function dealFiles() {
		foreach($this->files as $key=>$file){
			if(!is_array($file['name'])){
				$this->files = $file;
			}
		}
	}
	/**
	 * 功能：执行文件上传
	 * 2015-1-3@Alei
	 */
	private function exec_up($file){
		$info = getimagesize(realpath($file));
		$info = explode('/', $info['mime']);
                                    
		$return_data = exec('curl -H "Content-Type:'.$info[1].'" --data-binary @'.realpath($file).' "http://211.155.230.114:4869/upload"');
		$return_data = json_decode($return_data);
		if($return_data->ret){
			$this->url[] = $this->curl.'/'.$return_data->info->md5;
		}else{
			$this->e++;
		}
	}
	/**
	 * 功能：文件是否存在
	 * 2015-1-3@Alei
	 */
	private function isfile() {
		$isfile = true;
		if(is_array($this->files['tmp_name'])){
			foreach ($this->files['tmp_name'] as $file){
				if(!is_file(realpath($file)))$isfile = false;
			}
		}else{
			if(!is_file(realpath($this->files['tmp_name'])))$isfile = false;
		}
		return $isfile;
	}
	/**
	 * 功能：返回图片网址
	 * 2015-1-3@Alei
	 */
	public function get_url() {
		if(count($this->url) ==1){
			return $this->url[0];
		}else{
			return $this->url;
		}
	}
	/**
	 * 功能：返回错误信息
	 * 2015-1-3@Alei
	 */
	public function get_error() {
		return $this->error;
	}
}