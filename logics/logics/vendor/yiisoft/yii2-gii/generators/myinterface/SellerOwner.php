<?php

namespace yii\gii\generators\myinterface;

/**
 * This is the model class for table "seller_owner".
 
 */
class SellerOwner{
    /**
     * @inheritdoc
     */
    const TABLE_NAME = "seller_owner";
	protected function resolveFields($v = array(),$default_arr = array()){
            $info_arr = array();
                        if(isset($v["seller_id"])){ $info_arr["seller_id"]= $v["seller_id"];}//所有者编号,代理商/供应商/分销商
                        if(isset($v["seller_owner_id"])){ $info_arr["seller_owner_id"]= $v["seller_owner_id"];}//从【客户信息扩展表】获取自定义客户序号
                        if(isset($v["cate_sys_id"])){ $info_arr["cate_sys_id"]= $v["cate_sys_id"];}//行业编号
                        if(isset($v["customer_type"])){ $info_arr["customer_type"]= $v["customer_type"];}//2:线下供应商 4: 线下分销商 8: 线下零售客户16: 线下竞争对手 32:内部员工 64:平台商城会员
                        if(isset($v["customer_name"])){ $info_arr["customer_name"]= $v["customer_name"];}//公司名称/个人姓名
                        if(isset($v["seller_cust_no"])){ $info_arr["seller_cust_no"]= $v["seller_cust_no"];}//客户自定义编号,当客户有自己的编码时，使用 内部员工时，可以设置员工的工号
                        if(isset($v["parent_cust_id"])){ $info_arr["parent_cust_id"]= $v["parent_cust_id"];}//客户父编号
                        if(isset($v["relation_p_c"])){ $info_arr["relation_p_c"]= $v["relation_p_c"];}//客户父子:1:父亲 2:母亲 3:哥哥 4:姐姐 5:
                        if(isset($v["relation_level"])){ $info_arr["relation_level"]= $v["relation_level"];}//关系级别:可以设置两级,<=2
                        if(isset($v["zip_code"])){ $info_arr["zip_code"]= $v["zip_code"];}//邮政编码
                        if(isset($v["address_part1"])){ $info_arr["address_part1"]= $v["address_part1"];}//客户地址,精确到城市
                        if(isset($v["address_part2"])){ $info_arr["address_part2"]= $v["address_part2"];}//客户地址,街道、乡镇级别信息
                        if(isset($v["homepage_url"])){ $info_arr["homepage_url"]= $v["homepage_url"];}//客户网址,客户自有网址域名
                        if(isset($v["logo_url"])){ $info_arr["logo_url"]= $v["logo_url"];}//头像图片路径
                        if(isset($v["logo_base_name"])){ $info_arr["logo_base_name"]= $v["logo_base_name"];}//存放图片的基本名称
                        if(isset($v["image_tips"])){ $info_arr["image_tips"]= $v["image_tips"];}//图片说明
                        if(isset($v["tel"])){ $info_arr["tel"]= $v["tel"];}//公司电话
                        if(isset($v["email"])){ $info_arr["email"]= $v["email"];}//电子邮件
                        if(isset($v["contacts"])){ $info_arr["contacts"]= $v["contacts"];}//公司的平台使用负责人姓名
                        if(isset($v["contacts_birthday"])){ $info_arr["contacts_birthday"]= $v["contacts_birthday"];}//联系人生日
                        if(isset($v["mobile_no"])){ $info_arr["mobile_no"]= $v["mobile_no"];}//手机号码
                        if(isset($v["description"])){ $info_arr["description"]= $v["description"];}//备注
                        if(isset($v["start_date"])){ $info_arr["start_date"]= $v["start_date"];}//财年的开始日期
                        if(isset($v["end_date"])){ $info_arr["end_date"]= $v["end_date"];}//财年的结束日期
                        if(isset($v["is_deleted"])){ $info_arr["is_deleted"]= $v["is_deleted"];}//是否删除
                        $info_arr = array_merge($info_arr,$default_arr);//加入的数组覆盖/加入数组中 
            return $info_arr;
     }
     public function colulist(){
        return  array("seller_id","seller_owner_id","cate_sys_id","customer_type","customer_name","seller_cust_no","parent_cust_id","relation_p_c","relation_level","zip_code","address_part1","address_part2","homepage_url","logo_url","logo_base_name","image_tips","tel","email","contacts","contacts_birthday","mobile_no","description","start_date","end_date","is_deleted",); 
     }
     public function checkdata($args){
         $colulist=$this->colulist(); //返回字段列表
         foreach($args as $v){
            if(!in_array($v,$colulist)){
                return false;
            }
         }
     }
     	/* (non-PHPdoc)
	 * @see \console\models\BaseInterface::add()
	 */
	public function add($event) {
		// TODO Auto-generated method stub
		$data_arr =  $event->SellerOwner;
        //$now = time();
        if(!$this->notEmptyArr($data_arr)){return 1;}//参数为空数组，则直接返回成功，不做处理
        $default_arr = array(//需要加入的其它字段信息数组[一维数组]
            //"create" => $now,
        );
        $data_list = self::resolveParameter($date_arr,$default_arr);//解析数组参数字段信息 
		parent::insert(self::TABLE_NAME, $data_list, $event);
	}

	/* (non-PHPdoc)
	 * @see \console\models\BaseInterface::modify()
	 */
	public function modify($event) {
		// TODO Auto-generated method stub
		
	}
	/* (non-PHPdoc)
	 * @see \console\models\BaseInterface::delete()
	 */
	public function delete($event) {
		// TODO Auto-generated method stub
		
	}
    public function fetch_all($event){
    	
    	$condition = $event->Condition;
    	$args = $event->ParamsList;
    	
      	$a=parent::query(self::TABLE_NAME, $condition,$args, $event);
      	
      	$event->Postback($a);
    }
}
