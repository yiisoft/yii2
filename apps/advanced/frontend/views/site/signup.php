<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\base\View $this
 * @var yii\widgets\ActiveForm $form
 * @var common\models\User $model
 */
$this->title = 'Signup';
$this->params['breadcrumbs'][] = $this->title;
?>
<h1><?php echo Html::encode($this->title); ?></h1>

<p>Please fill out the following fields to signup:</p>

<?php $form = ActiveForm::begin(array('options' => array('class' => 'form-horizontal'))); ?>
	<?php echo $form->field($model, 'username')->textInput(); ?>
	<?php echo $form->field($model, 'email')->checkbox(); ?>
	<?php echo $form->field($model, 'password')->textInput(); ?>
	<div class="form-actions">
		<?php echo Html::submitButton('Signup', array('class' => 'btn btn-primary')); ?>
	</div>
<?php ActiveForm::end(); ?>
