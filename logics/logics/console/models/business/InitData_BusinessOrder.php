<?php
namespace console\models\business;

use console\models\BaseModel;
use console\models\business\BusinessOrderCommissionQuot;
use console\models\business\BusinessOrderOperate;
use console\models\user\View_UserLogin;
use console\models\base\View_BaseProduct;
use console\models\user\View_UserSeller;
use console\models\base\View_BaseProductAttr;
use console\models\base\View_BaseProductAttrItem;
use console\models\base\View_BaseProductCommission;
use console\models\business\View_BusinessOrder;
use console\models\business\View_BusinessOrderAttr;
use console\models\business\View_BusinessOrderAttrItem;
use console\models\business\View_BusinessOrderSalesman;
use console\models\business\View_BusinessOrderCommission;

class InitData_BusinessOrder extends BaseModel {
    
    private $sellerId;
    private $productId;
    private $salesManId;
    private $merchandiserId;

    private $Product;
    private $SalesMan;
    private $Merchandiser;
    private $Attrs;
    private $Commission;


    private function checkProduct($event){
        
        $View_BaseProduct = new View_BaseProduct();
        $Product = $View_BaseProduct->getOne($event, $this->productId, $this->sellerId);
        unset($View_BaseProduct);
        
        if (empty($Product)) {
            return 1;
        }
        
        $this->Product = $Product;
        
        return 0;
    }
    
    private function checkSalesMan($event){
        
        $View_UserSeller = new View_UserSeller();
        $SalesMan = $View_UserSeller->checkSalesMan($event, $this->sellerId, $this->salesManId);
        unset($View_UserSeller);
        
        if (empty($SalesMan)) {
            return 1;
        }
        
        $this->SalesMan = $SalesMan;
        
        return 0;        
    }   
    
    private function checkMerchandiser($event){
        
        $View_UserSeller = new View_UserSeller();
        $Merchandiser = $View_UserSeller->checkMerchandiser($event, $this->sellerId, $this->merchandiserId);
        unset($View_UserSeller);
        
        if (empty($Merchandiser)) {
            return 1;
        }
        
        $this->Merchandiser = $Merchandiser;
        
        return 0;        
    }      
    
    private function getProductCommission($event){
        
        $View_BaseProductCommission = new View_BaseProductCommission();
        if ($this->Product["algorithm"] == 1) {
            $this->Commission = $View_BaseProductCommission->getOneCommissionQuot($event, $this->sellerId, $this->productId);
        } elseif ($this->Product["algorithm"] == 2) {
            $this->Commission = $View_BaseProductCommission->getOneCommissionPercentage($event, $this->sellerId, $this->productId);
        } else {
            return 1;
        }
        unset($View_BaseProductCommission);
        return 0;
    }

    private function handleProductAttr($event){
        
        $View_BaseProductAttr = new View_BaseProductAttr();
        $ProductAttr = $View_BaseProductAttr->getProductAttr($event, $this->sellerId, $this->productId);
        unset($View_BaseProductAttr);

        $View_BaseProductAttrItem = new View_BaseProductAttrItem();
        $ProductAttrItem = $View_BaseProductAttrItem->getProductAttrItem($event, $this->sellerId, $this->productId);
        unset($View_BaseProductAttrItem);
        
        $attrItemMap = [];
        foreach ($ProductAttrItem as $ProductAttrItemItem) {
            $attrItemValueObj = array(
                "seller_id" => $this->sellerId,
                "orderId" => &$event->orderId,
                "attrId" => $ProductAttrItemItem["attrId"],
                "itemId" => $ProductAttrItemItem["attrItemId"],
                "itemName" => $ProductAttrItemItem["attrItemName"]
            );
            $attrItemMap[$ProductAttrItemItem["attrItemId"]] = &$attrItemValueObj;
            $event->business_order_attr_item_data[] = $attrItemValueObj;
            unset($attrItemValueObj);
        }

        foreach ($ProductAttr as $attrItem) {
          
            $attrId = $attrItem["attrId"];
            $required = $attrItem["required"];  
            $genre = $attrItem["genre"];  
            
            if ($required && (!isset($this->Attrs[$attrId]) || empty($this->Attrs[$attrId]))) {
                 return 1;
            }
            
            $itemId = 0;
            $attrValue = "";
            
            $event->business_order_attr_data[] = array(
                "seller_id" => $this->sellerId,
                "orderId" => &$event->orderId,
                "attrId" => $attrId,
                "attrName" => $attrItem["attrName"],
                "genre" => $attrItem["genre"],
                "required" => $attrItem["required"],
                "imageCount" => $attrItem["imageCount"],
                "attrItemId" => &$itemId,
                "attrValue" => &$attrValue
            );             
            
            if (isset($this->Attrs[$attrId]) && !empty($this->Attrs[$attrId])) {

                $value = $this->Attrs[$attrId];
                
                switch ($genre) {
                    case 1: 
                        if (is_string($value) && !empty($value)) {
                            $attrValue = $value;                          
                        }
                        break;
                    case 2:
                        if (is_numeric($value) && !empty($value)) {
                            $attrValue = $value;                          
                        }                        
                        break;
                    case 3:
                        if (!isset($attrItemMap[$value])) {
                            continue;
                        }
                        $attrItemName = $attrItemMap[$value];
                        
                        if (is_numeric($value) && !empty($value)) {
                            $itemId = $attrItemName["itemId"];  
                            $attrValue = $attrItemName["itemName"];                           
                        }                          
                        break;
                    case 4:
                        if (is_array($value) && !empty($value)) {
                            
                            foreach ($value as $attrItemId) {

                                if (!isset($attrItemMap[$attrItemId])) {
                                    continue;
                                }
                                $attrItemName = $attrItemMap[$attrItemId]["itemName"];
                                
                                $attrItemObject = array(
                                    "seller_id" => $this->sellerId,
                                    "orderId" => &$event->orderId,
                                    "attrId" => $attrId,
                                    "itemId" => $attrItemId,
                                    "itemName" => $attrItemName                      
                                );                                  
                                
                                $event->business_order_attr_value_data[] = $attrItemObject;
                            } 
                        }
                        break;
                    case 5:
                        $imageCount = $attrItem["imageCount"];
                        if (!is_numeric($imageCount)) {   
                            continue;
                        }

                        $isMore = 0;
                        if ($imageCount == 1) {
                            $attrValue = (is_array($value) && !empty($value) && isset($value[0])) ? $value[0] : " ";
                        } elseif ($imageCount > 1) {
                            $isMore = 1;
                            $value = array_slice($value, 0, $imageCount);
                        } else {
                            $isMore = 1;
                        }
                        
                        if ($isMore && is_array($value)) {
                            foreach ($value as $valueItem) {

                                if (!is_string($valueItem)) {
                                    continue;
                                }

                                $attrItemObject = array(
                                    "seller_id" => $this->sellerId,
                                    "orderId" => &$event->orderId,
                                    "attrId" => $attrId,
                                    "itemId" => 0,
                                    "itemName" => $valueItem                      
                                );                                  

                                $event->business_order_attr_value_data[] = $attrItemObject;
                            }                             
                        }

                }
                
                unset($attrValue);
                unset($itemId);
            }
        }

    }  
    
