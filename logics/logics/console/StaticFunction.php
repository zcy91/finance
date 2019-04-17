<?php
namespace console;

class StaticFunction {

    const LEN_NONCE = 8;

    public static function arrToJson($data) {
        $jsonData = msgpack_pack($data);

        return $jsonData;
    }

    /**
     * json字符串转数组
     * @param unknown_type $reData
     */
    public static function jsonToArr($reData) {
        $objData = msgpack_unpack($reData);
        //$dataArr=json_decode($objData,true);
        return $objData;
    }

    public static function create_guid($namespace = '') {

        $uuid = "";
        if (function_exists("uuid_create")) {
            $uuid = uuid_create();

            return $uuid;
        } else {
            $timestamp = time();
            $nonce = self::randomkeys(8);

            $new_token = md5($namespace . $timestamp . $nonce);
            $new_token = substr($new_token, 2, 15);

            $guid = '';
            $uid = uniqid("", true);

            $hash = strtoupper(hash('ripemd128', $uid . $guid . $new_token));
            $guid = '{' .
                    substr($hash, 0, 8) .
                    '-' .
                    substr($hash, 8, 4) .
                    '-' .
                    substr($hash, 12, 4) .
                    '-' .
                    substr($hash, 16, 4) .
                    '-' .
                    substr($hash, 20, 12) .
                    '}';
            return $guid;
        }
    }

    /**
     * 生成随机数
     * @param int $length
     */
    public static function randomkeys($length) {
        $key = "";

        $pattern = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';

        for ($i = 0; $i < $length; $i++) {
            $key .= $pattern{mt_rand(0, 35)};    //生成php随机数
        }

        return $key;
    }

    /**
     * 给Web站点生成一个随机序列号
     * @param varchar(250) $site_url
     */
    public static function publishToken($site_url) {
        $timestamp = time();
        $nonce = self::randomkeys(self::LEN_NONCE);

        $new_token = md5($site_url . $timestamp . $nonce);
        $new_token = substr($new_token, 2, 15);

        return array(
            "token" => $new_token,
            "timestamp" => $timestamp,
            "nonce" => $nonce
        );
    }

    /**
     * 核对传入密码
     * 合法：True 不合法：False
     */
    public static function checkPwd($seller_id, $shop_id, $md5_pwd, $db_pwd) {

        $is_volid = false;

        $tmpStr = self::resetPwd($seller_id, $shop_id, $md5_pwd);
        if ($tmpStr == $db_pwd) {
            $is_volid = true;
        }

        return $is_volid;
    }

    public static function resetPwd($seller_id, $shop_id, $md5_pwd) {
        //随机生成密码
        if (empty($md5_pwd)) {
            $nonce = self::randomkeys(self::LEN_NONCE);
            $md5_pwd = md5($nonce);
        }

        $tmpArr = array($md5_pwd, $seller_id, $shop_id);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        return $tmpStr;
    }

}
