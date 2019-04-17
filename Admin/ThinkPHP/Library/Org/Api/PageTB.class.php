<?php
namespace Org\Api;
class PageTB {
	
	// 分页栏每页显示的页数
	public $rollPage = 7;
	// 页数跳转时要带的参数
	public $parameter  ;
	// 分页URL地址
	public $url     =   '';
	// 默认列表每页显示行数
	public $listRows = 1;
	// 起始行数
	public $firstRow    ;
	// 分页总页面数
	protected $totalPages  ;
	// 总行数
	protected $totalRows  ;
	// 当前页数
	protected $nowPage    ;
	// 分页的栏的总页数
	protected $coolPages   ;
	// 分页显示定制
	protected $config ;
	// 默认分页变量名
	protected $varPage;
	
	//语言
	protected $lang;

	/**
	 * 架构函数
	 * @access public
	 * @param array $totalRows  总的记录数
	 * @param array $listRows  每页显示记录数
	 * @param array $parameter  分页跳转的参数
	 * @param array $hasInput   是否显示页码输入框
	 */
	public function __construct($totalRows,$listRows='',$parameter='',$url='') {
		$this->totalRows    =   $totalRows;
		$this->parameter    =   $parameter;
		$this->varPage      =   C('VAR_PAGE') ? C('VAR_PAGE') : 'p' ;
		if(!empty($listRows)) {
			$this->listRows =   intval($listRows);
		}
		$this->totalPages   =   ceil($this->totalRows/$this->listRows);     //总页数
		$this->coolPages    =   ceil($this->totalPages/$this->rollPage);
		$this->nowPage      =   !empty($_GET[$this->varPage])?intval($_GET[$this->varPage]):1;
		if($this->nowPage<1){
			$this->nowPage  =   1;
		}elseif(!empty($this->totalPages) && $this->nowPage>$this->totalPages) {
			$this->nowPage  =   $this->totalPages;
		}
		$this->firstRow     =   $this->listRows*($this->nowPage-1);
		if(!empty($url))    $this->url  =   $url; 

		$this->initConfig();
	}
	/**
	 * 初始化分页显示定制信息
	 * @param string $lang 语言类型
	 */
	protected function initConfig(){
		//如果开启了语言包
		if(C('LANG_SWITCH_ON')){
			$this->config=array(
								'prevPage'  =>  L('prevPage'),
								'nextPage'  =>  L('nextPage'),
								'totalPages'=>  L('totalPages'),
								'pages'     =>  L('pages'),
								'page'      =>  L('page'),
								'goPage'    =>  L('goPage'),
								'goBtn'     =>  L('goBtn')
								);			
		}else{
			$this->config=array(
								'prevPage'  =>  '上一页',
								'nextPage'  =>  '下一页',
								'totalPages'=>  '共',
								'pages'     =>  '页，',
								'page'      =>  '页',
								'goPage'    =>  '到第',
								'goBtn'     =>  '确定'
								);
		}
		$this->config['theme']='<div class="pageOut"><div class="pagination"><ul>%uppage%%linkPage%%downPage%</ul><div class="totalPages">%totalPages%</div><div class="from">%from%</div><div class="cl"></div></div></div>';
	}
	public function setConfig($name,$value) {
		if(isset($this->config[$name])) {
			$this->config[$name]    =   $value;
		}
	}
	/**
	 * 分页显示输出
	 * @access public
	 */
	public function show() {
		if($this->totalPages<2) return '';
		$p              =   $this->varPage;
		$nowCoolPage    =   ceil($this->nowPage/$this->rollPage);
		// 分析分页参数
		if($this->url){
			$depr       =   C('URL_PATHINFO_DEPR');
			$url        =   rtrim(U('/'.$this->url,'',false),$depr).$depr.'__PAGE__';
		}else{
			if($this->parameter && is_string($this->parameter)) {
				parse_str($this->parameter,$parameter);
			}elseif(is_array($this->parameter)){
				$parameter      =   $this->parameter;
			}elseif(empty($this->parameter)){
				unset($_GET[C('VAR_URL_PARAMS')]);
				$var =  !empty($_POST)?$_POST:$_GET;
				if(empty($var)) {
					$parameter  =   array();
				}else{
					$parameter  =   $var;
				}
			}
			$parameter[$p]  =   '__PAGE__';
			$url            =   U('',$parameter);
		}
		//上下翻页字符串
		$upRow          =   $this->nowPage-1;
		$downRow        =   $this->nowPage+1;
		if ($upRow>0){
			$upPage     =    "<li class=\"item prev\"><a href='".str_replace('__PAGE__',$upRow,$url)."'>".$this->config['prevPage']."</a></li>";
		}else{
			$upPage     =    '<li class="item prev prev-disabled"><span>'.$this->config['prevPage'].'</span></li>';
		}
		if ($downRow <= $this->totalPages){
			$downPage   =   '<li class="item next"><a href="'.str_replace('__PAGE__',$downRow,$url).'">'.$this->config['nextPage'].'</a></li>';
		}else{
			$downPage   =   '<li class="item next next-disabled"><span>'.$this->config['nextPage'].'</span></li>';
		}
		
		// 1 2 3 4 5
		$linkPage = "";
		$leftPage=2+ceil(($this->rollPage-2)/2);
		if($this->nowPage>$leftPage){
			for($j=1;$j<=2;$j++){
				$linkPage .= "<li class=\"item num\"><a href='".str_replace('__PAGE__',$j,$url)."'>".$j."</a></li>";
			}
			$currRollPage=$this->rollPage-2;
			$linkPage.='<li class="item dot">...</li>';
		}else{
			$currRollPage=$this->rollPage;
		}
		
		//计算显示的最大页数和最小页数
		$halfPages=floor($currRollPage/2);
		$minPage=$this->nowPage-$halfPages;
		$maxPage=$this->nowPage+$halfPages;
		if($minPage<1){
			$maxPage-=$minPage;
			$minPage=1;
		}elseif($minPage==2){
			$minPage=1;
			$maxPage-=1;
		}
		if($maxPage>$this->totalPages){
			$minPage=$minPage-($maxPage-$this->totalPages);
			$maxPage=$this->totalPages;
		}
		if($minPage<1){
			$minPage=1;
		}
		
		for($i=$minPage;$i<=$maxPage;$i++){
			$page       =   $i;
			if($page!=$this->nowPage){
				if($page<=$this->totalPages){
					$linkPage .= "<li class=\"item num\"><a href='".str_replace('__PAGE__',$page,$url)."'>".$page."</a></li>";
				}else{
					break;
				}
			}else{
				if($this->totalPages != 1){
					$linkPage .= "<li class=\"item active\"><span>".$page."</span></li>";
				}
			}
		}
		if($this->totalPages>$maxPage){
			$linkPage.='<li class="item dot">...</li>';
		}
		//总页数
		$totalPages=$this->config['totalPages'].$this->totalPages.$this->config['pages'];
		//去那一页
		$from=$this->config['goPage'].'<input type="text" id="pageText" class="input-text" />'.$this->config['page'];
		$from.='<input class="btn-go" type="button" value="'.$this->config['goBtn'].'" onclick="goPage('.$this->totalPages.');">';
		$from.='<input id="goPageStr" type="hidden" value="'.str_replace('__PAGE__','{PAGE}',$url).'" />';
		$pageStr     =   str_replace(
			array('%uppage%','%linkPage%','%downPage%','%totalPages%','%from%'),
			array($upPage,$linkPage,$downPage,$totalPages,$from),$this->config['theme']);
		return $pageStr;
	}
}