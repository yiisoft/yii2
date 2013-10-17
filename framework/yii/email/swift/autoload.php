<?php
/**
 * @var $this \yii\email\swift\Mailer
 */
$swiftMailerLibPath = Yii::getAlias('@vendor/swiftmailer/lib');
require_once $swiftMailerLibPath . '/classes/Swift.php';
spl_autoload_register(array('Swift', 'autoload'));
require_once $swiftMailerLibPath . '/swift_init.php';