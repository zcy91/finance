<?php

namespace console\models;

use yii\base\Component;
use yii\db\Exception;
use yii\db\Query;

class BaseModel extends Component {

    const MAXROWS = 100000;

    static $DB_Trans = NULL;
    static $Is_BeginTrans = false;
    static $db = array();
    static $is_use_trans = true;

    const PROC_ARGS_MAXLENGTH = 1000;

    public static function initDB() {
        self::$db = \Yii::$app->db;
    }

    public static function setDB($newDB) {
        self::$db = $newDB;
    }

    public static function useTrans($is_use = true) {
        self::$is_use_trans = $is_use;
    }

    /**
     * 记录包含外部启动事务的启动个数
     * @var smallint
     */
    static $count_beginTrans = 0;

    /**
     * 记录不包含外部启动事务的内部启动事务个数
     * @var smallint
     */
    static $count_validTrans = 0;

    /**
     * 两个地方调用该方法
     * 1、一个控制器要执行所有模块之前 is_reset = true
     * 2、单个模块执行之前 is_reset = false
     * @param type $event
     * @param type $is_reset
     * @return boolean
     */
    public static function beginTrans($event, $is_reset = false) {
        $is_success = true;

        //不使用事务时，返回
        if (!self::$is_use_trans) {
            return $is_success;
        }

        //如果数据库操作有异常，就直接终止该请求的后续操作执行(一般是批量操作时，第一次出错了，后面就不需要操作了)
        if ($event->error_code == -20) {
            $is_success = false;

            return $is_success;
        }

        if (!self::$Is_BeginTrans) {
            try {
                self::$DB_Trans = self::$db->beginTransaction();
                self::$Is_BeginTrans = true;
            } catch (Exception $e) {
                $is_success = false;

                //无法开启数据库事务，请联系管理员
                self::save_error($event, $e, -20);
            }
        }

        //一共多少次该方法被调用
        self::$count_beginTrans ++;

        if ($is_success && !$is_reset) {
            //有多少个模块调用了事务,在调用模块前，控制器会事先建立事务，再执行模块，最后提交事务
            //这里只对模块调用计数
            self::$count_validTrans ++;
        }

        return $is_success;
    }

    public static function commitTrans($event = null) {
        //执行出错错误时，直接结束模块的处理
        if (!empty($event)) {
            //取消后续的所有事件响应
            $event->handled = true;

            //重置个数，释放事务
            self::$count_beginTrans = 0;
        }

        //不用事务时，返回
        if (!self::$is_use_trans) {
            return;
        }

        //控制器已经启动了事务
        if (self::$Is_BeginTrans) {
            //每提交一次事务处理该变量次数减1
            self::$count_beginTrans --;

            try {
                //已经启动了事务时，如果符合如下两个条件，说明需要回滚
                //1、当出现错误并重置启动数量时，count_beginTrans才会小于0
                //2、count_validTrans为0的时候，表示没有启动模块(控制器启动事务，中间不执行任何模块，直接提交事务结束)
                if (self::$count_beginTrans < 0 || self::$count_validTrans == 0) {
                    self::$DB_Trans->rollback();
                } else {
                    //事务已经按照启动的数量都执行完成(必须是有模块执行成功的)
                    //执行完模块且是执行最后的控制器的提交事务
                    if (self::$count_beginTrans == 0) {
                        self::$DB_Trans->commit();
                    }
                }
            } catch (Exception $e) {
                //无法开启数据库事务，请联系管理员
                self::save_error($event, $e, -20);
            }
        }

        //有错误发生时，相应回滚后，重置事务信息
        if ((self::$Is_BeginTrans && self::$count_beginTrans <= 0) || (!empty($event) && $event->error_code == -20)) {
            self::$Is_BeginTrans = false;
            self::$count_beginTrans = 0;
            self::$count_validTrans = 0;
        }
    }

    public function insert($table_name, $data_list, $event = null) {
        $res = 0;
        $is_one = 1; //1一维数组[单个添加]2二维数组[批量添加]
        $key_arr = array();
        foreach ($data_list as $v) {
            if (is_array($v)) {
                $key_arr = array_keys($v);
                $is_one = 2;
            }
            break;
        }

        $index = 0;

        try {

            if (BaseModel::beginTrans($event)) {
                if ($is_one == 1) {
                    $res = self::$db->createCommand()->insert($table_name, $data_list)->execute();
                } else {
                    $res = self::$db->createCommand()->batchInsert($table_name, $key_arr, $data_list)->execute();
                }  
                $index = self::$db->getLastInsertID();

                //如果不是自增长时，取回插入的条数
                if ($index == 0) {
                    $index = $res;
                }

                BaseModel::commitTrans();
            } else {
                //中止所有事件响应,并回滚
                BaseModel::commitTrans($event);
            }
        } catch (Exception $e) {
            self::save_error($event, $e, -20);

            //中止所有事件响应,并回滚
            BaseModel::commitTrans($event);
        }

        return $index;
    }

    public function update($table_name, $data_list, $condition, $params = array(), $event = null) {
        $res = 0;

        if (empty($params)) {

            $params = array();
        }

        try {
            if (BaseModel::beginTrans($event)) {
                // 创建命令，可以通过 $command->sql 来查看真正的 SQL 语句。
                $command = self::$db->createCommand();
                $res = $command->update($table_name, $data_list, $condition, $params)->execute();

                BaseModel::commitTrans();
            } else {
                //中止所有事件响应,并回滚
                BaseModel::commitTrans($event);
            }
        } catch (Exception $e) {
            self::save_error($event, $e, -20);

            //中止所有事件响应,并回滚
            BaseModel::commitTrans($event);
        }

        return $res;
    }

    public function update_sql($sql, $event = null, $args = null) {
        $res = 0;

        try {
            if (BaseModel::beginTrans($event)) {
                // 创建命令，可以通过 $command->sql 来查看真正的 SQL 语句。
                $command = self::$db->createCommand($sql);
                if (is_array($args)) {
                    foreach ($args as $k => $v) {
                        //echo '$k=' . $k . '<br/>';
                        //echo '$v=' . $v . '<br/>';
                        //if(strpos($sql, $k) !== false){
                        $command->bindValue($k, $v);
                        //}
                    }
                }
                $res = $command->execute();

                BaseModel::commitTrans();
            } else {
                //中止所有事件响应,并回滚
                BaseModel::commitTrans($event);
            }
        } catch (Exception $e) {
            self::save_error($event, $e, -20);

            //中止所有事件响应,并回滚
            BaseModel::commitTrans($event);
        }

        return $res;
    }

    public function deleteAll($table_name, $condition, $event = null, $params = array()) {
        $res = 0;
        try {
            if (BaseModel::beginTrans($event)) {
                // 创建命令，可以通过 $command->sql 来查看真正的 SQL 语句。
                $res = self::$db->createCommand()->delete($table_name, $condition, $params)->execute();

                BaseModel::commitTrans();
            } else {
                //中止所有事件响应,并回滚
                BaseModel::commitTrans($event);
            }
        } catch (Exception $e) {
            self::save_error($event, $e, -20);

            //中止所有事件响应,并回滚
            BaseModel::commitTrans($event);
        }

        return $res;
    }

