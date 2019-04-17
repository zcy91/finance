<?php
namespace Vendor\com\alibaba\openapi\sdk\aeopenapi\param;

use Vendor\com\alibaba\openapi\client\entity\SDKDomain;
use Vendor\com\alibaba\openapi\client\entity\ByteArray;

class ApiGetLogisticsSellerAddressesParam {

        
    /**
    * @return 获取请求条件
    */
    public function getRequestQuery() {
        $tempResult = $this->sdkStdResult["request"];
        return $tempResult;
    }
    
    /**
     * 设置请求条件
     * @param string $sellerAddressQuery     
     * 参数示例：<pre>["sender","pickup","refund"]</pre>     
     * 此参数必填     */
    public function setRequestQuery($sellerAddressQuery) {
        $this->sdkStdResult["request"] = $sellerAddressQuery;
    }
    
        
    private $sdkStdResult=array();
    
    public function getSdkStdResult(){
    	return $this->sdkStdResult;
    }
}
?>