<?php

namespace Kuba\Core;

final class ServerState {

    /**
     * 检查服务器的状态
     * @return boolean
     */
    public static function check() {
        $result = true;
        return $result;
    }

    /**
     * 设置服务器状态为运行状态
     */
    public static function on() {
        
    }

    /**
     * 设置服务器状态为停止状态
     */
    public static function off() {
        
    }

    public static function errors_add() {
        self::off();
    }

    public static function mailTo($title, $content) {
        
    }

    /**
     * 访问数加一
     */
    public static function access_add($access_module) {
        
    }

    /*
     * 访问数减一
     */

    public static function accesssub($access_module) {
        
    }

}

?>