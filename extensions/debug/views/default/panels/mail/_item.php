<?php
use yii\helpers\Html;
use yii\widgets\DetailView;

$path = $model['file'];
if (!empty($path) && is_file($path)) {
	$arr = explode(".", $path);
	$extension = end($arr);
	if ($extension == 'eml') {
		$model['file'] = Html::a('Download eml', ['download', 'path'=>$model['file']]);
	}
} else {
	$model['file'] = null;
}

echo Html::tag('b', '#'.++$index);
echo DetailView::widget([
		'model' => $model,
		'attributes' => [
			[
				'name' => 'headers',
				'format' => 'html',
				'body' => '<pre>' . $model['headers'] . '</pre>'
			],
			[
				'label' => 'Text body',
				'name' => 'body',
			],
			[
				'name' => 'isSuccessful',
				'value' => $model['isSuccessful'] ? 'true' : 'false'
			],
			[
				'name' => 'file',
				'format' => 'html',
				'visible' => !empty($model['file'])
			],
		],
]);