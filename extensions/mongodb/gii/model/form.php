<?php
/**
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var yii\mongodb\gii\model\Generator $generator
 */

echo $form->field($generator, 'collectionName');
echo $form->field($generator, 'databaseName');
echo $form->field($generator, 'attributeList');
echo $form->field($generator, 'modelClass');
echo $form->field($generator, 'ns');
echo $form->field($generator, 'baseClass');
echo $form->field($generator, 'db');
echo $form->field($generator, 'enableI18N')->checkbox();
echo $form->field($generator, 'messageCategory');
