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

$this->title = 'Create <?php echo Inflector::camel2words(StringHelper::basename($generator->modelClass)); ?>';
?>
<div class="<?php echo Inflector::camel2id(StringHelper::basename($generator->modelClass)); ?>-create">

	<h1><?php echo "<?php"; ?> echo Html::encode($this->title); ?></h1>

	<?php echo "<?php"; ?> echo $this->render('_form', array(
		'model' => $model,
	)); ?>

</div>
