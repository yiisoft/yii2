<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\captcha\Captcha;

/**
 * @var yii\base\View $this
 * @var yii\widgets\ActiveForm $form
 * @var app\models\ContactForm $model
 */
$this->title = 'Contact';
$this->params['breadcrumbs'][] = $this->title;
?>
<h1><?php echo Html::encode($this->title); ?></h1>

<p>
	If you have business inquiries or other questions, please fill out the following form to contact us. Thank you.
</p>

<?php $form = ActiveForm::begin(array(
	'options' => array('class' => 'form-horizontal'),
	'fieldConfig' => array('inputOptions' => array('class' => 'input-xlarge')),
)); ?>
	<?php echo $form->field($model, 'name')->textInput(); ?>
	<?php echo $form->field($model, 'email')->textInput(); ?>
	<?php echo $form->field($model, 'subject')->textInput(); ?>
	<?php echo $form->field($model, 'body')->textArea(array('rows' => 6)); ?>
	<?php echo $form->field($model, 'verifyCode')->widget(Captcha::className(), array(
		'options' => array('class' => 'input-medium'),
	)); ?>
	<div class="form-actions">
		<?php echo Html::submitButton('Submit', array('class' => 'btn btn-primary')); ?>
	</div>
<?php ActiveForm::end(); ?>
