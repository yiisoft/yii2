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
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ProfilingPanel extends Panel
{
	public function getName()
	{
		return 'Profiling';
	}

	public function getDetail()
	{
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
					$timings[$last[4]] = array(count($stack), $token, $category, $timestamp - $last[3]);
				}
			}
		}

		$now = microtime(true);
		while (($last = array_pop($stack)) !== null) {
			$delta = $now - $last[3];
			$timings[$last[4]] = array(count($stack), $last[0], $last[2], $delta);
		}
		ksort($timings);

		$rows = array();
		foreach ($timings as $timing) {
			$time = sprintf('%.1f ms', $timing[3] * 1000);
			$procedure = str_repeat('<span class="indent">→</span>', $timing[0]) . Html::encode($timing[1]);
			$category = Html::encode($timing[2]);
			$rows[] = "<tr><td style=\"width: 80px;\">$time</td><td style=\"width: 220px;\">$category</td><td>$procedure</td>";
		}
		$rows = implode("\n", $rows);

		return <<<EOD
<h1>Performance Profiling</h1>

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
		return array(
			'messages' => $messages,
		);
	}
}