    /**
     * 
     * @param type $table_name
     * @param type $condition
     * @param type $args
     * @param type $limit
     * @param type $event
     * @param string $column
     *            "id, category_id as type, name"
     *            ['user.name AS author', 'post.title as title']
     * @param type $order
     * @return type
     */
    public function query($table_name, $condition, $args, $limit, $event = null, $column = null, $order = null) {
        $res = [];

        if (empty($limit)) {
            $limit = BaseModel::MAXROWS;
        }

        if (empty($column)) {
            $column = " * ";
        }

        try {
            if (BaseModel::beginTrans($event)) {
                $query = (new Query())
                        ->select($column)
                        ->from("$table_name")
                        ->where($condition, $args);

                if (!empty($order) && is_array($order)) {
                    $query->orderBy($order);
                }

                is_array($limit) ? $query->limit($limit[0])->offset($limit[1] - 1) : $query->limit($limit);

                // 创建命令，可以通过 $command->sql 来查看真正的 SQL 语句。
                $command = $query->createCommand();

                $command->db = self::$db;

                // 执行命令：
                $res = $command->queryAll();
//echo $command->sql;                  
                BaseModel::commitTrans();
            } else {
                //中止所有事件响应,并回滚
                BaseModel::commitTrans($event);
            }
        } catch (Exception $e) {
            self::save_error($event, $e, -20);

            //中止所有事件响应,并回滚
            BaseModel::commitTrans($event);
        }

        return $res;
    }

    public function query_SQL($sql, $event = null, $limit = null, $args = null) {
        $res = [];

        if (!empty($limit)) {//有limit条件
            $sql_limit = $this->get_limit($limit);
            $sql .= $sql_limit;
        }
        try {
            if (BaseModel::beginTrans($event)) {
                // 创建命令，可以通过 $command->sql 来查看真正的 SQL 语句。
                $command = self::$db->createCommand($sql);
                if (is_array($args)) {
                    foreach ($args as $k => $v) {
                        $command->bindValue($k, $v);
                    }
                }
                // 执行命令：
                $res = $command->queryAll();

                BaseModel::commitTrans();
            } else {
                //中止所有事件响应,并回滚
                BaseModel::commitTrans($event);
            }
        } catch (Exception $e) {
            self::save_error($event, $e, -20);

            //中止所有事件响应,并回滚
            BaseModel::commitTrans($event);
        }

        return $res;
    }

    public function count($table_name, $condition, $args, $column = null, $event = null) {
        $res = 0;

        if (empty($column)) {

            $column = "*";
        }

        try {
            if (BaseModel::beginTrans($event)) {
                $query = (new Query())
                        ->select('count(' . $column . ')')
                        ->from("$table_name")
                        ->where($condition, $args);
                // 创建命令，可以通过 $command->sql 来查看真正的 SQL 语句。
                $command = $query->createCommand();

                $command->db = self::$db;

                // 执行命令：
                $res = $command->queryScalar();

                BaseModel::commitTrans();
            } else {
                //中止所有事件响应,并回滚
                BaseModel::commitTrans($event);
            }
        } catch (Exception $e) {
            self::save_error($event, $e, -20);

            //中止所有事件响应,并回滚
            BaseModel::commitTrans($event);
        }

        return $res;
    }

    public function max($table_name, $condition, $args, $column = null, $event = null) {
        $res = 0;
        if (empty($column))
            $column = "*";
        try {
            if (BaseModel::beginTrans($event)) {
                $query = (new Query())
                        ->select('max(' . $column . ')')
                        ->from("$table_name")
                        ->where($condition, $args);
                // 创建命令，可以通过 $command->sql 来查看真正的 SQL 语句。
                $command = $query->createCommand();

                $command->db = self::$db;

                // 执行命令：
                $res = $command->queryScalar();

                BaseModel::commitTrans();
            } else {
                //中止所有事件响应,并回滚
                BaseModel::commitTrans($event);
            }
        } catch (Exception $e) {
            self::save_error($event, $e, -20);

            //中止所有事件响应,并回滚
            BaseModel::commitTrans($event);
        }

        return $res;
    }

    /**
     * 访问存储过程的方法
     * @param 存储过程名称 $proc_name
     * @param 传入参数 $args_in
     * @param 传出值 $args_out
     * @param string $event
     */
    public function proc_call($proc_name, $args_in, &$args_out, $event = null, $data_type = null) {
        try {
            if (BaseModel::beginTrans($event)) {

                $proc_args_string = "";

                foreach ($args_in as $key => $value) {
                    if (!empty($data_type) && isset($data_type[$key])) {
                        if ($data_type[$key] == 'string') {
                            $proc_args_string = $proc_args_string . ",'" . $value . "'";
                        } else if ($data_type[$key] == 'bool') {
                            $proc_args_string = $proc_args_string . "," . ($value ? 1 : 0);

                            if (!is_bool($value)) {
                                //传入参数有问题
                                self::save_error($event, null, -12);

                                //中止所有事件响应,并回滚
                                BaseModel::commitTrans($event);

                                return null;
                            }
                        } else {
                            if (!is_numeric($value)) {
                                //传入参数有问题
                                self::save_error($event, null, -12);

                                //中止所有事件响应,并回滚
                                BaseModel::commitTrans($event);

                                return null;
                            }

                            $proc_args_string = $proc_args_string . "," . $value;
                        }
                    } else if (is_bool($value)) {
                        if (!is_bool($value)) {
                            //传入参数有问题
                            self::save_error($event, null, -12);

                            //中止所有事件响应,并回滚
                            BaseModel::commitTrans($event);

                            return null;
                        }

                        $proc_args_string = $proc_args_string . "," . ($value ? 1 : 0);
                    } else {
                        if (!is_numeric($value)) {
                            //传入参数有问题
                            self::save_error($event, null, -12);

                            //中止所有事件响应,并回滚
                            BaseModel::commitTrans($event);

                            return null;
                        }

                        $proc_args_string = $proc_args_string . "," . $value;
                    }
                }

                $proc_args_string = substr($proc_args_string, 1);

                if (strlen($proc_args_string) > self::PROC_ARGS_MAXLENGTH) {
                    //传入参数有问题
                    self::save_error($event, null, -12);

                    //中止所有事件响应,并回滚
                    BaseModel::commitTrans($event);

                    return null;
                }

                $proc_args_string = 'select ' . $proc_name . '(' . $proc_args_string . ')';

                $command = self::$db->createCommand($proc_args_string);

                $args_out = $command->queryScalar();

                BaseModel::commitTrans();
            } else {
                //中止所有事件响应,并回滚
                BaseModel::commitTrans($event);
            }
        } catch (Exception $e) {
            self::save_error($event, $e, -20);

            //中止所有事件响应,并回滚
            BaseModel::commitTrans($event);
        }
    }

