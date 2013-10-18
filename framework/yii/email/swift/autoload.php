<?php
/**
 * This file attempts to register autoloader for the Swift library, assuming
 * it is located under the 'vendor' path.
 *
 * @var $this \yii\email\swift\Mailer
 */
$swiftMailerLibPath = Yii::getAlias('@vendor/swiftmailer/swiftmailer/lib');
require_once $swiftMailerLibPath . '/swift_required.php';