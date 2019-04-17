<?php
namespace console\controllers;

use yii\console\Controller;
use console\behaviors\BaseBehavior;
use yii\base\Exception;

class BaseController extends Controller {
    /*
     * 处理行为对象
     */

    public $behavior = null;

    /*
     * 传递事件的对象
     */
    public $event = null;

    /*
     * 检查行为是否符合要求
     */

    function checkAction($event) {
        if ($event->error_code < 0) {
            return $event->error_code;
        } else {
            return 1;
        }
    }

    public function beforeAction($action) {

        $controllerName = strtolower($action->controller->id);

        $actionName = strtolower($action->id);

        $this->event->set_action_name($controllerName, $actionName, $this->route);

        return parent::beforeAction($action);
    }

    protected function add($models, $event, $is_UseTrans = true) {
        $action_status = $this->checkAction($event);

        //判断是否为正确访问
        if ($action_status != 1) {
            //如果访问权限异常，返回异常编号
            return $action_status;
        }

        if (!$is_UseTrans) {
            $this->undoTrans();
        }

        $this->handler($this, $models, BaseBehavior::ADD_ACTION, BaseBehavior::ADD_EVENT);

        $this->trigger(BaseBehavior::TRANS_BEGIN, $event);

        if ($event->error_code == 0) {
            try {
                //触发添加事件
                $this->trigger(BaseBehavior::ADD_EVENT, $event);
            } catch (Exception $e) {
                self::save_error($event, $e);
            }
        }

        $this->trigger(BaseBehavior::TRANS_END, $event);

        $this->do_error($event);
    }

    protected function modify($models, $event, $is_UseTrans = true) {
        $action_status = $this->checkAction($event);

        //判断是否为正确访问
        if ($action_status != 1) {
            //如果访问权限异常，返回异常编号
            return $action_status;
        }

        if (!$is_UseTrans) {
            $this->undoTrans();
        }

        $this->handler($this, $models, BaseBehavior::MOD_ACTION, BaseBehavior::MOD_EVENT);

        $this->trigger(BaseBehavior::TRANS_BEGIN, $event);

        if ($event->error_code == 0) {
            try {
                //触发添加事件
                $this->trigger(BaseBehavior::MOD_EVENT, $event);
            } catch (Exception $e) {
                self::save_error($event, $e);
            }
        }

        $this->trigger(BaseBehavior::TRANS_END, $event);

        $this->do_error($event);
    }

    protected function delete($models, $event, $is_UseTrans = true) {
        $action_status = $this->checkAction($event);

        //判断是否为正确访问
        if ($action_status != 1) {
            //如果访问权限异常，返回异常编号
            return $action_status;
        }

        if (!$is_UseTrans) {
            $this->undoTrans();
        }

        $this->handler($this, $models, BaseBehavior::DEL_ACTION, BaseBehavior::DEL_EVENT);

        $this->trigger(BaseBehavior::TRANS_BEGIN, $event);

        if ($event->error_code == 0) {
            try {
                //触发添加事件
                $this->trigger(BaseBehavior::DEL_EVENT, $event);
            } catch (Exception $e) {
                self::save_error($event, $e);
            }
        }

        $this->trigger(BaseBehavior::TRANS_END, $event);

        $this->do_error($event);
    }

    protected function fetch_all($models, $event, $is_UseTrans = false) {
    
        $action_status = $this->checkAction($event);

        //判断是否为正确访问
        if ($action_status != 1) {
            //如果访问权限异常，返回异常编号
            return $action_status;
        }

        if (!$is_UseTrans) {
            $this->undoTrans();
        }
  
        $this->handler($this, $models, BaseBehavior::FETCH_ALL_ACTION, BaseBehavior::FETCH_ALL_EVENT);

        $this->trigger(BaseBehavior::TRANS_BEGIN, $event);

        if ($event->error_code == 0) {
            try { 
                //触发添加事件
                $this->trigger(BaseBehavior::FETCH_ALL_EVENT, $event);
            } catch (Exception $e) {
                self::save_error($event, $e);
            }
        }

        $this->trigger(BaseBehavior::TRANS_END, $event);

        $this->do_error($event);
    }

    private function do_error($event) {

        if (!empty($event->Mail_Info_Data)) {
            $error_model = new \console\models\system\SendMail();
            $error_model->add($event);
        }
    }

    private static function save_error($event, $e, $e_code = -16, $e_msg = "", $e_trace = "") {
        if (YII_DEBUG) {
            if (empty($e)) {
                echo $e_msg;
            } else {
                echo $e->getMessage();
            }
        }

        if (!empty($event)) {
            $event->error_code = $e_code;

            if (empty($e)) {
                $event->post_error($e_code, $e_msg, self::className(), $e_trace);
            } else {
                $event->post_error($e->getCode(), $e->getMessage(), self::className(), $e->getTraceAsString());
            }
        }
    }

}

?>