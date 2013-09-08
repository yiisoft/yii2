<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * @var yii\base\View $this
 * @var yii\gii\generators\crud\Generator $generator
 */

echo "<?php\n";
?>

use yii\helpers\Html;

/**
 * @var yii\base\View $this
 * @var <?php echo ltrim($generator->modelClass, '\\'); ?> $model
 */

$this->title = 'Update <?php echo Inflector::camel2words(StringHelper::basename($generator->modelClass)); ?>: ' . $model-><?php echo $generator->getNameAttribute(); ?>;
?>
<div class="<?php echo Inflector::camel2id(StringHelper::basename($generator->modelClass)); ?>-update">

	<h1><?php echo "<?php"; ?> echo Html::encode($this->title); ?></h1>

	<?php echo "<?php"; ?> echo $this->render('_form', array(
		'model' => $model,
	)); ?>

</div>
