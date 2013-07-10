<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug\panels;

use Yii;
use yii\debug\Panel;
use yii\helpers\Html;
use yii\log\Logger;
use yii\log\Target;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class LogPanel extends Panel
{
	public function getName()
	{
		return 'Logs';
	}

	public function getSummary()
	{
		$output = array();
		$errorCount = count(Target::filterMessages($this->data['messages'], Logger::LEVEL_ERROR));
		if ($errorCount) {
			$output[] = '<span class="label label-important">' . $errorCount . '</span> ' . ($errorCount > 1 ? 'errors' : 'error');
		}
		$warningCount = count(Target::filterMessages($this->data['messages'], Logger::LEVEL_WARNING));
		if ($warningCount) {
			$output[] = '<span class="label label-warning">' . $warningCount . '</span> ' . ($warningCount > 1 ? 'warnings' : 'warning');
		}
		if (!empty($output)) {
			$log = implode(', ', $output);
			$url = $this->getUrl();
			return <<<EOD
<div class="yii-debug-toolbar-block">
	<a href="$url">$log</a>
</div>
EOD;
		} else {
			return '';
		}
	}

	public function getDetail()
	{
		$rows = array();
		foreach ($this->data['messages'] as $log) {
			list ($message, $level, $category, $time, $traces) = $log;
			$time = date('H:i:s.', $time) . sprintf('%03d', (int)(($time - (int)$time) * 1000));
			$message = Html::encode($message);
			if (!empty($traces)) {
				$message .= Html::ul($traces, array(
					'class' => 'trace',
					'item' => function ($trace) {
						return "<li>{$trace['file']}({$trace['line']})</li>";
					},
				));
			}
			if ($level == Logger::LEVEL_ERROR) {
				$class = ' class="error"';
			} elseif ($level == Logger::LEVEL_WARNING) {
				$class = ' class="warning"';
			} elseif ($level == Logger::LEVEL_INFO) {
				$class = ' class="info"';
			} else {
				$class = '';
			}
			$level = Logger::getLevelName($level);
			$rows[] = "<tr$class><td style=\"width: 100px;\">$time</td><td style=\"width: 100px;\">$level</td><td style=\"width: 250px;\">$category</td><td>$message</td></tr>";
		}
		$rows = implode("\n", $rows);
		return <<<EOD
<h1>Log Messages</h1>

<table class="table table-condensed table-bordered table-striped table-hover" style="table-layout: fixed;">
<thead>
<tr>
	<th style="width: 100px;">Time</th>
	<th style="width: 100px;">Level</th>
	<th style="width: 250px;">Category</th>
	<th>Message</th>
</tr>
</thead>
<tbody>
$rows
</tbody>
</table>
EOD;
	}

	public function save()
	{
		$target = $this->module->logTarget;
		$messages = $target->filterMessages($target->messages, Logger::LEVEL_ERROR | Logger::LEVEL_INFO | Logger::LEVEL_WARNING | Logger::LEVEL_TRACE);
		return array(
			'messages' => $messages,
		);
	}
}
