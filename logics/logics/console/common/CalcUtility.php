<?php
namespace console\common;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Function
 *
 * @author sicnco
 */
class CalcUtility {

    /**
     * 1*pow(2,64)的35进制字符串
     */
    const FLAG_1 = "5G24A25UXKXFG";

    /**
     * 2*pow(2,64)的35进制字符串
     */
    const FLAG_2 = "AX48K4BPV6UVX";

    /**
     * 3*pow(2,64)的35进制字符串
     */
    const FLAG_3 = "GD6CV6HJSSSBD";

    private static $dic = array(
        0 => '0', 1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5', 6 => '6', 7 => '7', 8 => '8',
        9 => '9', 10 => 'A', 11 => 'B', 12 => 'C', 13 => 'D', 14 => 'E', 15 => 'F', 16 => 'G', 17 => 'H',
        18 => 'I', 19 => 'J', 20 => 'K', 21 => 'L', 22 => 'M', 23 => 'N', 24 => 'P', 25 => 'Q',
        26 => 'R', 27 => 'S', 28 => 'T', 29 => 'U', 30 => 'V', 31 => 'W', 32 => 'X', 33 => 'Y', 34 => 'Z'
    );

    /**
     * 反转数字12345=>54321
     * @param type $ii
     * @param type $radix
     * @param type $stritoa
     * @return string
     */
    public static function itoa($ii, $radix = 10, $stritoa = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabc") {
        if ($radix > strlen($stritoa))    //    does request makes sense?
            return "";    //    think of your own way to handle this case
        $sign = $ii < 0 ? "-" : "";
        $ii = abs($ii);
        $rc = "";
        do {
            $rc .= $stritoa[$ii % $radix];
            $ii = floor($ii / $radix);
        } while ($ii > 0);

        return $sign . strrev($rc);
    }

    /**
     * 三十五进制转数字
     * @param type $char
     * @return type
     */
    public static function getalphnum($ids) {
        $dics = self::$dic;
        $dnum = 35; //进制数
        //键值交换
        $dedic = array_flip($dics);
        //去零
        $id = ltrim($ids, $dics[0]);
        //反转
        $id = strrev($id);
        $v = 0;
        for ($i = 0, $j = strlen($id); $i < $j; $i++) {
            $v = bcadd(bcmul($dedic[$id {
                            $i }
                            ], bcpow($dnum, $i, 0), 0), $v, 0);
        }

        return $v;
    }

    /**
     * 数字转62进制
     * @param type $n
     * @return type
     */
    public static function dec62($n) {
        $base = 62;
        $index = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $ret = '';
        for ($t = floor(log10($n) / log10($base)); $t >= 0; $t --) {
            $a = floor($n / pow($base, $t));
            $ret .= substr($index, $a, 1);
            $n -= $a * pow($base, $t);
        }
        return $ret;
    }

    /**
     * 数字转35进制
     * @param type $n
     * @return type
     */
    public static function dec35($int, $format = 8) {
        $dics = self::$dic;
        $dnum = 35; //进制数
        $arr = array();
        $loop = true;
        while ($loop) {
            $arr[] = $dics[bcmod($int, $dnum)];
            $int = bcdiv($int, $dnum, 0);
            if ($int == '0') {
                $loop = false;
            }
        }
        if (count($arr) < $format) {
            $arr = array_pad($arr, $format, $dics[0]);
        }

        return implode('', array_reverse($arr));
    }

    /**
     * 获取毫秒时间戳(1436971232.8677)
     * @return type
     */
    public static function microtime_float() {
        list($usec, $sec) = explode(" ", microtime());
        return ((float) $usec + (float) $sec);
    }

    /**
     * 从20150701开始的毫秒时间戳(1436971232.867)
     * @return type
     */
    public static function microtime_float_2() {
        $start_time = strtotime('2015-07-01 00:00:00');
        list($usec, $sec) = explode(" ", microtime());

        return array((float) $usec * 1000, (float) $sec - $start_time);
    }

    /**
     * 获取自动生产的SKU（ABCD-123）
     * @return type
     */
    public static function get_sku_no() {
        $sku_no = "";

        $start_time_list = self::microtime_float_2();

        $sku = self::dec35($start_time_list[1]);

        $len = strlen($sku);

        if ($len > 4) {
            $sku_no = substr($sku, 0, 4) . '-' . substr($sku, 4);
        } else {
            $sku_no = $sku;
        }

        $sku_no .= '-' . self::dec35(substr($start_time_list[0], 0, 3));

        return $sku_no;
    }