    /**
     * 访问存储过程的方法
     * @param 存储过程名称 $proc_name
     * @param 传入参数 $args_in
     * @param 传出值 $args_out
     * @param string $event
     */
    public function proc_call_queryScalar($proc_name, $args_in, $event = null, $data_type = null) {
        $args_out = array();

        try {
            if (BaseModel::beginTrans($event)) {

                $proc_args_string = "";

                foreach ($args_in as $key => $value) {
                    if (!empty($data_type) && isset($data_type[$key])) {
                        if ($data_type[$key] == 'string') {
                            $proc_args_string = $proc_args_string . ",'" . $value . "'";
                        } else if ($data_type[$key] == 'bool') {
                            $proc_args_string = $proc_args_string . "," . ($value ? 1 : 0);

                            if (!is_bool($value)) {
                                //传入参数有问题
                                self::save_error($event, null, -12);

                                //中止所有事件响应,并回滚
                                BaseModel::commitTrans($event);

                                return null;
                            }
                        } else {
                            if (!is_numeric($value)) {
                                //传入参数有问题
                                self::save_error($event, null, -12);

                                //中止所有事件响应,并回滚
                                BaseModel::commitTrans($event);

                                return null;
                            }

                            $proc_args_string = $proc_args_string . "," . $value;
                        }
                    } else if (is_bool($value)) {
                        if (!is_bool($value)) {
                            //传入参数有问题
                            self::save_error($event, null, -12);

                            //中止所有事件响应,并回滚
                            BaseModel::commitTrans($event);

                            return null;
                        }

                        $proc_args_string = $proc_args_string . "," . ($value ? 1 : 0);
                    } else {
                        if (!is_numeric($value)) {
                            //传入参数有问题
                            self::save_error($event, null, -12);

                            //中止所有事件响应,并回滚
                            BaseModel::commitTrans($event);

                            return null;
                        }

                        $proc_args_string = $proc_args_string . "," . $value;
                    }
                }

                $proc_args_string = substr($proc_args_string, 1);

                if (strlen($proc_args_string) > self::PROC_ARGS_MAXLENGTH) {
                    //传入参数有问题
                    self::save_error($event, null, -12);

                    //中止所有事件响应,并回滚
                    BaseModel::commitTrans($event);

                    return null;
                }

                $proc_args_string = 'call ' . $proc_name . '(' . $proc_args_string . ')';

                $command = self::$db->createCommand($proc_args_string);

                $args_out = $command->queryScalar();

                BaseModel::commitTrans();
            } else {
                //中止所有事件响应,并回滚
                BaseModel::commitTrans($event);
            }
        } catch (Exception $e) {
            //数据库操作执行失败，请联系管理员。
            self::save_error($event, $e, -20);

            //中止所有事件响应,并回滚
            BaseModel::commitTrans($event);
        }

        return $args_out;
    }

    /**
     * 访问存储过程的方法
     * @param 存储过程名称 $proc_name
     * @param 传入参数 $args_in
     * @param 传出值 $args_out
     * @param string $event
     * @param list $data_type [1=>'number',3=>'string',5=>'bool']
     */
    public function proc_call_exec($proc_name, $args_in, $has_return_data, $event = null, $data_type = null) {
        $args_out = array();

        try {
            if (BaseModel::beginTrans($event)) {

                $proc_args_string = "";

                foreach ($args_in as $key => $value) {
                    if (!empty($data_type) && isset($data_type[$key])) {
                        if ($data_type[$key] == 'string') {
                            $proc_args_string = $proc_args_string . ",'" . $value . "'";
                        } else if ($data_type[$key] == 'bool') {
                            $proc_args_string = $proc_args_string . "," . ($value ? 1 : 0);

                            if (!is_bool($value)) {
                                //传入参数有问题
                                self::save_error($event, null, -12);

                                //中止所有事件响应,并回滚
                                BaseModel::commitTrans($event);

                                return null;
                            }
                        } else {
                            if (!is_numeric($value)) {
                                //传入参数有问题
                                self::save_error($event, null, -12);

                                //中止所有事件响应,并回滚
                                BaseModel::commitTrans($event);

                                return null;
                            }

                            $proc_args_string = $proc_args_string . "," . $value;
                        }
                    } else if (is_bool($value)) {
                        if (!is_bool($value)) {
                            //传入参数有问题
                            self::save_error($event, null, -12);

                            //中止所有事件响应,并回滚
                            BaseModel::commitTrans($event);

                            return null;
                        }

                        $proc_args_string = $proc_args_string . "," . ($value ? 1 : 0);
                    } else {
                        if (!is_numeric($value)) {
                            //传入参数有问题
                            self::save_error($event, null, -12);

                            //中止所有事件响应,并回滚
                            BaseModel::commitTrans($event);

                            return null;
                        }

                        $proc_args_string = $proc_args_string . "," . $value;
                    }
                }

                $proc_args_string = substr($proc_args_string, 1);

                if (strlen($proc_args_string) > self::PROC_ARGS_MAXLENGTH) {
                    //传入参数有问题
                    self::save_error($event, null, -12);

                    //中止所有事件响应,并回滚
                    BaseModel::commitTrans($event);

                    return null;
                }

                if (!$has_return_data) {
                    $proc_args_string = 'call ' . $proc_name . '(' . $proc_args_string . ')';
                } else {
                    $proc_args_string = 'call ' . $proc_name . '(' . $proc_args_string . ',@args_out)';
                }

                $command = self::$db->createCommand($proc_args_string);

                $command->execute();

                if ($has_return_data) {
                    $args_out = self::$db->createCommand("select @args_out as result;")->queryScalar();
                }

                BaseModel::commitTrans();
            } else {
                //中止所有事件响应,并回滚
                BaseModel::commitTrans($event);
            }
        } catch (Exception $e) {
            self::save_error($event, $e, -20);

            //中止所有事件响应,并回滚
            BaseModel::commitTrans($event);
        }

        return $args_out;
    }

