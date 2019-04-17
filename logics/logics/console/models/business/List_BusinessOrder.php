<?php
namespace console\models\business;

use console\models\BaseModel;
use console\models\user\View_UserLogin;
use console\models\business\View_BusinessOrder;
use console\models\business\View_BusinessOrderAttr;
use console\models\business\View_BusinessOrderAttrItem;
use console\models\business\View_BusinessOrderAttrValue;
use console\models\business\View_BusinessOrderSumm;
use console\models\right\View_RightPost;

class List_BusinessOrder extends BaseModel {
    
    public function orderList($event){
        
        $data = &$event->RequestArgs;
        $ownSellerId = View_UserLogin::getOperateSellerId($data);
        if (empty($ownSellerId)) {
            return parent::go_error($event, -2011);
        } 
        
        $now_time = date('Y-m-d H:i:s');
        $time_limit = isset($data["time_limit"]) && !empty($data["time_limit"]) ? $data["time_limit"] : $now_time;
        
        $limit_arr = $event->Pagination;//分页信息
        $limit = parent::getLimitArr($event);//获得分类信息数组或空值
        $ispage = isset($limit_arr["ispage"]) ? $limit_arr["ispage"] : 0;
        
        $userId = View_UserLogin::getOperateUserId($data);
        $View_UserLogin =new View_UserLogin();
        $fetchDataType = $View_UserLogin->fetchDataType($event, $userId, $ownSellerId);
        unset($View_UserLogin);
        $attachStr = View_BusinessOrder::orderAttachStr($fetchDataType);
        $condition = $attachStr["whereStr"];
        $condition .= " AND bos.relatedTime <= :time_limit";
        
        $params = array(
            ":sellerId" => $ownSellerId,
            ":userId" => $userId,
            ":time_limit" => $time_limit
        );
        //1:新订单 2:审核订单 3:签约订单 4:回款订单 5:全部订单
        $statu = (isset($data["status"]) && is_numeric($data["status"]) && in_array($data["status"], [1,2,3,4,5])) ? $data["status"] : 1;          
        $useStatu = $statu == 5 ? 2 : $statu;
        $statuStr = View_BusinessOrder::orderStatuStr($useStatu);

        switch ($statu) {
            case 1: 
                $condition .= " AND bo.dstatus = 2";
                $params[":dstatus"] = 2;
                break;
            case 2:
                $condition .= " AND bo.dstatus > 1";
                $params[":dstatus"] = 2;
                break;
            case 3:
                $condition .= " AND bo.dstatus > 7";
                $params[":dstatus"] = 6;
                break;
            case 4:
                $condition .= " AND bo.dstatus > 12";
                $params[":dstatus"] = 10;
                break;
            case 5:
                $params[":dstatus"] = 2;
                break;                
        }
        
        $attach = array(
            "selectStr" => $statuStr["selectStr"],
            "fromStr" => $attachStr["fromStr"],
            "joinStr" => $statuStr["joinStr"],
            "orderStr" => $statuStr["orderStr"],
        );
        
        //申请人
        if (isset($data["applyName"]) && !empty($data["applyName"])) {
            $condition .= " AND bo.applyName LIKE :applyName";
            $params[":applyName"] = "%" . $data["applyName"] . "%";
        } 

        //申请人手机
        if (isset($data["applyMobile"]) && !empty($data["applyMobile"])) {
            $condition .= " AND bo.applyMobile LIKE :applyMobile";
            $params[":applyMobile"] = "%" . $data["applyMobile"] . "%";
        }
        
        //申请人身份证
        if (isset($data["applyCard"]) && !empty($data["applyCard"])) {
            $condition .= " AND bo.applyCard LIKE :applyCard";
            $params[":applyCard"] = "%" . $data["applyCard"] . "%";
        }             
        
        //订单状态 2:提交, 7:审核，8:拒绝审核，12:签约, 13:拒绝签约, 17:回款
        if (isset($data["orderStatus"]) && !empty($orderStatus = $data["orderStatus"]) && in_array($orderStatus, [0,2,7,8,12,13,17])) {
            $orderStatus = $data["orderStatus"];
            switch ($orderStatus) {
                case 2:
                    $condition .= " AND bo.dstatus = 2";
                    break;
                case 7:
                    $condition .= " AND bo.dstatus = 7";
                    break;
                case 8:
                    $condition .= " AND bo.dstatus = 8";
                    break;
                case 12:
                    $condition .= " AND bo.dstatus = 12";
                    break;
                case 13:
                    $condition .= " AND bo.dstatus = 13";
                    break;
                case 17:
                    $condition .= " AND bo.dstatus = 17";
                    break;
                default:
                    $condition .= "";
            }
        }      
        
        //日期开始
        if (isset($data["begin_date"]) && !empty($data["begin_date"])) {
            $condition .= " AND up.entryTime >= :begin_date";
            $params[":begin_date"] = $data["begin_date"];
        } 

        //日期结束
        if (isset($data["end_date"]) && !empty($data["end_date"])) {
            $condition .= " AND up.entryTime >= :end_date";
            $params[":end_date"] = $data["end_date"];
        }            
        
        $View_BusinessOrder = new View_BusinessOrder();
        $dataAttr = $View_BusinessOrder->getAllOrder($event, $ispage, $condition, $params, $limit, $attach);
        unset($View_BusinessOrder);
        
        if ($ispage) {
            
            $sql = " SELECT FOUND_ROWS() as record_count; ";
            $return_count = $this->query_SQL($sql, $event);        
            $recode_count = $return_count[0]['record_count']; 
            
            $return_data = array(
                "pagesize" => $limit_arr["pagesize"],
                "pageindex" => $limit_arr["pageindex"],
                "recordcount" => $recode_count,
                "time_limit" => $time_limit,
                "data" => &$dataAttr
            );              
        } else {
            $return_data = &$dataAttr;
        }          
        
        $event->Postback($return_data);        
    }
    
