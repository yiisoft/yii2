<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\elasticsearch;

use yii\debug\Panel;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
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
    public $db = 'elasticsearch';

    public function init()
    {
        $this->actions['elasticsearch-query'] = [
            'class' => 'yii\\elasticsearch\\DebugAction',
            'panel' => $this,
            'db' => $this->db,
        ];
    }

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
        $timings = $this->calculateTimings();
        ArrayHelper::multisort($timings, 3, SORT_DESC);
        $rows = [];
        $i = 0;
        foreach ($timings as $logId => $timing) {
            $duration = sprintf('%.1f ms', $timing[3] * 1000);
            $message = $timing[1];
            $traces = $timing[4];
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
            $ajaxUrl = Url::to(['elasticsearch-query', 'logId' => $logId, 'tag' => $this->tag]);
            \Yii::$app->view->registerJs(<<<JS
$('#elastic-link-$i').on('click', function () {
    var result = $('#elastic-result-$i');
    result.html('Sending request...');
    result.parent('tr').show();
    $.ajax({
        type: "POST",
        url: "$ajaxUrl",
        success: function (data) {
            $('#elastic-time-$i').html(data.time);
            $('#elastic-result-$i').html(data.result);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            $('#elastic-time-$i').html('');
            $('#elastic-result-$i').html('<span style="color: #c00;">Error: ' + errorThrown + ' - ' + textStatus + '</span><br />' + jqXHR.responseText);
        },
        dataType: "json"
    });

    return false;
});
JS
, View::POS_READY);
            $runLink = Html::a('run query', '#', ['id' => "elastic-link-$i"]) . '<br/>';
            $rows[] = <<<HTML
<tr>
    <td style="width: 10%;">$duration</td>
    <td style="width: 75%;"><div><b>$url</b><br/><p>$body</p>$traceString</div></td>
    <td style="width: 15%;">$runLink</td>
</tr>
<tr style="display: none;"><td id="elastic-time-$i"></td><td colspan="3" id="elastic-result-$i"></td></tr>
HTML;
            $i++;
        }
        $rows = implode("\n", $rows);

        return <<<HTML
<h1>Elasticsearch Queries</h1>

<table class="table table-condensed table-bordered table-striped table-hover" style="table-layout: fixed;">
<thead>
<tr>
    <th style="width: 10%;">Time</th>
    <th style="width: 75%;">Url / Query</th>
    <th style="width: 15%;">Run Query on node</th>
</tr>
</thead>
<tbody>
$rows
</tbody>
</table>
HTML;
    }

    private $_timings;

    public function calculateTimings()
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
