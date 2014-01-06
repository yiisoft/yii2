<?php
use yii\helpers\Html;
use yii\grid\GridView;
?>
<h1>Email messages</h1>
<?php
echo GridView::widget([
	'dataProvider' => $dataProvider,
	'id' => 'db-panel-detailed-grid',
	'options' => ['class' => 'detail-grid-view'],
	'filterModel' => $searchModel,
	'filterUrl' => $panel->getUrl(),
	'columns' => [
		['class' => 'yii\grid\SerialColumn'],
		'from',
		'to',
		'replyTo',
		'cc',
		'bcc',
		'subject',
		[
			'format' => 'html',
			'value' => function($data)
				{
					$path = $data['file'];
					if (!empty($path) && is_file($path)) {
						$arr = explode(".", $path);
						$extension = end($arr);
						if ($extension == 'eml') {
							return Html::a('eml', ['download', 'path'=>$data['file']]);
						}
					}
					return '';
				}
		]
	],
]);
?>