    private function handleOrderAttr($event){
        
        $View_BusinessOrderAttr = new View_BusinessOrderAttr();
        $OrderAttr = $View_BusinessOrderAttr->getOrderAttr($event, $this->sellerId, $event->orderId);
        unset($View_BusinessOrderAttr);

        $View_BusinessOrderAttrItem = new View_BusinessOrderAttrItem();
        $OrderAttrItem = $View_BusinessOrderAttrItem->getOrderAttrItem($event, $this->sellerId, $event->orderId);
        unset($View_BaseProductAttrItem);
        
        $OrderAttrItem = array_combine(array_column($OrderAttrItem, "itemId"), $OrderAttrItem);

        foreach ($OrderAttr as $attrItem) {
            
            $attrId = $attrItem["attrId"];
            $required = $attrItem["required"];  
            $genre = $attrItem["genre"];  
            
            if ($required && (!isset($this->Attrs[$attrId]) || empty($this->Attrs[$attrId]))) {
                 return 1;
            }
            
            $itemId = $attrItem["attrItemId"];
            $attrValue = $attrItem["attrValue"];
            
            $event->business_order_attr_data[] = array(
                "seller_id" => $this->sellerId,
                "orderId" => &$event->orderId,
                "attrId" => $attrId,
                "attrName" => $attrItem["attrName"],
                "genre" => $genre,
                "required" => $required,
                "imageCount" => $attrItem["imageCount"],
                "attrItemId" => &$itemId,
                "attrValue" => &$attrValue
            );              
            
            if (isset($this->Attrs[$attrId]) && !empty($this->Attrs[$attrId])) {
                
                $value = $this->Attrs[$attrId];
                
                switch ($genre) {
                    case 1: 
                        if (is_string($value) && !empty($value)) {
                            $attrValue = $value;                            
                        }
                        break;
                    case 2:
                        if (is_numeric($value) && !empty($value)) {
                            $attrValue = $value;                            
                        }                        
                        break;
                    case 3:
                        if (!isset($OrderAttrItem[$value])) {
                            continue;
                        }
                        $attrItemName = $OrderAttrItem[$value];
                        
                        if (is_numeric($value) && !empty($value)) {
                            $itemId = $attrItemName["itemId"];
                            $attrValue = $attrItemName["itemName"];                            
                        }                          
                        break;
                    case 4:
                        if (is_array($value) && !empty($value)) {
    
                            foreach ($value as $attrItemId) {

                                if (!isset($OrderAttrItem[$attrItemId])) {
                                    continue;
                                }
                                $attrItemName = $OrderAttrItem[$attrItemId]["itemName"];
                                
                                $attrItemObject = array(
                                    "seller_id" => $this->sellerId,
                                    "orderId" => &$event->orderId,
                                    "attrId" => $attrId,
                                    "itemId" => $attrItemId,
                                    "itemName" => $attrItemName                      
                                );                                  
                                
                                $event->business_order_attr_value_data[] = $attrItemObject;
                            } 
                        }
                        break;
                    case 5:
                        $imageCount = $attrItem["imageCount"];
                        if (!is_numeric($imageCount)) {
                            continue;
                        }

                        $isMore = 0;
                        if ($imageCount == 1) {
                            $attrValue = (is_array($value) && !empty($value) && isset($value[0])) ? $value[0] : " ";
                        } elseif ($imageCount > 1) {
                            $isMore = 1;
                            $value = array_slice($value, 0, $imageCount);
                        } else {
                            $isMore = 1;
                        }
                        
                        if ($isMore && is_array($value)) {
                            foreach ($value as $valueItem) {

                                if (!is_string($valueItem)) {
                                    continue;
                                }

                                $attrItemObject = array(
                                    "seller_id" => $this->sellerId,
                                    "orderId" => &$event->orderId,
                                    "attrId" => $attrId,
                                    "itemId" => 0,
                                    "itemName" => $valueItem                      
                                );                                  

                                $event->business_order_attr_value_data[] = $attrItemObject;
                            }                             
                        }
                }
                
                unset($attrValue);
                unset($itemId);                
            }
        }

    }     

    private function handleCommission($event){
        if ($this->Product["algorithm"] == 1) {
            $event->business_order_commission_quot_data = array(
                "seller_id" => $this->sellerId,
                "orderId" => &$event->orderId,
                "minAmount" => $this->Commission["minAmount"],
                "minDays" => $this->Commission["minDays"],
                "commission" => $this->Commission["commission"],
                "mediumCommission" => $this->Commission["mediumCommission"],
                "salesmanCommission" => $this->Commission["salesmanCommission"],
                "applyDays" => &$event->applyDays,
                "resultDays" => &$event->resultDays,                  
            );
        } elseif ($this->Product["algorithm"] == 2){
            $event->business_order_commission_percentage_data = array(
                "seller_id" => $this->sellerId,
                "orderId" => &$event->orderId,
                "commission" => $this->Commission["commission"],
                "mediumCommission" => $this->Commission["mediumCommission"],
                "salesmanCommission" => $this->Commission["salesmanCommission"],                
            );            
        } else {
            return 1;
        }        
        
        return 0;
    }    

