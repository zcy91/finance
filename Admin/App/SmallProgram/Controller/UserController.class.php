<?php
/*
用户控制器
 *  */
namespace SmallProgram\Controller;
use SmallProgram\Model\UserModel;
use SmallProgram\Plugin\Upfile;

class UserController extends CommonController {
    /**
    获取用户信息登录
    **/
    public function get_user_info(){
        $post_data = I("post.");
        $User = new UserModel();
        $apiData = $User->login($post_data);
       
        $status = 0; 
        $isSuper = 0;
        $user_info = [];
        $info = "";
        
        $returnData = array(
            "status" => &$status,
            "isSuper" => &$isSuper,
            "data"   => &$user_info,
            "info"   => &$info,
        );
        if($apiData['returnState'] == 1 && !empty($apiData['returnData'])){
            $status = 1;
            $user_info = $apiData['returnData'];
            $isSuper = $user_info['isSuper'];
            session("userId",$user_info['userId']);
            session("ADMIN_ID",$user_info['userId']);
            session("currentRoleId",$user_info['currentRoleId']);
        }else{
            $status = $apiData['returnState'];
            $info = get_error_info($status);
        }
        $this->ajaxReturn($returnData,json);
    }
    
    /**
    客户列表
     * $customer_type 1：普通员工/跟单员  2：中介  3：普通会员  4:业务员
     *  */
    public function view_single_info(){
        $post_data = I("post.");
        if(!isset($post_data['roleId']) || empty($post_data['roleId'])
               || !isset($post_data['id']) || empty($post_data['id']))
        {
            $this->ajaxReturn(array("status"=>0,"info"=>"参数错误"),json);
        }
        
        $User = new UserModel();
        
        $customer_type = I("post.roleId",1,intval);
        $apiData = [];
        unset($post_data['roleId']);
        switch($customer_type){
            case 2: $apiData = $User->intermediary_singleview($post_data); break;  //2:中介
            case 3: $apiData = $User->user_single_view($post_data);break;//3:用户
            case 4: $apiData = $User->staff_single_view($post_data);break;//4:业务员
            case 5: $apiData = $User->intermediary_single_view($post_data);break;//5:跟单员
            case 6: $apiData = $User->intermediary_single_view($post_data);break;//6:普通员工
        }
        $info = "";$responseData = [];
      
        if($apiData['returnState'] == 1 && !empty($apiData['returnData'])){
            $responseData = $apiData["returnData"];
        }else{
            $info = get_error_info($apiData['returnState']);
        }
        unset($User);//释放对象
        $this->ajaxReturn(array("status"=>$apiData['returnState'],"data"=>$responseData,"info"=>$info));
    }
    
     /**
    添加修改客户
     * $customer_type 1：普通员工/跟单员  2：中介  3：普通会员  4:业务员
     *  */
    public function add_save(){
        $post_data = I("post.");
        $User = new UserModel();
        if(!isset($post_data['id']) || empty($post_data['id'])
                || !isset($post_data['roleId']) || empty($post_data['roleId'])){
            $this->ajaxReturn(array("status"=>0,"info"=>"参数错误"));
        }
        
        $role_type = I("post.roleId",1,intval);
      
        //处理图片上传
        if($_FILES['file']['name']){
            $file = new Upfile($_FILES['file']);
            $post_data['pic']= $file->get_url();
        }
        $apiData = [];
        switch($role_type){
            case 2: $apiData = $User->admin_save($post_data); break;  //中介
            case 3: $apiData = $User->customeredit($post_data); break;
            case 4: $apiData = $User->admin_save($post_data); break;
            case 5: $apiData = $User->admin_save($post_data);break;
            case 6: $apiData = $User->admin_save($post_data);break;
        }
        $status = 0; $info = "";
        if($apiData['returnState'] == 1){
            $status = 1;
        }else{
            $info = get_error_info($apiData['returnState']);
        }
        
        $this->ajaxReturn(array("status"=>$status,"info"=>$info),'json');
    }
   
    
     /**
    停用/启用客户
     * $customer_type 1：普通员工/跟单员  2：中介  3：普通会员  4:业务员
     *  */
    public function delete(){
        $post_data = I("post.");
        $User = new UserModel();
        
        $customer_type = I("post.customer_type",1,intval);
        
        switch($customer_type){
            case 1: $apiData = $User->admin_delete($post_data); break;
            case 2: $User->user_delete($post_data);break;
            case 3: $User->intermediary_delete($post_data);break;
        }
        $info = "";
        if($apiData['returnState'] != 1){
            $info = get_error_info($apiData['returnState']);
        }
        $this->ajaxReturn(array("status"=>$apiData['returnState'],"info"=>$info));
    }
    
    public function get_code(){
        $phone = I("mobile_no");
        $code = $this->make_random(5);
        $content="【网货汇】此验证码在20分钟内，平台验证码为：".$code."信息来自于网货汇";
        $phone_count = intval(cookie($phone))+1;
        if($phone_count>=4){
            $this->ajaxReturn(array("status"=>2),json);
        }

//        $result = $this->send_info($phone,$content);
        $result = 1;
        if($result == 1){
            $this->ajaxReturn(array("status"=>1,"code"=>$code),json);
        }else{
            $this->ajaxReturn(array("status"=>0),json);
        }
    }
    
