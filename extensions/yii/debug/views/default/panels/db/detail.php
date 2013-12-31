<?php
use yii\helpers\Html;
use yii\grid\GridView;
?>
<h1>Database Queries</h1>

<?php

echo GridView::widget([
	'dataProvider' => $dataProvider,
	'id' => 'db-panel-detailed-grid',
	'filterModel' => $searchModel,
	'filterUrl' => $panel->getUrl(),
	'columns' => [
		['class' => 'yii\grid\SerialColumn'],
		[
			'attribute' => 'duration',
			'value' => function ($data) {
				return sprintf('%.1f ms',$data['duration']);
			},
		],
		[
			'attribute' => 'type',
			'value' => function ($data) {
				return Html::encode(mb_strtoupper($data['type'],'utf8'));
			},
		],
		[
			'attribute' => 'query',
			'value' => function ($data) {
				$query = Html::encode($data['query']);

				if (!empty($data['trace'])) {
					$query .= Html::ul($data['trace'], [
						'class' => 'trace',
						'item' => function ($trace) {
							return "<li>{$trace['file']} ({$trace['line']})</li>";
						},
					]);
				}
				return $query;
			},
			'format' => 'html',
			'options' => [
				'width' => '70%',
			],
		]
	],
]);
?>