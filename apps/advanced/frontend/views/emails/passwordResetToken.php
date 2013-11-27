<?php
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var common\models\User $user;
 */

$resetLink = Yii::$app->urlManager->createAbsoluteUrl('site/reset-password', ['token' => $user->password_reset_token]);
?>

<?= \Yii::t('base','Hello') ?> <?= Html::encode($user->username) ?>,

<?= \Yii::t('base','Follow the link below to reset your password:') ?>

<?= Html::a(Html::encode($resetLink), $resetLink) ?>