    public function orderAdd($event){
        
        $data = &$event->RequestArgs;

        if (empty($data) ||
            !isset($data["applyName"]) || empty($data["applyName"]) || !is_string($data["applyName"]) ||
            !isset($data["applyMobile"]) || empty($data["applyMobile"]) || !is_string($data["applyMobile"]) ||
            !isset($data["applyCard"]) || empty($data["applyCard"]) || !is_string($data["applyCard"]) ||
            !isset($data["productId"]) || empty($data["productId"]) || !is_numeric($data["productId"]) ||
            !isset($data["salesManId"]) || empty($data["salesManId"]) || !is_numeric($data["salesManId"]) ||
            !isset($data["applyAmount"]) || empty($data["applyAmount"]) ||! is_numeric($data["applyAmount"])) {
            return parent::go_error($event, -12);
        }  
        
        $ownSellerId = $this->sellerId = View_UserLogin::getOperateSellerId($data);
        if (empty($ownSellerId)) {
            return parent::go_error($event, -2011);
        }  
        
        $logUserId = View_UserLogin::getOperateUserId($data);        
        $nowTime = date('Y-m-d H:i:s');  
        
        $logUser = View_UserLogin::getOrderLogUser($event, $ownSellerId, $logUserId);
        if (empty($logUser)) {
            return parent::go_error($event, -5001);
        }
        
        $customerName = $customerAccount = " ";
        $customerId = $sellerId = $sectionId = 0;
        if ($logUser["roleId"] != 4) {
            $customerId = $logUser["customerId"];
            $customerName = $logUser["customerName"];
            $customerAccount = $logUser["customerAccount"];
        } else {
            $sellerId = $ownSellerId;
            $sectionId = View_UserLogin::getOperateSectionId($data);
        }

        $this->productId = $data["productId"];
        $this->salesManId = $data["salesManId"];

        if ($this->checkProduct($event)){
            return parent::go_error($event, -5002);
        }
        
        $algorithm = $this->Product["algorithm"];
        if ($algorithm == 1 && ( !isset($data["applyDays"]) || empty($data["applyDays"]) || !is_numeric($data["applyDays"]))) {
            return parent::go_error($event, -5003);
        }
        
        if ($this->checkSalesMan($event)){
            return parent::go_error($event, -5004);
        }
        
        if (isset($data["attrs"]) && !empty($data["attrs"]) || is_array($data["attrs"])) {
            $this->Attrs = $data["attrs"];
            if ($this->handleProductAttr($event)) {
                return parent::go_error($event, -5005);
            }
        }

        if ($this->getProductCommission($event)){
            return parent::go_error($event, -5006);
        }
        
        if ($algorithm == 1) {
            $resultAmount = &$event->resultAmount;
        } elseif ($algorithm == 2) {
            $resultAmount = &$data["applyAmount"];
        } else {
            $resultAmount = 0;
        }

        $event->business_order_data = array(
            "id" => &$event->orderId,
            //"nos" => &$event->orderNo,
            "customerUserName" => $customerName,
            "customerUserAccount" => $customerAccount,
            "applyName" => $data["applyName"],
            "applyMobile" => $data["applyMobile"],
            "applyCard" => $data["applyCard"],
            //"productNos" => $this->Product["nos"],
            "productNames" => $this->Product["dnames"],
            "productImage" => $this->Product["image"],
            "applyAmount" => $data["applyAmount"],
            "algorithm" => $algorithm,
            "resultAmount" => &$resultAmount,
            "dstatus" => 1,
            "seller_id" => $ownSellerId,
            "productId" => $data["productId"],
            "customerUserId" => $customerId,
            "creatTime" => $nowTime,
            "nowTime" => $nowTime              
        );
        
        if ($this->Product["algorithm"] == 1) {
            $orderCommission = array(
                "applyDays" => $data["applyDays"],
                "applyAmount" => $data["applyAmount"]
            ); 
            if ($checkStatus = BusinessOrderCommissionQuot::calculateCreatCommission($event, $this->Commission, $orderCommission)) {
                if ($checkStatus == 1) {
                    return parent::go_error($event, -5010);
                } elseif ($checkStatus == 2) {
                    return parent::go_error($event, -5011);
                }
            }
        }
        
        $event->business_order_salesman_data = array(
            "seller_id" => $ownSellerId,
            "orderId" => &$event->orderId,
            "sectionId" => $this->SalesMan["sectionId"],
            "salesmanId" => $data["salesManId"], 
            "salesmanName" => $this->SalesMan["userName"], 
            "salesmanAccount" => $this->SalesMan["userAccount"], 
            "relatedTime" => $nowTime
        );
        
        $event->business_order_status_data = array(
            "seller_id" => $ownSellerId,
            "orderId" => &$event->orderId,
            "dstatus" => 1,
            "sectionId" => $this->SalesMan["sectionId"],
            "relatedUserId" => $data["salesManId"],
            "relatedUserName" => $this->SalesMan["userName"],
            "relatedUserAccount" => $this->SalesMan["userAccount"],
            "relatedTime" => $nowTime 
            
        );    
        
        if ($this->handleCommission($event)){
            return parent::go_error($event, -5009);
        }

        BusinessOrderOperate::setAddData($event, 1, $sellerId, $sectionId, $logUserId, $nowTime);
/*        
var_dump("business_order_data",$event->business_order_data);  
var_dump("business_order_attr_data",$event->business_order_attr_data);  
var_dump("business_order_attr_item_data",$event->business_order_attr_item_data);  

var_dump("business_order_attr_value_data",$event->business_order_attr_value_data);  
var_dump("business_order_commission_quot_data",$event->business_order_commission_quot_data);  
var_dump("business_order_commission_percentage_data",$event->business_order_commission_percentage_data); 

var_dump("business_order_salesman_data",$event->business_order_salesman_data);  
var_dump("business_order_status_data",$event->business_order_status_data); 
var_dump("business_order_operate_data",$event->business_order_operate_data); 
return parent::go_error($event, -10000);
*/      
    }
    
