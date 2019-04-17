<?php
namespace Common\Model;

// 描述：用于地区管理下的操作
class BaseModel{
	
    /**
     * 获取当前站点的信息
     * @return type
     */
    public static function get_siteinfo()
    {
        //获取当前站点的所属客户信息
        $api = new \Org\Api\ServiceProxy();

        $sellerInfo = $api->fetch_site_info();      
        
        if($sellerInfo["returnState"] < 1){

            return null;
        } 
        else {

            $sellerInfo = $sellerInfo["returnData"];
            
            return $sellerInfo;
        }        
    }
    
    /**
     * 获取错误信息
     * @param type $error_code 错误编号
     * @param type $refresh 是否重新读取服务器端数据
     * @return type 错误信息
     */
    public static function get_error_msg($error_code,$refresh=1)
    {
        $objAccessModel = new \Org\Api\AccessModule();
        $error_msg = $objAccessModel->get_error_id($error_code);    
        
        //如果不存在，则重新获取系统信息
        if(empty($error_msg) && $refresh == 1)
        {
            $proxy = new \Org\Api\ServiceProxy();
            $proxy->cache_system_info();
            $error_msg = $objAccessModel->get_error_id($error_code);
            
        }
        
        return $error_msg;        
    }
    
    /**
     * 重置系统信息
     * 清空缓存
     */
    public static function reset_sysinfo()
    {
        $proxy = new \Org\Api\ServiceProxy();
        $proxy->cache_system_info();
        $proxy->fetch_site_info(true);        
    }    
     /**
     * 返回数据格式
     * $apiData 数据格式
     *  $type 0：不带分页格式    1：带分页格式
     * */
    protected function return_data($apiData,$type=false){
        
        if($apiData['returnState']==1){
            if(!$type){
                if(empty($apiData['returnData'])){
                    $result = 2;//接口调用成功，但没有取到数据
                }else{
                    $result = $apiData['returnData'];
                }
            }else{
                if(empty($apiData['returnData']['data'])){
                    $result = 2;//接口调用成功，但没有取到数据
                }else{
                    $result = $apiData['returnData']['data'];
                }
            }
            
        }else{
            $result = $apiData['returnState'];//接口未调成功 返回状态不正常
        }
        
        return $result;
    }
    
    /***
       功能：获取购物车uuid
     * 参数：$seller_owner_id :客户在系统里的编号  $user_id:客户id $param_cart_uuid：参数购物车uuid $is_ajax：是否是ajax提交
     * 返回：返回购物车uuid
     *      */
    public function getUuid($seller_owner_id,$user_id,$is_ajax = false,$type_access = 2,$param_cart_uuid = ''){
        $key_uuid = 'uuid';
        
        $cart_uuid = '';
        
        $uuid_s = session($key_uuid);
        
        $uuid_c = cookie($key_uuid);
        
        if(!empty($uuid_s))
        {
            if(!empty($param_cart_uuid) && $uuid_s != $param_cart_uuid)
            {
                //说明传入的参数的cart_uuid已经被篡改
                if($is_ajax){
                    return -1;
                }else{
                    $this->error(L('CartIdFailed'));
                }
            }
            else
            {
                $cart_uuid = $uuid_s;
            }
        }
        else if(!empty ($uuid_c))
        {
            if(!empty($param_cart_uuid))
            {
                if($is_ajax){
                    return -1;
                }else{
                    $cart_uuid = $param_cart_uuid;
                }
            }
            else
            {
                $cart_uuid = $uuid_c;
            }
        }//session，cookie都为空时，使用传入的参数作为原始key
        else
        {
            $cart_uuid = $param_cart_uuid;
        }
        
       
        $seller_id = SELLER_ID;
        $shop_id = SHOP_ID;
        $customer_id = $seller_owner_id;
        $user_id = $user_id;
        $session_id = session_id();
       
        $data = array(
            'seller_id'=>$seller_id,
            'shop_id'=>$shop_id,
            'customer_id'=>$customer_id,
            'user_id'=>$user_id,
            'cart_uuid'=>$cart_uuid,
            'session_id'=>$session_id,
        );

        $res=c_call_service('Cart', 'CartShop', 'getUUID', $data,$type_access);

        if($res["returnState"] < 0)
        {
            if($is_ajax){
                return -1;
            }else{
                $this->error(L('CartIdFailed'));
            }
        }
        
        $new_uuid = $res['returnData']['cart_uuid'];

        $sellerInfo = c_get_seller_info(2);
        $cart_period = 0 ;
        if($sellerInfo["returnState"]==1){
             $sellerInfo = $sellerInfo["returnData"];
             $cart_period = $sellerInfo['cart_period'];
        }
        $cookie_time = empty($cart_period)? C('COOKIE_EXPIRE') : $cart_period * 24 * 3600;


        cookie($key_uuid, $new_uuid, array('expire'=>$cookie_time)); 


        session($key_uuid,$new_uuid);
            
        return session($key_uuid);
    }
}