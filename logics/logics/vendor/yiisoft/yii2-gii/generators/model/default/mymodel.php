<?php
/**
 * This is the template for generating the model class of a specified table.
 */

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\model\Generator */
/* @var $tableName string full table name */
/* @var $className string class name */
/* @var $tableSchema yii\db\TableSchema */
/* @var $labels string[] list of attribute labels (name => label) */
/* @var $rules string[] list of validation rules */
/* @var $relations array list of relations (name => relation declaration) */

echo "<?php\n";
?>

namespace yii\gii\generators\myinterface;

/**
 * This is the model class for table "<?= $generator->generateTableName($tableName) ?>".
 
<?php if (!empty($relations)): ?>
 *
<?php foreach ($relations as $name => $relation): ?>
 * @property <?= $relation[1] . ($relation[2] ? '[]' : '') . ' $' . lcfirst($name) . "\n" ?>
<?php endforeach; ?>
<?php endif; ?>
 */
class <?= $className ?>
{
    /**
     * @inheritdoc
     */
    const TABLE_NAME = "<?= $generator->generateTableName($tableName) ?>";
<?php if ($generator->db !== 'db'): ?>

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('<?= $generator->db ?>');
    }
<?php endif; ?>
	protected function resolveFields($v = array(),$default_arr = array()){
            $info_arr = array();
            <?php foreach ($tableSchema->columns as $column): ?>
            if(isset($v["<?= "$column->name" ?>"])){ $info_arr["<?= "$column->name" ?>"]= $v["<?= "$column->name" ?>"];}//<?= "$column->comment"."\n"?>
            <?php endforeach; ?>
            $info_arr = array_merge($info_arr,$default_arr);//加入的数组覆盖/加入数组中 
            return $info_arr;
     }
     public function colulist(){
        return  array(<?php foreach ($tableSchema->columns as $column): ?>"<?= "$column->name" ?>",<?php endforeach; ?>); 
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
		$data_arr =  $event-><?= $className ?>;
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
