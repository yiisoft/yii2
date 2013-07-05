<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug\panels;

use yii\debug\Panel;
use yii\helpers\Html;

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
		return "<h3>\$_GET</h3>\n" . $this->renderTable($this->data['GET']) . "\n"
			. "<h3>\$_POST</h3>\n" . $this->renderTable($this->data['POST']) . "\n"
			. "<h3>\$_COOKIE</h3>\n" . $this->renderTable($this->data['COOKIE']) . "\n"
			. "<h3>\$_FILES</h3>\n" . $this->renderTable($this->data['FILES']) . "\n"
			. "<h3>\$_SESSION</h3>\n" . $this->renderTable($this->data['SESSION']) . "\n"
			. "<h3>\$_SERVER</h3>\n" . $this->renderTable($this->data['SERVER']);
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

	protected function renderTable($values)
	{
		$rows = array();
		foreach ($values as $name => $value) {
			$rows[] = '<tr><th>' . Html::encode($name) . '</th><td>' . Html::encode(var_export($value, true)) . '</td></tr>';
		}
		if (!empty($rows)) {
			return "<table class=\"table table-condensed table-bordered table-hover\">\n<thead>\n<tr><th>Name</th><th>Value</th></tr>\n</thead>\n<tbody>\n" . implode("\n", $rows) . "\n</tbody>\n</table>";
		} else {
			return 'Empty.';
		}
	}
}
