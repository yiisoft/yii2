<?php
/**
 * @var yii\base\View $this
 * @var yii\widgets\ActiveForm $form
 * @var yii\gii\generators\module\Generator $generator
 */
?>
<div class="module-form">
<?php
	echo $form->field($generator, 'moduleClass');
	echo $form->field($generator, 'moduleID');
?>
</div>