    public function orderDescEd($event) {
        
        $data = &$event->RequestArgs;
        
        if (!isset($data["id"]) || empty($data["id"]) || !is_numeric($data["id"])) {
            return parent::go_error($event, -12);
        }    
        $orderId = $data["id"];
                
        $ownSellerId = View_UserLogin::getOperateSellerId($data);
        if (empty($ownSellerId)) {
            return parent::go_error($event, -2011);
        } 
        
        $View_BusinessOrder = new View_BusinessOrder();
        $return_data = $View_BusinessOrder->getOne($event, $orderId, $ownSellerId);
        unset($View_BusinessOrder);
        
        if (!empty($return_data)) {
            $View_BusinessOrderAttr = new View_BusinessOrderAttr();
            $attr = $View_BusinessOrderAttr->getOrderAttr($event, $ownSellerId, $orderId);
            unset($View_BusinessOrderAttr);
            
            if (!empty($attr)) {
                
                $attrData = [];
                
                $View_BusinessOrderAttrItem = new View_BusinessOrderAttrItem();
                $attrItem = $View_BusinessOrderAttrItem->getOrderAttrItem($event, $ownSellerId, $orderId);
                unset($View_BusinessOrderAttrItem);

                foreach ($attrItem as $itemAttrItem) {
                    $attrId = $itemAttrItem["attrId"];
                    $attrData[$attrId]["items"][] = array(
                        "attrItemId" => $itemAttrItem["itemId"],
                        "attrItemName" => $itemAttrItem["itemName"]
                    );
                }
                
                $View_BusinessOrderAttrValue = new View_BusinessOrderAttrValue();
                $attrItemVaule = $View_BusinessOrderAttrValue->getOrderAttrValue($event, $ownSellerId, $orderId);
                unset($View_BusinessOrderAttrValue);       
          
                foreach ($attrItemVaule as $itemAttrItemVaule) {
                    $attrId = $itemAttrItemVaule["attrId"];
                    $attrData[$attrId]["vaules"][] = array(
                        "attrItemId" => $itemAttrItemVaule["itemId"],
                        "attrItemName" => $itemAttrItemVaule["itemName"]
                    );
                } 
             
                foreach ($attr as &$itemAttr) {
              
                    $genre = $itemAttr["genre"];
                    $attrId = $itemAttr["attrId"];
                    
                    if (in_array($genre, [3,4]) && array_key_exists($attrId, $attrData) && isset($attrData[$attrId]["items"])) {
                        $itemAttr["items"] = $attrData[$attrId]["items"];
                    }
                   
                    if ($genre == 4 && array_key_exists($attrId, $attrData) && isset($attrData[$attrId]["vaules"])) {                    
                         $itemAttr["attrValue"] = $attrData[$attrId]["vaules"];
                    } elseif ($genre == 5 && $itemAttr["imageCount"] > 1 && array_key_exists($attrId, $attrData) && isset($attrData[$attrId]["vaules"])) {                       
                        $itemAttr["attrValue"] = array_column($attrData[$attrId]["vaules"], "attrItemName");
                    }
                    
                    unset($itemAttr);
                }
            }
           
            $return_data["attrs"] = $attr;
            
        }
        
        $event->Postback($return_data);      
    }
    
