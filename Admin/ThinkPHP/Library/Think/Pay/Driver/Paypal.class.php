<?php

namespace Think\Pay\Driver;

class Paypal extends \Think\Pay\Pay {

//    private $apiURL = 'https://sandbox.paypal.com/cgi-bin/webscr';
    private $apiURL = 'https://www.paypal.com/cgi-bin/webscr';
    protected $config = array(
        'business' => ''
    );

    public function check() {
        $this->config['business'] = isset($this->config['paypal_account']) ? $this->config['paypal_account'] : '';
        if (!$this->config['business']) {
//            E(L('PAYPAL_PARAM_ERROR'));
              return false;
        }
        return true;
    }

    public function buildRequestForm(\Think\Pay\PayVo $vo) {
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
        $sHtml = $this->_buildForm($param, $this->apiURL);

        return $sHtml;
    }

    public function verifyNotify($notify) {
        if (empty($notify['txn_id']) || $notify['receiver_email'] == $this->config['business']){
            return false;
        }
        $tmpAr = array_merge($notify, array("cmd" => "_notify-validate"));

        $ppResponseAr = $this->notifyFromCurl($this->apiURL, $tmpAr);
        // TODO 这里要把改成if (strcmp($ppResponseAr, "VERIFIED") == 0)
        if (strcmp($ppResponseAr, "VERIFIED") == 0) {
            $info = array();
            //支付状态
            $info['status'] = $notify['payment_status'] == 'Completed' ? true : false;
            $info['money'] = $notify['mc_gross'];
            $info['out_trade_no'] = $notify['invoice'];
            $this->info = $info;
            return true;
        }
        return false;
    }

}
