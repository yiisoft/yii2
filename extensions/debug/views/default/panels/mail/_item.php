<?php

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
                'name' => 'time',
                'value' => $timeFormatter->asDateTime($model['time'], 'short'),
            ],
            'subject',
            [
                'name' => 'body',
                'label' => 'Text body',
            ],
            [
                'name' => 'isSuccessful',
                'label' => 'Successfully sent',
                'value' => $model['isSuccessful'] ? 'Yes' : 'No'
            ],
            'reply',
            'bcc',
            'cc',
            [
                'name' => 'file',
                'format' => 'html',
                'value' => Html::a('Download eml', ['download-mail', 'file' => $model['file']]),
            ],
        ],
]);
