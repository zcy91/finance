<?php
namespace Kuba\Core;

final class LogFactory {

    /**
     * 写日志
     * @param varchar(5) $msg_type("INFO","WARN","FATAL","DEBUG")
     * @param MessageBase $msg_obj
     */
    public static function write($msg_type, $msg_obj) {

        //获取系统配置规则，决定写哪些日志
    }
}
?>