    public function orderDesc($event) {
        
        $data = &$event->RequestArgs;
        
        if (!isset($data["id"]) || empty($data["id"]) || !is_numeric($data["id"]) ||
            !isset($data["status"]) || empty($data["status"]) || !is_numeric($data["status"])) {
            return parent::go_error($event, -12);
        }    
        $orderId = $data["id"];
        $status = $data["status"];
                
        $ownSellerId = View_UserLogin::getOperateSellerId($data);
        if (empty($ownSellerId)) {
            return parent::go_error($event, -2011);
        } 
        
        //$status 1:新订单，2:审核订单，3:签约订单，4:回款订单 5:(中介/客户)
        $statu = (isset($data["status"]) && is_numeric($data["status"]) && in_array($data["status"], [1,2,3,4,5])) ? $data["status"] : 1;          
        $statuStr = View_BusinessOrder::orderStatuDescStr($statu); 
       
        $View_BusinessOrder = new View_BusinessOrder();
        $return_data = $View_BusinessOrder->getOrderDesc($event, $orderId, $ownSellerId, $statuStr);
        unset($View_BusinessOrder);
        
        if (!empty($return_data)) {
            $View_BusinessOrderAttr = new View_BusinessOrderAttr();
            $attr = $View_BusinessOrderAttr->getOrderAttr($event, $ownSellerId, $orderId);
            unset($View_BusinessOrderAttr);
            
            if (!empty($attr)) {
                
                $attrData = [];
                
                $View_BusinessOrderAttrValue = new View_BusinessOrderAttrValue();
                $attrItemVaule = $View_BusinessOrderAttrValue->getOrderAttrValue($event, $ownSellerId, $orderId);
                unset($View_BusinessOrderAttrValue);       
                
                foreach ($attrItemVaule as $itemAttrItemVaule) {
                    $attrId = $itemAttrItemVaule["attrId"];
                    $attrData[$attrId][] = array(
                        "attrItemId" => $itemAttrItemVaule["itemId"],
                        "attrItemName" => $itemAttrItemVaule["itemName"]
                    );
                } 
                
                foreach ($attr as &$itemAttr) {
                    
                    $genre = $itemAttr["genre"];
                    $attrId = $itemAttr["attrId"];
                    
                    if ($genre == 4 && array_key_exists($attrId, $attrData)) {
                         $itemAttr["attrValue"] = $attrData[$attrId];
                    } elseif ($genre == 5 && $itemAttr["imageCount"] > 1 && array_key_exists($attrId, $attrData)) {                      
                        $itemAttr["attrValue"] = array_column($attrData[$attrId], "attrItemName");                        
                    } elseif($genre == 5 && $itemAttr["imageCount"] == 1){
                        $itemAttr["attrValue"] = [$itemAttr["attrValue"]];
                    }
                    
                    unset($itemAttr);
                }
            }
            
            $return_data["attrs"] = $attr;
            
        }
        
        $event->Postback($return_data);  
    }    
    
