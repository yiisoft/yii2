<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\elasticsearch;

use yii\debug\Panel;
use yii\log\Logger;
use yii\helpers\Html;
use yii\web\View;

/**
 * Debugger panel that collects and displays elasticsearch queries performed.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class DebugPanel extends Panel
{
	public function getName()
	{
		return 'Elasticsearch';
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
	<a href="$url" title="Executed $queryCount elasticsearch queries which took $queryTime.">
		ES <span class="label">$queryCount</span> <span class="label">$queryTime</span>
	</a>
</div>
EOD;
		return $queryCount > 0 ? $output : '';
	}

	public function getDetail()
	{
		$rows = [];
		$i = 0;
		foreach ($this->data['messages'] as $log) {
			list ($message, $level, $category, $time, $traces) = $log;
			if ($level == Logger::LEVEL_PROFILE_BEGIN) {
				continue;
			}
			if (($pos = mb_strpos($message, "#")) !== false) {
				$url = mb_substr($message, 0, $pos);
				$body = mb_substr($message, $pos + 1);
			} else {
				$url = $message;
				$body = null;
			}
			$traceString = '';
			if (!empty($traces)) {
				$traceString .= Html::ul($traces, [
					'class' => 'trace',
					'item' => function ($trace) {
							return "<li>{$trace['file']}({$trace['line']})</li>";
						},
				]);
			}
			$runLinks = '';
			$c = 0;
			\Yii::$app->elasticsearch->open();
			foreach(\Yii::$app->elasticsearch->nodes as $node) {
				$pos = mb_strpos($url, ' ');
				$type = mb_substr($url, 0, $pos);
				if ($type == 'GET' && !empty($body)) {
					$type = 'POST';
				}
				$host = $node['http_address'];
				if (strncmp($host, 'inet[/', 6) == 0) {
					$host = substr($host, 6, -1);
				}
				$nodeUrl = 'http://' . $host . '/' . mb_substr($url, $pos + 1);
				$nodeUrl .= (strpos($nodeUrl, '?') === false) ? '?pretty=true' : '&pretty=true';
				$nodeBody = json_encode($body);
				\Yii::$app->view->registerJs(<<<JS
$('#elastic-link-$i-$c').on('click', function() {
	$('#elastic-result-$i').html('Sending $type request to $nodeUrl...');
	$('#elastic-result-$i').parent('tr').show();
	$.ajax({
		type: "$type",
		url: "$nodeUrl",
		body: $nodeBody,
		success: function( data ) {
			$('#elastic-result-$i').html(data);
		},
		dataType: "text"
	});

	return false;
});
JS
, View::POS_READY);
				$runLinks .= Html::a(isset($node['name']) ? $node['name'] : $node['http_address'], '#', ['id' => "elastic-link-$i-$c"]) . '<br/>';
				$c++;
			}
			$rows[] = "<tr><td style=\"width: 80%;\"><div><b>$url</b><br/><p>$body</p>$traceString</div></td><td style=\"width: 20%;\">$runLinks</td></tr><tr style=\"display: none;\"><td colspan=\"2\" id=\"elastic-result-$i\"></td></tr>";
			$i++;
		}
		$rows = implode("\n", $rows);
		return <<<HTML
<h1>Elasticsearch Queries</h1>

<table class="table table-condensed table-bordered table-striped table-hover" style="table-layout: fixed;">
<thead>
<tr>
	<th style="width: 80%;">Url / Query</th>
	<th style="width: 20%;">Run Query on node</th>
</tr>
</thead>
<tbody>
$rows
</tbody>
</table>
HTML;
	}

	private $_timings;

	protected function calculateTimings()
	{
		if ($this->_timings !== null) {
			return $this->_timings;
		}
		$messages = $this->data['messages'];
		$timings = [];
		$stack = [];
		foreach ($messages as $i => $log) {
			list($token, $level, $category, $timestamp) = $log;
			$log[5] = $i;
			if ($level == Logger::LEVEL_PROFILE_BEGIN) {
				$stack[] = $log;
			} elseif ($level == Logger::LEVEL_PROFILE_END) {
				if (($last = array_pop($stack)) !== null && $last[0] === $token) {
					$timings[$last[5]] = [count($stack), $token, $last[3], $timestamp - $last[3], $last[4]];
				}
			}
		}

		$now = microtime(true);
		while (($last = array_pop($stack)) !== null) {
			$delta = $now - $last[3];
			$timings[$last[5]] = [count($stack), $last[0], $last[2], $delta, $last[4]];
		}
		ksort($timings);
		return $this->_timings = $timings;
	}

	public function save()
	{
		$target = $this->module->logTarget;
		$messages = $target->filterMessages($target->messages, Logger::LEVEL_PROFILE, ['yii\elasticsearch\Connection::httpRequest']);
		return ['messages' => $messages];
	}
}
