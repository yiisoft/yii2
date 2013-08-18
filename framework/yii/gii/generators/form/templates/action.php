<?php

use yii\helpers\Inflector;

/**
 * This is the template for generating an action view file.
 *
 * @var yii\base\View $this
 * @var yii\gii\generators\form\Generator $generator
 */

echo "<?php\n";
?>

public function action<?php echo Inflector::id2camel(trim(basename($generator->viewName), '_')); ?>()
{
	$model = new <?php echo $generator->modelClass; ?><?php echo empty($generator->scenarioName) ? '' : "(array('scenario' => '{$generator->scenarioName}'))"; ?>;

	if ($model->load($_POST)) {
		if($model->validate()) {
			// form inputs are valid, do something here
			return;
		}
	}
	return $this->render('<?php echo $generator->viewName; ?>', array(
		'model' => $model,
	));
}