    public function orderEdit($event){
        
        $data = &$event->RequestArgs;
        
        if (empty($data) ||
            !isset($data["id"]) || empty($data["id"]) || !is_numeric($data["id"])) {
            return $this->go_error($event, -12);
        }  
        
        if (!(isset($data["productId"]) && !empty($data["productId"]) && is_string($data["productId"])) &&
            !(isset($data["salesManId"]) && !empty($data["salesManId"]) && is_string($data["salesManId"])) &&
            !(isset($data["applyAmount"]) && !empty($data["applyAmount"]) && is_string($data["applyAmount"]))) {
            return;
        }         
        
        $ownSellerId = $this->sellerId = View_UserLogin::getOperateSellerId($data);
        if (empty($ownSellerId)) {
            return parent::go_error($event, -2011);
        } 
        
        $logUserId = View_UserLogin::getOperateUserId($data);        
        $nowTime = date('Y-m-d H:i:s'); 
        
        $logUser = View_UserLogin::getOrderLogUser($event, $ownSellerId, $logUserId);    
        if (empty($logUser)) {
            return parent::go_error($event, -5001);
        }        

        $id = $event->orderId = $data["id"];
        
        $View_BusinessOrder = new View_BusinessOrder();
        $oldOrder = $View_BusinessOrder->getOne($event, $id, $ownSellerId);
        unset($View_BusinessOrder);  
        if (empty($oldOrder)) {
            return parent::go_error($event, -5007);
        }

        if ($oldOrder["dstatus"] != 1) {
            return parent::go_error($event, -5008);
        }
        
        $changProduct = 0;
        
        $event->business_order_data = array(
            "id" => $id
        );
        
        $business_order_data = &$event->business_order_data;
        
        if (isset($data["productId"]) && is_numeric($data["productId"]) && !empty($data["productId"]) && $oldOrder["productId"] != $data["productId"]) {
            
            $this->productId = $data["productId"]; 
            
            if ($this->checkProduct($event)){
                return parent::go_error($event, -5002);
            }

            $algorithm = $this->Product["algorithm"];

            if (isset($data["attrs"]) && !empty($data["attrs"]) || is_array($data["attrs"])) {
                $this->Attrs = $data["attrs"];
                if ($this->handleProductAttr($event)) {
                    return parent::go_error($event, -5005);
                }
            }

            if ($this->getProductCommission($event)){
                return parent::go_error($event, -5006);
            }    
            
            $delete = array(
                "sellerId" => $ownSellerId,
                "orderId" => $id               
            );                
            $event->business_order_attr_delete = $delete;
            $event->business_order_attr_item_delete = $delete;
            $event->business_order_attr_value_delete = $delete;
            
            $business_order_data["productId"] = $this->productId; 
            $business_order_data["productNos"] = $this->Product["dnos"];
            $business_order_data["productNames"] = $this->Product["names"];
            $business_order_data["productImage"] = $this->Product["image"]; 
            $business_order_data["algorithm"] = $algorithm; 
            
            if ($algorithm == 1) {
                $orderCommission = array(
                    "applyDays" => (isset($data["applyDays"]) && is_numeric($data["applyDays"]) && !empty($data["applyDays"])) ? $data["applyDays"] : $oldOrder["applyDays"],
                    "applyAmount" => (isset($data["applyAmount"]) && is_numeric($data["applyAmount"]) && !empty($data["applyAmount"])) ? $data["applyAmount"] : $oldOrder["applyAmount"]
                ); 
                if ($checkStatus = BusinessOrderCommissionQuot::calculateCreatCommission($event, $this->Commission, $orderCommission)) {
                    if ($checkStatus == 1) {
                        return parent::go_error($event, -5010);
                    } elseif ($checkStatus == 2) {
                        return parent::go_error($event, -5011);
                    }
                }                
            }            

            if ($this->handleCommission($event)){
                return parent::go_error($event, -5009);
            }       
            
            $oldalgorithm = $oldOrder["algorithm"];
            if ($oldalgorithm == 1) {
                $event->business_order_commission_quot_delete = array(
                    "seller_id" => $ownSellerId,
                    "orderId" => $id 
                );
            } elseif ($oldalgorithm == 2) {
                $event->business_order_commission_percentage_delete = array(
                    "seller_id" => $ownSellerId,
                    "orderId" => $id 
                );                    
            }            
            
            if ($algorithm == 1) {
                $event->business_order_commission_quot_data["operate"] = 1;
            }
        } else {
            if (isset($data["attrs"]) && !empty($data["attrs"]) || is_array($data["attrs"])) {
                $delete = array(
                    "sellerId" => $ownSellerId,
                    "orderId" => $id               
                );                
                $event->business_order_attr_delete = $delete;
                $event->business_order_attr_value_delete = $delete;
                $this->Attrs = $data["attrs"];
                $this->handleOrderAttr($event);
            } 
            
            $algorithm = $oldOrder["algorithm"];
            
            if ($algorithm == 1) {
                
                $orderCommission = [];
                $applyDaysChange = $applyAmountChange = 0;
                
                if (isset($data["applyDays"]) && is_numeric($data["applyDays"]) && !empty($data["applyDays"])) {
                    $orderCommission["applyDays"] = $applyDays = $data["applyDays"];
                    $applyDaysChange = 1;
                }
                if (isset($data["applyAmount"]) && is_numeric($data["applyAmount"]) && !empty($data["applyAmount"])) {
                    $orderCommission["applyAmount"] = $applyAmount = $data["applyAmount"];
                    $applyAmountChange = 1;
                }   
                
                if ($applyDaysChange || $applyAmountChange) {
                    $View_BusinessOrderCommission = new View_BusinessOrderCommission();
                    $CommissionQuot = $View_BusinessOrderCommission->getOneCommissionQuot($event, $ownSellerId, $id);
                    unset($View_BusinessOrderCommission);  
                    
                    if ($checkStatus = BusinessOrderCommissionQuot::calculateCreatCommission($event, $CommissionQuot, $orderCommission)) {
                        if ($checkStatus == 1) {
                            return parent::go_error($event, -5010);
                        } elseif ($checkStatus == 2) {
                            return parent::go_error($event, -5011);
                        }
                    }                     
                    if ($applyDaysChange) {
                        $event->business_order_commission_quot_data = array(
                            "id" => $CommissionQuot["id"],
                            "applyDays" => &$event->applyDays,
                            "resultDays" => &$event->resultDays, 
                            "operate" => 0
                        );
                    }
                    
                    if ($applyAmountChange) {
                        $business_order_data["applyAmount"] = $applyAmount;
                        $business_order_data["resultAmount"] = &$event->resultAmount;
                    }
                }
            } else {
                if (isset($data["applyAmount"]) && is_numeric($data["applyAmount"]) && !empty($data["applyAmount"])) {
                    $applyAmount = $data["applyAmount"];
                    $business_order_data["applyAmount"] = $applyAmount;
                    $business_order_data["resultAmount"] = $applyAmount;
                }                   
            }
        }
        
        if (isset($data["applyName"]) && is_numeric($data["applyName"]) && !empty($data["applyName"]) && $oldOrder["applyName"] != $data["applyName"]){
            $business_order_data["applyName"] = $data["applyName"];
        }

        if (isset($data["applyMobile"]) && is_numeric($data["applyMobile"]) && !empty($data["applyMobile"]) && $oldOrder["applyMobile"] != $data["applyMobile"]){
            $business_order_data["applyMobile"] = $data["applyMobile"];
        }

        if (isset($data["applyCard"]) && is_numeric($data["applyCard"]) && !empty($data["applyCard"]) && $oldOrder["applyCard"] != $data["applyCard"]){
            $business_order_data["applyCard"] = $data["applyCard"];
        }        

        if (isset($data["salesManId"]) && is_numeric($data["salesManId"]) && !empty($data["salesManId"])) {  
            $salesManId = $data["salesManId"];
            $View_BusinessOrderSalesman = new View_BusinessOrderSalesman();
            $OrderSalesman = $View_BusinessOrderSalesman->getOneOrderSalesman($event, $id, $ownSellerId);
            unset($View_BusinessOrderSalesman);
            
            if ($salesManId != $OrderSalesman["salesmanId"]) {
                
                $this->salesManId = $salesManId;
                if ($this->checkSalesMan($event)){
                    return parent::go_error($event, -5004);
                }                 
                
                $event->business_order_salesman_data = array(
                    "id" => $OrderSalesman["salesmanId"],
                    "sectionId" => $this->SalesMan["sectionId"],
                    "salesmanId" => $salesManId, 
                    "salesmanName" => $this->SalesMan["userName"], 
                    "salesmanAccount" => $this->SalesMan["userAccount"], 
                    "relatedTime" => $nowTime
                );                

        $event->business_order_status_data = array(
            "seller_id" => $ownSellerId,
            "orderId" => &$event->orderId,
                    "dstatus" => 1,
                    "sectionId" => $this->SalesMan["sectionId"],
            "relatedUserId" => $data["salesManId"],
            "relatedUserName" => $this->SalesMan["userName"],
                    "relatedUserAccount" => $this->SalesMan["userAccount"],
            "relatedTime" => $nowTime 
        );        
            }              
        }
        
        if (count($event->business_order_data) > 1) {
            $event->business_order_data["nowTime"] = $nowTime;   
            if (empty($event->business_order_status_data)) {
                $event->business_order_status_data = array(
                    "seller_id" => $ownSellerId,
                    "orderId" => &$event->orderId,
                    "dstatus" => 1,
                    "relatedTime" => $nowTime 
                );                  
            }
        }

        BusinessOrderOperate::setAddData($event, 2, 0, 0, $logUserId,  $nowTime);        
/*        
var_dump("business_order_data",$event->business_order_data);  
var_dump("business_order_attr_data",$event->business_order_attr_data);  
var_dump("business_order_attr_item_data",$event->business_order_attr_item_data);  
var_dump("business_order_attr_value_data",$event->business_order_attr_value_data);  
var_dump("business_order_commission_quot_data",$event->business_order_commission_quot_data);  
var_dump("business_order_commission_percentage_data",$event->business_order_commission_percentage_data); 
var_dump("business_order_salesman_data",$event->business_order_salesman_data);  
var_dump("business_order_status_data",$event->business_order_status_data); 
var_dump("business_order_operate_data",$event->business_order_operate_data); 
        
var_dump("business_order_attr_delete",$event->business_order_attr_delete);  
var_dump("business_order_attr_item_delete",$event->business_order_attr_item_delete); 
var_dump("business_order_attr_value_delete",$event->business_order_attr_value_delete);
var_dump("business_order_commission_quot_delete",$event->business_order_attr_item_delete); 
var_dump("business_order_commission_percentage_delete",$event->business_order_attr_value_delete);
return parent::go_error($event, -10000);
*/
    }    
    
