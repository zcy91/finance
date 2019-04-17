<?php
namespace console\models\base;

use console\models\BaseModel;

class BaseProductCategory extends BaseModel{
    
    const TABLE_NAME = "base_product_category";

    public function primaryKey() {
        return ['id' => 'auto'];
    }
    
    protected function resolveFields($v = array(), $default_arr = array()) {

        $subset = array(
            "id",
            "dnames",
            "sort",
            "parentsPath",
            "algorithm",
            "seller_id",
            "level",
            "pid",
            "nowTime"
        );

        $info_arr = parent::key_values_intersect($v, $subset, $default_arr);
        return $info_arr;
    }
    
    public function fetch_all($event){
        $args = &$event->RequestArgs;
        $seller_id = isset($args['seller_id'])?$args['seller_id']:$_SERVER['seller_info']['seller_id'];
        
        $column = " id, dnames, sort,  pid ,`level`, algorithm, parentsPath ";
        if(isset($args['cate_id']) && !empty($args['cate_id'])){
            $condition = " seller_id = :seller_id AND id = :cate_id";
            $params = array(
                ":seller_id" =>  $seller_id,
                ":cate_id" =>  $args['cate_id']
            );
            $data = self::fetch_inner_base($event, $condition, $params, null, $column);
            
            if(empty($data)){
                return parent::go_error($event, -3000);
            }
            $returnData = $data[0];
        }else{
            $condition = " seller_id = :seller_id AND `level` = :level";
            $params = array(
                ":seller_id" =>  $seller_id,
                ":level" =>  1
            );
            $data1 = self::fetch_inner_base($event, $condition, $params, null, $column);

            $params[":level"] = 2;
            $data2 = self::fetch_inner_base($event, $condition, $params, null, $column);

            $params[":level"] = 3;
            $data3 = self::fetch_inner_base($event, $condition, $params, null, $column);

            $params[":level"] = 4;
            $data4 = self::fetch_inner_base($event, $condition, $params, null, $column);

            $params[":level"] = 5;
            $data5 = self::fetch_inner_base($event, $condition, $params, null, $column);

            $returnData = array(
                "level1" => $data1,
                "level2" => $data2,
                "level3" => $data3,
                "level4" => $data4,
                "level5" => $data5,
            );
        }
        $event->Postback($returnData);
    }
    
    /**
     * 同一个Action内的单表查询(模块间调用)
     * @param type $event
     * @param type $condition
     * @param type $args
     * @param type $limit_arr
     * @param type $column
     * @param type $order_by
     * @return type
     */
    public function fetch_inner_base($event,$condition,$args,$limit_arr = null,$column = null,$order_by=null,$is_reset_data = 1)
    {
        $table_name = $this->get_table_name();   
        
    	$limit = $this->getLimitArr_inner($limit_arr);//获得分类信息数组或空值
        
        $ispage = isset($limit_arr["ispage"])?$limit_arr["ispage"]:0;//是否按翻页结果反回0不是翻页1翻页
        
        if($ispage == 1){//需要按分页的形式返回
                $page_size = isset($limit_arr["pagesize"])?$limit_arr["pagesize"]:0;//每页显示数量
                $page_no = isset($limit_arr["pageindex"])?$limit_arr["pageindex"]:0;//当前页码
                
                $record_count = isset($limit_arr["recordcount"])?$limit_arr["recordcount"]:0;//记录总数量
                $recode_count= $record_count;
                
                if($record_count==0){
                    $recode_count = $this->count($table_name, $condition,$args,'',$event);
                }
                
                $db_data = array();
                if($recode_count>0){
                    
                    $db_data = $this->query($table_name, $condition,$args,$limit,$event, $column, $order_by);   
                    
                    if($is_reset_data)
                    {
                        $db_data = $this->rebuild_data($event,$db_data);
                    }
                }
                $returnData = $event->addPagination($recode_count, $page_no, $page_size, $db_data);
        }else{//不用按分类直接返回结果
                $returnData = $this->query($table_name, $condition,$args,$limit,$event, $column, $order_by);
                
                if($is_reset_data)
                {
                    //对数据进行其它格式化操作
                    $returnData = $this->rebuild_data($event,$returnData);
                }
        }
	
    	return $returnData;
    } 
    
    public function get_seq_no($event, $data_arr, $array_dim) {

        $count = $array_dim == 1 ? 1 : count($data_arr);
        $seq_no = 0;
        $this->proc_call('getKeyValue', array(501, $count), $seq_no, $event);

        return $seq_no;
    }
    
    public function refer_to($event, $data_arr, $array_dim, $seq_no, $count) {
        $event->set_cate_id($seq_no);
    }
    
    
}