    /**
     * 访问存储过程的方法
     * @param 存储过程名称 $proc_name
     * @param 传入参数 $args_in
     * @param 传出值 $args_out
     * @param string $event
     */
    public function proc_call_query($proc_name, $args_in, $event = null, $data_type = null) {
        $args_out = array();

        try {
            if (BaseModel::beginTrans($event)) {

                $proc_args_string = "";

                foreach ($args_in as $key => $value) {
                    if (!empty($data_type) && isset($data_type[$key])) {
                        if ($data_type[$key] == 'string') {
                            $proc_args_string = $proc_args_string . ",'" . $value . "'";
                        } else if ($data_type[$key] == 'bool') {
                            $proc_args_string = $proc_args_string . "," . ($value ? 1 : 0);

                            if (!is_bool($value)) {
                                //传入参数有问题
                                self::save_error($event, null, -12);

                                //中止所有事件响应,并回滚
                                BaseModel::commitTrans($event);

                                return null;
                            }
                        } else {
                            if (!is_numeric($value)) {
                                //传入参数有问题
                                self::save_error($event, null, -12);

                                //中止所有事件响应,并回滚
                                BaseModel::commitTrans($event);

                                return null;
                            }

                            $proc_args_string = $proc_args_string . "," . $value;
                        }
                    } else if (is_bool($value)) {
                        if (!is_bool($value)) {
                            //传入参数有问题
                            self::save_error($event, null, -12);

                            //中止所有事件响应,并回滚
                            BaseModel::commitTrans($event);

                            return null;
                        }

                        $proc_args_string = $proc_args_string . "," . ($value ? 1 : 0);
                    } else {
                        if (!is_numeric($value)) {
                            //传入参数有问题
                            self::save_error($event, null, -12);

                            //中止所有事件响应,并回滚
                            BaseModel::commitTrans($event);

                            return null;
                        }

                        $proc_args_string = $proc_args_string . "," . $value;
                    }
                }

                $proc_args_string = substr($proc_args_string, 1);
                if (strlen($proc_args_string) > self::PROC_ARGS_MAXLENGTH) {
                    //传入参数有问题
                    self::save_error($event, null, -12);

                    //中止所有事件响应,并回滚
                    BaseModel::commitTrans($event);

                    return null;
                }

                $proc_args_string = 'call ' . $proc_name . '(' . $proc_args_string . ')';

                $command = self::$db->createCommand($proc_args_string);

                $args_out = $command->queryAll();

                BaseModel::commitTrans();
            } else {
                //中止所有事件响应,并回滚
                BaseModel::commitTrans($event);
            }
        } catch (Exception $e) {
            self::save_error($event, $e, -20);

            //中止所有事件响应,并回滚
            BaseModel::commitTrans($event);
        }

        return $args_out;
    }

    /**
     * 单表查询
     * @param type $event
     */
    public function fetch_all($event) {

        $condition = $event->Condition;
        $args = $event->ParamsList;
        $limit_arr = $event->Pagination; //分页信息

        $order_list = $event->OrderList;

        $table_name = $this->get_table_name();

        if (empty($table_name)) {
            return $this->go_error($event, -3);
        }

        $returnData = $this->fetch_inner_base($event, $condition, $args, $limit_arr, null, $order_list);

        $event->Postback($returnData);
    }

    public function get_table_name() {
        static $s_table_name = "";

        if (empty($s_table_name)) {
            $key_table_name = $this->className() . "::" . "TABLE_NAME";

            if (defined($key_table_name)) {
                $s_table_name = constant($this->className() . "::" . "TABLE_NAME");
            }
        }

        return $s_table_name;
    }

    /**
     * 同一个Action内的单表查询(模块间调用)
     * @param type $event
     * @param type $condition
     * @param type $args
     * @param type $limit_arr
     * @param type $column
     * @param type $order_by
     * @return type
     */
    public function fetch_inner_base($event, $condition, $args, $limit_arr = null, $column = null, $order_by = null, $is_reset_data = 1) {
        $table_name = $this->get_table_name();

        $limit = $this->getLimitArr_inner($limit_arr); //获得分类信息数组或空值

        $ispage = isset($limit_arr["ispage"]) ? $limit_arr["ispage"] : 0; //是否按翻页结果反回0不是翻页1翻页

        if ($ispage == 1) {//需要按分页的形式返回
            $page_size = isset($limit_arr["pagesize"]) ? $limit_arr["pagesize"] : 0; //每页显示数量
            $page_no = isset($limit_arr["pageindex"]) ? $limit_arr["pageindex"] : 0; //当前页码

            $record_count = isset($limit_arr["recordcount"]) ? $limit_arr["recordcount"] : 0; //记录总数量
            $recode_count = $record_count;

            if ($record_count == 0) {
                $recode_count = $this->count($table_name, $condition, $args, '', $event);
            }

            $db_data = array();
            if ($recode_count > 0) {

                $db_data = $this->query($table_name, $condition, $args, $limit, $event, $column, $order_by);

                if ($is_reset_data) {
                    $db_data = $this->rebuild_data($event, $db_data);
                }
            }
            $returnData = $event->addPagination($recode_count, $page_no, $page_size, $db_data);
        } else {//不用按分类直接返回结果
            $returnData = $this->query($table_name, $condition, $args, $limit, $event, $column, $order_by);

            if ($is_reset_data) {
                //对数据进行其它格式化操作
                $returnData = $this->rebuild_data($event, $returnData);
            }
        }

        return $returnData;
    }

    /**
     * 重新封装数据
     * @param type $result_data
     */
    protected function rebuild_data($event, $result_data) {
        return $result_data;
    }

    /**
     * 功能：解析数组参数字段信息
     * @param array $date_arr 传入的数组信息[一维(单个)/二维(批量)] 
     * @param array $default_arr 需要加入的其它字段信息数组[一维数组]
     * @return array 解析后可以操作的字段下标数组[一维(单个)/二维(批量)] 
     */
    protected function resolveParameter($date_arr = array(), $default_arr = array()) {
        $data_list = array();
        foreach ($date_arr as $v) {
            if (!is_array($v)) {//是一维数组
                $data_list = $this->resolveFields($date_arr, $default_arr);
                break;
            } else {
                $info_arr = $this->resolveFields($v, $default_arr);
                array_push($data_list, $info_arr);
            }
        }
        return $data_list;
    }

    /**
     * 功能：判断数组是不为有数据的数组
     * @param array $in_arr 需要判断的数组
     * @return boolean 是否不是空数组false不是数组或空数组 true是有值的数组
     */
    protected function notEmptyArr($in_arr) {
        $not_emptyarr = true; //是否不是空数组false不是数组或空数组 true是有值的数组
        if ((!is_array($in_arr)) || count($in_arr) <= 0) {//不是数组或是空数组
            $not_emptyarr = false;
        }
        return $not_emptyarr;
    }

    /**
     * 获取Event传递过来的模块数据
     * @param type $obj
     * @return type
     */
    public function get_model_data($obj) {
        static $s_model_data = null;

        if (empty($obj)) {
            return null;
        }

        if (!empty($obj)) {
            $properties = get_object_vars($obj);
            $owner_key = $this->get_table_name() . "_data";

            if (empty($properties) || !key_exists($owner_key, $properties) || empty($properties[$owner_key])) {
                return null;
            } else {
                $s_model_data = $properties[$owner_key];
            }
        }

        return $s_model_data;
    }

    /**
     * 功能：判断$event对象的分类信息，返回分页操作数据库的信息
     * @param obj $event $event对象
     * @return mixed 空值;或数组 array(每页数量,读取偏移量)或 数字:每页数量
     */
    protected function getLimitArr($event) {
        $limit = $event->Pagination; //分页信息

        return $this->getLimitArr_inner($limit);
    }

