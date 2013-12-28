<?php

use Yii;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\data\ArrayDataProvider;

/**
 * @var \yii\web\View $this
 * @var array $manifest
 */

$this->title = 'Yii Debugger';
?>
<div class="default-index">
	<div id="yii-debug-toolbar">
		<div class="yii-debug-toolbar-block title">
			Yii Debugger
		</div>
	</div>

	<div class="container">
		<div class="row">
			<h1>Available Debug Data</h1>

<?php

$timeFormatter = extension_loaded('intl') ? Yii::createObject(['class' => 'yii\i18n\Formatter']) : Yii::$app->formatter;

echo GridView::widget([
	'dataProvider' => $dataProvider,
	'filterModel' => $searchModel,
	'rowOptions' => function ($model, $key, $index, $grid) use ($searchModel)
	{
		if ($searchModel->isCodeCritical($model['statusCode']))
			return ['class'=>'danger'];
	},
	'columns' => [
		['class' => 'yii\grid\SerialColumn'],
		[
			'attribute' => 'tag',
			'value' => function ($data)
			{
				return Html::a($data['tag'], ['view', 'tag' => $data['tag']]);
			},
			'format' => 'html',
		],
		[
			'attribute' => 'time',
			'value' => function ($data) use ($timeFormatter)
			{
				return $timeFormatter->asDateTime($data['time'], 'long');
			},
		],
		'ip',
		[
			'attribute' => 'sqlCount',
			'label' => 'Total queries count'
		],
		[
			'attribute' => 'method',
			'filter' => ['get' => 'GET', 'post' => 'POST', 'delete' => 'DELETE', 'put' => 'PUT', 'head' => 'HEAD']
		],
		[
			'attribute'=>'ajax',
			'value' => function ($data)
			{
				return $data['ajax'] ? 'Yes' : 'No';
			},
			'filter' => ['No', 'Yes'],
		],
		'url',
		[
			'attribute' => 'statusCode',
			'filter' => [200=>200, 404=>404, 403=>403, 500=>500],
			'label' => 'Status code'
		],
	],
]); ?>
		</div>
	</div>
</div>
