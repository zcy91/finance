<?php

/**
 * Created by PhpStorm.
 * User: j
 * Date: 2015/6/9
 * Time: 14:56
 */
class Payment
{

    protected $config = [];
    protected $handle; // 支付驱动

    public function __construct($class = 'alipay', $config = [])
    {
        $this->config = array($this->config, $config);
        $this->handle = new $class();
    }

    public function buildRequestForm()
    {
        $param = array(
            'cmd' => '_xclick',
            'charset' => 'utf-8',
            'business' => $this->config['business'],
            'currency_code' => $this->config['paypal_currency'],
            'notify_url' => $this->config['notify_url'],
            'return' => $this->config['return_url'],
            'invoice' => $vo->getOrderNo(),
            'item_name' => $vo->getTitle(),
            'amount' => $vo->getFee(),
            'no_note' => 1,
            'no_shipping' => 1
        );
        $htmlRequest = $this->_buildForm($param);

        return $htmlRequest;
    }

    protected function _buildForm($param)
    {

        return false;
    }

    public function verifyNotify()
    {

    }

    public function __call($method, $arguments)
    {
        if (method_exists($this->handle, $method)) {
            return call_user_func([&$this->handle, $method], $arguments);
        }else{
            E(__CLASS__.':'.$method.L('_METHOD_NOT_EXIST_'));
            return;
        }
    }
}