    public function orderDelete($event){
        
        $data = &$event->RequestArgs;
        
        if (empty($data) ||
            !isset($data["id"]) || empty($data["id"]) || !is_numeric($data["id"])) {
            return $this->go_error($event, -12);
        }         
        
        $ownSellerId = $this->sellerId = View_UserLogin::getOperateSellerId($data);
        if (empty($ownSellerId)) {
            return parent::go_error($event, -2011);
        }  
        
        $logUserId = View_UserLogin::getOperateUserId($data);        
        $nowTime = date('Y-m-d H:i:s');        
        
        $logUser = View_UserLogin::getOrderLogUser($event, $ownSellerId, $logUserId);    
        if (empty($logUser)) {
            return parent::go_error($event, -5001);
        }        
        
        $id = $event->orderId = $data["id"];
        
        $View_BusinessOrder = new View_BusinessOrder();
        $oldOrder = $View_BusinessOrder->getOne($event, $id, $ownSellerId);
        unset($View_BusinessOrder); 
        if (empty($oldOrder)) {
            return parent::go_error($event, -5007);
        }

        if ($oldOrder["dstatus"] != 1) {
            return parent::go_error($event, -5008);
        }
        
        $event->business_order_data = array(
            "id" => $id,
            "sellerId" => $ownSellerId
        );
        
        $delete = array(
            "sellerId" => $ownSellerId,
            "orderId" => $id
        );                
        $event->business_order_attr_delete = $delete;
        $event->business_order_attr_item_delete = $delete;
        $event->business_order_attr_value_delete = $delete;        
        $event->business_order_salesman_delete = $delete;
        $event->business_order_commission_quot_delete = $delete; 
        $event->business_order_commission_percentage_delete = $delete; 
        $event->business_order_status_data = $delete;

        BusinessOrderOperate::setAddData($event, 3, 0, 0, $logUserId, $nowTime);   
/*        
var_dump("business_order_data",$event->business_order_data);  
var_dump("business_order_attr_delete",$event->business_order_attr_delete);  
var_dump("business_order_attr_item_delete",$event->business_order_attr_item_delete);  

var_dump("business_order_attr_value_delete",$event->business_order_attr_value_delete);  
var_dump("business_order_salesman_delete",$event->business_order_salesman_delete);  
var_dump("business_order_commission_quot_delete",$event->business_order_commission_quot_delete); 

var_dump("business_order_commission_percentage_delete",$event->business_order_commission_percentage_delete);  
var_dump("business_order_status_data",$event->business_order_status_data); 
return parent::go_error($event, -10000);
*/
    }     
    
    
    public function orderCommit($event){
        
        $data = &$event->RequestArgs;
        
        if (empty($data) ||
            !isset($data["id"]) || empty($data["id"]) || !is_numeric($data["id"])) {
            return $this->go_error($event, -12);
        }         
        
        $ownSellerId = $this->sellerId = View_UserLogin::getOperateSellerId($data);
        if (empty($ownSellerId)) {
            return parent::go_error($event, -2011);
        }  
        
        $logUserId = View_UserLogin::getOperateUserId($data);        
        $nowTime = date('Y-m-d H:i:s');        
        
        $logUser = View_UserLogin::getOrderLogUser($event, $ownSellerId, $logUserId);    
        if (empty($logUser)) {
            return parent::go_error($event, -5001);
        }        
        
        $id = $event->orderId = $data["id"];
        
        $View_BusinessOrder = new View_BusinessOrder();
        $oldOrder = $View_BusinessOrder->getOne($event, $id, $ownSellerId);
        unset($View_BusinessOrder); 
        if (empty($oldOrder)) {
            return parent::go_error($event, -5007);
        }

        if ($oldOrder["dstatus"] != 1) {
            return parent::go_error($event, -5008);
        }
        
        $event->business_order_data = array(
            "id" => $id,
            "seller_id" => $ownSellerId,
            "dstatus" => 2,
            "nowTime" => $nowTime,
        );
        
        $event->business_order_status_data = array(
            "sellerId" => $ownSellerId,
            "orderId" => $id,
            "nowTime" => $nowTime
        );         

        BusinessOrderOperate::setAddData($event, 4, 0, 0, $logUserId, $nowTime);   
/*       
var_dump("business_order_data",$event->business_order_data);  
var_dump("business_order_status_data",$event->business_order_status_data);   
var_dump("business_order_operate_data",$event->business_order_operate_data); 
return parent::go_error($event, -10000);
*/         
    }    
    
