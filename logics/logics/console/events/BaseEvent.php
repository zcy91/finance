<?php
namespace console\events;

use yii\base\Event;
use console\LocalResponse;
use console\behaviors\BaseBehavior;
use console\models\BaseModel;

// 定义事件的关联数据
class BaseEvent extends Event {

    public $error_code = 0;
    public $controllerName = null; //当前控制器名称[全小写]
    public $actionName = null; //当前控制器方法名称[全小写]

    /**
     * 查询时的数据格式
     * @var string
     */
    public $Condition;
    public $ParamsList;
    public $Pagination;
    public $SearchList;
    public $OrderList;
    public $Columns;
    public $RequestArgs;
    public $Route;

    /*
     * 返回客户端的消息封装对象
     */
    private $ResponseObj = null;

    /*
     * 模块处理结果存放的对象
     */
    private $Result = null;

    public function init() {
        parent::init();

        $this->ResponseObj = new LocalResponse();
        //默认设置为业务逻辑处理失败(-6)
        $this->ResponseObj->onFailure(-6);

        $this->Condition = null;
        $this->ParamsList = null;
        $this->Pagination = null;
        $this->SearchList = null;
        $this->OrderList = null;
        $this->Columns = null;
        $this->RequestArgs = null;
    }

    /*
     * 功能：获得控制器方法名
     * 在action调用前使用
     */

    public function set_action_name($controllerName, $actionName, $route) {

        $this->controllerName = $controllerName; //控制器名称
        $this->actionName = $actionName; //当前控制器方法名称
        $this->Route = $route;
    }

    /**
     * 把传入值初始化到传播事件里
     * @param type $args
     * @param type $action_type
     * @param type $extend_action_type 当事件有多种类型的数据的时候，通过该字段自信区分
     */
    public function set($args, $action_type, $extend_action_type = '') {
        $this->RequestArgs = $args;

        switch ($action_type) {
            case BaseBehavior::ADD_ACTION:
                $this->trans_method($args, $extend_action_type);
                break;
            case BaseBehavior::MOD_ACTION:
                $this->trans_method($args, $extend_action_type);
                break;
            case BaseBehavior::DEL_ACTION:
                $this->set_condition($args);
                break;
            case BaseBehavior::FETCH_ALL_ACTION:
                $this->set_condition($args);
                break;
            default:
        }
    }

    private function trans_method($args, $extend_action_type) {
        if (empty($extend_action_type)) {
            $this->set_data($args);
        } else {
            if (method_exists($this, $extend_action_type)) {
                $method = new \ReflectionMethod($this, $extend_action_type);
                $method->invoke($this, $args);
            } else {
                //事件对象内指定方法不存在
                $this->error_code = -18;
            }
        }
    }

    protected function set_data($args) {
        return $args;
    }

    protected function set_condition($args) {
        $this->Pagination = array(
            "pagesize" => BaseModel::MAXROWS, //每页显示数量
            "pageindex" => 1, //当前页
            "recordindex" => 1, //偏移量
            "recordcount" => 0, //总数量
            "ispage" => 0, //是否按翻页结果反回0不是翻页1翻页
        );

        //没有参数，获取所有数据
        if (empty($args) || !is_array($args)) {
            return;
        }

        if (is_array($args) && array_key_exists("search_list", $args)) {
            $this->SearchList = $args["search_list"];
        }

        if (array_key_exists("order_list", $args)) {
            $this->OrderList = $args["order_list"];
        }

        if (array_key_exists("condition", $args)) {
            $this->Condition = $args["condition"];

            if (array_key_exists("params_list", $args)) {
                $this->ParamsList = $args["params_list"];
            }
        }

        if (array_key_exists("pagination", $args)) {
            $pagination = $args['pagination'];

            $pageIndex = isset($pagination["pageindex"]) ? intval($pagination["pageindex"]) : 1;

            $pageSize = isset($pagination["pagesize"]) ? intval($pagination["pagesize"]) : BaseModel::MAXROWS;

            $recordCount = isset($pagination["recordcount"]) ? intval($pagination["recordcount"]) : 0;

            //防止传入异常数据
            $pageIndex = ($pageIndex == INF || $pageIndex < 1) ? 1 : $pageIndex;

            //防止传入异常数据
            $pageSize = ($pageSize == INF || $pageSize < 1 || $pageSize > BaseModel::MAXROWS) ? BaseModel::MAXROWS : $pageSize;

            //防止传入参数计算出数据超出最大值
            $recordIndex = intval(($pageIndex - 1) * $pageSize + 1);

            $recordIndex = $recordIndex < 0 ? 1 : $recordIndex;

            $this->Pagination = array(
                "pagesize" => $pageSize, //每页显示数量
                "pageindex" => $pageIndex, //当前页
                "recordindex" => $recordIndex, //偏移量
                "recordcount" => $recordCount, //总数量
                "ispage" => 1, //是否按翻页结果反回0不是翻页1翻页
            );
        }
    }

