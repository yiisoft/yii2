<?php
/** @var $controller \yii\console\controllers\CreateController */
$controller = $this;

return array(
	'default' => array(
		'index.php' => array(
			'handler' => function($source) use ($controller) {
				return $controller->replaceRelativePath($source, realpath(YII_PATH.'/yii.php'), 'yii');
			},
			'permissions' => 0777,
		),
		'protected/runtime' => array(
			'permissions' => 0755,
		),
	),
);