<?php
/**
 * This is the template for generating a module class file.
 *
 * @var yii\web\View $this
 * @var yii\gii\generators\module\Generator $generator
 */
$className = $generator->moduleClass;
$pos = strrpos($className, '\\');
$ns = ltrim(substr($className, 0, $pos), '\\');
$className = substr($className, $pos + 1);

echo "<?php\n";
?>

namespace <?= $ns ?>;


class <?= $className ?> extends \yii\base\Module
{
	public $controllerNamespace = '<?= $generator->getControllerNamespace() ?>';

	public function init()
	{
		parent::init();

		// custom initialization code goes here
	}
}
