<?php

use yii\helpers\Inflector;

/**
 * This is the template for generating a controller class file.
 *
 * @var yii\base\View $this
 * @var yii\gii\generators\controller\Generator $generator
 */
?>
<?php echo "<?php\n"; ?>

<?php if (!empty($generator->ns)): ?>
namespace <?php echo $generator->ns; ?>;
<?php endif; ?>

class <?php echo $generator->getControllerClass(); ?> extends <?php echo '\\' . trim($generator->baseClass, '\\') . "\n"; ?>
{
<?php foreach($generator->getActionIDs() as $action): ?>
	public function action<?php echo Inflector::id2camel($action); ?>()
	{
		return $this->render('<?php echo $action; ?>');
	}

<?php endforeach; ?>
}
