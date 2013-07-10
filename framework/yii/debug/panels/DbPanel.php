<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug\panels;

use yii\debug\Panel;
use yii\log\Logger;
use yii\helpers\Html;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DbPanel extends Panel
{
	public function getName()
	{
		return 'Database';
	}

	public function getSummary()
	{
		$timings = $this->calculateTimings();
		$queryCount = count($timings);
		$queryTime = 0;
		foreach ($timings as $timing) {
			$queryTime += $timing[3];
		}
		$queryTime = number_format($queryTime * 1000) . ' ms';
		$url = $this->getUrl();
		$output = <<<EOD
<div class="yii-debug-toolbar-block">
	<a href="$url">
		DB queries: <span class="label">$queryCount</span>
		time: <span class="label">$queryTime</span>
	</a>
</div>
EOD;
		return $queryCount > 0 ? $output : '';
	}

	public function getDetail()
	{
		$timings = $this->calculateTimings();
		$rows = array();
		foreach ($timings as $timing) {
			$duration = sprintf('%.1f ms', $timing[3] * 1000);
			$procedure = str_repeat('<span class="indent">→</span>', $timing[0]) . Html::encode($timing[1]);
			$rows[] = "<tr><td style=\"width: 80px;\">$duration</td><td>$procedure</td>";
		}
		$rows = implode("\n", $rows);

		return <<<EOD
<h1>Database Queries</h1>

<table class="table table-condensed table-bordered table-striped table-hover" style="table-layout: fixed;">
<thead>
<tr>
	<th style="width: 80px;">Time</th>
	<th>Query</th>
</tr>
</thead>
<tbody>
$rows
</tbody>
</table>
EOD;
	}

	private $_timings;

	protected function calculateTimings()
	{
		if ($this->_timings !== null) {
			return $this->_timings;
		}
		$messages = $this->data['messages'];
		$timings = array();
		$stack = array();
		foreach ($messages as $i => $log) {
			list($token, $level, $category, $timestamp) = $log;
			$log[4] = $i;
			if ($level == Logger::LEVEL_PROFILE_BEGIN) {
				$stack[] = $log;
			} elseif ($level == Logger::LEVEL_PROFILE_END) {
				if (($last = array_pop($stack)) !== null && $last[0] === $token) {
					$timings[$last[4]] = array(count($stack), $token, $last[3], $timestamp - $last[3]);
				}
			}
		}

		$now = microtime(true);
		while (($last = array_pop($stack)) !== null) {
			$delta = $now - $last[3];
			$timings[$last[4]] = array(count($stack), $last[0], $last[2], $delta);
		}
		ksort($timings);
		return $this->_timings = $timings;
	}

	public function save()
	{
		$target = $this->module->logTarget;
		$messages = $target->filterMessages($target->messages, Logger::LEVEL_PROFILE, array('yii\db\Command::queryInternal'));
		return array(
			'messages' => $messages,
		);
	}
}
