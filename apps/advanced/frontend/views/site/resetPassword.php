<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\base\View $this
 * @var yii\widgets\ActiveForm $form
 * @var common\models\User $model
 */
$this->title = 'Reset password';
$this->params['breadcrumbs'][] = $this->title;
?>
<h1><?php echo Html::encode($this->title); ?></h1>

<p>Please choose your new password:</p>

<?php $form = ActiveForm::begin(array('options' => array('class' => 'form-horizontal'))); ?>
	<?php echo $form->field($model, 'password')->passwordInput(); ?>
	<div class="form-actions">
		<?php echo Html::submitButton('Save', array('class' => 'btn btn-primary')); ?>
	</div>
<?php ActiveForm::end(); ?>
