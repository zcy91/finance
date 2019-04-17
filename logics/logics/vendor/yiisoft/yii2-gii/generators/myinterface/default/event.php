<?php
/**
 * This is the template for generating a behavior class file.
 */

use yii\helpers\Inflector;
echo $generator->test;
print_R($generator->table_data);
/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\controller\Generator */
echo "<?php\n";
?>
<?php if (!empty($generator->ne)): ?>
namespace <?= $generator->ne ?>;
use yii\helpers\Json;
<?php endif; ?>
class <?= $generator->getEventClass() ?> extends BaseEvent
{

	/**
	 * 获取待处理的逻辑模块的Class名称
	 * @see \console\behaviors\BaseBehavior::getLogicClass()
	 */
    <?php foreach ($generator->getActionArgsIDs() as $action): ?>
public function <?= $action ?>($data)
    {
    }
    <?php endforeach; ?>
<?php
echo "\n";
?>
}