<?php

#aspect-mock should be included only once. Codeception calls this bootstrap file per each test file.
require_once __DIR__ . '/aspect_mock.php';
Yii::setAlias('@tests', __DIR__ . '/../');
