<?php
use app\widgets\Form1;
/**
 * @var yii\base\View $this
 * @var app\widgets\models\Form2Model $form1
 * @var app\widgets\models\Form2Model $form2
 * @var app\widgets\models\Form2Model $form3
 */
$this->title = 'Demo02';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php
(new Form1($form1))->run();
(new Form1($form2))->run();
(new Form1($form3))->run();
