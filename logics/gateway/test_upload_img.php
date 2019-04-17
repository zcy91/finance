<?php
    //读取文件
    $filePath="/upload/tmp/1441677752337762.jpg";
    $fh = fopen($filePath, "rb");
    $data = fread($fh, filesize($filePath));
    fclose($fh);


    $appKey = '2204043';
    $appSecret ='mfm0qwLsatGO';
    $accessToken = '750fa55f-1490-4596-8e7e-7c0e78174f04';    
    $fileName = '1441677752337762.jpg';


    //post地址
    $upload_image_server = 'http://gw.api.alibaba.com/fileapi/param2/1/aliexpress.open/api.uploadImage/'.$appKey.'?access_token='.$accessToken.'&fileName='.$fileName;


    //post提交
    echo request_post($upload_image_server,$data);


	echo 'end';




// post数据到url的函数
function request_post($remote_server,$content){
   $http_entity_type = 'application/x-www-from-urlencoded'; //发送的格式
    $context = array(
        'http'=>array(
            'method'=>'POST',
         // 这里可以增加其他header..
            'header'=>"Content-type: " .$http_entity_type ."\r\n".
                      'Content-length: '.strlen($content),
            'content'=>$content)
         );
    $stream_context = stream_context_create($context);
    $data = file_get_contents($remote_server,FALSE,$stream_context);
     return $data;
}




?>