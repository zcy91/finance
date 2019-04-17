<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace console\models\system;

use console\models\BaseModel;
/**
 * Description of SendMail
 *
 * @author sicnco
 */
class SendMail extends BaseModel {
    /**
     * 发送邮件
     * @param type $event
     */
    public function add($event) {
        $data_arr=$event->Mail_Info_Data;
        if(!$this->notEmptyArr($data_arr)){return 1;}//参数为空数组，则直接返回成功，不做处理        
        
        if(YII_DEBUG)
        {
            var_dump($data_arr);
            return;
        }
        
        foreach ($data_arr as $mail_info)
        {
            \Yii::$app->mailer->compose()
            ->setSubject($mail_info['title'])
            ->setTextBody(print_r($data_arr, true))
            ->send();
            
            break;
        }
    }
}
