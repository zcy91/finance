<?php
/**
 * This is the template for generating a controller class file.
 */

use yii\helpers\Inflector;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\controller\Generator */

echo "<?php\n";
?>
<?php if (!empty($generator->ns)): ?>
namespace <?= $generator->ns ?>;
<?php endif; ?>
use console\behaviors\<?= $generator->getControllerClassName() ?>Behavior;
use console\events\<?= $generator->getControllerClassName() ?>Event;
use console\controllers\BaseController;
use console\LocalResponse;
use console\behaviors\BaseBehavior;
class <?= $generator->getControllerClass() ?> extends BaseController
{
	private $behavior = null;	
	
	private $event = null;
	
	public function init()
	{
		parent::init();
		
		//给方法添加行为
		$this->behavior = new <?= $generator->getControllerClassName() ?>Behavior();
		$this->attachBehavior("<?= $generator->getControllerID()?>", $this->behavior);		
		
		//整理Web服务器传入的参数
		$this->event = new <?= $generator->getControllerClassName() ?>Event();
	}
    <?php foreach ($generator->getActionIDs() as $action): ?>
public function action<?= Inflector::id2camel($action) ?>($data)
    {
    <?php
    switch($action){
    case "fetchall":
    ?>
//整理传入的数据
		$this->event->set($data, BaseBehavior::FETCH_ALL_ACTION);	
		parent::fetch_all($this->getModels_F(),$this->event);
		$this->event->Display();
    <?php
    break;
    case "add":
    ?>
//整理传入的数据
		$this->event->set($data,BaseBehavior::ADD_ACTION);			
		parent::add($this->getModels_IDM(),$this->event);
		$this->event->ResponseObj->onSuccess("aaa");
		return 0;
    <?php
    break;
    case "login":
    ?>
    //整理传入的数据
		$this->event->set($data, BaseBehavior::LOGIN_ACTION);	
		parent::fetch_all($this->getModels_F(),$this->event);
		$this->event->Display();
   <?php
   break;
   }
   ?>
}
    <?php endforeach; ?>
<?php
echo "\n";
?>
}

