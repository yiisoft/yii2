<?php
/**
 * @var yii\base\View $this
 * @var yii\widgets\ActiveForm $form
 * @var yii\gii\generators\crud\Generator $generator
 */

echo $form->field($generator, 'modelClass');
echo $form->field($generator, 'controllerID');
echo $form->field($generator, 'baseControllerClass');
echo $form->field($generator, 'indexWidgetType')->dropDownList(array(
	'grid' => 'GridView',
	'list' => 'ListView',
));
echo $form->field($generator, 'enableSearch')->checkbox();
echo $form->field($generator, 'searchModelClass');
