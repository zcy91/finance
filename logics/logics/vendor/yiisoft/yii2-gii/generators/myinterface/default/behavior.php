<?php
/**
 * This is the template for generating a behavior class file.
 */

use yii\helpers\Inflector;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\controller\Generator */

echo "<?php\n";
?>
<?php if (!empty($generator->nb)): ?>
namespace <?= $generator->nb ?>;
<?php endif; ?>
class <?= $generator->getBehaviorClass() ?> extends BaseBehavior
{
	/**
	 * 获取待处理的逻辑模块的Class名称
	 * @see \console\behaviors\BaseBehavior::getLogicClass()
	 */
	public function getLogicClass()
	{
		return array(
            <?php foreach ($generator->getActionTableIDs() as $action): ?>
             '<?php echo $action; ?>';
            <?php endforeach; ?>
		);
	}
}
