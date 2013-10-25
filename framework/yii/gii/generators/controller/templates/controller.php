<?php

use yii\helpers\Inflector;

/**
 * This is the template for generating a controller class file.
 *
 * @var yii\base\View $this
 * @var yii\gii\generators\controller\Generator $generator
 */

echo "<?php\n";
?>

<?php if (!empty($generator->ns)): ?>
namespace <?= $generator->ns ?>;
<?php endif; ?>

class <?= $generator->getControllerClass() ?> extends <?= '\\' . trim($generator->baseClass, '\\') . "\n" ?>
{
<?php foreach($generator->getActionIDs() as $action): ?>
	public function action<?= Inflector::id2camel($action) ?>()
	{
		return $this->render('<?= $action ?>');
	}

<?php endforeach; ?>
}
