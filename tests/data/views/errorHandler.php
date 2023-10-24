<?php

/* @var $exception Exception */

?>
Code: <?= Yii::$app->response->statusCode ?>

Message: <?= $exception->getMessage() ?>

Exception: <?= $exception::class ?>
