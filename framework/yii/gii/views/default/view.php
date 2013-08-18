<?php

use yii\gii\Generator;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\gii\components\ActiveField;
use yii\gii\CodeFile;

/**
 * @var yii\base\View $this
 * @var yii\gii\Generator $generator
 * @var yii\widgets\ActiveForm $form
 * @var string $results
 * @var boolean $hasError
 * @var CodeFile[] $files
 * @var array $answers
 */

$this->title = $generator->getName();
$templates = array();
foreach ($generator->templates as $name => $path) {
	$templates[$name] = "$name ($path)";
}
?>
<div class="default-view">
	<h1><?php echo Html::encode($this->title); ?></h1>

	<p><?php echo $generator->getDescription(); ?></p>

	<?php $form = ActiveForm::begin(array('fieldConfig' => array('class' => ActiveField::className()))); ?>
		<div class="row">
			<div class="col-lg-6">
				<?php echo $this->renderFile($generator->formView(), array(
					'generator' => $generator,
					'form' => $form,
				)); ?>
				<?php echo $form->field($generator, 'template')->sticky()
					->label(array('label' => 'Code Template'))
					->dropDownList($templates)->hint('
						Please select which set of the templates should be used to generated the code.
				'); ?>
				<div class="form-group">
					<?php echo Html::submitButton('Preview', array('name' => 'preview', 'class' => 'btn btn-success')); ?>

					<?php if(isset($files)): ?>
						<?php echo Html::submitButton('Generate', array('name' => 'generate', 'class' => 'btn btn-danger')); ?>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<?php
		if (isset($results)) {
			echo $this->render('view/results', array(
				'generator' => $generator,
				'results' => $results,
				'hasError' => $hasError,
			));
		} elseif (isset($files)) {
			echo $this->render('view/files', array(
				'generator' => $generator,
				'files' => $files,
				'answers' => $answers,
			));
		}
		?>
	<?php ActiveForm::end(); ?>
</div>
