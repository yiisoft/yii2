<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * @var yii\base\View $this
 * @var yii\gii\generators\crud\Generator $generator
 */

$urlParams = $generator->generateUrlParams();

echo "<?php\n";
?>

use yii\helpers\Html;

/**
 * @var yii\base\View $this
 * @var <?= ltrim($generator->modelClass, '\\') ?> $model
 */

$this->title = 'Update <?= Inflector::camel2words(StringHelper::basename($generator->modelClass)) ?>: ' . $model-><?= $generator->getNameAttribute() ?>;
$this->params['breadcrumbs'][] = ['label' => '<?= Inflector::pluralize(Inflector::camel2words(StringHelper::basename($generator->modelClass))) ?>', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model-><?= $generator->getNameAttribute() ?>, 'url' => ['view', <?= $urlParams ?>]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-update">

	<h1><?= "<?= " ?>Html::encode($this->title) ?></h1>

	<?= "<?php " ?>echo $this->render('_form', [
		'model' => $model,
	]); ?>

</div>
