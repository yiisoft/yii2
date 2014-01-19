<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\data\ArrayDataProvider;
use yii\log\Logger;
?>
<h1>Log Messages</h1>
<?php

echo GridView::widget([
	'dataProvider' => $dataProvider,
	'id' => 'log-panel-detailed-grid',
	'options' => ['class' => 'detail-grid-view'],
	'filterModel' => $searchModel,
	'filterUrl' => $panel->getUrl(),
	'rowOptions' => function ($model, $key, $index, $grid) {
		switch($model['level']) {
			case Logger::LEVEL_ERROR : return ['class' => 'danger'];
			case Logger::LEVEL_WARNING : return ['class' => 'warning'];
			case Logger::LEVEL_INFO : return ['class' => 'success'];
			default: return [];
		}
	},
	'columns' => [
		['class' => 'yii\grid\SerialColumn'],
		[
			'attribute' => 'time',
			'value' => function ($data) {
				$timeInSeconds = $data['time'] / 1000;
				$millisecondsDiff = (int)(($timeInSeconds - (int)$timeInSeconds) * 1000);
				return date('H:i:s.', $timeInSeconds) . sprintf('%03d', $millisecondsDiff);
			},
			'headerOptions' => [
				'class' => 'sort-numerical'
			]
		],
		[
			'attribute' => 'level',
			'value' => function ($data) {
				return Logger::getLevelName($data['level']);
			},
			'filter' => [
				Logger::LEVEL_TRACE => ' Trace ',
				Logger::LEVEL_INFO => ' Info ',
				Logger::LEVEL_WARNING => ' Warning ',
				Logger::LEVEL_ERROR => ' Error ',
			],
		],
		'category',
		[
			'attribute' => 'message',
			'value' => function ($data) {
				$message = nl2br(Html::encode($data['message']));

				if (!empty($data['trace'])) {
					$message .= Html::ul($data['trace'], [
						'class' => 'trace',
						'item' => function ($trace) {
							return "<li>{$trace['file']} ({$trace['line']})</li>";
						}
					]);
				};

				return $message;
			},
			'format' => 'html',
			'options' => [
				'width' => '50%',
			],
		],
	],
]);
?>