    /**
     * 第一个为1字节整数（0,1,2,3），第二个为4字节整数，第三个为3字节整数,第四个为1字节整数
     * 
     * @param type $op_type 0:商品条码 1：订单条码 2：采购单条码 3:运单条码 4:出库单条码
     * @param type $id_1
     * @param type $id_2
     * @param type $id_3
     * @param type $return_type 1:条码 2：长整形单据号
     * @return type
     */
    public static function get_sku_no_2($op_type, $id_1, $id_2, $id_3) {
        if (!in_array($op_type, [0, 1, 2, 3, 4]) ||
                $id_1 > 4294967290 ||
                $id_2 > 16777214 ||
                $id_3 > 254) {
            return 0;
        }

        $flag = bcmul($op_type, bcpow(2, 64));

        $id_new_1 = bcmul($id_1, bcpow(2, 32));
        $id_new_2 = bcmul($id_2, bcpow(2, 8));

        $id_new_1_2 = bcadd($id_new_1, $id_new_2);
        $id_new_1_2_3 = bcadd($id_new_1_2, $id_3);
        $id_new_id = bcadd($id_new_1_2_3, $flag);

        return array(
            "barcode" => $id_new_1_2_3,
            "base36_code" => self::dec35($id_new_id)
        );
    }

    /**
     * 第一个为1字节整数（0,1,2,3），第二个为35位（4个字节+3位：存放11位手机号码），第三个为29位（三个字节+5位）
     * 
     * @param type $op_type 0:商品条码 1：订单条码 2：采购单条码 3:运单条码
     * @param type $id_1
     * @param type $id_2
     * @param type $id_3
     * @param type $return_type 1:条码 2：长整形单据号
     * @return type
     */
    public static function get_sku_no_3($op_type, $id_1, $id_2) {
        $start_time = strtotime('2015-07-01 00:00:00');

        $id_2 = $id_2 - $start_time;

        if (!in_array($op_type, [0, 1, 2, 3]) ||
                $id_1 > 34359738367 ||
                $id_2 > 536870911) {
            return 0;
        }

        $flag = bcmul($op_type, bcpow(2, 64));

        $id_new_1 = bcmul($id_1, bcpow(2, 29));
        $id_new_2 = $id_2;

        $id_new_1_2 = bcadd($id_new_1, $id_new_2);
        $id_new_id = bcadd($id_new_1_2, $flag);

        return array(
            "barcode" => $id_new_1_2,
            "base36_code" => self::dec35($id_new_id)
        );
    }

    /**
     * 第一个为1字节整数（0,1,2,3），第二个为29位（三个字节+5位:存放系统内部的customer_id）,第三个为35位（4个字节+3位：存放订单数）
     * 
     * @param type $op_type 0:商品条码 1：订单条码 2：采购单条码 3:运单条码
     * @param type $id_1
     * @param type $id_2
     * @param type $id_3
     * @param type $return_type 1:条码 2：长整形单据号
     * @return type
     */
    public static function get_sku_no_4($op_type, $id_1, $id_2) {
        if (!in_array($op_type, [0, 1, 2, 3]) ||
                $id_1 > 268435455 ||
                $id_2 > 34359738367) {
            return 0;
        }

        $flag = bcmul($op_type, bcpow(2, 64));

        $id_new_1 = bcmul($id_1, bcpow(2, 35));
        $id_new_2 = $id_2;

        $id_new_1_2 = bcadd($id_new_1, $id_new_2);
        $id_new_id = bcadd($id_new_1_2, $flag);

        return array(
            "barcode" => $id_new_1_2,
            "base36_code" => self::dec35($id_new_id)
        );
    }

    public static function decode_sku_no_2($barcode) {
        $op_type = 0;
        $id_1 = 0;
        $id_2 = 0;
        $id_3 = 0;
        $new_barcode = 0;
        $error_code = 0;

        $return_data = array(
            "op_type" => &$op_type,
            "id_1" => &$id_1,
            "id_2" => &$id_2,
            "id_3" => &$id_3,
            "barcode" => &$new_barcode,
            "error_code" => &$error_code
        );

        //采用13位条码
        if (empty($barcode) || strlen($barcode) > 13) {
            //无效条码
            $error_code = -12;
            return $return_data;
        }

        $barcode = strtoupper($barcode);

        $org_id = self::getalphnum($barcode);

        //采用13位条码，所以不支持超过13位的
        if ($org_id == 0) {
            //无效条码
            $error_code = -12;
            return $return_data;
        }

        if (strlen($barcode) < 13) {
            $op_type = 0;
        } else if (strcasecmp($barcode, self::FLAG_3) >= 0) {
            $op_type = 3;

            $org_id = bcsub($org_id, bcmul(3, bcpow(2, 64)));
        } else if (strcasecmp($barcode, self::FLAG_2) >= 0) {
            $op_type = 2;

            $org_id = bcsub($org_id, bcmul(2, bcpow(2, 64)));
        } else if (strcasecmp($barcode, self::FLAG_1) >= 0) {
            $op_type = 1;

            $org_id = bcsub($org_id, bcmul(1, bcpow(2, 64)));
        }

        $new_barcode = $org_id;

        $id_new_1 = floor(bcdiv($org_id, bcpow(2, 32)));

        $id_new_2 = bcmod($org_id, bcpow(2, 32));

        $id_new_3 = bcmod($id_new_2, 256);

        $id_new_2 = floor(bcdiv($id_new_2, 256));

        $id_1 = $id_new_1;
        $id_2 = $id_new_2;
        $id_3 = $id_new_3;

        return $return_data;
    }

