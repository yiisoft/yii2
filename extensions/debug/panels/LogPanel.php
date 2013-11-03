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
 * Debugger panel that collects and displays logs.
 *
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
		$output = ['<span class="label">' . count($this->data['messages']) . '</span>'];
		$title = 'Logged ' . count($this->data['messages']) . ' messages';
		$errorCount = count(Target::filterMessages($this->data['messages'], Logger::LEVEL_ERROR));
		if ($errorCount) {
			$output[] = '<span class="label label-important">' . $errorCount . '</span>';
			$title .= ", $errorCount errors";
		}
		$warningCount = count(Target::filterMessages($this->data['messages'], Logger::LEVEL_WARNING));
		if ($warningCount) {
			$output[] = '<span class="label label-warning">' . $warningCount . '</span>';
			$title .= ", $warningCount warnings";
		}
		$log = implode('&nbsp;', $output);
		$url = $this->getUrl();
		return <<<EOD
<div class="yii-debug-toolbar-block">
	<a href="$url" title="$title">Log $log</a>
</div>
EOD;
	}

	public function getDetail()
	{
		$rows = [];
		foreach ($this->data['messages'] as $log) {
			list ($message, $level, $category, $time, $traces) = $log;
			$time = date('H:i:s.', $time) . sprintf('%03d', (int)(($time - (int)$time) * 1000));
			$message = nl2br(Html::encode($message));
			if (!empty($traces)) {
				$message .= Html::ul($traces, [
					'class' => 'trace',
					'item' => function ($trace) {
						return "<li>{$trace['file']}({$trace['line']})</li>";
					},
				]);
			}
			if ($level == Logger::LEVEL_ERROR) {
				$class = ' class="danger"';
			} elseif ($level == Logger::LEVEL_WARNING) {
				$class = ' class="warning"';
			} elseif ($level == Logger::LEVEL_INFO) {
				$class = ' class="success"';
			} else {
				$class = '';
			}
			$level = Logger::getLevelName($level);
			$rows[] = "<tr$class><td style=\"width: 100px;\">$time</td><td style=\"width: 100px;\">$level</td><td style=\"width: 250px;\">$category</td><td><div>$message</div></td></tr>";
		}
		$rows = implode("\n", $rows);
		return <<<EOD
<h1>Log Messages</h1>

<table class="table table-condensed table-bordered table-striped table-hover" style="table-layout: fixed;">
<thead>
<tr>
	<th style="width: 100px;">Time</th>
	<th style="width: 65px;">Level</th>
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
		return ['messages' => $messages];
	}
}
