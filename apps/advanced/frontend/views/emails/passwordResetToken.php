<?php
use yii\helpers\Html;

/**
 * @var yii\base\View $this
 * @var common\models\User $user;
 */

$resetLink = Yii::$app->urlManager->createAbsoluteUrl('site/reset-password', ['token' => $user->password_reset_token]);
?>

Hello <?php echo Html::encode($user->username)?>,

Follow the link below to reset your password:

<?php echo Html::a(Html::encode($resetLink), $resetLink)?>
