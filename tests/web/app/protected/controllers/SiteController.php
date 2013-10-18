<?php

use yii\helpers\Html;

class DefaultController extends \yii\web\Controller
{
	public function actionIndex()
	{
		echo 'hello world';
	}
	
	public function actionForm()
	{
		echo Html::beginForm();
		echo Html::checkboxList('test', [
			'value 1' => 'item 1',
			'value 2' => 'item 2',
			'value 3' => 'item 3',
		], isset($_POST['test']) ? $_POST['test'] : null,
		function ($index, $label, $name, $value, $checked) {
			return Html::label(
				$label . ' ' . Html::checkbox($name, $value, $checked),
				null, ['class' => 'inline checkbox']
			);
		});
		echo Html::submitButton();
		echo Html::endForm();
		print_r($_POST);
	}
}
