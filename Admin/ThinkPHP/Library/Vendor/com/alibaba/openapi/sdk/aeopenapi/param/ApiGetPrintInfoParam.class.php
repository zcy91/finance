<?php
namespace Vendor\com\alibaba\openapi\sdk\aeopenapi\param;

use Vendor\com\alibaba\openapi\client\entity\SDKDomain;
use Vendor\com\alibaba\openapi\client\entity\ByteArray;

class ApiGetPrintInfoParam {

        
    /**
    * @return 国际运单号
    */
    public function getLogisticsId() {
        $tempResult = $this->sdkStdResult["internationalLogisticsId"];
        return $tempResult;
    }
    
    /**
     * 设置国际运单号
     * @param string $internationalLogisticsId     
     * 参数示例：<pre>RE700150389CN</pre>     
     * 此参数必填     */
    public function setLogisticsId($internationalLogisticsId) {
        $this->sdkStdResult["internationalLogisticsId"] = $internationalLogisticsId;
    }
    
        
    private $sdkStdResult=array();
    
    public function getSdkStdResult(){
    	return $this->sdkStdResult;
    }
}
?>