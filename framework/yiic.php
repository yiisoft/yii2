<?php
define('YII_DEBUG', true);
/**
 * Yii console bootstrap file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

// fcgi doesn't have STDIN defined by default
defined('STDIN') or define('STDIN', fopen('php://stdin', 'r'));

require(__DIR__ . '/yii.php');

$config = array(
	'controllerPath' => '@yii/console/controllers',
);
$id = 'yiic';
$basePath = __DIR__ . '/console';

$application = new yii\console\Application($id, $basePath, $config);
$application->run();