    /**
     * 功能：根据分页条件，获得limit 字符串
     * @param mixed $limit 空值;或数组 array(每页数量,读取偏移量)或 数字:每页数量
     * @return  string limit串 ''或 ' limit 偏移量,读取数量 '
     */
    protected function get_limit($limit) {
        $sql_limit = '';
        if (is_array($limit)) {
            $page_size = isset($limit[0]) ? intval($limit[0]) : 0; //每页显示数量
            $offset_num = isset($limit[1]) ? (intval($limit[1]) - 1) : 0; //开始读取的偏移量
            $sql_limit = ' LIMIT ' . $offset_num . ',' . $page_size;
        } else {
            if (is_numeric($limit)) {
                $sql_limit = ' LIMIT 0,' . $limit;
            }
        }
        return $sql_limit;
    }

    protected function get_pager_limit($pageIndex, $pageSize, $recordCount) {

        //防止传入异常数据
        $pageIndex = ($pageIndex == INF || $pageIndex < 1) ? 1 : $pageIndex;

        //防止传入异常数据
        $pageSize = ($pageSize == INF || $pageSize < 1 || $pageSize > BaseModel::MAXROWS) ? BaseModel::MAXROWS : $pageSize;

        //防止传入参数计算出数据超出最大值
        $recordIndex = intval(($pageIndex - 1) * $pageSize + 1);

        $recordIndex = $recordIndex < 0 ? 1 : $recordIndex;

        return array(
            "pagesize" => $pageSize, //每页显示数量
            "pageindex" => $pageIndex, //当前页
            "recordindex" => $recordIndex, //偏移量
            "recordcount" => $recordCount, //总数量
            "ispage" => 1, //是否按翻页结果反回0不是翻页1翻页
        );
    }

    /**
     * 功能：判断$event对象的分类信息，返回分页操作数据库的信息
     * @param obj $event $event对象
     * @return mixed 空值;或数组 array(每页数量,读取偏移量)或 数字:每页数量
     */
    protected function getLimitArr_inner($limit) {
        $limit_arr = '';

        if (isset($limit) && is_array($limit)) {
            //是数组并且不是空数组
            $page_size = isset($limit["pagesize"]) ? $limit["pagesize"] : -1; //每页显示数量
            $offset_num = isset($limit["recordindex"]) ? $limit["recordindex"] : -1; //开始读取的偏移量

            if ($page_size >= 0 && $offset_num >= 0) {//有每页数量及读取偏移量
                $limit_arr = array($page_size, $offset_num);
            } elseif ($page_size >= 0) {//只有每页数量
                $limit_arr = $page_size;
            } elseif ($offset_num >= 0) {//只有读取偏移量
                $limit_arr = array(BaseModel::MAXROWS, $offset_num);
            }
        }
        return $limit_arr;
    }

    public function addPagination($count, $page_index, $page_size, $data) {
        return array(
            "pagesize" => $page_size,
            "pageindex" => $page_index,
            "recordcount" => $count,
            "data" => $data);
    }

    /**
     * 功能：从mysql数据库获得UUID
     * @param obj $event $event对象
     * @return string  获取到的uuid
     */
    public function getUUID($event) {
        $uuid = "";
        if (function_exists("uuid_create")) {
            $uuid = uuid_create();
        } else {
            $returnData = $this->query_SQL("SELECT UUID() AS UUID", $event);
            $uuid = isset($returnData[0]["UUID"]) ? $returnData[0]["UUID"] : '';
        }

        return $uuid;
    }

    /**
     * 获取当前前端访问所需的语言
     * @param type $event
     * @return type
     */
    public function get_dis_lang_id($event) {
        //获取当前店铺的显示语言                                
        $dis_lang_id = isset($event->SearchList['dis_lang_id']) ? $event->SearchList['dis_lang_id'] : (isset($_SERVER["seller_info"]["dis_lang_id"]) ? $_SERVER["seller_info"]["dis_lang_id"] : 1);

        return $dis_lang_id;
    }

    /**
     * 解析数组 for where in
     * @param 存储过程名称 $proc_name
     * @param 传入参数 $arr //$arr=array(1,2,3,4);
     * @param 传入参数 $parname(字符类型) 指定sql参数值 // ":goods_id"
     * @param 传出值 $in  //$in['str']为 " :goods_id,:goods_id,:goods_id..." $in['par']为array (:goods_id0' => int 0,':goods_id1' => int 2,...);
     */
    public function implode_arr($arr, $parname) {

        $in = "";
        $i = 0;

        foreach ($arr as $v) {
            $in.=$parname . $i . ",";
            $para = $parname . $i;
            $params[$para] = $v;
            $i++;
        }
        $in = array(
            'str' => substr($in, 0, strlen($in) - 1),
            'par' => $params
        );
        return $in;
    }

    /**
     * 获取默认的数据
     * @param type $is_update
     * @return type
     */
    protected function get_default_data($is_update = true) {
        $now = date('Y-m-d H:i:s');

        $default_data = array(
            "update_time" => $now,
            "update_uid" => $_SERVER["seller_info"]["user_id"]
        );

        if (!$is_update) {
            $default_data["create_time"] = $now;
            $default_data["create_uid"] = $_SERVER["seller_info"]["user_id"];
        }

        return $default_data;
    }

    protected function get_pagination_info($event) {
        $limit_arr = $event->Pagination; //分页信息     

        if (empty($limit_arr)) {
            return array(
                "is_page" => 0,
                "page_index" => 1,
                "page_size" => 50,
                "start_column" => 0,
                "recode_count" => 100
            );
        }

        $ispage = isset($limit_arr["ispage"]) ? $limit_arr["ispage"] : 0; //是否按翻页结果反回0不是翻页1翻页

        $page_index = $limit_arr["pageindex"];

        $page_size = $limit_arr["pagesize"];
        $page_size = $page_size > 50 ? 50 : $page_size;

        //不用Offset处理时，要从0开始
        $start_column = intval($page_index - 1) * $page_size;
        $recode_count = $limit_arr["recordcount"];

        return array(
            "is_page" => $ispage,
            "page_index" => $page_index,
            "page_size" => $page_size,
            "page_index" => $page_index,
            "start_column" => $start_column,
            "recode_count" => $recode_count
        );
    }

    public function go_error($event, $error_code) {
        $this->commitTrans($event);

        $event->error_code = $error_code;

        return $error_code;
    }

    public static function save_error($event, $e, $e_code = -16, $e_msg = "", $e_trace = "", $is_only_warnning = 0, $is_contain_request_args = 1) {
        if (YII_DEBUG) {
            if (empty($e)) {
                echo $e_msg;
            } else {
                echo $e->getMessage();
            }
        }

        if (!empty($event)) {
            if ($is_only_warnning == 0) {
                $event->error_code = $e_code;
            }

            if (empty($e)) {
                $event->post_error($e_code, $e_msg, self::className(), $e_trace, $is_contain_request_args);
            } else {
                $event->post_error($e->getCode(), $e->getMessage(), self::className(), $e->getTraceAsString(), $is_contain_request_args);
            }
        }
    }