    public function orderOwnList($event){
        
        $data = &$event->RequestArgs;
       
        if (!isset($data["status"]) || !is_numeric($data["status"]) || !in_array($data["status"], [1,2,3])) {
            return parent::go_error($event, -12);
        }            
        
        $ownSellerId = View_UserLogin::getOperateSellerId($data);
        if (empty($ownSellerId)) {
            return parent::go_error($event, -2011);
        } 
        
        $now_time = date('Y-m-d H:i:s');
        $time_limit = isset($data["time_limit"]) && !empty($data["time_limit"]  && $data["time_limit"] != 'null') ? $data["time_limit"] : $now_time;
        
        $limit_arr = $event->Pagination;//分页信息
        $limit = parent::getLimitArr($event);//获得分类信息数组或空值
        $ispage = isset($limit_arr["ispage"]) ? $limit_arr["ispage"] : 0; 
        
        $userId = View_UserLogin::getOperateUserId($data);
       
        $condition = " AND bo.creatTime <= :time_limit";
        $params = array(
            ":sellerId" => $ownSellerId,
            ":usrId" => $userId,
            ":time_limit" => $time_limit
        );
        //2:客户 3:中介 4:业务员 5:业务员
        $ownType = $event::OwnType; 
        //1:未提交 2:未完成 3:已完成
        $statu = $data["status"];

        switch ($statu) {
            case 1:
                $condition .= " AND bo.dstatus = 1";
                break;
            case 2:
                $condition .= " AND bo.dstatus < 17 AND bo.dstatus > 1";
                break;
            case 3:
                $condition .= " AND bo.dstatus = 17";
        }

        //申请人
        if (isset($data["applyName"]) && !empty($data["applyName"])) {
            $condition .= " AND bo.applyName LIKE :applyName";
            $params[":applyName"] = "%" . $data["applyName"] . "%";
        } 

        //申请人手机
        if (isset($data["applyMobile"]) && !empty($data["applyMobile"])) {
            $condition .= " AND bo.applyMobile LIKE :applyMobile";
            $params[":applyMobile"] = "%" . $data["applyMobile"] . "%";
        }
        
        //申请人身份证
        if (isset($data["applyCard"]) && !empty($data["applyCard"])) {
            $condition .= " AND bo.applyCard LIKE :applyCard";
            $params[":applyCard"] = "%" . $data["applyCard"] . "%";
        }             
        
        //日期开始
        if (isset($data["begin_date"]) && !empty($data["begin_date"])) {
            $condition .= " AND bos.relatedTime >= :begin_date";
            $params[":begin_date"] = $data["begin_date"];
        } 

        //日期结束
        if (isset($data["end_date"]) && !empty($data["end_date"])) {
            $condition .= " AND bos.relatedTime >= :end_date";
            $params[":end_date"] = $data["end_date"];
        }   
        
        $View_BusinessOrder = new View_BusinessOrder();
        $dataAttr = $View_BusinessOrder->orderOwnList($event, $ownType, $ispage, $condition, $params, $limit);
        unset($View_BusinessOrder);
        
        if ($ispage) {
            
            $sql = " SELECT FOUND_ROWS() as record_count; ";
            $return_count = $this->query_SQL($sql, $event);        
            $recode_count = $return_count[0]['record_count']; 
            
            $return_data = array(
                "pagesize" => $limit_arr["pagesize"],
                "pageindex" => $limit_arr["pageindex"],
                "recordcount" => $recode_count,
                "time_limit" => $time_limit,
                "data" => &$dataAttr
            );              
        } else {
            $return_data = &$dataAttr;
        }            
        
        $event->Postback($return_data);        
    }    

    public function orderCountSumm($event){
        
        $data = &$event->RequestArgs;      
        
        if (!isset($data["summType"]) || !is_numeric($data["summType"]) || !in_array($data["summType"], [1,2,3])) {
            return parent::go_error($event, -12);
        }             
        
        $ownSellerId = View_UserLogin::getOperateSellerId($data);
        $summType= $data["summType"];
        
        $condition = "";
        $params = array(
            ":sellerId" => $ownSellerId
        );  

        //日期开始
        if (isset($data["begin_date"]) && !empty($data["begin_date"])) {
            $condition .= " AND bos.relatedTime >= :begin_date";
            $params[":begin_date"] = $data["begin_date"];
        } 

        //日期结束
        if (isset($data["end_date"]) && !empty($data["end_date"])) {
            $condition .= " AND bos.relatedTime <= :end_date";
            $params[":end_date"] = $data["end_date"];
        }            
        
        $View_BusinessOrderSumm = new View_BusinessOrderSumm();
        $return_data = $View_BusinessOrderSumm->orderCountSumm($event, $summType, $condition, $params);
        unset($View_BusinessOrderSumm);        
        
        $event->Postback($return_data);    
    }
    
