<?php
namespace console\behaviors;

use yii\base\Behavior;
use console\models\BaseModel;

class BaseBehavior extends Behavior {

    const TRANS_BEGIN = "trans_begin";
    const TRANS_END = "trans_end";
    const ADD_EVENT = "event_add";
    const ADD_ACTION = "add";
    const MOD_EVENT = "event_modify";
    const MOD_ACTION = "modify";
    const DEL_EVENT = "event_delete";
    const DEL_ACTION = "delete";
    const FETCH_ALL_EVENT = "event_fetch_all";
    const FETCH_ALL_ACTION = "fetch_all";

    private $is_use_trans = true;

    public function init() {
        BaseModel::initDB();

        BaseModel::useTrans();
    }

    public function undoTrans() {
        $this->is_use_trans = false;

        BaseModel::useTrans($this->is_use_trans);
    }

    public function changeDB($db) {
        BaseModel::setDB($db);
    }

    public function handler($owner, $modelList, $actionName, $eventName) {
        if ($this->is_use_trans) {
            $owner->off(self::TRANS_BEGIN);

            //开启事务
            $owner->on(self::TRANS_BEGIN, function($event) {
                BaseModel::beginTrans($event, true);
            });
        }

        $old_keys = [];

        foreach ($modelList as $key => $row) {
            $modelName = is_int($key) ? $row : $key;
            $new_action_name = is_int($key) ? $actionName : $row;             
            $model_obj = \Yii::createObject($modelName);

            //如果已经删过一边了，就不能删第二遍,因为这里是循环执行
            if (!isset($old_keys[$eventName])) {
                $owner->off($eventName);

                $old_keys[$eventName] = 1;
            }

            $owner->on($eventName, [$model_obj, $new_action_name]);
        }

        unset($old_keys);

        if ($this->is_use_trans) {
            $owner->off(self::TRANS_END);

            //提交事务
            $owner->on(self::TRANS_END, function($event) {
                BaseModel::commitTrans();
            });
        }
    }

}

?>