    public function register(){
        $post_data = I("post.");
        if(session("smyzm") != $post_data['code']){
            $this->ajaxReturn(array("status"=>0,"info"=>"手机验证码不正确，请重试!"),'json');
        }
        $User = new UserModel();
        unset($post_data['code']);
        $apiData = $User->bindaccount($post_data);
        $status = 0;$isSuper = 0;$user_info = [];$info = "";
        $returnData = array(
            "status"  => &$status,
            "isSuper" => &$isSuper,
            "data"    => &$user_info,
            "info"    => &$info,
        );
        if($apiData['returnState'] == -4005){ //需要新注册
            $post_data['mobile'] = $post_data['mobile_no'];
            $apiData1 = $User->customerregister($post_data);
           
            $status = $apiData1['returnState'];
            $user_info = $apiData1['returnData'];
            
            session("userId",$user_info['userId']);
            session("ADMIN_ID",$user_info['userId']);
            session("currentRoleId",$user_info['currentRoleId']);
        }else if($apiData['returnState'] == 1 && !empty ($apiData['returnData'])){
            $status = 1;
            $user_info = $apiData['returnData'];
            session("userId",$user_info['userId']);
            session("ADMIN_ID",$user_info['userId']);
            session("currentRoleId",$user_info['currentRoleId']);
        }else{
            $status = $apiData['returnState'];
            $info = get_error_info($apiData['returnState']);
        }
        
        $this->ajaxReturn($returnData,json);
    }
    
    /*功能：随机生成验证码
    * params $len生成位数
    * */
    protected function make_random($len=5){
            $str="";
            for ($i=1;$i<=$len;$i++){
                    $str.=mt_rand(0,9);
            }
            session("smyzm",$str);
            return $str;
    }
    //发送信息调用函数
    protected function send_info($phone,$content){
        $sms = new \SmallProgram\Plugin\Sms($sms_server='smsbao',$sms_account='flb520',$sms_password='jiuyukeji123',$param = array());
        $apiData = $sms->sendsms($phone,$content);
        return $apiData;
    }
    
    public function uploadImg(){
        $post_data = I("post.");
        if($_FILES['file']['name']){
            $file = new Upfile($_FILES['file']);
            $post_data['pic']= $file->get_url();
        }
        $role_type = I("post.roleId",3,intval);
        $apiData = [];
        $User = new UserModel();
        switch($role_type){
            case 2: $apiData = $User->admin_save($post_data); break;
            case 3: $apiData = $User->customeredit($post_data); break;
            case 4: $apiData = $User->admin_save($post_data); break;
            case 5: $apiData = $User->user_save($post_data);break;
            case 6: $apiData = $User->intermediary_save($post_data);break;
        }
        $status = 0; $info = "";
        if($apiData['returnState'] == 1){
            $status = 1;
        }else{
            $info = get_error_info($apiData['returnState']);
        }
        
        $this->ajaxReturn(array("status"=>$status,"info"=>$info,"picurl"=>$post_data['pic']),'json');
    }
    
    public function add_intermediary(){
        $post_data = I("post.");
        $User = new UserModel();
        $id = I("post.id",0,intval);
        if($id == 0){
            $apiData = $User->intermediary_add($post_data);
        }else{
            $apiData = $User->intermediary_edit($post_data);
        }
        $info = "";
        if($apiData['returnState'] != 1){
            $info = get_error_info($apiData['returnState']);
        }
        $this->ajaxReturn(array("status"=>$apiData['returnState'],"info"=>$info));
    }
    
    public function rolestafflist(){
        $post_data = I("post.");
        
        $role_id = I("post.roleId",4,intval);
        
        $params = array(
            "roleId" => $role_id
        );
        
        $User = new UserModel();
        $apiData = $User->rolestafflist($params);
        
        $info = "";$userInfo = [];
        if($apiData['returnState'] == 1 && !empty($apiData['returnData'])){
            $userInfo = $apiData['returnData'];
        }else{
            $info = get_error_info($apiData['returnState']);
        }
        
        $this->ajaxReturn(array("status"=>$apiData['returnState'],"data"=>$userInfo,"info"=>$info));
    
    }
    
    public function intermediary_list(){
        $userId = I("post.logUserId",0,intval);
        
        if($userId == 0){
            $this->ajaxReturn(array("status"=>0,"info"=>"参数错误!!"));
        }
        
        $params = array(
            "userId" => $userId
        );
        
        $User = new UserModel();
        $apiData = $User->intermediary_list($params);
        
        $info = "";$userInfo = [];
        if($apiData['returnState'] == 1 && !empty($apiData['returnData'])){
            $userInfo = $apiData['returnData'];
        }else{
            $info = get_error_info($apiData['returnState']);
        }
        
        $this->ajaxReturn(array("status"=>$apiData['returnState'],"data"=>$userInfo,"info"=>$info));
    }
}