<?php
/**
 * @var yii\debug\panels\ProfilingPanel $panel
 * @var yii\debug\models\search\Profile $searchModel
 * @var yii\data\ArrayDataProvider $dataProvider
 * @var integer $time
 * @var integer $memory
 */

use yii\grid\GridView;
use yii\helpers\Html;

?>
<h1>Performance Profiling</h1>
<p>Total processing time: <b><?= $time ?></b>; Peak memory: <b><?= $memory ?></b>.</p>
<?php

$highlighter = $this->context->module->highlighter;

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'id' => 'profile-panel-detailed-grid',
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
            'attribute' => 'category',
            'value' => function ($data) {

                $content = Html::tag('span', Html::encode($data['category']), [
                    'class' => 'hljs-variable'
                ]);

                return Html::tag('pre', $content, [
                    'class' => 'hljs php'
                ]);
            },
            'format' => 'html',
        ],
        [
            'attribute' => 'info',
            'value' => function ($data) use ($highlighter) {

                $highlighter->setAutodetectLanguages(['php', 'sql']);
                $highlighted = $highlighter->highlightAuto($data['info']);

                $text = Html::tag('pre', $highlighted->value, [
                    'class' => 'hljs ' . $highlighted->language,
                ]);

                return str_repeat('<span class="indent">→</span>', $data['level']) . $text;
            },
            'format' => 'html',
            'options' => [
                'width' => '60%',
            ],
        ],
    ],
]);
