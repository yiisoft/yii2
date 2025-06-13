<?php

/**
 * @var string $name
 * @var string $message
 * @var \Exception $exception
 */

?>
Name: <?= $name ?>

Code: <?= Yii::$app->response->statusCode ?>

Message: <?= $message ?>

Exception: <?= $exception::class ?>
