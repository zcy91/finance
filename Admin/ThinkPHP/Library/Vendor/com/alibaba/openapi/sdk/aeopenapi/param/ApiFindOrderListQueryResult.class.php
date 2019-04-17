<?php
namespace Vendor\com\alibaba\openapi\sdk\aeopenapi\param;

use Vendor\com\alibaba\openapi\sdk\aeopenapi\param\OrderListVO;

class ApiFindOrderListQueryResult {

        	
    private $result;
    
        /**
    * @return 
    */
        public function getResult() {
        return $this->result;
    }
    
    /**
     * 设置     
     * @param OrderListVO $result     
          
     * 此参数必填     */
    public function setResult(OrderListVO $result) {
        $this->result = $result;
    }
    
    	
	private $stdResult;
	
	public function setStdResult($stdResult) {
            $this->stdResult = $stdResult;
            if (array_key_exists ( "result", $this->stdResult )) {
                    $resultResult=$this->stdResult->{"result"};
                    $this->result = new OrderListVO();
                    $this->result->setStdResult ( $resultResult);
            } 
        }
	
	private $arrayResult;
	public function setArrayResult($arrayResult) {
		$this->arrayResult = $arrayResult;
				    		    		if (array_key_exists ( "result", $this->arrayResult )) {
    		$resultResult=$arrayResult['result'];
    			    			$this->result = new OrderListVO();
    			    			$this->result->$this->setStdResult ( $resultResult);
    		}
    		    	    		}

}
?>