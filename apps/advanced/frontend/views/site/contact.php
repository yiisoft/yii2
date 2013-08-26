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
<div class="site-contact">
	<h1><?php echo Html::encode($this->title); ?></h1>

	<p>
		If you have business inquiries or other questions, please fill out the following form to contact us. Thank you.
	</p>

	<div class="row">
		<div class="col-lg-5">
			<?php $form = ActiveForm::begin(array('id' => 'contact-form')); ?>
				<?php echo $form->field($model, 'name'); ?>
				<?php echo $form->field($model, 'email'); ?>
				<?php echo $form->field($model, 'subject'); ?>
				<?php echo $form->field($model, 'body')->textArea(array('rows' => 6)); ?>
				<?php echo $form->field($model, 'verifyCode')->widget(Captcha::className(), array(
					'options' => array('class' => 'form-control'),
					'template' => '<div class="row"><div class="col-lg-3">{image}</div><div class="col-lg-6">{input}</div></div>',
				)); ?>
				<div class="form-group">
					<?php echo Html::submitButton('Submit', array('class' => 'btn btn-primary')); ?>
				</div>
			<?php ActiveForm::end(); ?>
		</div>
	</div>

</div>
