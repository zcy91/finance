<?php

namespace Kuba\Core;

final class ServerResponse {

    /**
     * 返回“服务器维护中，请联系管理员”的消息
     * @return multitype:number ReturnData
     */
    public static function response($state, $data = null) {
        //服务器维护中，请联系管理员
        $returnData = array(
            "returnState" => $state,
            "returnData" => $data
        );

        return $returnData;
    }

}

?>