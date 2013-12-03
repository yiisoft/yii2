<?php

Yii::setAlias('tests', realpath(__DIR__ . '/../tests'));

$config = require(__DIR__ . '/web.php');

// ... customize $config for the "test" environment here...

return $config;
