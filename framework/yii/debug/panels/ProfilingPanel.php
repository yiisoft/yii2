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

/**
 * Debugger panel that collects and displays performance profiling info.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ProfilingPanel extends Panel
{
	public function getName()
	{
		return 'Profiling';
	}

	public function getSummary()
	{
		$memory = sprintf('%.1f MB', $this->data['memory'] / 1048576);
		$time = number_format($this->data['time'] * 1000) . ' ms';
		$url = $this->getUrl();

		return <<<EOD
<div class="yii-debug-toolbar-block">
	<a href="$url" title="Total request processing time was $time">Time <span class="label">$time</span></a>
</div>
<div class="yii-debug-toolbar-block">
	<a href="$url" title="Peak memory consumption">Memory <span class="label">$memory</span></a>
</div>
EOD;
	}

	public function getDetail()
	{
		$messages = $this->data['messages'];
		$timings = [];
		$stack = [];
		foreach ($messages as $i => $log) {
			list($token, $level, $category, $timestamp, $traces) = $log;
			if ($level == Logger::LEVEL_PROFILE_BEGIN) {
				$stack[] = $log;
			} elseif ($level == Logger::LEVEL_PROFILE_END) {
				if (($last = array_pop($stack)) !== null && $last[0] === $token) {
					$timings[] = [count($stack), $token, $category, $timestamp - $last[3], $traces];
				}
			}
		}

		$now = microtime(true);
		while (($last = array_pop($stack)) !== null) {
			$timings[] = [count($stack), $last[0], $last[2], $now - $last[3], $last[4]];
		}

		$rows = [];
		foreach ($timings as $timing) {
			$time = sprintf('%.1f ms', $timing[3] * 1000);
			$procedure = str_repeat('<span class="indent">→</span>', $timing[0]) . Html::encode($timing[1]);
			$category = Html::encode($timing[2]);
			$rows[] = "<tr><td style=\"width: 80px;\">$time</td><td style=\"width: 220px;\">$category</td><td>$procedure</td>";
		}
		$rows = implode("\n", $rows);

		$memory = sprintf('%.1f MB', $this->data['memory'] / 1048576);
		$time = number_format($this->data['time'] * 1000) . ' ms';

		return <<<EOD
<h2>Performance Profiling</h2>

<p>Total processing time: <b>$time</b>; Peak memory: <b>$memory</b>.</p>

<table class="table table-condensed table-bordered table-striped table-hover" style="table-layout: fixed;">
<thead>
<tr>
	<th style="width: 80px;">Time</th>
	<th style="width: 220px;">Category</th>
	<th>Procedure</th>
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
		$messages = $target->filterMessages($target->messages, Logger::LEVEL_PROFILE);
		return [
			'memory' => memory_get_peak_usage(),
			'time' => microtime(true) - YII_BEGIN_TIME,
			'messages' => $messages,
		];
	}
}
