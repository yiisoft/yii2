<?php

require_once(__DIR__ . '/../../vendor/yiisoft/yii2/yii/Yii.php');
Yii::setAlias('@tests', __DIR__ . '/../');

new yii\web\Application(require(__DIR__ . '/_config.php'));
