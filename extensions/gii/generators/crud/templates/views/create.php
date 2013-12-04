<?php

use yii\helpers\FileHelper;
use yii\helpers\Inflector;

/**
 * @var yii\web\View $this
 * @var yii\gii\generators\crud\Generator $generator
 */

echo "<?php\n";
?>

use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var <?= ltrim($generator->modelClass, '\\') ?> $model
 */

$this->title = 'Create <?= Inflector::camel2words(FileHelper::basename($generator->modelClass)) ?>';
$this->params['breadcrumbs'][] = ['label' => '<?= Inflector::pluralize(Inflector::camel2words(BaseFileHelper::basename($generator->modelClass))) ?>', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="<?= Inflector::camel2id(FileHelper::basename($generator->modelClass)) ?>-create">

	<h1><?= "<?= " ?>Html::encode($this->title) ?></h1>

	<?= "<?php " ?>echo $this->render('_form', [
		'model' => $model,
	]); ?>

</div>
