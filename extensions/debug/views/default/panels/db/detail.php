<?php
/**
 * @var yii\debug\panels\DbPanel $panel
 * @var yii\debug\models\search\Db $searchModel
 * @var yii\data\ArrayDataProvider $dataProvider
 */

use yii\helpers\Html;
use yii\grid\GridView;

?>
<h1>Database Queries</h1>

<?php

$highlighter = $this->context->module->highlighter;

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'id' => 'db-panel-detailed-grid',
    'options' => ['class' => 'detail-grid-view'],
    'filterModel' => $searchModel,
    'filterUrl' => $panel->getUrl(),
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        [
            'attribute' => 'seq',
            'label' => 'Time',
            'value' => function ($data) {
                $timeInSeconds = $data['timestamp'] / 1000;
                $millisecondsDiff = (int) (($timeInSeconds - (int) $timeInSeconds) * 1000);

                return date('H:i:s.', $timeInSeconds) . sprintf('%03d', $millisecondsDiff);
            },
            'headerOptions' => [
                'class' => 'sort-numerical'
            ]
        ],
        [
            'attribute' => 'duration',
            'value' => function ($data) {
                return sprintf('%.1f ms', $data['duration']);
            },
            'options' => [
                'width' => '10%',
            ],
            'headerOptions' => [
                'class' => 'sort-numerical'
            ]
        ],
        [
            'attribute' => 'type',
            'value' => function ($data) {

                $content = Html::tag('span', Html::encode(mb_strtoupper($data['type'], 'utf8')), [
                    'class' => 'hljs-variable'
                ]);

                return Html::tag('pre', $content, [
                    'class' => 'hljs php'
                ]);
            },
            'format' => 'html',
        ],
        [
            'attribute' => 'query',
            'value' => function ($data) use ($highlighter) {
                $highlighted = $highlighter->highlight('sql', $data['query']);
                $text = Html::tag('pre', $highlighted->value, [
                    'class' => 'hljs sql'
                ]);

                if (!empty($data['trace'])) {
                    $text .= Html::ul($data['trace'], [
                        'class' => 'trace',
                        'item' => function ($trace) {
                            return "<li>" . Html::encode($trace['file']) . " " . Html::encode($trace['line']) . "</li>";
                        },
                    ]);
                }
                return $text;
            },
            'format' => 'html',
            'options' => [
                'width' => '60%',
            ],
        ]
    ],
]);
