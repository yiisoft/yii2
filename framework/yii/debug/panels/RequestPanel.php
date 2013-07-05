<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug\panels;

use yii\debug\Panel;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class RequestPanel extends Panel
{
	public function getName()
	{
		return 'Request';
	}

	public function getSummary()
	{
		$memory = sprintf('%.2fMB', $this->data['memory'] / 1048576);
		$time = sprintf('%.3fs', $this->data['time']);

		return <<<EOD
<div class="yii-debug-toolbar-block">
Peak memory: $memory
</div>

<div class="yii-debug-toolbar-block">
	Time spent: $time
</div>
EOD;
	}

	public function getDetail()
	{
		return '<h2>Request</h2>';
	}

	public function save()
	{
		return array(
			'memory' => memory_get_peak_usage(),
			'time' => microtime(true) - YII_BEGIN_TIME,
			'SERVER' => $_SERVER,
			'GET' => $_GET,
			'POST' => $_POST,
			'COOKIE' => $_COOKIE,
			'FILES' => empty($_FILES) ? array() : $_FILES,
			'SESSION' => empty($_SESSION) ? array() : $_SESSION,
		);
	}
}
