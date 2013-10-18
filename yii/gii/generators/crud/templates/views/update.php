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
 * @var <?=ltrim($generator->modelClass, '\\'); ?> $model
 */

$this->title = 'Update <?=Inflector::camel2words(StringHelper::basename($generator->modelClass)); ?>: ' . $model-><?=$generator->getNameAttribute(); ?>;
$this->params['breadcrumbs'][] = array('label' => '<?=Inflector::pluralize(Inflector::camel2words(StringHelper::basename($generator->modelClass))); ?>', 'url' => array('index'));
$this->params['breadcrumbs'][] = array('label' => $model-><?=$generator->getNameAttribute(); ?>, 'url' => array('view', <?=$urlParams; ?>));
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="<?=Inflector::camel2id(StringHelper::basename($generator->modelClass)); ?>-update">

	<h1><?="<?php"; ?> echo Html::encode($this->title); ?></h1>

	<?="<?php"; ?> echo $this->render('_form', array(
		'model' => $model,
	)); ?>

</div>