    public function orderCheck($event){
        
        $data = &$event->RequestArgs;
        
        if (empty($data) ||
            !isset($data["id"]) || empty($data["id"]) || !is_numeric($data["id"]) ||
            !isset($data["status"]) || !is_numeric($data["status"]) || !in_array($data["status"], [7,8])) {
            return $this->go_error($event, -12);
        }   
        
        $ownSellerId = $this->sellerId = View_UserLogin::getOperateSellerId($data);
        if (empty($ownSellerId)) {
            return parent::go_error($event, -2011);
        }   

        // 8:审核 7:拒绝审核
        $dstatus = $data["status"];
        
        if ($dstatus == 8) {
            if (!isset($data["merchandiserId"]) || empty($data["merchandiserId"]) || !is_string($data["merchandiserId"])) {
                return $this->go_error($event, -12);
            }
            $this->merchandiserId = $data["merchandiserId"];
            if ($this->checkMerchandiser($event)) {
                return $this->go_error($event, -5023);
            }
        }        
        
        if ($dstatus == 7 && (!isset($data["reason"]) || empty($data["reason"]) || !is_string($data["reason"]))) {
            return $this->go_error($event, -12);
        }    
        
        $logSectionId = View_UserLogin::getOperateSectionId($data);
        $logUserId = View_UserLogin::getOperateUserId($data);        
        $nowTime = date('Y-m-d H:i:s');       
                
        $user = View_UserLogin::getStaffInfo($event, $ownSellerId, $logUserId);
        if (empty($user)) {
            return parent::go_error($event, -5020);
        }                  

        if (!$user["superd"] && !$logSectionId) {
            return $this->go_error($event, -5022);
        }        

        $id = $event->orderId = $data["id"];
        
        $View_BusinessOrder = new View_BusinessOrder();
        $oldOrder = $View_BusinessOrder->getOne($event, $id, $ownSellerId);
        if (empty($oldOrder)) {
            unset($View_BusinessOrder); 
            return parent::go_error($event, -5007);
        }

        if (!$user["superd"]) {
        $hasCheckOrder = $View_BusinessOrder->checkOrderSection($event, $logUserId, $ownSellerId, $id, 2);        
        unset($View_BusinessOrder);
        if (!$hasCheckOrder) {
            return parent::go_error($event, -5021);
        }
        }
 
        if ($oldOrder["dstatus"] != 2) {
            return parent::go_error($event, -5008);
        }  
        
        $event->business_order_data = array(
            "id" => $id,
            "dstatus" => $dstatus,
            "nowTime" => $nowTime     
        );
        
        $event->business_order_status_data = array(
            "seller_id" => $ownSellerId,
            "orderId" => $id,
            "dstatus" => 6,
            "sectionId" => $logSectionId,
            "relatedTime" => $nowTime,
            "relatedUserId" => $logUserId,
            "relatedUserName" => $user["userName"],  
            "relatedUserAccount" => $user["userAccount"]
        );        
        
        if ($dstatus == 8) {
            $event->business_order_merchandiser_data = array(
                "seller_id" => $ownSellerId,
                "orderId" => $id,
                "sectionId" => $this->Merchandiser["sectionId"],
                "merchandiserId" => $this->Merchandiser["userId"],
                "merchandiserName" => $this->Merchandiser["userName"],
                "merchandiserAccount" => $this->Merchandiser["userAccount"],
                "relatedTime" => $nowTime
            );            
        } else {
            $event->business_order_reason_data = array(
                "seller_id" => $ownSellerId,
                "orderId" => $id,
                "dstatus" => 6,
                "reason" => $data["reason"]
            );               
        }

        BusinessOrderOperate::setAddData($event, $dstatus, $ownSellerId, $logSectionId, $logUserId, $nowTime);      
/*       
var_dump("business_order_data",$event->business_order_data);  
var_dump("business_order_status_data",$event->business_order_status_data);   
var_dump("business_order_merchandiser_data",$event->business_order_merchandiser_data);  
var_dump("business_order_reason_data",$event->business_order_reason_data);  
var_dump("business_order_operate_data",$event->business_order_operate_data); 
return parent::go_error($event, -10000);
*/         
    }
    
