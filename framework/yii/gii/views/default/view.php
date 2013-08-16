<?php

use yii\gii\Generator;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\gii\CodeFile;

/**
 * @var yii\base\View $this
 * @var yii\gii\Generator $generator
 * @var yii\widgets\ActiveForm $form
 * @var string $result
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

	<?php $form = ActiveForm::begin(array('fieldConfig' => array('class' => 'yii\gii\ActiveField'))); ?>
	<div class="row">
		<div class="col-lg-6">
			<?php echo $this->renderFile($generator->formView(), array(
				'generator' => $generator,
				'form' => $form,
			)); ?>
			<?php echo $form->field($generator, 'template')->label(array('label' => 'Code Template'))->dropDownList($templates)->hint('
				Please select which set of the templates should be used to generated the code.
			'); ?>
			<div class="form-group">
				<?php echo Html::submitButton('Preview', array('name' => 'preview', 'class' => 'btn btn-primary')); ?>

				<?php if(isset($files)): ?>
					<?php echo Html::submitButton('Generate', array('name' => 'generate', 'class' => 'btn btn-danger')); ?>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<?php
	if (isset($result)) {
		echo '<div class="result">' . $result . '</div>';
	} elseif (isset($files)) {
		echo $this->render('_files', array(
			'generator' => $generator,
			'files' => $files,
			'answers' => $answers,
		));
	}
	?>

	<?php ActiveForm::end(); ?>
</div>