    public function orderAmountSumm($event){
        
        $data = &$event->RequestArgs;      
        
        if (!isset($data["summType"]) || !is_numeric($data["summType"]) || !in_array($data["summType"], [1,2,3])) {
            return parent::go_error($event, -12);
        }             
        
        $ownSellerId = View_UserLogin::getOperateSellerId($data);
        $summType= $data["summType"];
        
        $condition = "";
        $params = array(
            ":sellerId" => $ownSellerId
        );  

        //日期开始
        if (isset($data["begin_date"]) && !empty($data["begin_date"])) {
            $condition .= " AND bos.relatedTime >= :begin_date";
            $params[":begin_date"] = $data["begin_date"];
        } 

        //日期结束
        if (isset($data["end_date"]) && !empty($data["end_date"])) {
            $condition .= " AND bos.relatedTime <= :end_date";
            $params[":end_date"] = $data["end_date"];
        }            
        
        $View_BusinessOrderSumm = new View_BusinessOrderSumm();
        $return_data = $View_BusinessOrderSumm->orderAmountSumm($event, $summType, $condition, $params);
        unset($View_BusinessOrderSumm);        
        
        $event->Postback($return_data);    
    }    

    private function changeUseStatu($showId) {
        switch ($showId) {
            case 40:
                return array(
                    "useStatu" => 2,
                    "dstatus" => 2,
                    "condition" => ""
                );
            case 41:
                return array(
                    "useStatu" => 2,
                    "dstatus" => 2,
                    "condition" => " AND bo.dstatus = 2"
                );                
            case 42:
                return array(
                    "useStatu" => 3,
                    "dstatus" => 6,
                    "condition" => " AND bo.dstatus = 8"
                );                 
            case 43:
                return array(
                    "useStatu" => 4,
                    "dstatus" => 10,
                    "condition" => " AND bo.dstatus = 13"
                );                   
        }        
    }
    
    private function getSummSelectStr($showId) {
        switch ($showId) {
            case 40:
                return " COUNT(*) AS allCount,
                        SUM(IF(bo.dstatus = 2,1,0)) AS checkCount,
                        SUM(IF(bo.dstatus = 8,1,0)) AS singCount,
                        SUM(IF(bo.dstatus = 13,1,0)) AS receiveCount";
            case 41:
                return " COUNT(*) AS allCount,
                        SUM(IF(bo.dstatus = 2,1,0)) AS checkCount";           
            case 42:
                return " COUNT(*) AS allCount,
                        SUM(IF(bo.dstatus = 8,1,0)) AS singCount";               
            case 43:
                return " COUNT(*) AS allCount,
                        SUM(IF(bo.dstatus = 13,1,0)) AS receiveCount";                 
        }            
    }

