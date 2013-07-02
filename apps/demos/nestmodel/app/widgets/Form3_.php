<?php
/** @var \yii\Controller $controller */
/** @var \app\widgets\models\Form2Model $model*/
use yii\widgets\ActiveForm;
use yii\helpers\Html;


foreach ( $model->ranges as $mm ) {
  $form = ActiveForm::begin(array('options' => array('class' => 'form-horizontal', 'style' => 'border:1px solid #000; padding: 10px;')));
  $ww = new \app\widgets\DateRangePicker();
  $ww->attach($mm);
  $ww->run();
  echo '<br/>';
}
?>
<?php echo Html::submitButton('Select', null, null, array('class' => 'btn btn-primary')); ?>
You have selected : <br/>
<?php
  $ss = ''; $ii =0 ;
  foreach ($model->ranges as $range) {
    $ii++;
    echo sprintf("%d : %s<br/>",$ii, $range->format());
  }
?>
<?php ActiveForm::end(); ?>
