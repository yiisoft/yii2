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
class LogPanel extends Panel
{
	public function getName()
	{
		return 'Logs';
	}

	public function getSummary()
	{
		$count = count($this->data['messages']);
		return <<<EOD
<div class="yii-debug-toolbar-block">
Log messages: $count
</div>
EOD;
	}

	public function getDetail()
	{
		$rows = array();
		foreach ($this->data['messages'] as $log) {
			$time = date('H:i:s.', $log[3]) . sprintf('%03d', (int)(($log[3] - (int)$log[3]) * 1000));
			$level = Logger::getLevelName($log[1]);
			$message = Html::encode(wordwrap($log[0]));
			$rows[] = "<tr><td style=\"width: 100px;\">$time</td><td style=\"width: 100px;\">$level</td><td style=\"width: 250px;\">{$log[2]}</td><td>$message</td></tr>";
		}
		$rows = implode("\n", $rows);
		return <<<EOD
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
		return array(
			'messages' => Yii::$app->getLog()->targets['debug']->messages,
		);
	}
}
