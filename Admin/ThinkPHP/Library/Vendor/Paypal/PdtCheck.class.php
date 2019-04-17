<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Vendor\Paypal;

/**
 * 在买家支付后，系统在return里通过商家唯一识别号检查付款情况
 *
 * @author sicnco
 */
class PdtCheck {

    /**
     *  If true, the recommended cURL PHP library is used to send the post back 
     *  to PayPal. If flase then fsockopen() is used. Default true.
     *
     *  @var boolean
     */
    public $use_curl = true;     
    
    /**
     *  If true, explicitly sets cURL to use SSL version 3. Use this if cURL
     *  is compiled with GnuTLS SSL.
     *
     *  @var boolean
     */
    public $force_tls_v1 = true;     
   
    /**
     *  If true, cURL will use the CURLOPT_FOLLOWLOCATION to follow any 
     *  "Location: ..." headers in the response.
     *
     *  @var boolean
     */
    public $follow_location = false;     
    
    /**
     *  If true, an SSL secure connection (port 443) is used for the post back 
     *  as recommended by PayPal. If false, a standard HTTP (port 80) connection
     *  is used. Default true.
     *
     *  @var boolean
     */
    public $use_ssl = true;      
    
    /**
     *  If true, the paypal sandbox URI www.sandbox.paypal.com is used for the
     *  post back. If false, the live URI www.paypal.com is used. Default false.
     *
     *  @var boolean
     */
    public $use_sandbox = true; 
    
    /**
     *  The amount of time, in seconds, to wait for the PayPal server to respond
     *  before timing out. Default 30 seconds.
     *
     *  @var int
     */
    public $timeout = 30;       
    
    private $post_data = array();
    private $post_uri = '';     
    private $response_status = '';
    private $response = '';

    const PAYPAL_HOST = 'www.paypal.com';
    const SANDBOX_HOST = 'www.sandbox.paypal.com';
    
    /**
     *  Post Back Using cURL
     *
     *  Sends the post back to PayPal using the cURL library. Called by
     *  the processIpn() method if the use_curl property is true. Throws an
     *  exception if the post fails. Populates the response, response_status,
     *  and post_uri properties on success.
     *
     *  @param  string  The post data as a URL encoded string
     */
    protected function curlPost($encoded_data) {

        if ($this->use_ssl) {
            $uri = 'https://'.$this->getPaypalHost().'/cgi-bin/webscr';
            $this->post_uri = $uri;
        } else {
            $uri = 'http://'.$this->getPaypalHost().'/cgi-bin/webscr';
            $this->post_uri = $uri;
        }
        
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded_data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
//         curl_setopt($ch, CURLOPT_CAINFO, 
//         dirname(__FILE__)."/cert/cert_key.pem");
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Host: ".$this->getPaypalHost()));
        
        
//         curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $this->follow_location);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        
//         curl_setopt($ch, CURLOPT_HEADER, true);
        
//         if ($this->force_tls_v1) {
//             curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
//         }
        
        $this->response = curl_exec($ch);
        $this->response_status = strval(curl_getinfo($ch, CURLINFO_HTTP_CODE));
        
        if ($this->response === false || $this->response_status == '0') {
            $errno = curl_errno($ch);
            $errstr = curl_error($ch);
            throw new \Exception("cURL error: [$errno] $errstr");
        }
    }
    
    /**
     *  Post Back Using fsockopen()
     *
     *  Sends the post back to PayPal using the fsockopen() function. Called by
     *  the processIpn() method if the use_curl property is false. Throws an
     *  exception if the post fails. Populates the response, response_status,
     *  and post_uri properties on success.
     *
     *  @param  string  The post data as a URL encoded string
     */
    protected function fsockPost($encoded_data) {
    
        if ($this->use_ssl) {
            $uri = 'ssl://'.$this->getPaypalHost();
            $port = '443';
            $this->post_uri = $uri.'/cgi-bin/webscr';
        } else {
            $uri = $this->getPaypalHost(); // no "http://" in call to fsockopen()
            $port = '80';
            $this->post_uri = 'http://'.$uri.'/cgi-bin/webscr';
        }

        $fp = fsockopen($uri, $port, $errno, $errstr, $this->timeout);
        
        if (!$fp) { 
            // fsockopen error
            throw new \Exception("fsockopen error: [$errno] $errstr");
        } 

        $header = "POST /cgi-bin/webscr HTTP/1.1\r\n";
        $header .= "Host: ".$this->getPaypalHost()."\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= "Content-Length: ".strlen($encoded_data)."\r\n";
        $header .= "Connection: Close\r\n\r\n";
        
        fputs($fp, $header.$encoded_data."\r\n\r\n");
        
        while(!feof($fp)) { 
            if (empty($this->response)) {
                // extract HTTP status from first line
                $this->response .= $status = fgets($fp, 1024); 
                $this->response_status = trim(substr($status, 9, 4));
            } else {
                $this->response .= fgets($fp, 1024); 
            }
        } 
        
        fclose($fp);
    }
    
    private function getPaypalHost() {
        if ($this->use_sandbox) return self::SANDBOX_HOST;
        else return self::PAYPAL_HOST;
    }
    
    /**
     *  Get POST URI
     *
     *  Returns the URI that was used to send the post back to PayPal. This can
     *  be useful for troubleshooting connection problems. The default URI
     *  would be "ssl://www.sandbox.paypal.com:443/cgi-bin/webscr"
     *
     *  @return string
     */
    public function getPostUri() {
        return $this->post_uri;
    }
    
