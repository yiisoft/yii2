<?php
use yii\helpers\Html;
/**
 * @var yii\base\View $this
 * @var yii\widgets\ActiveForm $form
 * @var app\models\ContactForm $model
 */
$this->title = 'Contact';
?>
<h1><?php echo Html::encode($this->title); ?></h1>

<p>Please fill out the following fields:</p>

<?php $form = $this->beginWidget('yii\widgets\ActiveForm', array(
	'options' => array('class' => 'form-horizontal'),
	'fieldConfig' => array('inputOptions' => array('class' => 'input-xlarge')),
)); ?>
	<?php echo $form->field($model, 'name')->textInput(); ?>
	<?php echo $form->field($model, 'email')->textInput(); ?>
	<?php echo $form->field($model, 'subject')->textInput(); ?>
	<?php echo $form->field($model, 'body')->textArea(array('rows' => 6)); ?>
	<div class="control-group">
		<div class="controls">
			<?php echo Html::submitButton('Submit', null, null, array('class' => 'btn btn-primary')); ?>
		</div>
	</div>
<?php $this->endWidget(); ?>