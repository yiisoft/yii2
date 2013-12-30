<?php 
use yii\grid\GridView;
?>
<h1>Performance Profiling</h1>
<p>Total processing time: <b><?php echo $time; ?></b>; Peak memory: <b><?php echo $memory; ?></b>.</p>
<?php
echo GridView::widget([
	'dataProvider' => $dataProvider,
	'id' => 'profile-panel-detailed-grid',
	'filterModel' => $searchModel,
	'filterUrl' => $panel->getUrl(),
	'columns' => [
		['class' => 'yii\grid\SerialColumn'],
		[
			'attribute' => 'duration',
			'value' => function ($data)
			{
				return sprintf('%.1f ms',$data['duration']);
			},
		],
		'category',
		[
			'attribute' => 'info',
			'value' => function ($data)
			{
				return str_repeat('<span class="indent">→</span>', $data['level']) . $data['info'];
			},
			'options' => [
				'width' => '60%',
			],
		],
	],
]);
?>