<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\widgets\DateRangePicker;
use app\widgets\Form1;
/**
 * @var yii\base\View $this
 * @var app\widgets\models\Form1Model $form1
 */
$this->title = 'Demo01';
$this->params['breadcrumbs'][] = $this->title;
?>
<h1><?php echo Html::encode($this->title); ?></h1>


<?php
$f1 = new Form1($form1);
$f1->run();
?>


