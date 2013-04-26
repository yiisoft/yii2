<?php
/** @var $this \yii\base\View */

use yii\helpers\Html;

$this->title = 'Hello World';

$user = Yii::$app->getUser();
if ($user->isGuest) {
	echo Html::a('login', array('login'));
} else {
	echo "You are logged in as " . $user->identity->username . "<br/>";
	echo Html::a('logout', array('logout'));
}
?>