    public static function decode_sku_no_3($barcode) {
        $op_type = 0;
        $id_1 = 0;
        $id_2 = 0;
        $new_barcode = 0;
        $error_code = 0;

        $return_data = array(
            "op_type" => &$op_type,
            "id_1" => &$id_1,
            "id_2" => &$id_2,
            "barcode" => &$new_barcode,
            "error_code" => &$error_code
        );

        //采用13位条码
        if (empty($barcode) || strlen($barcode) > 13) {
            //无效条码
            $error_code = -12;
            return $return_data;
        }

        $barcode = strtoupper($barcode);

        $org_id = self::getalphnum($barcode);

        //采用13位条码，所以不支持超过13位的
        if ($org_id == 0) {
            //无效条码
            $error_code = -12;
            return $return_data;
        }

        if (strlen($barcode) < 13) {
            $op_type = 0;
        } else if (strcasecmp($barcode, self::FLAG_3) >= 0) {
            $op_type = 3;

            $org_id = bcsub($org_id, bcmul(3, bcpow(2, 64)));
        } else if (strcasecmp($barcode, self::FLAG_2) >= 0) {
            $op_type = 2;

            $org_id = bcsub($org_id, bcmul(2, bcpow(2, 64)));
        } else if (strcasecmp($barcode, self::FLAG_1) >= 0) {
            $op_type = 1;

            $org_id = bcsub($org_id, bcmul(1, bcpow(2, 64)));
        }

        $new_barcode = $org_id;

        $id_new_1 = floor(bcdiv($org_id, bcpow(2, 29)));

        $id_new_2 = bcmod($org_id, bcpow(2, 29));

        $id_1 = $id_new_1;
        $id_2 = $id_new_2;

        return $return_data;
    }

    public static function decode_sku_no_4($barcode) {
        $op_type = 0;
        $id_1 = 0;
        $id_2 = 0;
        $new_barcode = 0;
        $error_code = 0;

        $return_data = array(
            "op_type" => &$op_type,
            "id_1" => &$id_1,
            "id_2" => &$id_2,
            "barcode" => &$new_barcode,
            "error_code" => &$error_code
        );

        //采用13位条码
        if (empty($barcode) || strlen($barcode) > 13) {
            //无效条码
            $error_code = -12;
            return $return_data;
        }

        $barcode = strtoupper($barcode);

        $org_id = self::getalphnum($barcode);

        //采用13位条码，所以不支持超过13位的
        if ($org_id == 0) {
            //无效条码
            $error_code = -12;
            return $return_data;
        }

        if (strlen($barcode) < 13) {
            $op_type = 0;
        } else if (strcasecmp($barcode, self::FLAG_3) >= 0) {
            $op_type = 3;

            $org_id = bcsub($org_id, bcmul(3, bcpow(2, 64)));
        } else if (strcasecmp($barcode, self::FLAG_2) >= 0) {
            $op_type = 2;

            $org_id = bcsub($org_id, bcmul(2, bcpow(2, 64)));
        } else if (strcasecmp($barcode, self::FLAG_1) >= 0) {
            $op_type = 1;

            $org_id = bcsub($org_id, bcmul(1, bcpow(2, 64)));
        }

        $new_barcode = $org_id;

        $id_new_1 = floor(bcdiv($org_id, bcpow(2, 35)));

        $id_new_2 = bcmod($org_id, bcpow(2, 35));

        $id_1 = $id_new_1;
        $id_2 = $id_new_2;

        return $return_data;
    }

    public static function fetch_num_from_str($str, $is_with_dot = 0) {
        if ($is_with_dot == 1) {
            return preg_replace('/[^\.0123456789]/s', '', trim($str));
        } else {
            return preg_replace('/[^\0123456789]/s', '', trim($str));
        }
    }

    public static function fetch_id_from_mobile_no($country_id, $mobile_no, $tel_no) {
        //根据手机号码获取新的订单号
        $mobile_str_list = [];

        $mobile_no = self::fetch_num_from_str($mobile_no);
        $tel_no = self::fetch_num_from_str($tel_no);

        $mobile = empty($mobile_no) ? $tel_no : $mobile_no;

        if (empty($mobile)) {
            $mobile = $country_id > 1 ? 20000000000 : 10000000000;
        } else {
            if (strlen($mobile) > 11) {
                $mobile = "1" . substr($mobile, -10);
            } else if (strlen($mobile) < 11) {
                $mobile = ($country_id > 1 ? 2 : 1) . str_pad($mobile, 10, '0', STR_PAD_LEFT);
            } else if ($mobile > 34359738367) {
                $mobile = substr($mobile, -10);
            }
        }

        return $mobile;
    }

}
