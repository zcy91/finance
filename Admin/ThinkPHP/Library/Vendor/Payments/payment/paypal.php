<?php
/* 模块的基本信息 */


/**
 * 类
 */
class paypal
{
    /**
     * paypal::getConfig()
     * @name 支付方式的配置
     * @param mixed $set_modules
     * @return
     */
    public function getConfig($set_modules)
    {
        if (isset($set_modules) && $set_modules == TRUE) {
            $i = isset($modules) ? count($modules) : 0;

            /* 代码 */
            $modules[$i]['code'] = basename(__FILE__, '.php');

            /* 描述对应的语言项 */
            $modules[$i]['desc'] = 'paypal_desc';

            /* 是否支持货到付款 */
            $modules[$i]['is_cod'] = '0';

            /* 是否支持在线支付 */
            $modules[$i]['is_online'] = '1';

            /* 作者 */
            $modules[$i]['author'] = 'Yan kuan';

            /* 网址 */
            $modules[$i]['website'] = 'http://www.paypal.com';

            /* 版本号 */
            $modules[$i]['version'] = '1.0.0';

            /* 配置信息 */
            $modules[$i]['config'] = array(
                array('name' => 'paypal_account', 'type' => 'text', 'value' => ''),
                array('name' => 'paypal_currency', 'type' => 'select', 'value' => 'USD')
            );

            return $modules;
        }
    }
    /**
     * 构造函数
     *
     * @access  public
     * @param
     *
     * @return void
     */
    /**
     * paypal::paypal()
     *
     * @return
     */
    function paypal()
    {
    }

    /**
     * paypal::__construct()
     *
     * @return
     */
    function __construct()
    {
        $this->paypal();
    }

    /**
     * 生成支付代码
     * @param   array $order 订单信息
     * @param   array $payment 支付方式信息
     */
    /**
     * paypal::get_code()
     *
     * @param mixed $order
     * @param mixed $payment
     * @return
     */
    function get_code($order, $payment)
    {

        $data_pay_url = $payment['pay_url'];//不同站点的域名

        if (strpos($payment['pay_url'], "http") >= 0) {
            $data_pay_url = str_replace("http://", "", $data_pay_url);
        }

        if (substr($data_pay_url, -1) != "/") {
            $data_pay_url .= "/";
        }

        $data_order_id = $order['log_id'];
        $data_amount = $order['all_price'];
        $data_return_url = "http://" . $data_pay_url . "Mycart/respond_paypal";
        $data_pay_account = $payment['paypal_account'];
        $currency_code = $payment['paypal_currency'];
        $data_notify_url = "http://" . $data_pay_url . "Mycart/respond_paypal";
        $cancel_return = "";
//https://www.sandbox.paypal.com/
        // $def_url  = '<br /><form id="payform" style="text-align:center;display:none" action="https://www.paypal.com/cgi-bin/webscr" method="post">' .   // 不能省略
        $def_url = '<br /><form id="payform" style="text-align:center;display:none" action="https://sandbox.paypal.com/cgi-bin/webscr" method="post">' .   // 不能省略
            "<input type='hidden' name='cmd' value='_xclick'>" .                             // 不能省略
            "<input type='hidden' name='business' value='$data_pay_account'>" .                 // 贝宝帐号
            "<input type='hidden' name='item_name' value='$order[order_sn]'>" .                 // payment for
            "<input type='hidden' name='amount' value='$data_amount'>" .                        // 订单金额
            "<input type='hidden' name='currency_code' value='$currency_code'>" .            // 货币
            "<input type='hidden' name='return' value='$data_return_url'>" .                    // 付款后页面
            "<input type='hidden' name='invoice' value='$data_order_id'>" .                      // 订单号
            "<input type='hidden' name='charset' value='utf-8'>" .                              // 字符集
            "<input type='hidden' name='no_shipping' value='1'>" .                              // 不要求客户提供收货地址
            "<input type='hidden' name='no_note' value=''>" .                                  // 付款说明
            "<input type='hidden' name='notify_url' value='$data_notify_url'>" .
            "<input type='hidden' name='rm' value='2'>" .
            "<input type='hidden' name='cancel_return' value='$cancel_return'>" .
            "<input type='submit'  value=''>" .                      // 按钮
            "</form><br />";

        return $def_url;
    }
}

?>