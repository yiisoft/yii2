<?php
/* @var $model array */

use yii\helpers\Html;
use yii\widgets\DetailView;

echo DetailView::widget([
    'model' => $model,
    'attributes' => [
        'headers',
        'from',
        'to',
        'charset',
        [
            'attribute' => 'time',
            'format' => 'datetime',
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
