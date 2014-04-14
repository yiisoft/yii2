<?php

$config = [];

if (!YII_ENV_TEST) {
    // configuration adjustments for 'dev' environment
    $config['modules']['debug'] = 'yii\debug\Module';
    $config['modules']['gii'] = 'yii\gii\Module';

    $config['bootstrap'][] = 'debug';
    $config['bootstrap'][] = 'gii';
}

return $config;