    /**
     *  Get Response
     *
     *  Returns the entire response from PayPal as a string including all the
     *  HTTP headers.
     *
     *  @return string
     */
    public function getResponse() {
        return $this->response;
    }
    
    /**
     *  Get Response Status
     *
     *  Returns the HTTP response status code from PayPal. This should be "200"
     *  if the post back was successful. 
     *
     *  @return string
     */
    public function getResponseStatus() {
        return $this->response_status;
    }
    
    /**
     *  Get Text Report
     *
     *  Returns a report of the IPN transaction in plain text format. This is
     *  useful in emails to order processors and system administrators. Override
     *  this method in your own class to customize the report.
     *
     *  @return string
     */
    public function getTextReport() {
        
        $r = '';
        
        // date and POST url
        for ($i=0; $i<80; $i++) { $r .= '-'; }
        $r .= "\n[".date('m/d/Y g:i A').'] - '.$this->getPostUri();
        if ($this->use_curl) $r .= " (curl)\n";
        else $r .= " (fsockopen)\n";
        
        // HTTP Response
        for ($i=0; $i<80; $i++) { $r .= '-'; }
        $r .= "\n{$this->getResponse()}\n";
        
        // POST vars
        for ($i=0; $i<80; $i++) { $r .= '-'; }
        $r .= "\n";
        
        foreach ($this->post_data as $key => $value) {
            $r .= str_pad($key, 25)."$value\n";
        }
        $r .= "\n\n";
        
        return $r;
    }
    
    /**
     * 通过PDT验证付款后paypal返回的数据
     * @param type $tx 交易流水号,通过Get获取
     * @param type $pdt_identity_token 商家唯一身份标记
     * @return 订单明细数据
     * @throws \Exception
     */
    public function verifyReturn($tx, $pdt_identity_token) {

        if(empty($tx))
        {
            throw new \Exception("Unexpected response from PayPal or Others.");
        }
        
        $encoded_data = http_build_query(array
                (
                        'cmd' => '_notify-synch',
                        'tx' => strtoupper($tx),
                        'at' => $pdt_identity_token,
                ));
        
        
        if ($this->use_curl) 
        {
            $this->curlPost($encoded_data); 
        }
        else 
        {
            $this->fsockPost($encoded_data);
        }

        $status = strpos($this->response_status, '200');

        // check responses, if first 7 letters are SUCCESS then we're good
        if($this->response_status == 200 && strpos($this->response, "SUCCESS") !== false)
        {
                // get rid of success
                $curlResponse = substr($this->response, 7);
                // decode
                $curlResponse = urldecode($curlResponse);
                // make associative array
                preg_match_all('/^([^=\r\n]++)=(.*+)/m', $curlResponse, $m, PREG_PATTERN_ORDER);
                
                $curlResponse = array_combine($m[1], $m[2]);
                // keysort to keep in order
                ksort($curlResponse);

                // end
                return $curlResponse;
        }
        else
        {
                throw new \Exception("Invalid response status: ".$this->response_status);
        }                
    }
    
    /**
     *  Require Post Method
     *
     *  Throws an exception and sets a HTTP 405 response header if the request
     *  method was not POST. 
     */    
    public function requirePostMethod() {

        // require POST requests
        if ($_SERVER['REQUEST_METHOD'] && $_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Allow: POST', true, 405);
            throw new \Exception("Invalid HTTP request method.");
        }
    }    
}



//<?php
//// get info
//include 'token';
//// can call function to get details on transaction ID $tx
//function pdt($tx)
//{
//        // start cURL
//        $curlRequest = curl_init();
//        // set cURL options
//        curl_setopt_array($request, array
//        (
//        
//                // https://www.sandbox.paypal.com/cgi-bin/webscr if testing
//                CURLOPT_URL => 'https://www.paypal.com/cgi-bin/webscr',
//                CURLOPT_POST => TRUE,
//                CURLOPT_POSTFIELDS => http_build_query(array
//                (
//                        'cmd' => '_notify-synch',
//                        'tx' => $tx,
//                        
//                        // your identity token here
//                        'at' => 'token',
//                )),
//                CURLOPT_RETURNTRANSFER => TRUE,
//                CURLOPT_HEADER => FALSE,
//                
//                // i have problems if this is true, false less secure
//                CURLOPT_SSL_VERIFYPEER => FALSE,
//                CURLOPT_CAINFO => 'cacert.pem',
//        ));
//        // get response ,status
//        $curlResponse = curl_exec($curlRequest);
//        $status   = curl_getinfo($curlRequest, CURLINFO_HTTP_CODE);
//        // close connection
//        curl_close($curlRequest);
//        // check responses, if first 7 letters are SUCCESS then we're good
//        if($status == 200 AND strpos($curlResponse, 'SUCCESS') === 0)
//        {
//                // get rid of success
//                $curlResponse = substr($curlResponse, 7);
//                // decode
//                $curlResponse = urldecode($curlResponse);
//                // make associative array
//                preg_match_all('/^([^=\r\n]++)=(.*+)/m', $curlResponse, $m, PREG_PATTERN_ORDER);
//                $curlResponse = array_combine($m[1], $m[2]);
//                // keysort to keep in order
//                ksort($curlResponse);
//                // end
//                return $curlResponse;
//        }
//		else
//		{
//			return "error";
//		}
//        return FALSE;
//}