    public function addPagination($count, $page_index, $page_size, $data) {
        return array(
            "pagesize" => $page_size,
            "pageindex" => $page_index,
            "recordcount" => $count,
            "data" => $data);
    }

    /**
     * 模块传回数据用
     * @param object $data
     */
    public function Postback($data, $is_list = true, $key = '') {
        if ($is_list) {
            $this->Result = $data;
        } else {
            if (!isset($this->Result) || empty($this->Result)) {
                $this->Result = array();
            }

            if (empty($key)) {
                $this->Result[] = $data;
            } else {
                $this->Result[$key] = $data;
            }
        }
    }

    /*
     * 供调用方取结束数据
     */

    public function GetReturnData() {
        return $this->Result;
    }

    /*
     * 返回结果值给客户端
     */

    public function Display() {
        if ($this->error_code < 0) {
            $this->ResponseObj->onFailure($this->error_code, $this->Result);
        } else {
            $this->ResponseObj->onSuccess($this->Result);
        }
    }

    /*
     * 返回出错结果给客户端
     */

    public function Error($error_code, $msg = "error") {
        $this->ResponseObj->onFailure($error_code);
    }

    public $Mail_Info_Data = [];

    public function post_error($error_code = "0", $error_msg = "Error", $class_name = "", $error_trace = "", $is_contain_request_args = 1) {
        //当首次访问后台时，没有获取当前站点的信息(get_site_info无法成功)
        if (!isset($_SERVER["seller_info"]['seller_id'])) {
            $body = "Error title:" . $error_msg . "\n" .
                    "Occurred time:" . date('Y-m-d H:i:s') . "\n" .
                    "Source of error:" . $class_name . "\n" .
                    "Error code:" . $error_code . "\n" .
                    "Error trace:" . $error_trace;
        } else {
            $body = "Error title:" . $error_msg . "\n" .
                    "Occurred time:" . date('Y-m-d H:i:s') . "\n" .
                    "Occurred seller id:" . $_SERVER["seller_info"]['seller_id'] . "\n" .
                    "Occurred shop id:" . $_SERVER["seller_info"]['shop_id'] . "\n" .
                    "Occurred user id:" . $_SERVER["seller_info"]['user_id'] . "\n" .
                    "Access client ip:" . $_SERVER["seller_info"]['client_ip'] . "\n" .
                    "Access site url:" . $_SERVER["seller_info"]['site_url'] . "\n" .
                    "Access server ip:" . $_SERVER["seller_info"]['webserver_ip'] . "\n" .
                    "Access request args:" . ($is_contain_request_args == 1 ? $_SERVER["seller_info"]['args'] : "") . "\n" .
                    "Source of error:" . $class_name . "\n" .
                    "Error code:" . $error_code . "\n" .
                    "Error trace:" . $error_trace;
        }

        $this->Mail_Info_Data[] = array(
            "title" => \Yii::$app->params['const_message']['error_prefix_title'] . ":" . substr($error_msg, 0, 20) . '...',
            "body" => $body
        );
    }

}

?>