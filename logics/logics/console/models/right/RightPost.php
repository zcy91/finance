<?php
namespace console\models\right;

use console\models\BaseModel;

class RightPost extends BaseModel {

    const TABLE_NAME = "right_post";

    public function primaryKey() {
        return ['id' => 'auto'];
    }

    protected function resolveFields($v = array(), $default_arr = array()) {
        $subset = array(
            "id",
            "dnames",
            "display",
            "deleted",
            "seller_id",
            "sectionName",
            "nowTime"     
        );

        $info_arr = parent::key_values_intersect($v, $subset, $default_arr);
        return $info_arr;
    }
    
    public function fetch_all($event){
        $args = &$event->RequestArgs;
        $seller_id = isset($args['seller_id'])?$args['seller_id']:$_SERVER['seller_info']['seller_id'];
        
        $column = " id, dnames, display, nowTime ,sectionName";
        $condition = " seller_id = :seller_id AND deleted = :deleted";
        $params = array(
            ":seller_id" =>  $seller_id,
            ":deleted"   =>  0
        );
        $returnData = self::fetch_inner_base($event, $condition, $params, null, $column);

        $event->Postback($returnData);
    }
    
    public function viewSingle($event){
        $args = &$event->RequestArgs;
        $seller_id = isset($args['seller_id'])?$args['seller_id']:$_SERVER['seller_info']['seller_id'];
        if(!isset($args['post_id']) || empty($args['post_id'])){
            return parent::go_error($event, -12);
        }
        
        $sql = "SELECT
                        t5.id AS moudleId,
                        t5.dnames AS moudle_name,
                        t1.dnames,
                        t1.sectionName,
                        t1.display,
                        t3.dnames AS node,
                        t2.baseId,
                        t1.nowTime,
                        t6.sectionId
                FROM
                        right_post t1
                INNER JOIN right_post_module t4 ON t1.id = t4.postId
                INNER JOIN right_module t5 ON t4.moduleId = t5.id
                INNER JOIN right_post_base t2 ON t1.id = t2.postId
                INNER JOIN right_base t3 ON t2.baseId = t3.id
                LEFT JOIN(
                        select t01.postId,GROUP_CONCAT(t01.sectionId) as sectionId from right_post_section t01
                        GROUP BY t01.postId
                ) t6 on t1.id = t6.postId
                WHERE
                        t1.id = :post_id
                AND t1.deleted = 0
                AND t1.seller_id = :seller_id";
        
//        $sql = "SELECT
//                        t5.id as moudleId,
//                        t5.dnames as moudle_name,
//                        t1.dnames,
//                        t1.sectionName,
//                        t1.display,
//                        t3.dnames AS node,
//                        t1.nowTime
//                FROM
//                        right_post t1
//                        
//                RIGHT JOIN right_post_module t4 on t1.id = t4.postId
//                RIGHT JOIN right_module t5 on t4.moduleId = t5.id
//                RIGHT JOIN right_post_base t2 ON t1.id = t2.postId
//                RIGHT JOIN right_base t3 ON t2.baseId = t3.id
//                WHERE
//                        t1.id =:post_id
//                AND t1.deleted = 0
//                AND t1.seller_id =:seller_id";
        
        $params = array(
            ":post_id" => $args['post_id'],
            ":seller_id" => $seller_id
        );
      
        $result = $this->query_SQL($sql, $event, null, $params); 
        $event->Postback($result);
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
    
    public function deletePost($event){
        $data = $event->RequestArgs;
        
        if (!empty($data)) {
            $seller_id = isset($args['seller_id'])?$args['seller_id']:$_SERVER['seller_info']['seller_id'];
            
            if(!isset($data['post_id']) || empty($data['post_id'])){
                return parent::go_error($event, -12);
            }
            
            $sql = "DELETE FROM right_post WHERE seller_id = :seller_id AND id = :postId";

            $params = array(
                ":seller_id" => $seller_id,
                ":postId" => $data['post_id']
            ); 
            $this->update_sql($sql, $event, $params);    
        }          
    }
    
    public function get_seq_no($event, $data_arr, $array_dim) {

        $count = $array_dim == 1 ? 1 : count($data_arr);
        $seq_no = 0;
        $this->proc_call('getKeyValue', array(101, $count), $seq_no, $event);

        return $seq_no;
    }
    
    public function refer_to($event, $data_arr, $array_dim, $seq_no, $count) {
        $event->set_post_id($seq_no);
    }

}