    /**
     * $big = array("first"=>2,"second"=>7,"third"=>3,"fourth"=>5);
     * $subset = array("first","third");
     * This will return:
     * Array ( [first] => 2 [third] => 3 )
     * @param type $values
     * @param type $keys
     * @return type
     */
    public static function key_values_intersect($values, $keys, $default_arr = []) {

        foreach ($keys as $key => $value) {
            if (is_int($key)) {
                if (key_exists($value, $default_arr)) {
                    $key_val_int[$value] = $default_arr[$value];
                } else if (key_exists($value, $values)) {
                    $key_val_int[$value] = $values[$value];
                }
            } else {
                if (key_exists($key, $default_arr)) {
                    $key_val_int[$key] = $default_arr[$key];
                } else if (key_exists($key, $values)) {
                    $key_val_int[$key] = $values[$key];
                }
            }
        }

        return $key_val_int;
    }

    public function primaryKey() {
        return ['id'];
    }

    /**
     * 添加单个标签
     * @param type $event
     * @return int
     */
    public function add($event) {

        $data_arr = $this->get_model_data($event);

        if (empty($data_arr)) {
            return 1;
        }//参数为空数组，则直接返回成功，不做处理

        $return_data = [];
        $merge_data = [];

        //二维数组
        if (isset($data_arr[0]) && is_array($data_arr[0])) {
            if (count($data_arr) == 1) {
                $return_data = $this->single_add_inner($event, $data_arr[0]);

                $merge_data = $this->refer_add_result($event, $data_arr[0], 1, $return_data);
            } else {
                $return_data = $this->batch_add_inner($event, $data_arr);

                $merge_data = $this->refer_add_result($event, $data_arr, 2, $return_data);
            }
        } else {
            $return_data = $this->single_add_inner($event, $data_arr);

            $merge_data = $this->refer_add_result($event, $data_arr, 1, $return_data);
        }

        if ($return_data["error_code"] < 0) {
            return $this->go_error($event, $return_data["error_code"]);
        }

        if (!empty($merge_data)) {
            $return_data = $merge_data;
        }

        $event->Postback($return_data, false, $this->get_table_name());
    }

    /**
     * 提交结果给Event对象(比如某一个字段，在后续参数里会用到)
     * @param type $event
     * @param type $seq_no
     */
    public function refer_to($event, $data_arr, $array_dim, $seq_no, $count) {
        
    }

    /**
     * 获取当前模块的关键字(从seller_extend获取)
     * @param type $event
     * @param type $data_arr 传入数组
     * @param type $array_dim 1:一维数组 2：二维数组
     * @return int 0:表示自增，不从seller_extend获取
     */
    public function get_seq_no($event, $data_arr, $array_dim) {
        return 0;
    }

    /**
     * 返回执行结果
     * @param type $event
     * @param type $data_arr
     * @param type $array_dim
     * @param type $return_data
     */
    public function refer_add_result($event, $data_arr, $array_dim, $return_data) {
        return $return_data;
    }

    /**
     * 返回执行结果
     * @param type $event
     * @param type $data_arr
     * @param type $array_dim
     * @param type $return_data
     */
    public function refer_modify_result($event, $data_arr, $array_dim, $return_data) {
        return $return_data;
    }

    /**
     * 批量添加操作
     * $is_key_data_existed主要为了先删后插用,$out_seq_no_list也是
     * 这里不允许字段不一致，比如有的记录含自增字段值（修改：先删后插），有的不含（直接插入新的记录）
     * 注意：一次要么全是插入，要么全是修改（保持原始ID不变）
     * 因为在Delete_Add是先删除，再插入
     * @param type $event
     * @param type $data_arr 二维数组
     * @param type $is_key_data_existed 是否已经存在，不需要去获取新的自增字段
     * @param type $out_seq_no_list 自增字段值
     * @return int
     */
    public function batch_add_inner($event, $data_arr, $is_key_data_existed = false, $out_seq_no_list = []) {

        //返回标签数组
        $auto_id = [];
        $effect_rows = 0;
        $error_code = 0;

        $seq_no = 0;

        $return_data = array(
            "id" => &$auto_id,
            "effect_rows" => &$effect_rows,
            "error_code" => &$error_code
        );

        if (empty($data_arr)) {
            $error_code = -12;

            return $return_data;
        }

        $count = count($data_arr);

        $auto_id_info = $this->get_auto_info($data_arr, 2);

        //不是修改的时候或者修改时传入seqo为空        
        if (!$is_key_data_existed || empty($out_seq_no_list)) {
            //只有传入为空时，才会重新去获取
            $seq_no = $this->get_seq_no($event, $data_arr, 2);

            //只有通过get_seq_no才会调用refer_to
            if (!empty($seq_no)) {
                $this->refer_to($event, $data_arr, 2, $seq_no, $count);
            }
        } else {
            //可能为空，因为有时不存在自增字段
            $auto_id = $out_seq_no_list;
        }

        $default_arr = $this->get_default_data(false);

        $data_list = $this->resolveParameter($data_arr, $default_arr); //解析数组参数字段信息 
        //first_id为自增字段,所以返回first_id
        $rtn_data = $this->insert($this->get_table_name(), $data_list, $event);

        if ($event->error_code < 0) {
            $error_code = $event->error_code;
        } else {
            $effect_rows = count($data_list);

            //只有在存在自增字段，且前面seq_no为空的情况下，才从插入的结果获取自增的首个数字
            if (empty($seq_no) && !empty($auto_id_info["auto_field_name"])) {
                $seq_no = $rtn_data;
            }

            //存在自增字段，需要把单值生成为一个数组
            if (!empty($seq_no)) {
                for ($index = 0; $index < $effect_rows; $index ++) {
                    $auto_id[] = $seq_no + $index;
                }
            }
        }

        return $return_data;
    }

    /**
     * 添加单个标签
     * @param type $event
     * @param type $data_arr 一维数组
     * @return int
     */
    public function single_add_inner($event, $data_arr, $is_key_data_existed = false, $out_seq_no = 0) {
        $auto_id = 0;
        $effect_rows = 0;
        $error_code = 0;

        $seq_no = 0;

        $return_data = array(
            "id" => &$auto_id,
            "effect_rows" => &$effect_rows,
            "error_code" => &$error_code
        );

        if (empty($data_arr)) {
            $error_code = -12;

            return $return_data;
        }

        $count = count($data_arr);

        $auto_id_info = $this->get_auto_info($data_arr, 1);

        if (!$is_key_data_existed || empty($out_seq_no)) {
            $seq_no = $this->get_seq_no($event, $data_arr, 1);

            //只有通过get_seq_no才会调用refer_to
            if (!empty($seq_no)) {
                $this->refer_to($event, $data_arr, 1, $seq_no, $count);
            }
        } else {
            //可能为空，因为有时不存在自增字段
            $auto_id = $out_seq_no;
        }

        $default_arr = $this->get_default_data(false);

        $data_list = $this->resolveParameter($data_arr, $default_arr); //解析数组参数字段信息 
        //first_id为自增字段,所以返回first_id
        $rtn_data = $this->insert($this->get_table_name(), $data_list, $event);

        if ($event->error_code < 0) {
            $error_code = $event->error_code;
        } else {
            $effect_rows = 1;

            //只有在存在自增字段，且前面seq_no为空的情况下，才从插入的结果获取自增的首个数字
            if (empty($seq_no) && !empty($auto_id_info["auto_field_name"])) {
                $seq_no = $rtn_data;
            }

            //存在自增字段，需要把单值生成为一个数组
            if (!empty($seq_no)) {
                $auto_id = $seq_no;
            }
        }

        return $return_data;
    }