    public function orderSign($event){
        
        $data = &$event->RequestArgs;
        
        if (empty($data) ||
            !isset($data["id"]) || empty($data["id"]) || !is_numeric($data["id"]) ||
            !isset($data["status"]) || !is_numeric($data["status"]) || !in_array($data["status"], [12,13])) {
            return $this->go_error($event, -12);
        }   
        
        $ownSellerId = $this->sellerId = View_UserLogin::getOperateSellerId($data);
        if (empty($ownSellerId)) {
            return parent::go_error($event, -2011);
        }   

        // 13:签约 12:拒绝签约
        $dstatus = $data["status"];
        
        if ($dstatus == 13) {
            if (!isset($data["onePriced"]) || !is_numeric($data["onePriced"]) || !in_array($data["onePriced"], [0,1])) {
                return $this->go_error($event, -12);
            }
            // 0:不是一口价 1:一口价
            $onePriced = $data["onePriced"];            
            if ($onePriced && (!isset($data["actualAmount"]) || empty($data["actualAmount"]) || !is_numeric($data["actualAmount"]))) {
                return $this->go_error($event, -12);
            }
        }        
        
        if ($dstatus == 12 && (!isset($data["reason"]) || empty($data["reason"]) || !is_string($data["reason"]))) {
            return $this->go_error($event, -12);
        }    
        
        $logSectionId = View_UserLogin::getOperateSectionId($data);
        $logUserId = View_UserLogin::getOperateUserId($data);        
        $nowTime = date('Y-m-d H:i:s');       
                
        $user = View_UserLogin::getStaffInfo($event, $ownSellerId, $logUserId);
        if (empty($user)) {
            return parent::go_error($event, -5030);
        }                  

        if (!$user["superd"] && !$logSectionId) {
            return $this->go_error($event, -5022);
        }          

        $id = $event->orderId = $data["id"];
        
        $View_BusinessOrder = new View_BusinessOrder();
        $oldOrder = $View_BusinessOrder->getOne($event, $id, $ownSellerId);
        if (empty($oldOrder)) {
            unset($View_BusinessOrder); 
            return parent::go_error($event, -5007);
        }

        if (!$user["superd"]) {
        $hasCheckOrder = $View_BusinessOrder->checkOrderSection($event, $logUserId, $ownSellerId, $id, 6);
        unset($View_BusinessOrder);
        if (!$hasCheckOrder) {
            return parent::go_error($event, -5031);
        }
        }
 
        if ($oldOrder["dstatus"] != 8) {
            return parent::go_error($event, -5033);
        }  
        
        $algorithm = $oldOrder["algorithm"];
        
        if ($dstatus == 13) {
            if ($algorithm == 1 && (!isset($data["actualDays"]) || empty($data["actualDays"]) || !is_numeric($data["actualDays"]))) {
                return $this->go_error($event, -12);
            }     
            if (!$onePriced && (!isset($data["commission"]) || empty($data["commission"]) || !is_numeric($data["commission"]))){
                return $this->go_error($event, -12);
            } 
        }            
        
        $event->business_order_data = array(
            "id" => $id,
            "dstatus" => $dstatus,
            "nowTime" => $nowTime     
        );
        
        if ($dstatus == 13) {
            $event->business_order_data["onePriced"] = $onePriced;
        }
        
        $event->business_order_status_data = array(
            "seller_id" => $ownSellerId,
            "orderId" => $id,
            "dstatus" => 10,
            "sectionId" => $logSectionId,
            "relatedTime" => $nowTime,
            "relatedUserId" => $logUserId,
            "relatedUserName" => $user["userName"],
            "relatedUserAccount" => $user["userAccount"]
        );        
        
        if ($dstatus == 13) {

            $View_BusinessOrderCommission = new View_BusinessOrderCommission();
            if ($algorithm == 1) {
                $CommissionQuot = $View_BusinessOrderCommission->getOneCommissionQuot($event, $ownSellerId, $id);
                unset($View_BusinessOrderCommission);
                if (empty($CommissionQuot)) {
                    return parent::go_error($event, -5034);
                }
                if ($CommissionQuot["minDays"] > $data["actualDays"]) {
                    return parent::go_error($event, -5040);
                }
                if (isset($data["actualAmount"])  && $CommissionQuot["minAmount"] > $data["actualAmount"]) {
                    return parent::go_error($event, -5042);
                }                
            } elseif ($algorithm == 2) {
                $CommissionPercentage = $View_BusinessOrderCommission->getOneCommissionPercentage($event, $ownSellerId, $id);
                unset($View_BusinessOrderCommission);
                if (empty($CommissionPercentage)) {
                    return parent::go_error($event, -5035);
                }
            } else {
                return parent::go_error($event, -5036);
            }
            
            if ($onePriced) {
                
                $actualAmount = $data["actualAmount"];
                $actualDays = $data["actualDays"];
                $resultAmount =$oldOrder["resultAmount"];

                if ($algorithm == 1) {
                    if (!empty($actualDays) && !empty($resultAmount)) {
                        $commission = (int)($actualAmount / ($actualDays * $resultAmount)); 
                        $event->business_order_commission_quot_data = array( 
                            "seller_id" => $ownSellerId,
                            "orderId" => $id,
                            "actualDays" => $actualDays       
                        );                         
                    }
                } elseif ($algorithm == 2) {
                    $commission = $actualAmount / $resultAmount;
                }  
                
                if (!$commission) {
                    return parent::go_error($event, -5038);
                }
                $event->business_order_data["actualAmount"] = $actualAmount;
                $event->business_order_data["commission"] = $commission;
                
            } else {
                
                $commission = $data["commission"];
                $resultAmount =$oldOrder["resultAmount"];
                
                if ($algorithm == 1) {
                    
                    $actualDays = $data["actualDays"];
                    
                    $event->business_order_commission_quot_data = array( 
                        "seller_id" => $ownSellerId,
                        "orderId" => $id,
                        "actualDays" => $actualDays       
                    );  

                    $event->business_order_data["commission"] = $commission;
                    $event->business_order_data["actualAmount"] = $commission * $actualDays * $resultAmount;
                                        
                } elseif ($algorithm == 2) {

                    $event->business_order_data["commission"] = $commission;
                    $event->business_order_data["actualAmount"] = $commission * $resultAmount;  
                    
                }
            }           
            
        } else {
            $event->business_order_reason_data = array(
                "seller_id" => $ownSellerId,
                "orderId" => $id,
                "dstatus" => 10,
                "reason" => $data["reason"]
            );               
        }

        BusinessOrderOperate::setAddData($event, $dstatus, $ownSellerId, $logSectionId, $logUserId, $nowTime); 
/*      
var_dump("business_order_data",$event->business_order_data);  
var_dump("business_order_status_data",$event->business_order_status_data);   
var_dump("business_order_commission_quot_data",$event->business_order_commission_quot_data);  
var_dump("business_order_reason_data",$event->business_order_reason_data);  
var_dump("business_order_operate_data",$event->business_order_operate_data); 
return parent::go_error($event, -10000);
*/            
    }
    
