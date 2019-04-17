<?php
namespace Vendor\com\alibaba\openapi\sdk\aeopenapi\param;

use Vendor\com\alibaba\openapi\client\entity\SDKDomain;
use Vendor\com\alibaba\openapi\client\entity\ByteArray;

class ApiSellerShipmentResult {

        	
    private $success;
    
        /**
    * @return 返回结果
    */
        public function getSuccess() {
        return $this->success;
    }
    
    /**
     * 设置返回结果     
     * @param Boolean $success     
          
     * 此参数必填     */
    public function setSuccess( $success) {
        $this->success = $success;
    }
    
    	
	private $stdResult;
	
	public function setStdResult($stdResult) {
		$this->stdResult = $stdResult;
					    			    			if (array_key_exists ( "success", $this->stdResult )) {
    				$this->success = $this->stdResult->{"success"};
    			}
    			    		    		}
	
	private $arrayResult;
	public function setArrayResult($arrayResult) {
		$this->arrayResult = $arrayResult;
				    		    			if (array_key_exists ( "success", $this->arrayResult )) {
    			$this->success = $arrayResult['success'];
    			}
    		    	    		}

}
?>