    /**
     * 修改模块信息
     */
    public function modify($event) {

        $data_arr = $this->get_model_data($event);

        if (empty($data_arr)) {
            return 1;
        }//参数为空数组，则直接返回成功，不做处理

        $return_data = [];
        $merge_data = [];

        //二维数组
        if (isset($data_arr[0]) && is_array($data_arr[0])) {
            if (count($data_arr) == 1) {
                $return_data = $this->single_modify_inner($event, $data_arr[0]);

                $merge_data = $this->refer_modify_result($event, $data_arr[0], 1, $return_data);
            } else {
                $return_data = $this->batch_modify_inner($event, $data_arr, true);

                $merge_data = $this->refer_modify_result($event, $data_arr, 2, $return_data);
            }
        } else {
            $return_data = $this->single_modify_inner($event, $data_arr);

            $merge_data = $this->refer_modify_result($event, $data_arr, 1, $return_data);
        }

        if ($return_data["error_code"] < 0) {
            return $this->go_error($event, $return_data["error_code"]);
        }

        if (!empty($merge_data)) {
            $return_data = $merge_data;
        }

        $event->Postback($return_data, false, $this->get_table_name());
    }

    /**
     * 修改模块信息(先删后插执行更新)
     */
    public function delete_add($event) {

        $data_arr = $this->get_model_data($event);

        if (empty($data_arr)) {
            return 1;
        }//参数为空数组，则直接返回成功，不做处理

        $return_data = [];
        $merge_data = [];

        //二维数组
        if (isset($data_arr[0]) && is_array($data_arr[0])) {
            if (count($data_arr) == 1) {
                $return_data = $this->single_modify_inner($event, $data_arr[0], false);

                $merge_data = $this->refer_modify_result($event, $data_arr[0], 1, $return_data);
            } else {
                $return_data = $this->batch_modify_inner($event, $data_arr, false);

                $merge_data = $this->refer_modify_result($event, $data_arr, 2, $return_data);
            }
        } else {
            $return_data = $this->single_modify_inner($event, $data_arr, false);

            $merge_data = $this->refer_modify_result($event, $data_arr, 1, $return_data);
        }

        if ($return_data["error_code"] < 0) {
            return $this->go_error($event, $return_data["error_code"]);
        }

        if (!empty($merge_data)) {
            $return_data = $merge_data;
        }

        $event->Postback($return_data, false, $this->get_table_name());
    }

    /**
     * 批量修改标签
     * @param type $event
     * @param type $data_arr 二维数组
     * @return int
     */
    public function batch_modify_inner($event, $data_arr, $is_update_only = true) {

        //返回标签数组
        $auto_id = [];
        $effect_rows = 0;
        $error_code = 0;

        $auto_flag = "";

        $return_data = array(
            "id" => &$auto_id,
            "effect_rows" => &$effect_rows,
            "error_code" => &$error_code
        );

        if (empty($data_arr)) {
            $error_code = -12;

            return $return_data;
        }

        $keys = $this->primaryKey();

        //获取主键对应的值
        $keys_data = $this->key_values_intersect($data_arr[0], $keys);

//        //判断主键是否都有值
//        if(empty($keys_data) || count($keys_data) != count($keys))
//        {
//            $error_code = -12;
//            
//            return $return_data;
//        }

        $new_keys = [];
        $auto_field_name = "";
        $condition_all = [];
        $condition_small = [];

        //拼接查询条件
        foreach ($keys as $key => $value) {
            $field_name = is_int($key) ? $value : $key;

            $auto_flag = is_int($key) ? "" : $value;

            if ($auto_flag == "auto") {
                $auto_field_name = $field_name;
            }

            //关键字不一定在更改的时候传入，因为会有先删后插
            if (!key_exists($key, $keys_data)) {
                continue;
            }

            $new_keys[] = $field_name;

            $param_key = ":" . $field_name;
            $condtion_item = $field_name . " = " . $param_key;
            $condition_all[] = $condtion_item;

            if ($auto_flag != "auto") {
                $condition_small[] = $condtion_item;
            }

            $params[":" . $field_name] = $field_name;
        }

        if (empty($condition_all)) {
            $error_code = -12;
            return $return_data;
        }

        //获取默认数据
        $default_arr = $this->get_default_data(false);

        if ($is_update_only) {
            foreach ($data_arr as $item) {
                //有的时候，更新不是按照自增编号来处理的
                if (isset($item[$auto_field_name])) {
                    $auto_id[] = $item[$auto_field_name];
                }

                $new_params = $params;
                foreach ($params as $key => $value) {
                    $new_params[$key] = $item[$value];
                }

                $condition = implode(' and ', $condition_all);

                //合并
                $data_list = $this->resolveParameter($item, $default_arr); //解析数组参数字段信息 
                //取消主键的更新
                $data_list = array_diff_key($data_list, $keys_data);

                $effect_rows = $this->update($this->get_table_name(), $data_list, $condition, $new_params, $event);
            }
        } else {
            if (count($condition_small) == 0) {
                $condition = implode(' and ', $condition_all);
            } else {
                $condition = implode(' and ', $condition_small);

                if (isset($params[$auto_field_name])) {
                    unset($params[$auto_field_name]);
                }
            }

            //当一批数据里既有现成的数据，又有新增的数据时 
            //如果这时需要保留原始的ID，那么就要把现有的数据和新增的数据分开插入
            $auto_key_existed_data = [];
            $new_key_not_existed_data = [];
            $auto_key_existed_list = [];

            //记录是否含相同的删除条件，如果相同则不再执行删除
            $delete_list = [];

            foreach ($data_arr as $item) {
                if (!empty($auto_field_name) && isset($item[$auto_field_name]) && !empty($item[$auto_field_name])) {
                    $auto_key_existed_data[] = $item;
                    $auto_key_existed_list[] = $item[$auto_field_name];
                } else {
                    $new_key_not_existed_data[] = $item;
                }

                $delete_key = $this->delete_item($event, $this->get_table_name(), $item, $auto_field_name, $condition_small, $condition_all, $params, $delete_list);

                if (!empty($delete_key) && !in_array($delete_key, $delete_list)) {
                    $delete_list[] = $delete_key;
                }
            }

            if ($event->error_code < 0) {
                $error_code = $event->error_code;
            } else {
                //合并
                //$data_list = $this->resolveParameter($data_arr,$default_arr);//解析数组参数字段信息 

                $result_data = [];

                if (count($auto_key_existed_data) > 0) {
                    $result_data2 = $this->batch_add_inner($event, $auto_key_existed_data, true, $auto_key_existed_list);

                    $error_code = $result_data2["error_code"];

                    if ($error_code < 0) {
                        return $return_data;
                    }

                    $result_data = array_merge($result_data, $result_data2);
                }

                if (count($new_key_not_existed_data) > 0) {
                    $result_data1 = $this->batch_add_inner($event, $new_key_not_existed_data, true, $auto_id);

                    $auto_id = $result_data1["id"];
                    $effect_rows = $result_data1["effect_rows"];
                    $error_code = $result_data1["error_code"];

                    if ($error_code < 0) {
                        return $return_data;
                    }

                    $result_data = array_merge($result_data, $result_data1);
                }
            }
        }

        return $return_data;
    }

