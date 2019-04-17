<?php


/**
 * 类
 */
class alipay
{
       /**
     * paypal::getConfig()
     * @name 支付方式的配置
     * @param mixed $set_modules
     * @return
     */
    public function getConfig($set_modules){
        if (isset($set_modules) && $set_modules == TRUE)
            {
            $i = isset($modules) ? count($modules) : 0;
        
            $modules[$i]['code']    = basename(__FILE__, '.php');
        
            /* 描述对应的语言项 */
            $modules[$i]['desc']    = 'alipay_desc';
        
            /* 是否支持货到付款 */
            $modules[$i]['is_cod']  = '0';
        
            /* 是否支持在线支付 */
            $modules[$i]['is_online']  = '1';
        
            /* 作者 */
            $modules[$i]['author']  = 'SHOPERP TEAM';
        
            /* 网址 */
            $modules[$i]['website'] = 'http://www.alipay.com';
        
            /* 版本号 */
            $modules[$i]['version'] = '1.0.2';
        
            /* 配置信息 */
            $modules[$i]['config']  = array(
                array('name' => 'alipay_account',           'type' => 'text',   'value' => ''),
                array('name' => 'alipay_key',               'type' => 'text',   'value' => ''),
                array('name' => 'alipay_partner',           'type' => 'text',   'value' => ''),
                array('name' => 'alipay_pay_method',        'type' => 'select', 'value' => '')
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
     
    function alipay()
    {
    }

    function __construct()
    {
        $this->alipay();
    }

    /**
     * 生成支付代码
     * @param   array   $order      订单信息
     * @param   array   $pay_config    支付方式信息
     */
    function get_code($order, $pay_config)
    {
        
        $data_notify_url = $order['url_info']['notify_url'];
        $data_return_url = $order['url_info']['return_url'];
        $charset = 'utf-8';
        $real_method = $pay_config['alipay_pay_method'];

        switch ($real_method){
            case '0':
                $service = 'trade_create_by_buyer';
                break;
            case '1':
                $service = 'create_partner_trade_by_buyer';
                break;
            case '2':
                $service = 'create_direct_pay_by_user';
                break;
        }

        $extend_param = 'isv^sh22';
        $parameter = array(
            'extend_param'      => $extend_param,
            'service'           => $service,
            'partner'           => $pay_config['alipay_partner'],
            //'partner'           => ALIPAY_ID,
            '_input_charset'    => $charset,
            'notify_url'        => $data_notify_url,
            'return_url'        => $data_return_url,
            /* 业务参数 */
            'subject'           => $order['subject'], //名称
            'out_trade_no'      => $order['order_sn']."-".$order['seller_id']."-".$order['buyer_id'], //订单号
            'price'             => $order['price'], //价格
            'quantity'          => $order['quantity'],//数量
                                                  
            'payment_type'      => 1,
            /* 物流参数 */
            'logistics_type'    => $order['logistics']['logistics_type'],//物流类型
            'logistics_fee'     => $order['logistics']['logistics_fee'],//物流费用
            'logistics_payment' => $order['logistics']['logistics_payment'],//物流支付类型
            /* 买卖双方信息 */
            'seller_email'      => $pay_config['alipay_account']
        );

        ksort($parameter);
        reset($parameter);

        $param = '';
        $sign  = '';

        foreach ($parameter AS $key => $val)
        {
            $param .= "$key=" .urlencode($val). "&";
            $sign  .= "$key=$val&";
        }

        $param = substr($param, 0, -1);
        $sign  = substr($sign, 0, -1). $pay_config['alipay_key'];
        //$sign  = substr($sign, 0, -1). ALIPAY_AUTH;

        $button = '<div style="text-align:center;display:none"><a id="myform_alipay"  href="https://mapi.alipay.com/gateway.do?'.$param. '&sign='.md5($sign).'&sign_type=MD5" target="_self" /></a></div>';

        return $button;
    }
}

?>