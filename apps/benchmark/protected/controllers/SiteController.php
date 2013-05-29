<?php

use yii\web\Controller;

class SiteController extends Controller
{
	public $defaultAction = 'hello';

	public function actionHello()
	{
		echo 'hello world';
	}
}