    public function orderReceive($event){

        $data = &$event->RequestArgs;
        
        if (empty($data) ||
            !isset($data["id"]) || empty($data["id"]) || !is_numeric($data["id"])) {
            return $this->go_error($event, -12);
        }   
        
        $ownSellerId = $this->sellerId = View_UserLogin::getOperateSellerId($data);
        if (empty($ownSellerId)) {
            return parent::go_error($event, -2011);
        }   
        $logSectionId = View_UserLogin::getOperateSectionId($data);
        $logUserId = View_UserLogin::getOperateUserId($data);        
        $nowTime = date('Y-m-d H:i:s');       
                
        $user = View_UserLogin::getStaffInfo($event, $ownSellerId, $logUserId);
        if (empty($user)) {
            return parent::go_error($event, -5030);
        }                  

        if (!$user["superd"] && !$logSectionId) {
            return $this->go_error($event, -5022);
        }          

        $id = $event->orderId = $data["id"];
        
        $View_BusinessOrder = new View_BusinessOrder();
        $oldOrder = $View_BusinessOrder->getOne($event, $id, $ownSellerId);
        if (empty($oldOrder)) {
            unset($View_BusinessOrder); 
            return parent::go_error($event, -5007);
        }    
        if (!$user["superd"]) {
        $hasCheckOrder = $View_BusinessOrder->checkOrderSection($event, $logUserId, $ownSellerId, $id, 10);
        unset($View_BusinessOrder);
        if (!$hasCheckOrder) {
            return parent::go_error($event, -5031);
        }
        }
 
        if ($oldOrder["dstatus"] != 13) {
            return parent::go_error($event, -5039);
        }  
        
        $algorithm = $oldOrder["algorithm"];      
        if ($algorithm == 1) {
            if (!isset($data["receiveDays"]) || empty($data["receiveDays"]) || !is_numeric($data["receiveDays"])) {
                return $this->go_error($event, -12);
            }
            
            $receiveDays = $data["receiveDays"];
            $commission = $oldOrder["commission"];
            $resultAmount = $oldOrder["resultAmount"];

            $View_BusinessOrder = new View_BusinessOrderCommission();
            $CommissionQuot = $View_BusinessOrder->getOneCommissionQuot($event, $ownSellerId, $id);
            unset($View_BusinessOrder);
            
            if ($receiveDays < $CommissionQuot["actualDays"]) {
                if ($receiveDays < $CommissionQuot["minDays"]) {
                    return parent::go_error($event, -5041);
                }
            }
            $event->business_order_commission_quot_data = array( 
                "seller_id" => $ownSellerId,
                "orderId" => $id,
                "receiveDays" => $receiveDays       
            );              
            $receiveAmount = $commission * $receiveDays * $resultAmount;
        } else {
            $receiveAmount = $oldOrder["actualAmount"];
        }    
        
        $event->business_order_data = array(
            "id" => $id,
            "dstatus" => 17,
            "receiveAmount" => $receiveAmount,
            "nowTime" => $nowTime     
        );
        
        $event->business_order_status_data = array(
            "seller_id" => $ownSellerId,
            "orderId" => $id,
            "dstatus" => 15,
            "sectionId" => $logSectionId,
            "relatedTime" => $nowTime,
            "relatedUserId" => $logUserId,
            "relatedUserName" => $user["userName"],
            "relatedUserAccount" => $user["userAccount"]
        );         
        
        BusinessOrderOperate::setAddData($event, 17, $ownSellerId, $logSectionId, $logUserId, $nowTime); 
/*     
var_dump("business_order_data",$event->business_order_data);  
var_dump("business_order_status_data",$event->business_order_status_data);   
var_dump("business_order_commission_quot_data",$event->business_order_commission_quot_data);  
var_dump("business_order_operate_data",$event->business_order_operate_data); 
return parent::go_error($event, -10000);
*/
    }
    
    public function orderDelay($event){
        
        $data = &$event->RequestArgs;
        
        if (empty($data) ||
            !isset($data["id"]) || empty($data["id"]) || !is_numeric($data["id"]) ||
            !isset($data["delayDays"]) || empty($data["delayDays"]) || !is_numeric($data["delayDays"])) {
            return $this->go_error($event, -12);
        }   
        
        $ownSellerId = $this->sellerId = View_UserLogin::getOperateSellerId($data);
        if (empty($ownSellerId)) {
            return parent::go_error($event, -2011);
        }   
        $logSectionId = View_UserLogin::getOperateSectionId($data);
        $logUserId = View_UserLogin::getOperateUserId($data);        
        $nowTime = date('Y-m-d H:i:s');       
                
        $user = View_UserLogin::getStaffInfo($event, $ownSellerId, $logUserId);
        if (empty($user)) {
            return parent::go_error($event, -5030);
        }                  

        if (!$user["superd"] && !$logSectionId) {
            return $this->go_error($event, -5022);
        }           

        $id = $event->orderId = $data["id"];
        
        $View_BusinessOrder = new View_BusinessOrder();
        $oldOrder = $View_BusinessOrder->getOne($event, $id, $ownSellerId);
        if (empty($oldOrder)) {
            unset($View_BusinessOrder); 
            return parent::go_error($event, -5007);
        }    
        
        if (!$user["superd"]) {        
        $hasCheckOrder = $View_BusinessOrder->checkOrderSection($event, $logUserId, $ownSellerId, $id, 10);
        unset($View_BusinessOrder);
        if (!$hasCheckOrder) {
            return parent::go_error($event, -5031);
        }
        }
 
        if ($oldOrder["dstatus"] != 13) {
            return parent::go_error($event, -5039);
        }  
        
        $algorithm = $oldOrder["algorithm"];      
        if ($algorithm == 1) {
            $View_BusinessOrderCommission = new View_BusinessOrderCommission();
            $CommissionQuot = $View_BusinessOrderCommission->getOneCommissionQuot($event, $ownSellerId, $id);
            unset($View_BusinessOrderCommission);
            
            $delayDays = $data["delayDays"];
            if ($delayDays < $CommissionQuot["actualDays"] ) {
                return parent::go_error($event, -5037);
            }

            $commission = $oldOrder["commission"];
            $resultAmount = $oldOrder["resultAmount"];
            $actualAmount = $commission * $delayDays * $resultAmount;
            
            $event->business_order_data = array(
                "id" => $id,
                "actualAmount" => $actualAmount,
                "nowTime" => $nowTime     
            );   
            
            $event->business_order_commission_quot_data = array( 
                "seller_id" => $ownSellerId,
                "orderId" => $id,
                "actualDays" => $delayDays       
            );  
            
            $event->business_order_delay_data = array( 
                "seller_id" => $ownSellerId,
                "orderId" => $id,
                "actualDays" => $CommissionQuot["actualDays"],
                "delayDays" => $delayDays,
                "actualTime" => $oldOrder["nowTime"],
                "delayTime" => $nowTime,     
            );             
            
            BusinessOrderOperate::setAddData($event, 16, $ownSellerId, $logSectionId, $logUserId, $nowTime); 
        }
/*    
var_dump("business_order_data",$event->business_order_data);  
var_dump("business_order_delay_data",$event->business_order_delay_data);   
var_dump("business_order_commission_quot_data",$event->business_order_commission_quot_data);  
var_dump("business_order_operate_data",$event->business_order_operate_data); 
return parent::go_error($event, -10000);
*/         
    }    
    
}
