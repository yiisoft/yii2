<?php
/* @var $model array */

use yii\helpers\Html;
use yii\widgets\DetailView;

$timeFormatter = extension_loaded('intl') ? Yii::createObject(['class' => 'yii\i18n\Formatter']) : Yii::$app->formatter;

echo DetailView::widget([
    'model' => $model,
    'attributes' => [
        'headers',
        'from',
        'to',
        'charset',
        [
            'attribute' => 'time',
            'value' => $timeFormatter->asDateTime($model['time'], 'short'),
        ],
        'subject',
        [
            'attribute' => 'body',
            'label' => 'Text body',
        ],
        [
            'attribute' => 'isSuccessful',
            'label' => 'Successfully sent',
            'value' => $model['isSuccessful'] ? 'Yes' : 'No'
        ],
        'reply',
        'bcc',
        'cc',
        [
            'attribute' => 'file',
            'format' => 'html',
            'value' => Html::a('Download eml', ['download-mail', 'file' => $model['file']]),
        ],
    ],
]);
