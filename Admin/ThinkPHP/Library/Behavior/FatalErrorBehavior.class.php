<?php
namespace Behavior;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
Vendor('Swift.swift_required');
/**
 * Description of errorBehavior
 *
 * @author sicnco
 */
class FatalErrorBehavior {
    //行为执行入口
    public function run(&$error){
        if (!is_array($error)) {
                $trace          = debug_backtrace();
                $e['message']   = $error;
                $e['file']      = $trace[0]['file'];
                $e['line']      = $trace[0]['line'];
                ob_start();
                debug_print_backtrace();
                $e['trace']     = ob_get_clean();
        } else {
            $e              = $error;
        }
            
        $content = $e['message']."\n".c_get_site_url();
        $content = $content."\n"."client ip:".  c_get_client_ip();
        $content = $content."\npage_view:".MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME;   
        $content = $content."\ntrace:".$e['trace'];        
        $content = $content."\n".$e['message']." ".date("Y-m-d H:i:s");  
        
        self::send("", "前端站点异常访问：".$e["message"], $content);       
    }
    
    /**
     * 发送邮件
     * @param type $to 如果该值为空，默认发给平台的系统管理员
     * @param type $subject
     * @param type $content
     */
    public static function send($to ,$subject, $content)
    {
        if(empty($to))
        {
            $to = C("EMAIL_TO");
        }
        
        $transport =\Swift_SmtpTransport::newInstance(C('EMAIL_HOST'), C('EMAIL_PORT'))
                ->setUsername(C('EMAIL_USER'))
                ->setPassword(C('EMAIL_PWD'));
                
        $mailer =\Swift_Mailer::newInstance($transport);

        $message = \Swift_Message::newInstance()
                ->setSubject($subject)
                ->setFrom(array(C('EMAIL_DOMAIN') =>C('EMAIL_NAME')))
                ->setTo($to)
                ->setContentType('text/plain')
                ->setCharset(C('EMAIL_CHARSET'))        
                ->setBody($content);
        $mailer->send($message);
    }    
}
