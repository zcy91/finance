<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\gii\generators\myinterface;


use Yii;
use yii\gii\CodeFile;
use yii\helpers\Html;
use yii\helpers\Inflector;

/**
 * This generator will generate the skeleton files needed by an myinterface.
 *
 * @property string $keywordsArrayJson A json encoded array with the given keywords. This property is
 * read-only.
 * @property boolean $outputPath The directory that contains the module class. This property is read-only.
 *
 * @author Tobias Munk <schmunk@usrbin.de>
 * @since 2.0
 */
class Generator extends \yii\gii\Generator
{

/**
     * @var string the controller ID
     */
    public $controller;
    /**
     * @var string the base class of the controller
     */
    public $baseClass = 'BaseController';
    /**
     * @var string the namespace of the controller class
     */
    public $ns; //接口控制器路径
    public $nb; //接口行为路径
    public $ne; //接口事件路径
    /**
     * @var string list of action IDs separated by commas or spaces
     */
    public $actions = ''; //方法名称
     
    public $actions_args = ''; //方法参数
    
    public $actions_table = ''; //需要操作的表名称
    
    public $table_data =array();
    
    public $test="a";

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
       $this->ns = \Yii::$app->controllerNamespace;
       $this->nb = "console\behaviors";
       $this->ne = "console\sss";
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return '接口生成器';
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return '这个生成器会生成接口的控制器、行为、和事件三个文件';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['controller', 'actions', 'baseClass', 'ns'], 'filter', 'filter' => 'trim'],
            [['controller', 'baseClass'], 'required'],
            [['controller'], 'match', 'pattern' => '/^[a-z][a-z0-9\\-\\/]*$/', 'message' => 'Only a-z, 0-9, dashes (-) and slashes (/) are allowed.'],
            [['actions'], 'match', 'pattern' => '/^[a-z][a-z0-9\\-,\\s]*$/', 'message' => 'Only a-z, 0-9, dashes (-), spaces and commas are allowed.'],
            [['actions_args'], 'required'],
            [['actions_table'],'required'],
            [['baseClass'], 'match', 'pattern' => '/^[\w\\\\]*$/', 'message' => 'Only word characters and backslashes are allowed.'],
            [['ns'], 'match', 'pattern' => '/^[\w\\\\]*$/', 'message' => 'Only word characters and backslashes are allowed.'],
            [['nb'], 'match', 'pattern' => '/^[\w\\\\]*$/', 'message' => 'Only word characters and backslashes are allowed.'],
            [['ne'], 'match', 'pattern' => '/^[\w\\\\]*$/', 'message' => 'Only word characters and backslashes are allowed.'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'baseClass' => 'Base Class',
            'controller' => '接口控制器名称',
            'actions' => '接口控制器方法',
            'actions_args' => '接口控制器方法的参数，与方法对应，可以留空',
            'actions_table' => '接口需要操作的表名称',
            'ns' => '接口控制器命名空间',
            'nb' => '接口行为命名空间',
            'ne' => '接口事件命名空间',
        ];
    }

    /**
     * @inheritdoc
     */
    public function requiredTemplates()
    {
        return [
            'controller.php',
            'behavior.php',
            'event.php',
        ];
    }

    /**
     * @inheritdoc
     */
    public function stickyAttributes()
    {
        return ['ns','nb','ne', 'baseClass'];
    }

    /**
     * @inheritdoc
     */
    public function hints()
    {
        return [
            'controller' => 'Controller ID should be in lower case and may contain module ID(s) separated by slashes. For example:
                <ul>
                    <li><code>order</code> generates <code>OrderController.php</code></li>
                    <li><code>order-item</code> generates <code>OrderItemController.php</code></li>
                    <li><code>admin/user</code> generates <code>UserController.php</code> within the <code>admin</code> module.</li>
                </ul>',
          //  'actions' => 'Provide one or multiple action IDs to generate empty action method(s) in the controller. Separate multiple action IDs with commas or spaces.
         //       Action IDs should be in lower case. For example:
             //   <ul>
             //       <li><code>index</code> generates <code>actionIndex()</code></li>
              //      <li><code>create-order</code> generates <code>actionCreateOrder()</code></li>
             //   </ul>',
            'ns' => 'This is the namespace that the new controller class will use.',
            'baseClass' => 'This is the class that the new controller class will extend from. Please make sure the class exists and can be autoloaded.',
        ];
    }

    /**
     * @inheritdoc
     */
    public function successMessage()
    {
        $actions = $this->getActionIDs();
        if (in_array('index', $actions)) {
            $route = $this->controller . '/index';
        } else {
            $route = $this->controller . '/' . reset($actions);
        }
        $link = Html::a('try it now', Yii::$app->getUrlManager()->createUrl($route), ['target' => '_blank']);

        return "The controller has been generated successfully. You may $link.";
    }

    /**
     * @inheritdoc
     */
    public function generate()
    {
        $files = [];

        $files[] = new CodeFile(
            Yii::getAlias('@' . str_replace('\\', '/', $this->ns)) . '/' . $this->getControllerClass() . '.php',
            //$this->getControllerFile(),
            $this->render('controller.php')
        );
        $files[] = new CodeFile(
            Yii::getAlias('@' . str_replace('\\', '/', $this->nb)) . '/' . $this->getBehaviorClass() . '.php',
            //$this->getControllerFile(),
            $this->render('behavior.php')
        );
        $files[] = new CodeFile(
            Yii::getAlias('@' . str_replace('\\', '/', $this->ne)) . '/' . $this->getEventClass() . '.php',
            //$this->getControllerFile(),
            $this->render('event.php')
        );
        return $files;
    }

    /**
     * Normalizes [[actions]] into an array of action IDs.
     * @return array an array of action IDs entered by the user
     */
    public function getActionIDs()
    {
        $actions = array_unique(preg_split('/[\s,]+/', $this->actions, -1, PREG_SPLIT_NO_EMPTY));
        sort($actions);

        return $actions;
    }
    /**
     * Normalizes [[actions]] into an array of action IDs.
     * @return array an array of action IDs entered by the user
     */
    public function getActionTableIDs()
    {
        $actions_table = array_unique(preg_split('/[\s,]+/', $this->actions_table, -1, PREG_SPLIT_NO_EMPTY));
        sort($actions_table);

        return $actions_table;
    }
    /**
     * Normalizes [[actions]] into an array of action IDs.
     * @return array an array of action IDs entered by the user
     */
    public function getActionArgsIDs()
    {
        $actions=$this->getActionIDs();
        $actions_args = explode("-",$this->actions_args);
        //sort($actions_args);
        //这里需要将actions和action_args比对 如果是add的方法的话这里需要对参数进行拆分，以便在视图中自动生成
        for($i=0;$i<count($actions);$i++){
             switch($actions[$i]){
                case "fetchall":
                break;
                case "add": //对add方法进行改装
                //开始分拆参数
                //把json格式的args数据筛选出来到各个属性里
        		$args_list = $this->jsonToArr($actions_args[$i]);
                $is_one = 1;//1一维数组[单个添加]2二维数组[批量添加]
                $key_arr = array();
                foreach($args_list as $v){
                    if(is_array($v)){
                        $key_arr = array_keys($v);
                        $is_one = 2;
                    }
                    break;
                }
                if($is_one==1){ //一维数组  检查这组数据和那张表相关
                 $this->test="cccc";
                $action_table=explode(",",$this->actions_table);
                //$args是一维数组
                    foreach($action_table as $key=>$row){
                           $model_obj = new UserSys;
                           $a=$model_obj->checkdata($args_list);
                           $actions_args[$i]=$a; 
                           if($a){  
                             // $actions_args[$i]="set_datas";                            
                              //开始构建数组
                              $this->table_data[$row]=$args_list;  
                           }
                           //下面开始自动生成配置项 以参数为标准
                    } 
                }
        		$time = time();
       
                break;
                default :
                unset($actions_args[$i]);
                break;
             }
        }
        return $actions_args ;
    }
    //-------------------------------------------------JSON串和数组的转换-----------------------------------------
    public function jsonToArr($reData){
		$objData=json_decode($reData);
		$dataArr=$this->objectToArr($objData);
		return $dataArr;
	}
	/**
	 * 对象转数组
	 * @param unknown_type $objData
	 */
	public function objectToArr($objData){
		$reArr=array();
		foreach($objData as $key=>$val){
			if(is_object($val)||is_array($val)){
				$reArr[$key]=self::objectToArr($val);
			}else{
				$reArr[$key]=$val;
			}
		}
		return $reArr;
	}
    //---------------------------------------------------转换完毕----------------------------------------------------
    /**
     * @return string the controller class name without the namespace part.
     */
    public function getControllerClass()
    {
        return Inflector::id2camel($this->getControllerID()) . 'Controller';
    }
     /**
     * @return string the controller class name without the namespace part.
     */
    public function getBehaviorClass()
    {
        return Inflector::id2camel($this->getControllerID()) . 'Behavior';
    }
      /**
     * @return string the controller class name without the namespace part.
     */
    public function getEventClass()
    {
        return Inflector::id2camel($this->getControllerID()) . 'Event';
    }
    /**
     * @return string the controller class name without the namespace part.
     */
    public function getControllerClassName()
    {
        return Inflector::id2camel($this->getControllerID());
    }
    /**
     * @return string the controller ID (without the module ID prefix)
     */
    public function getControllerID()
    {
        if (($pos = strrpos($this->controller, '/')) !== false) {
            return substr($this->controller, $pos + 1);
        } else {
            return $this->controller;
        }
    }

    /**
     * @return \yii\base\Module the module that the new controller belongs to
     */
    public function getModule()
    {
        if (($pos = strrpos($this->controller, '/')) !== false) {
            $id = substr($this->controller, 0, $pos);
            if (($module = Yii::$app->getModule($id)) !== null) {
                return $module;
            }
        }

        return Yii::$app;
    }

    /**
     * @return string the controller class file path
     */
    public function getControllerFile()
    {
        $module = $this->getModule();

        return $module->getControllerPath() . '/' . $this->getControllerClass() . '.php';
    }

    /**
     * @param string $action the action ID
     * @return string the action view file path
     */
    public function getViewFile($action)
    {
        $module = $this->getModule();

        return $module->getViewPath() . '/' . $this->getControllerID() . '/' . $action . '.php';
    }

}
