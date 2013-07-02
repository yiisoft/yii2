<?php
/** @var \yii\Controller $controller */
/** @var \app\widgets\models\Form1Model $model*/
use yii\widgets\ActiveForm;
use yii\helpers\Html;

$form = ActiveForm::begin(array('options' => array('class' => 'form-horizontal', 'style' => 'border:1px solid #000; padding: 10px;')));
$ww = new \app\widgets\DatePicker();
$ww->attach($model->date);
$ww->run();
?>
<?php echo Html::submitButton('Select', null, null, array('class' => 'btn btn-primary')); ?>
&nbsp; &nbsp; You have selected : <?php echo $model->date->format() ?>
<?php ActiveForm::end(); ?>
