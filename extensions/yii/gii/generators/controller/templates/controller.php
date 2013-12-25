<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * This is the template for generating a controller class file.
 *
 * @var yii\web\View $this
 * @var yii\gii\generators\controller\Generator $generator
 */

echo "<?php\n";
?>

<?php if (!empty($generator->ns)): ?>
namespace <?= $generator->ns ?>;
<?php endif; ?>

use <?= $generator->baseClass ?>;

class <?= $generator->getControllerClass() ?> extends <?= StringHelper::basename($generator->baseClass) . "\n" ?>
{
<?php foreach($generator->getActionIDs() as $action): ?>
	public function action<?= Inflector::id2camel($action) ?>()
	{
		return $this->render('<?= $action ?>');
	}

<?php endforeach; ?>
}
