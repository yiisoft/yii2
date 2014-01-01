<?php 
use yii\bootstrap\Tabs;

echo Tabs::widget([
	'items' => [
		[
			'label' => 'Parameters',
			'content' =>  $this->context->renderPartial('panels/request/_data_table', ['caption' => 'Routing', 'values' => $data])
						. $this->context->renderPartial('panels/request/_data_table', ['caption' => '$_GET', 'values' => $panel->data['GET']])
						. $this->context->renderPartial('panels/request/_data_table', ['caption' => '$_POST', 'values' => $panel->data['POST']])
						. $this->context->renderPartial('panels/request/_data_table', ['caption' => '$_FILES', 'values' => $panel->data['FILES']])
						. $this->context->renderPartial('panels/request/_data_table', ['caption' => '$_COOKIE', 'values' => $panel->data['COOKIE']]),
			'active' => true,
		],
		[
			'label' => 'Headers',
			'content' =>  $this->context->renderPartial('panels/request/_data_table', ['caption' => 'Request Headers', 'values' => $panel->data['requestHeaders']])
						. $this->context->renderPartial('panels/request/_data_table', ['caption' => 'Response Headers', 'values' => $panel->data['responseHeaders']])
		],
		[
			'label' => 'Session',
			'content' =>  $this->context->renderPartial('panels/request/_data_table', ['caption' => '$_SESSION', 'values' => $panel->data['SESSION']])
						. $this->context->renderPartial('panels/request/_data_table', ['caption' => 'Flashes', 'values' => $panel->data['flashes']])
		],
		[
			'label' => '$_SERVER',
			'content' => $this->context->renderPartial('panels/request/_data_table', ['caption' => '$_SERVER', 'values' => $panel->data['SERVER']]),
		],
	],
]);
?>