    /**
     * 修改单个标签
     * @param type $event
     * @param type $data_arr 一维数组
     * @return int
     */
    public function single_modify_inner($event, $data_arr, $is_update_only = true) {
        //返回标签数组
        $auto_id = 0;
        $effect_rows = 0;
        $error_code = 0;

        $auto_flag = "";

        $return_data = array(
            "id" => &$auto_id,
            "effect_rows" => &$effect_rows,
            "error_code" => &$error_code
        );

        if (empty($data_arr)) {
            $error_code = -12;

            return $return_data;
        }

        $keys = $this->primaryKey();

        //获取主键对应的值
        $keys_data = $this->key_values_intersect($data_arr, $keys);

        //判断主键是否都有值
//        if(empty($keys_data) || count($keys_data) != count($keys))
//        {
//            $error_code = -12;
//            
//            return $return_data;
//        }

        $new_keys = [];
        $auto_field_name = "";
        $condition_all = [];
        $condition_small = [];

        //拼接查询条件
        foreach ($keys as $key => $value) {
            $field_name = is_int($key) ? $value : $key;

            $auto_flag = is_int($key) ? "" : $value;

            if ($auto_flag == "auto") {
                $auto_field_name = $field_name;
            }

            //关键字不一定在更改的时候传入，因为会有先删后插
            if (!key_exists($key, $keys_data)) {
                continue;
            }

            $new_keys[] = $field_name;

            $param_key = ":" . $field_name;
            $condtion_item = $field_name . " = " . $param_key;
            $condition_all[] = $condtion_item;

            if ($auto_flag != "auto") {
                $condition_small[] = $condtion_item;
            } else {
                $auto_field_name = $field_name;
            }

            $params[":" . $field_name] = $field_name;
        }

        if (empty($condition_all)) {
            $error_code = -12;
            return $return_data;
        }

        //获取默认数据
        $default_arr = $this->get_default_data(false);

        if ($is_update_only) {
            //有的时候，更新不是按照自增编号来处理的
            if (isset($data_arr[$auto_field_name])) {
                $auto_id = $data_arr[$auto_field_name];
            }

            $new_params = $params;
            foreach ($params as $key => $value) {
                $new_params[$key] = $data_arr[$value];
            }

            $condition = implode(' and ', $condition_all);

            //合并
            $data_list = $this->resolveParameter($data_arr, $default_arr); //解析数组参数字段信息 
            //取消主键的更新
            $data_list = array_diff_key($data_list, $keys_data);

            $effect_rows = $this->update($this->get_table_name(), $data_list, $condition, $new_params, $event);
        } else {
            $this->delete_item($event, $this->get_table_name(), $data_arr, $auto_field_name, $condition_small, $condition_all, $params, []);

            if ($event->error_code < 0) {
                $error_code = $event->error_code;
            } else {
                //合并
                //$data_list = $this->resolveParameter($data_arr,$default_arr);//解析数组参数字段信息 

                $result_data = $this->single_add_inner($event, $data_arr, true, $auto_id);

                $auto_id = $result_data["id"];
                $effect_rows = $result_data["effect_rows"];
                $error_code = $result_data["error_code"];
            }
        }

        return $return_data;
    }

    protected function delete_item($event, $table_name, $data_item, $auto_field_name, $condition_small, $condition_all, $params, $delete_list) {
        $delete_key = "";

        $new_params = $params;
        foreach ($params as $key => $value) {
            if (isset($data_item[$value]) && $key != $auto_field_name) {
                $new_params[$key] = $data_item[$value];

                $delete_key .= $data_item[$value] . "_";
            } else {
                unset($new_params[$key]);
            }
        }

        if (count($condition_small) == 0) {
            $condition = implode(' and ', $condition_all);
        } else {
            $condition = implode(' and ', $condition_small);
        }

        //如果已经存在
        if (!empty($delete_list) && in_array($delete_key, $delete_list)) {
            return null;
        }

        //删除原始数据
        $this->deleteAll($table_name, $condition, $event, $new_params);

        return $delete_key;
    }

    /**
     * 获取当前数组里是否包含auto自增字段
     * @param type $data_arr
     * @param type $array_dim 1:一维数组 2：二维数组
     * @param type $index_of_data 当前数据的第几条记录
     */
    private function get_auto_info($data_arr, $array_dim, $index_of_data = 1) {
        $return_data = array(
            "auto_field_name" => "",
            "auto_field_value" => "",
        );

        $keys = $this->primaryKey();
        $auto_field_name = "";

        //拼接查询条件
        foreach ($keys as $key => $value) {
            $field_name = is_int($key) ? $value : $key;

            $auto_flag = is_int($key) ? "" : $value;

            if ($auto_flag == "auto") {
                $auto_field_name = $field_name;
                break;
            }
        }

        $return_data["auto_field_name"] = $auto_field_name;

        if ($array_dim == 2 && !isset($data_arr[$index_of_data])) {
            return $return_data;
        }

        $current_item = $array_dim == 1 ? $data_arr : $data_arr[$index_of_data];

        if (!empty($auto_field_name) && isset($current_item[$auto_field_name]) && !empty($current_item[$auto_field_name])) {
            $return_data["auto_field_value"] = $current_item[$auto_field_name];
        }

        return $return_data;
    }

    /**
     * 删除
     * @param type $event
     * @return int
     */
    public function delete($event) {

        $error_code = 0;
        $return_data = array(
            "error_code" => &$error_code
        );

        $condition = $event->Condition; //条件 如：seller_id=:seller_id and cate_shop_id=:cate_shop_id 或 seller_id=16842752 and cate_shop_id=34636834
        $args = $event->ParamsList; //条件值 如：Array([:seller_id] => 16842752[:cate_shop_id] => 34636834)

        if (empty($condition) || empty($args)) {

            $error_code = -12;

            return $return_data;
        }//参数为空不做处理  

        $error_code = $this->deleteAll($this->get_table_name(), $condition, $event, $args);

        $event->Postback($return_data);
    }

}

?>