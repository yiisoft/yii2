<?php

/** @var \Exception $exception */

?>
Code: <?= Yii::$app->response->statusCode ?>

Message: <?= $exception->getMessage() ?>

Exception: <?= $exception::class ?>