    public function orderConsoleSumm($event) {
        
        $data = &$event->RequestArgs;   
        
        $ownSellerId = View_UserLogin::getOperateSellerId($data);
        $logUserId = View_UserLogin::getOperateUserId($data);

        $View_UserLogin = new View_UserLogin();
        $fetchDataType = $View_UserLogin->fetchDataType($event, $logUserId, $ownSellerId);
        unset($View_UserLogin);    

        $attachStr = View_BusinessOrder::orderAttachStr($fetchDataType);
        $condition = $attachStr["whereStr"];
        $condition .= " AND bos.relatedTime <= :time_limit";  
        
        if ($fetchDataType == 1) {
            $showId = 40;
            $showArr = [40, 41 ,42, 43];
        } else {
            $View_RightPost = new View_RightPost();
            $showInfo = $View_RightPost->getShowInfo($event, $logUserId, $ownSellerId);
            unset($View_RightPost);
            
            if (!$showInfo["showId"]) { 
                return;
            }
            
            $showId = $showInfo["showId"];   
            $showArr = $showInfo["showArr"];
        }        
        
        $now_time = date('Y-m-d H:i:s');
        $time_limit = isset($data["time_limit"]) && !empty($data["time_limit"]) ? $data["time_limit"] : $now_time;        
        $params = array(
            ":sellerId" => $ownSellerId,
            ":userId" => $logUserId,
            ":time_limit" => $time_limit
        );
        
        $View_BusinessOrder = new View_BusinessOrder();
        
        //数据
        if ($fetchDataType == 1) {
            $sumChangeUse = $this->changeUseStatu($showId);
            $statuStrSum = View_BusinessOrder::orderStatuStr($sumChangeUse["useStatu"]);
            $attachSum = array(
                "selectStr" => $this->getSummSelectStr($showId),
                "fromStr" => $attachStr["fromStr"],
                "joinStr" => $statuStrSum["joinStr"]
            ); 
            $conditionSum = $condition . $sumChangeUse["condition"];
            $paramsSum = array_merge($params, [":dstatus" => $sumChangeUse["dstatus"]]);
            $dataSumm = $View_BusinessOrder->getConsoleSumm($event, $conditionSum, $paramsSum, $attachSum);
        } else {
            $dataSumm = ["allCount" => 0];
            foreach ($showInfo["showArr"] as $id) {
                $sumChangeUse = $this->changeUseStatu($id);
                $statuStrSum = View_BusinessOrder::orderStatuStr($sumChangeUse["useStatu"]);
                $attachSum = array(
                    "selectStr" => $this->getSummSelectStr($id),
                    "fromStr" => $attachStr["fromStr"],
                    "joinStr" => $statuStrSum["joinStr"]
                ); 
                $conditionSum = $condition . $sumChangeUse["condition"];
                $paramsSum = array_merge($params, [":dstatus" => $sumChangeUse["dstatus"]]);
                $consoleSumm = $View_BusinessOrder->getConsoleSumm($event, $conditionSum, $paramsSum, $attachSum);
                if (!empty($consoleSumm)) {
                    $dataSumm["allCount"] .=  $consoleSumm["allCount"];
                    switch ($id) {
//                        case 40:
//                            $dataSumm["checkCount"] =  $consoleSumm["checkCount"];
//                            $dataSumm["singCount"] =  $consoleSumm["singCount"];
//                            $dataSumm["receiveCount"] =  $consoleSumm["receiveCount"];
                        case 41:
                            $dataSumm["checkCount"] =  $consoleSumm["checkCount"];           
                        case 42:
                            $dataSumm["singCount"] =  $consoleSumm["singCount"];               
                        case 43:
                            $dataSumm["receiveCount"] =  $consoleSumm["receiveCount"];                 
                    }   
                }
            }            
        }

        //列表
        $changeUseStatu = $this->changeUseStatu($showId);

        $statuStrAll = View_BusinessOrder::orderStatuStr($changeUseStatu["useStatu"]);
        $attachAll = array(
            "selectStr" => $statuStrAll["selectStr"],
            "fromStr" => $attachStr["fromStr"],
            "joinStr" => $statuStrAll["joinStr"],
            "orderStr" => $statuStrAll["orderStr"],
        ); 
        $conditionAll = $condition . $changeUseStatu["condition"];
        $paramsAll = array_merge($params, [":dstatus" => $changeUseStatu["dstatus"]]);
        
        $limit_arr = $event->Pagination;//分页信息
        $limit = parent::getLimitArr($event);//获得分类信息数组或空值
        $ispage = isset($limit_arr["ispage"]) ? $limit_arr["ispage"] : 0;         
        
        //申请人
        if (isset($data["applyName"]) && !empty($data["applyName"])) {
            $condition .= " AND bo.applyName LIKE :applyName";
            $params[":applyName"] = "%" . $data["applyName"] . "%";
        }         
        
        $dataAttr = $View_BusinessOrder->getAllOrder($event, $ispage, $conditionAll, $paramsAll, $limit, $attachAll);
        unset($View_BusinessOrder);        
        
        if ($ispage) {
            
            $sql = " SELECT FOUND_ROWS() as record_count; ";
            $return_count = $this->query_SQL($sql, $event);        
            $recode_count = $return_count[0]['record_count']; 
            
            $return_data = array(
                "pagesize" => $limit_arr["pagesize"],
                "pageindex" => $limit_arr["pageindex"],
                "recordcount" => $recode_count,
                "time_limit" => $time_limit,
                "showId" => $showId,
                "showArr" => $showArr,
                "summ" => &$dataSumm,
                "data" => &$dataAttr
            );              
        } else {
            $return_data = array(
                "showId" => $showId,
                "showArr" => $showArr,
                "summ" => &$dataSumm,
                "data" => &$dataAttr
            );
        }            
        
        $event->Postback($return_data);         
    }

}
